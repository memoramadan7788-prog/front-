<?php

namespace App\Frontend\Candidate;

use App\Support\Frontend\CandidateDashboard\CandidateDashboardKpiGateway;
use Carbon\Carbon;
use Throwable;

/**
 * Aggregates Candidate Overview KPIs + analytics from authenticated Backend reads.
 * Presentation stays in Blade / JS; counting rules live here.
 */
class CandidateDashboardAdapter
{
    /**
     * Backend application.status values that mean the pipeline is still open.
     *
     * @var list<string>
     */
    public const APPLICATION_IN_PROGRESS = [
        'submitted',
        'screening',
        'shortlisted',
        'interviewing',
        'offered',
    ];

    /**
     * @var list<string>
     */
    public const APPLICATION_TERMINAL = [
        'hired',
        'rejected',
        'withdrawn',
    ];

    /**
     * @var list<string>
     */
    public const INTERVIEW_UPCOMING_STATUSES = [
        'scheduled',
        'rescheduled',
    ];

    /**
     * @var list<string>
     */
    public const PROFILE_COMPLETION_FIELDS = [
        'phone',
        'location',
        'headline',
        'bio',
        'experience',
        'education',
        'resume',
    ];

    private const MAX_PAGES = 20;

    public function __construct(
        private readonly CandidateDashboardKpiGateway $gateway,
        private readonly CandidateDashboardAnalytics $analytics = new CandidateDashboardAnalytics,
    ) {}

    /**
     * @return array{
     *     unauthorized: bool,
     *     loadError: ?string,
     *     kpis: array<string, array<string, mixed>>,
     *     analytics: array<string, mixed>
     * }
     */
    public function load(): array
    {
        $errors = [];
        $unauthorized = false;
        $now = Carbon::now();

        $appCatalog = $this->fetchApplicationCatalog($unauthorized, $errors);
        $interviewCatalog = $this->fetchInterviewCatalog($unauthorized, $errors);
        $offers = $this->aggregateOffers($unauthorized, $errors);
        $profile = $this->aggregateProfileCompletion($unauthorized, $errors);

        $assessments = $this->metric(
            available: false,
            value: null,
            href: 'frontend.candidate.exams.index',
            metaKey: 'kpi.assessments_unavailable',
        );

        $appKpis = $this->applicationKpisFromCatalog($appCatalog);
        $interviewKpi = $this->interviewKpiFromCatalog($interviewCatalog, $now);

        $analytics = [
            'activity' => $this->analytics->activity(
                $appCatalog['rows'],
                $appCatalog['complete'],
                $now,
            ),
            'pipeline' => $this->analytics->pipeline(
                $appCatalog['rows'],
                $appCatalog['complete'],
                $appKpis['total']['available'] ? (int) $appKpis['total']['value'] : null,
            ),
            'profile_ring' => $this->analytics->profileRing($profile),
            'interview_timeline' => $this->analytics->interviewTimeline(
                $interviewCatalog['rows'],
                $interviewCatalog['complete'],
                $now,
            ),
        ];

        if (! ($analytics['activity']['available'] ?? false) && ($analytics['activity']['reason'] ?? null) === 'missing_dates') {
            // Not an HTTP failure — surface as visualization unavailable only.
        }

        return [
            'unauthorized' => $unauthorized,
            'loadError' => $errors !== [] ? implode(' ', array_unique($errors)) : null,
            'kpis' => [
                'total_applications' => $appKpis['total'],
                'applications_in_progress' => $appKpis['in_progress'],
                'upcoming_interviews' => $interviewKpi,
                'offers_received' => $offers,
                'pending_assessments' => $assessments,
                'profile_completion' => $profile,
            ],
            'analytics' => $analytics,
        ];
    }

    /**
     * @param  list<string>  $errors
     * @return array{complete: bool, rows: list<array<string, mixed>>, meta_total: ?int}
     */
    private function fetchApplicationCatalog(bool &$unauthorized, array &$errors): array
    {
        $page = 1;
        $seen = 0;
        $metaTotal = null;
        $lastPage = null;
        $hasMore = false;
        $rows = [];

        do {
            $response = $this->gateway->applications($page, 100);
            $status = (int) ($response['statusCode'] ?? 500);

            if ($status === 401) {
                $unauthorized = true;

                return ['complete' => false, 'rows' => [], 'meta_total' => null];
            }

            if ($status !== 200) {
                $errors[] = $this->httpErrorMessage($response, $status, 'applications');

                return ['complete' => false, 'rows' => [], 'meta_total' => null];
            }

            if ($metaTotal === null) {
                $metaTotal = $this->metaTotal($response['meta'] ?? null);
                $lastPage = $this->metaLastPage($response['meta'] ?? null);
            }

            $pageRows = $this->rows($response['data'] ?? null);
            $seen += count($pageRows);
            $rows = array_merge($rows, $pageRows);
            $pageSize = $this->metaPerPage($response['meta'] ?? null) ?? max(count($pageRows), 1);

            $page++;
            $hasMore = $this->hasMorePages($page - 1, $lastPage, $metaTotal, $seen, count($pageRows), $pageSize);
        } while ($hasMore && ($page - 1) < self::MAX_PAGES);

        if ($hasMore) {
            $errors[] = __('candidate.kpi.applications_incomplete');

            return ['complete' => false, 'rows' => $rows, 'meta_total' => $metaTotal];
        }

        return ['complete' => true, 'rows' => $rows, 'meta_total' => $metaTotal];
    }

