<?php

namespace App\Frontend\Admin;

use App\Models\AdminAPI;
use App\Support\Frontend\AdminGrowthDelta;

/**
 * Super Admin Overview data loader.
 *
 * Primary sources (platform Admin token, Super Admin + reports.view):
 *   GET /api/admin/dashboard
 *   GET /api/admin/analytics?months=
 *
 * Distinguishes real zeros (available=true, value=0) from failed/unavailable metrics.
 * Avoids N+1 list-API fan-out when dashboard/analytics succeed.
 */
class AdminDashboardAdapter
{
    /** Matches Backend AccountStatus::DEACTIVATED / BLOCKED. */
    private const STATUS_DEACTIVATED = 2;

    private const STATUS_BLOCKED = 3;

    public function __construct(
        private readonly AdminAPI $api
    ) {}

    /**
     * @return array{
     *     unauthorized: bool,
     *     forbidden: bool,
     *     stats: array<string, mixed>,
     *     analytics: array<string, mixed>|null,
     *     analyticsAvailable: bool,
     *     months: int,
     *     loadError: ?string
     * }
     */
    public function load(int $months = 6): array
    {
        $months = min(max($months, 3), 12);
        $errors = [];
        $unauthorized = false;
        $forbidden = false;

        $dashboard = $this->api->getDashboard();
        $dashStatus = (int) ($dashboard['statusCode'] ?? 500);

        if ($dashStatus === 401) {
            return $this->emptyResult($months, unauthorized: true, error: 'Admin session expired.');
        }
        if ($dashStatus === 403) {
            $forbidden = true;
            $errors[] = trim((string) ($dashboard['message'] ?? '')) ?: 'Forbidden. Super Admin access only.';
        }

        $analytics = $this->api->getAnalytics(['months' => $months]);
        $analyticsStatus = (int) ($analytics['statusCode'] ?? 500);

        if ($analyticsStatus === 401) {
            return $this->emptyResult($months, unauthorized: true, error: 'Admin session expired.');
        }
        if ($analyticsStatus === 403) {
            $forbidden = true;
            $errors[] = trim((string) ($analytics['message'] ?? '')) ?: 'Analytics forbidden.';
        }

        $stats = $this->blankStats();
        $analyticsPayload = null;
        $analyticsAvailable = false;

        if ($dashStatus === 200 && is_array($dashboard['data'] ?? null)) {
            $stats = $this->mapDashboard($dashboard['data']);
        } elseif ($dashStatus !== 403) {
            $errors[] = 'Could not load platform dashboard statistics.';
            // Limited fallback: list totals only (no fabricated jobs/apps/interviews).
            $stats = $this->fallbackListStats($errors);
        }

        if ($analyticsStatus === 200 && is_array($analytics['data'] ?? null)) {
            $analyticsPayload = $this->mapAnalytics($analytics['data'], $months);
            $analyticsAvailable = true;
            // Prefer analytics monthly trend when present (respects months filter).
            if (($analyticsPayload['trends']['monthly'] ?? []) !== []) {
                $stats['trend'] = $this->trendFromAnalyticsMonthly($analyticsPayload['trends']['monthly']);
                $stats['trend_available'] = true;
                $stats['trend_source'] = 'analytics';
                $stats['trend_months'] = (int) ($analyticsPayload['period']['months'] ?? $months);
            }
            // Prefer analytics activity (includes Admin actors; up to 15 audit rows).
            if (($analyticsPayload['recent_activity'] ?? []) !== []) {
                $stats['recent_activity'] = $analyticsPayload['recent_activity'];
                $stats['recent_activity_source'] = 'analytics';
            }
        } elseif ($analyticsStatus !== 401 && $analyticsStatus !== 403) {
            $errors[] = 'Could not load detailed platform analytics.';
        }

        $stats = $this->attachGrowthAndDistributionMeta($stats);

        return [
            'unauthorized' => $unauthorized,
            'forbidden' => $forbidden,
            'stats' => $stats,
            'analytics' => $analyticsPayload,
            'analyticsAvailable' => $analyticsAvailable,
            'months' => $months,
            'loadError' => $errors !== [] ? implode(' ', array_unique($errors)) : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function mapDashboard(array $data): array
    {
        $users = is_array($data['users'] ?? null) ? $data['users'] : [];
        $companies = is_array($data['companies'] ?? null) ? $data['companies'] : [];
        $jobs = is_array($data['jobs'] ?? null) ? $data['jobs'] : [];
        $apps = is_array($data['applications'] ?? null) ? $data['applications'] : [];
        $interviews = is_array($data['interviews'] ?? null) ? $data['interviews'] : [];
        $trend = is_array($data['trend'] ?? null) ? $data['trend'] : [];

        $byType = is_array($users['by_type'] ?? null) ? $users['by_type'] : [];
        $byStatus = is_array($companies['by_status'] ?? null) ? $companies['by_status'] : [];

        $mappedTrend = array_values(array_filter(
            $trend,
            static fn ($row) => is_array($row)
        ));

        return [
            'generated_at' => $data['generated_at'] ?? null,
            'source' => 'admin.dashboard',
            'users' => [
                'total' => $this->intOrNull($users['total'] ?? null),
                'growth_available' => array_key_exists('new_30d', $users) && array_key_exists('prev_30d', $users),
                'new_30d' => $this->intOrNull($users['new_30d'] ?? null),
                'prev_30d' => $this->intOrNull($users['prev_30d'] ?? null),
                'inactive_available' => array_key_exists('inactive', $users),
                'inactive' => $this->intOrNull($users['inactive'] ?? null),
                'by_type' => [
                    'candidate' => $this->intOrNull($byType['candidate'] ?? null),
                    'company' => $this->intOrNull($byType['company'] ?? null),
                    'admin' => $this->intOrNull($byType['admin'] ?? null),
                ],
            ],
            'companies' => [
                'total' => $this->intOrNull($companies['total'] ?? null),
                'new_30d' => $this->intOrNull($companies['new_30d'] ?? null),
                'by_status' => [
                    'pending' => $this->intOrNull($byStatus['pending'] ?? null),
                    'approved' => $this->intOrNull($byStatus['approved'] ?? null),
                    'rejected' => $this->intOrNull($byStatus['rejected'] ?? null),
                    'suspended' => $this->intOrNull($byStatus['suspended'] ?? null),
                ],
            ],
            'jobs' => [
                'available' => array_key_exists('published', $jobs) || array_key_exists('total', $jobs),
                'published' => $this->intOrNull($jobs['published'] ?? null),
                'total' => $this->intOrNull($jobs['total'] ?? null),
            ],
            'applications' => [
                'available' => array_key_exists('total', $apps),
                'total' => $this->intOrNull($apps['total'] ?? null),
                'last_30d' => $this->intOrNull($apps['last_30d'] ?? null),
                'prev_30d' => $this->intOrNull($apps['prev_30d'] ?? null),
            ],
            'interviews' => [
                'available' => array_key_exists('scheduled', $interviews),
                'scheduled' => $this->intOrNull($interviews['scheduled'] ?? null),
            ],
            'trend' => $mappedTrend,
            'trend_available' => $mappedTrend !== [],
            'trend_source' => 'dashboard',
            'trend_months' => 6,
            'pending_companies' => $this->normalizePendingCompanies($data['pending_companies'] ?? []),
            'recent_activity' => $this->normalizeActivity($data['recent_activity'] ?? []),
            'recent_activity_source' => 'dashboard',
        ];
    }

    /**
     * Attach safe growth deltas and distribution sum checks (Frontend presentation only).
     *
     * @param  array<string, mixed>  $stats
     * @return array<string, mixed>
     */
    private function attachGrowthAndDistributionMeta(array $stats): array
    {
        $users = is_array($stats['users'] ?? null) ? $stats['users'] : [];
        $apps = is_array($stats['applications'] ?? null) ? $stats['applications'] : [];
        $companies = is_array($stats['companies'] ?? null) ? $stats['companies'] : [];

        $stats['users']['growth'] = AdminGrowthDelta::from(
            $this->intOrNull($users['new_30d'] ?? null),
            $this->intOrNull($users['prev_30d'] ?? null),
            (bool) ($users['growth_available'] ?? false)
        );
        $stats['applications']['growth'] = AdminGrowthDelta::from(
            $this->intOrNull($apps['last_30d'] ?? null),
            $this->intOrNull($apps['prev_30d'] ?? null),
            (bool) ($apps['available'] ?? false)
                && array_key_exists('last_30d', $apps)
                && array_key_exists('prev_30d', $apps)
        );

        $byStatus = is_array($companies['by_status'] ?? null) ? $companies['by_status'] : [];
        $statusSum = 0;
        $statusKnown = true;
        foreach (['pending', 'approved', 'rejected', 'suspended'] as $key) {
            $v = $byStatus[$key] ?? null;
            if ($v === null) {
                $statusKnown = false;
                break;
            }
            $statusSum += (int) $v;
        }
        $companyTotal = $this->intOrNull($companies['total'] ?? null);
        $stats['companies']['status_sum'] = $statusKnown ? $statusSum : null;
        $stats['companies']['status_matches_total'] = $statusKnown && $companyTotal !== null
            ? $statusSum === $companyTotal
            : null;

        $byType = is_array($users['by_type'] ?? null) ? $users['by_type'] : [];
        $typeSum = 0;
        $typeKnown = true;
        foreach (['candidate', 'company', 'admin'] as $key) {
            $v = $byType[$key] ?? null;
            if ($v === null) {
                $typeKnown = false;
                break;
            }
            $typeSum += (int) $v;
        }
        $userTotal = $this->intOrNull($users['total'] ?? null);
        $stats['users']['type_sum'] = $typeKnown ? $typeSum : null;
        $stats['users']['type_matches_total'] = $typeKnown && $userTotal !== null
            ? $typeSum === $userTotal
            : null;

        return $stats;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function mapAnalytics(array $data, int $months): array
    {
        $period = is_array($data['period'] ?? null) ? $data['period'] : ['months' => $months];
        $overview = is_array($data['overview'] ?? null) ? $data['overview'] : [];
        $breakdowns = is_array($data['breakdowns'] ?? null) ? $data['breakdowns'] : [];
        $funnel = is_array($data['recruitment_funnel'] ?? null) ? $data['recruitment_funnel'] : [];
        $trends = is_array($data['trends'] ?? null) ? $data['trends'] : [];
        $monthly = is_array($trends['monthly'] ?? null) ? array_values(array_filter($trends['monthly'], 'is_array')) : [];

        return [
            'available' => true,
            'period' => [
                'months' => (int) ($period['months'] ?? $months),
                'from' => $period['from'] ?? null,
                'to' => $period['to'] ?? null,
            ],
            'overview' => $overview,
            'breakdowns' => [
                'applications_by_status' => $this->intMap($breakdowns['applications_by_status'] ?? []),
                'companies_by_status' => $this->intMap($breakdowns['companies_by_status'] ?? []),
                'jobs_by_status' => $this->intMap($breakdowns['jobs_by_status'] ?? []),
                'users_by_type' => $this->intMap($breakdowns['users_by_type'] ?? []),
                'offers_by_status' => $this->intMap($breakdowns['offers_by_status'] ?? []),
                'interviews_by_status' => $this->intMap($breakdowns['interviews_by_status'] ?? []),
            ],
            // Backend definition: applications = total rows; stage fields = CURRENT status counts
            // (not cumulative progression through the pipeline). Frontend labels must not imply otherwise.
            'recruitment_funnel' => [
                'definition' => 'current_status',
                'applications' => $this->intOrNull($funnel['applications'] ?? null),
                'shortlisted' => $this->intOrNull($funnel['shortlisted'] ?? null),
                'interviewing' => $this->intOrNull($funnel['interviewing'] ?? null),
                'offered' => $this->intOrNull($funnel['offered'] ?? null),
                'hired' => $this->intOrNull($funnel['hired'] ?? null),
                'shortlist_rate' => is_numeric($funnel['shortlist_rate'] ?? null) ? (float) $funnel['shortlist_rate'] : null,
                'hire_rate' => is_numeric($funnel['hire_rate'] ?? null) ? (float) $funnel['hire_rate'] : null,
            ],
            'trends' => [
                'monthly' => $monthly,
            ],
            'top_companies' => array_values(array_filter(
                is_array($data['top_companies'] ?? null) ? $data['top_companies'] : [],
                'is_array'
            )),
            'top_jobs' => array_values(array_filter(
                is_array($data['top_jobs'] ?? null) ? $data['top_jobs'] : [],
                'is_array'
            )),
            'pending_actions' => is_array($data['pending_actions'] ?? null) ? $data['pending_actions'] : [],
            'recent_activity' => $this->normalizeActivity($data['recent_activity'] ?? []),
            'generated_at' => $data['generated_at'] ?? null,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $monthly
     * @return list<array<string, mixed>>
     */
    private function trendFromAnalyticsMonthly(array $monthly): array
    {
        $out = [];
        foreach ($monthly as $row) {
            if (! is_array($row)) {
                continue;
            }
            $month = (string) ($row['month'] ?? '');
            $label = $month !== '' ? date('M', strtotime($month.'-01')) : '';
            $out[] = [
                'month' => $month,
                'label' => $label,
                'users' => $this->intOrNull($row['users'] ?? null) ?? 0,
                'applications' => $this->intOrNull($row['applications'] ?? null) ?? 0,
                'jobs' => $this->intOrNull($row['jobs'] ?? null),
                'companies' => $this->intOrNull($row['companies'] ?? null),
                'interviews' => $this->intOrNull($row['interviews'] ?? null),
                'offers' => $this->intOrNull($row['offers'] ?? null),
                'hired' => $this->intOrNull($row['hired'] ?? null),
            ];
        }

        return $out;
    }

    /**
     * @param  array<int, string>  $errors
     * @return array<string, mixed>
     */
    private function fallbackListStats(array &$errors): array
    {
        $stats = $this->blankStats();
        $stats['source'] = 'list-fallback';

        $users = $this->api->getUsers(['per_page' => 1]);
        $companies = $this->api->getCompanies(['per_page' => 1]);

        $stats['users']['total'] = $this->totalFromMeta($users);
        $stats['companies']['total'] = $this->totalFromMeta($companies);

        if (($users['statusCode'] ?? 500) !== 200) {
            $errors[] = 'Could not load users statistics.';
        }
        if (($companies['statusCode'] ?? 500) !== 200) {
            $errors[] = 'Could not load companies statistics.';
        }

        foreach (['approved', 'pending', 'suspended', 'rejected'] as $status) {
            $result = $this->api->getCompanies(['status' => $status, 'per_page' => 1]);
            $stats['companies']['by_status'][$status] = $this->totalFromMeta($result);
            if (($result['statusCode'] ?? 500) !== 200) {
                $errors[] = "Could not load {$status} company statistics.";
            }
        }

        foreach (['candidate', 'company', 'admin'] as $type) {
            $result = $this->api->getUsers(['account_type' => $type, 'per_page' => 1]);
            $stats['users']['by_type'][$type] = $this->totalFromMeta($result);
        }

        $deactivated = $this->totalFromMeta($this->api->getUsers([
            'account_status_id' => self::STATUS_DEACTIVATED,
            'per_page' => 1,
        ]));
        $blocked = $this->totalFromMeta($this->api->getUsers([
            'account_status_id' => self::STATUS_BLOCKED,
            'per_page' => 1,
        ]));
        if ($deactivated !== null && $blocked !== null) {
            $stats['users']['inactive'] = $deactivated + $blocked;
            $stats['users']['inactive_available'] = true;
        }

        $pendingResponse = $this->api->getCompanies(['status' => 'pending', 'per_page' => 5]);
        if (($pendingResponse['statusCode'] ?? 500) === 200) {
            $stats['pending_companies'] = $this->normalizePendingCompanies($pendingResponse['data'] ?? []);
        }

        $logsResponse = $this->api->getAuditLogs(['per_page' => 5]);
        if (($logsResponse['statusCode'] ?? 500) === 200) {
            $stats['recent_activity'] = $this->normalizeActivity($logsResponse['data'] ?? []);
        }

        // Jobs / applications / interviews remain unavailable in list fallback.
        return $stats;
    }

    /** @return array<string, mixed> */
    private function blankStats(): array
    {
        return [
            'generated_at' => null,
            'source' => null,
            'users' => [
                'total' => null,
                'growth_available' => false,
                'new_30d' => null,
                'prev_30d' => null,
                'inactive_available' => false,
                'inactive' => null,
                'by_type' => ['candidate' => null, 'company' => null, 'admin' => null],
            ],
            'companies' => [
                'total' => null,
                'new_30d' => null,
                'by_status' => [
                    'pending' => null,
                    'approved' => null,
                    'rejected' => null,
                    'suspended' => null,
                ],
            ],
            'jobs' => ['available' => false, 'published' => null, 'total' => null],
            'applications' => [
                'available' => false,
                'total' => null,
                'last_30d' => null,
                'prev_30d' => null,
            ],
            'interviews' => ['available' => false, 'scheduled' => null],
            'trend' => [],
            'trend_available' => false,
            'trend_source' => null,
            'trend_months' => null,
            'pending_companies' => [],
            'recent_activity' => [],
            'recent_activity_source' => null,
        ];
    }

    /**
     * @return array{
     *     unauthorized: bool,
     *     forbidden: bool,
     *     stats: array<string, mixed>,
     *     analytics: null,
     *     analyticsAvailable: bool,
     *     months: int,
     *     loadError: ?string
     * }
     */
    private function emptyResult(int $months, bool $unauthorized = false, bool $forbidden = false, ?string $error = null): array
    {
        return [
            'unauthorized' => $unauthorized,
            'forbidden' => $forbidden,
            'stats' => $this->blankStats(),
            'analytics' => null,
            'analyticsAvailable' => false,
            'months' => $months,
            'loadError' => $error,
        ];
    }

    private function totalFromMeta(array $response): ?int
    {
        if (($response['statusCode'] ?? 500) !== 200) {
            return null;
        }
        $total = $response['meta']['total'] ?? null;

        return is_numeric($total) ? (int) $total : null;
    }

    private function intOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * @param  mixed  $map
     * @return array<string, int>
     */
    private function intMap(mixed $map): array
    {
        if (! is_array($map)) {
            return [];
        }
        $out = [];
        foreach ($map as $key => $value) {
            if (is_numeric($value)) {
                $out[(string) $key] = (int) $value;
            }
        }

        return $out;
    }

    /**
     * @param  mixed  $rows
     * @return list<array<string, mixed>>
     */
    private function normalizePendingCompanies(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }
        $out = [];
        foreach ($rows as $row) {
            if (is_array($row)) {
                $out[] = $row;
            }
        }

        return array_values($out);
    }

    /**
     * Normalize dashboard + analytics activity rows for Blade.
     *
     * @param  mixed  $rows
     * @return list<array<string, mixed>>
     */
    private function normalizeActivity(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $actor = $row['actor'] ?? null;
            $user = $row['user'] ?? null;
            $name = null;
            if (is_string($actor) && $actor !== '') {
                $name = $actor;
            } elseif (is_string($user) && $user !== '') {
                $name = $user;
            } elseif (is_array($user)) {
                $name = $user['name'] ?? null;
            }

            $out[] = [
                'id' => $row['id'] ?? null,
                'action' => $row['action'] ?? null,
                'auditable_type' => $row['auditable_type'] ?? null,
                'actor_type' => $row['actor_type'] ?? null,
                'created_at' => $row['created_at'] ?? null,
                'user' => ['name' => $name],
            ];
        }

        return $out;
    }
}
