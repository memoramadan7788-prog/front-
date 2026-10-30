<?php

namespace App\Frontend\Candidate;

use App\Support\Frontend\CandidateDashboard\CandidateDashboardKpiGateway;
use App\Support\Frontend\SafeApiMessage;
use Illuminate\Http\Request;

/**
 * Candidate My Applications catalog: walks Backend pagination (fixed page size),
 * then applies honest client-side tabs/filters against the loaded catalog.
 *
 * Backend GET /candidate/applications does not accept status/per_page filters.
 * Activity / stage history is detail-only — list pipeline is a current-stage snapshot.
 */
class CandidateApplicationsAdapter
{
    public const TAB_ALL = 'all';

    public const TAB_IN_PROGRESS = 'in_progress';

    public const TAB_INTERVIEWING = 'interviewing';

    public const TAB_OFFERED = 'offered';

    public const TAB_COMPLETED = 'completed';

    /** @var list<string> */
    public const TABS = [
        self::TAB_ALL,
        self::TAB_IN_PROGRESS,
        self::TAB_INTERVIEWING,
        self::TAB_OFFERED,
        self::TAB_COMPLETED,
    ];

    /**
     * Tab → Backend status membership (documented Application.status values only).
     *
     * @var array<string, list<string>|null>
     */
    public const TAB_STATUSES = [
        self::TAB_ALL => null,
        self::TAB_IN_PROGRESS => ['submitted', 'screening', 'shortlisted'],
        self::TAB_INTERVIEWING => ['interviewing'],
        self::TAB_OFFERED => ['offered'],
        self::TAB_COMPLETED => ['hired', 'rejected', 'withdrawn'],
    ];

    /** @var list<string> */
    public const DOCUMENTED_STATUSES = [
        'submitted',
        'screening',
        'shortlisted',
        'interviewing',
        'offered',
        'hired',
        'rejected',
        'withdrawn',
    ];

    /**
     * Ordered recruitment stages for the compact current-stage snapshot.
     * Terminal branches (rejected / withdrawn) are not progression milestones.
     *
     * @var list<string>
     */
    public const PIPELINE_STAGES = [
        'submitted',
        'screening',
        'shortlisted',
        'interviewing',
        'offered',
        'hired',
    ];

    /** @var list<string> */
    public const TERMINAL_STATUSES = ['hired', 'rejected', 'withdrawn'];

    /** @var list<string> */
    public const WITHDRAWABLE_BLOCKLIST = ['hired', 'rejected', 'withdrawn'];

    private const MAX_PAGES = 20;

    private const PER_PAGE = 10;

    public function __construct(
        private readonly CandidateDashboardKpiGateway $gateway,
    ) {}