    /**
     * @param  array{complete: bool, rows: list<array<string, mixed>>, meta_total: ?int}  $catalog
     * @return array{total: array<string, mixed>, in_progress: array<string, mixed>}
     */
    private function applicationKpisFromCatalog(array $catalog): array
    {
        $href = 'frontend.candidate.applications.index';

        if (! $catalog['complete']) {
            $totalMetric = $catalog['meta_total'] !== null
                ? $this->metric(true, (int) $catalog['meta_total'], $href)
                : $this->metric(false, null, $href, 'kpi.unavailable');

            return [
                'total' => $totalMetric,
                'in_progress' => $this->metric(false, null, $href, 'kpi.unavailable'),
            ];
        }

        $inProgress = 0;
        foreach ($catalog['rows'] as $row) {
            $statusKey = strtolower(trim((string) data_get($row, 'status', '')));
            if ($statusKey !== '' && in_array($statusKey, self::APPLICATION_IN_PROGRESS, true)) {
                $inProgress++;
            }
        }

        $total = $catalog['meta_total'] !== null
            ? (int) $catalog['meta_total']
            : count($catalog['rows']);

        return [
            'total' => $this->metric(true, $total, $href),
            'in_progress' => $this->metric(true, $inProgress, $href),
        ];
    }

    /**
     * @param  list<string>  $errors
     * @return array{complete: bool, rows: list<array<string, mixed>>}
     */
    private function fetchInterviewCatalog(bool &$unauthorized, array &$errors): array
    {
        $page = 1;
        $seen = 0;
        $metaTotal = null;
        $lastPage = null;
        $hasMore = false;
        $rows = [];

        do {
            $response = $this->gateway->interviews([
                'filter' => 'upcoming',
                'page' => $page,
                'per_page' => 100,
            ]);
            $status = (int) ($response['statusCode'] ?? 500);

            if ($status === 401) {
                $unauthorized = true;

                return ['complete' => false, 'rows' => []];
            }

            if ($status !== 200) {
                $errors[] = $this->httpErrorMessage($response, $status, 'interviews');

                return ['complete' => false, 'rows' => []];
            }

            if ($metaTotal === null) {
                $metaTotal = $this->metaTotal($response['meta'] ?? null);
                $lastPage = $this->metaLastPage($response['meta'] ?? null);
            }

            $pageRows = $this->rows($response['data'] ?? null);
            $seen += count($pageRows);
            $rows = array_merge($rows, $pageRows);
            $pageSize = $this->metaPerPage($response['meta'] ?? null) ?? max(count($pageRows), 1);

            $page++;
            $hasMore = $this->hasMorePages($page - 1, $lastPage, $metaTotal, $seen, count($pageRows), $pageSize);
        } while ($hasMore && ($page - 1) < self::MAX_PAGES);

        if ($hasMore) {
            $errors[] = __('candidate.kpi.interviews_incomplete');

            return ['complete' => false, 'rows' => $rows];
        }

        return ['complete' => true, 'rows' => $rows];
    }

    /**
     * @param  array{complete: bool, rows: list<array<string, mixed>>}  $catalog
     * @return array<string, mixed>
     */
    private function interviewKpiFromCatalog(array $catalog, Carbon $now): array
    {
        $href = 'frontend.candidate.interviews.index';

        if (! $catalog['complete']) {
            return $this->metric(false, null, $href, 'kpi.unavailable');
        }

        $count = 0;
        foreach ($catalog['rows'] as $row) {
            if ($this->isUpcomingInterview($row, $now)) {
                $count++;
            }
        }

        return $this->metric(true, $count, $href);
    }

    /**
     * @param  list<string>  $errors
     * @return array<string, mixed>
     */
    private function aggregateOffers(bool &$unauthorized, array &$errors): array
    {
        $response = $this->gateway->offers();
        $status = (int) ($response['statusCode'] ?? 500);

        if ($status === 401) {
            $unauthorized = true;

            return $this->metric(false, null, 'frontend.candidate.offers.index', 'kpi.unavailable');
        }

        if ($status !== 200) {
            $errors[] = $this->httpErrorMessage($response, $status, 'offers');

            return $this->metric(false, null, 'frontend.candidate.offers.index', 'kpi.unavailable');
        }

        return $this->metric(true, count($this->rows($response['data'] ?? null)), 'frontend.candidate.offers.index');
    }

    /**
     * @param  list<string>  $errors
     * @return array<string, mixed>
     */
    private function aggregateProfileCompletion(bool &$unauthorized, array &$errors): array
    {
        $response = $this->gateway->profile();
        $status = (int) ($response['statusCode'] ?? 500);

        if ($status === 401) {
            $unauthorized = true;

            return $this->metric(false, null, 'frontend.candidate.profile', 'kpi.unavailable');
        }

        if ($status !== 200) {
            $errors[] = $this->httpErrorMessage($response, $status, 'profile');

            return $this->metric(false, null, 'frontend.candidate.profile', 'kpi.unavailable');
        }

        $profile = $response['data'] ?? null;
        if (! is_array($profile)) {
            $errors[] = __('candidate.kpi.profile_unavailable');

            return $this->metric(false, null, 'frontend.candidate.profile', 'kpi.unavailable');
        }

        $checks = $this->profileChecks($profile);
        $filled = count(array_filter($checks));
        $total = count($checks);

        if ($total === 0) {
            return $this->metric(false, null, 'frontend.candidate.profile', 'kpi.unavailable');
        }

        $percent = (int) round(($filled / $total) * 100);

        return $this->metric(
            available: true,
            value: $percent,
            href: 'frontend.candidate.profile',
            display: $percent.'%',
            extra: [
                'filled' => $filled,
                'total_fields' => $total,
                'checks' => $checks,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $profile
     * @return array<string, bool>
     */
    public function profileChecks(array $profile): array
    {
        $experiences = $this->rows(data_get($profile, 'experiences'));
        $educations = $this->rows(data_get($profile, 'educations'));
        $cvs = $this->rows(data_get($profile, 'cvs'));

        $hasNoExperience = (bool) data_get($profile, 'has_no_experience', false);
        $years = data_get($profile, 'years_experience');

        return [
            'phone' => filled(data_get($profile, 'phone')),
            'location' => filled(data_get($profile, 'location')),
            'headline' => filled(data_get($profile, 'headline')),
            'bio' => filled(data_get($profile, 'bio')),
            'experience' => $hasNoExperience || $years !== null || $experiences !== [],
            'education' => $educations !== [],
            'resume' => $cvs !== [],
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function isUpcomingInterview(array $row, Carbon $now): bool
    {
        $status = strtolower(trim((string) data_get($row, 'status', '')));
        if (! in_array($status, self::INTERVIEW_UPCOMING_STATUSES, true)) {
            return false;
        }

        $startAt = data_get($row, 'start_at');
        if (! is_string($startAt) || trim($startAt) === '') {
            return false;
        }

        try {
            return Carbon::parse($startAt)->greaterThan($now);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function metric(
        bool $available,
        int|float|null $value,
        string $href,
        ?string $metaKey = null,
        ?string $display = null,
        array $extra = [],
    ): array {
        return array_merge([
            'available' => $available,
            'value' => $available ? $value : null,
            'display' => $available
                ? ($display ?? (string) (int) $value)
                : '—',
            'href' => $href,
            'meta_key' => $metaKey,
        ], $extra);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function rows(mixed $data): array
    {
        if (! is_array($data)) {
            return [];
        }

        if (isset($data['data']) && is_array($data['data'])) {
            $data = $data['data'];
        }

        if (! array_is_list($data)) {
            return [];
        }

        return array_values(array_filter($data, static fn ($row) => is_array($row)));
    }

    private function metaTotal(?array $meta): ?int
    {
        if ($meta === null) {
            return null;
        }

        $total = $meta['total'] ?? null;

        return is_numeric($total) ? (int) $total : null;
    }

    private function metaLastPage(?array $meta): ?int
    {
        if ($meta === null) {
            return null;
        }

        $last = $meta['last_page'] ?? null;

        return is_numeric($last) ? (int) $last : null;
    }

    private function metaPerPage(?array $meta): ?int
    {
        if ($meta === null) {
            return null;
        }

        $perPage = $meta['per_page'] ?? null;

        return is_numeric($perPage) && (int) $perPage > 0 ? (int) $perPage : null;
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
    private function httpErrorMessage(array $response, int $status, string $resource): string
    {
        $errors = $response['errors'] ?? null;
        if (is_array($errors) && $errors !== []) {
            $first = $errors['error'] ?? reset($errors);
            if (is_array($first)) {
                $first = reset($first);
            }
            if (is_string($first) && trim($first) !== '') {
                return trim($first);
            }
        }

        $message = $response['message'] ?? null;
        if (is_string($message) && trim($message) !== '') {
            return trim($message);
        }

        return match (true) {
            $status === 403 => __('candidate.kpi.forbidden', ['resource' => $resource]),
            $status >= 500 => __('candidate.kpi.server_error', ['resource' => $resource]),
            default => __('candidate.kpi.load_error', ['resource' => $resource]),
        };
    }
}