    /**
     * @return array{
     *     unauthorized: bool,
     *     loadError: ?string,
     *     loadStatus: ?int,
     *     catalog_warning: ?string,
     *     catalog_complete: bool,
     *     catalog_total: ?int,
     *     catalog_loaded: int,
     *     tabs: list<array{key: string, count: int|null, href: string, active: bool}>,
     *     filters: array{tab: string, q: string, status: string, sort: string},
     *     status_options: list<string>,
     *     applications: list<array<string, mixed>>,
     *     meta: ?array<string, mixed>,
     *     empty_catalog: bool,
     *     empty_filtered: bool,
     *     counts_reliable: bool,
     * }
     */
    public function load(Request $request): array
    {
        $filters = $this->normalizeFilters($request);
        $unauthorized = false;
        $errors = [];
        $loadStatus = null;

        $catalog = $this->fetchCatalog($unauthorized, $errors, $loadStatus);

        if ($unauthorized) {
            return $this->emptyPayload($filters, unauthorized: true);
        }

        if ($errors !== []) {
            return array_merge($this->emptyPayload($filters), [
                'loadError' => $errors[0],
                'loadStatus' => $loadStatus,
            ]);
        }

        $rows = array_map(fn (array $row) => $this->normalizeRow($row), $catalog['rows']);
        $countsReliable = $catalog['complete'];
        $tabCounts = $this->tabCounts($rows, $countsReliable);

        $filtered = $this->applyFilters($rows, $filters);
        $filtered = $this->sortRows($filtered, $filters['sort']);

        $page = max(1, (int) $request->query('page', 1));
        $total = count($filtered);
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        if ($page > $lastPage) {
            $page = $lastPage;
        }
        $offset = ($page - 1) * self::PER_PAGE;
        $pageRows = array_slice($filtered, $offset, self::PER_PAGE);

        $from = $total === 0 ? null : $offset + 1;
        $to = $total === 0 ? null : min($offset + self::PER_PAGE, $total);

        return [
            'unauthorized' => false,
            'loadError' => null,
            'loadStatus' => 200,
            'catalog_warning' => $catalog['complete']
                ? null
                : __('candidate.applications.catalog_incomplete'),
            'catalog_complete' => $catalog['complete'],
            'catalog_total' => $catalog['meta_total'],
            'catalog_loaded' => count($rows),
            'tabs' => $this->buildTabs($filters, $tabCounts, $request),
            'filters' => $filters,
            'status_options' => self::DOCUMENTED_STATUSES,
            'applications' => $pageRows,
            'meta' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => self::PER_PAGE,
                'total' => $total,
                'from' => $from,
                'to' => $to,
            ],
            'empty_catalog' => $rows === [],
            'empty_filtered' => $rows !== [] && $filtered === [],
            'counts_reliable' => $countsReliable,
        ];
    }

    /**
     * @param  array{tab: string, q: string, status: string, sort: string}  $filters
     * @return array<string, mixed>
     */
    private function emptyPayload(array $filters, bool $unauthorized = false): array
    {
        return [
            'unauthorized' => $unauthorized,
            'loadError' => null,
            'loadStatus' => $unauthorized ? 401 : null,
            'catalog_warning' => null,
            'catalog_complete' => false,
            'catalog_total' => null,
            'catalog_loaded' => 0,
            'tabs' => [],
            'filters' => $filters,
            'status_options' => self::DOCUMENTED_STATUSES,
            'applications' => [],
            'meta' => null,
            'empty_catalog' => true,
            'empty_filtered' => false,
            'counts_reliable' => false,
        ];
    }

    /**
     * @return array{tab: string, q: string, status: string, sort: string}
     */
    private function normalizeFilters(Request $request): array
    {
        $tab = strtolower(trim((string) $request->query('tab', self::TAB_ALL)));
        if (! in_array($tab, self::TABS, true)) {
            $tab = self::TAB_ALL;
        }

        $status = strtolower(trim((string) $request->query('status', '')));
        if ($status !== '' && ! in_array($status, self::DOCUMENTED_STATUSES, true)) {
            $status = '';
        }

        $sort = strtolower(trim((string) $request->query('sort', 'applied_at_desc')));
        if (! in_array($sort, ['applied_at_desc', 'applied_at_asc', 'title_asc'], true)) {
            $sort = 'applied_at_desc';
        }

        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) > 120) {
            $q = mb_substr($q, 0, 120);
        }

        return [
            'tab' => $tab,
            'q' => $q,
            'status' => $status,
            'sort' => $sort,
        ];
    }

    /**
     * @param  list<string>  $errors
     * @return array{complete: bool, rows: list<array<string, mixed>>, meta_total: ?int}
     */
    private function fetchCatalog(bool &$unauthorized, array &$errors, ?int &$loadStatus): array
    {
        $page = 1;
        $seen = 0;
        $metaTotal = null;
        $lastPage = null;
        $hasMore = false;
        $rows = [];

        do {
            $response = $this->gateway->applications($page, 15);
            $status = (int) ($response['statusCode'] ?? 500);
            $loadStatus = $status;

            if ($status === 401) {
                $unauthorized = true;

                return ['complete' => false, 'rows' => [], 'meta_total' => null];
            }

            if ($status !== 200) {
                $errors[] = $this->httpErrorMessage($response, $status);

                return ['complete' => false, 'rows' => [], 'meta_total' => null];
            }

            if ($metaTotal === null) {
                $metaTotal = $this->metaInt($response['meta'] ?? null, 'total');
                $lastPage = $this->metaInt($response['meta'] ?? null, 'last_page');
            }

            $pageRows = $this->rows($response['data'] ?? null);
            $seen += count($pageRows);
            $rows = array_merge($rows, $pageRows);
            $pageSize = $this->metaInt($response['meta'] ?? null, 'per_page') ?? max(count($pageRows), 1);

            $page++;
            $hasMore = $this->hasMorePages($page - 1, $lastPage, $metaTotal, $seen, count($pageRows), $pageSize);
        } while ($hasMore && ($page - 1) < self::MAX_PAGES);

        if ($hasMore) {
            return ['complete' => false, 'rows' => $rows, 'meta_total' => $metaTotal];
        }

        return ['complete' => true, 'rows' => $rows, 'meta_total' => $metaTotal];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array<string, int|null>
     */
    private function tabCounts(array $rows, bool $reliable): array
    {
        $counts = [
            self::TAB_ALL => count($rows),
            self::TAB_IN_PROGRESS => 0,
            self::TAB_INTERVIEWING => 0,
            self::TAB_OFFERED => 0,
            self::TAB_COMPLETED => 0,
        ];

        foreach ($rows as $row) {
            $status = (string) ($row['status'] ?? '');
            foreach (self::TAB_STATUSES as $tab => $statuses) {
                if ($statuses === null) {
                    continue;
                }
                if (in_array($status, $statuses, true)) {
                    $counts[$tab]++;
                }
            }
        }

        if (! $reliable) {
            // Avoid presenting incomplete catalog sizes as global totals.
            return [
                self::TAB_ALL => null,
                self::TAB_IN_PROGRESS => null,
                self::TAB_INTERVIEWING => null,
                self::TAB_OFFERED => null,
                self::TAB_COMPLETED => null,
            ];
        }

        return $counts;
    }

    /**
     * @param  array{tab: string, q: string, status: string, sort: string}  $filters
     * @param  array<string, int|null>  $counts
     * @return list<array{key: string, count: int|null, href: string, active: bool}>
     */
    private function buildTabs(array $filters, array $counts, Request $request): array
    {
        $tabs = [];
        foreach (self::TABS as $key) {
            $query = array_filter([
                'tab' => $key === self::TAB_ALL ? null : $key,
                'q' => $filters['q'] !== '' ? $filters['q'] : null,
                'status' => $filters['status'] !== '' ? $filters['status'] : null,
                'sort' => $filters['sort'] !== 'applied_at_desc' ? $filters['sort'] : null,
            ], static fn ($v) => $v !== null && $v !== '');

            $tabs[] = [
                'key' => $key,
                'count' => $counts[$key] ?? null,
                'href' => $request->url().($query !== [] ? '?'.http_build_query($query) : ''),
                'active' => $filters['tab'] === $key,
            ];
        }

        return $tabs;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  array{tab: string, q: string, status: string, sort: string}  $filters
     * @return list<array<string, mixed>>
     */
    private function applyFilters(array $rows, array $filters): array
    {
        $tabStatuses = self::TAB_STATUSES[$filters['tab']] ?? null;
        $q = mb_strtolower($filters['q']);
        $statusFilter = $filters['status'];

        return array_values(array_filter($rows, function (array $row) use ($tabStatuses, $q, $statusFilter) {
            $status = (string) ($row['status'] ?? '');

            if ($tabStatuses !== null && ! in_array($status, $tabStatuses, true)) {
                return false;
            }

            if ($statusFilter !== '' && $status !== $statusFilter) {
                return false;
            }

            if ($q !== '') {
                $haystack = mb_strtolower(
                    trim(($row['job_title'] ?? '').' '.($row['company_name'] ?? ''))
                );
                if (! str_contains($haystack, $q)) {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function sortRows(array $rows, string $sort): array
    {
        usort($rows, static function (array $a, array $b) use ($sort) {
            return match ($sort) {
                'applied_at_asc' => strcmp((string) ($a['applied_at'] ?? ''), (string) ($b['applied_at'] ?? '')),
                'title_asc' => strcasecmp((string) ($a['job_title'] ?? ''), (string) ($b['job_title'] ?? '')),
                default => strcmp((string) ($b['applied_at'] ?? ''), (string) ($a['applied_at'] ?? '')),
            };
        });

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function normalizeRow(array $row): array
    {
        $status = strtolower(trim((string) ($row['status'] ?? '')));
        $documented = in_array($status, self::DOCUMENTED_STATUSES, true);
        $jobTitle = (string) (
            $row['job_title']
            ?? data_get($row, 'job.title')
            ?? data_get($row, 'title')
            ?? ''
        );
        $company = (string) (
            $row['company_name']
            ?? data_get($row, 'job.company.name')
            ?? data_get($row, 'company.name')
            ?? ''
        );

        return [
            'id' => $row['id'] ?? null,
            'status' => $status,
            'status_documented' => $documented,
            'job_title' => $jobTitle !== '' ? $jobTitle : '—',
            'company_name' => $company !== '' ? $company : '—',
            'applied_at' => $row['applied_at'] ?? null,
            'stage_label' => $row['stage_label'] ?? null,
            'can_withdraw' => $documented && ! in_array($status, self::WITHDRAWABLE_BLOCKLIST, true),
            'pipeline' => $this->pipelineSnapshot($status, $documented),
        ];
    }

    /**
     * Current-stage snapshot only — does not claim historical progression.
     *
     * @return array{
     *     mode: string,
     *     current: string,
     *     stages: list<array{key: string, state: string}>
     * }
     */
    private function pipelineSnapshot(string $status, bool $documented): array
    {
        if (! $documented) {
            return [
                'mode' => 'unknown',
                'current' => $status !== '' ? $status : 'unknown',
                'stages' => [],
            ];
        }

        if (in_array($status, ['rejected', 'withdrawn'], true)) {
            return [
                'mode' => 'terminal',
                'current' => $status,
                'stages' => [
                    ['key' => $status, 'state' => 'current'],
                ],
            ];
        }

        $stages = [];
        foreach (self::PIPELINE_STAGES as $stage) {
            $stages[] = [
                'key' => $stage,
                'state' => $stage === $status ? 'current' : 'idle',
            ];
        }

        return [
            'mode' => 'snapshot',
            'current' => $status,
            'stages' => $stages,
        ];
    }

    /** @return list<array<string, mixed>> */
    private function rows(mixed $data): array
    {
        if (! is_array($data)) {
            return [];
        }

        if (array_is_list($data)) {
            return array_values(array_filter($data, 'is_array'));
        }

        if (isset($data['data']) && is_array($data['data'])) {
            return $this->rows($data['data']);
        }

        return [];
    }

    private function metaInt(?array $meta, string $key): ?int
    {
        if ($meta === null) {
            return null;
        }

        $value = $meta[$key] ?? null;

        return is_numeric($value) ? (int) $value : null;
    }

    private function hasMorePages(
        int $currentPage,
        ?int $lastPage,
        ?int $metaTotal,
        int $seen,
        int $pageCount,
        int $perPage,
    ): bool {
        if ($lastPage !== null) {
            return $currentPage < $lastPage;
        }

        if ($metaTotal !== null) {
            return $seen < $metaTotal;
        }

        return $pageCount >= $perPage;
    }

    /**
     * @param  array{message?: mixed, errors?: mixed}  $response
     */
    private function httpErrorMessage(array $response, int $status): string
    {
        $fallback = match (true) {
            $status === 403 => __('candidate.pipeline.forbidden'),
            $status === 422 => __('candidate.pipeline.validation_error'),
            $status >= 500 => __('candidate.pipeline.server_error'),
            default => __('candidate.pipeline.load_error'),
        };

        $message = $response['message'] ?? null;
        if (! is_string($message)) {
            $errors = $response['errors'] ?? null;
            if (is_array($errors) && $errors !== []) {
                $first = $errors['error'] ?? reset($errors);
                if (is_array($first)) {
                    $first = reset($first);
                }
                $message = is_string($first) ? $first : null;
            }
        }

        return SafeApiMessage::forUser(
            is_string($message) ? $message : null,
            $fallback,
            ['resource' => 'candidate.applications']
        );
    }
}
