<?php

namespace App\Frontend\Candidate;

use App\Support\Frontend\CandidateDashboard\CandidateDashboardKpiGateway;
use App\Support\Frontend\SafeApiMessage;
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
     * Seven-field Profile Completion (unchanged).
     *
     * Proposed (not applied): add `skills` as an 8th field when product approves
     * scoring against `profile.skills` / GET /candidate/skills. Do not change
     * silently — keep KPI accuracy stable until approved.
     *
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
     *     analytics: array<string, mixed>,
     *     overview: array<string, mixed>
     * }
     */
    public function load(): array
    {
        $errors = [];
        $unauthorized = false;
        $now = Carbon::now();

        $appCatalog = $this->fetchApplicationCatalog($unauthorized, $errors);
        $interviewCatalog = $this->fetchInterviewCatalog($unauthorized, $errors);
        $offersBundle = $this->fetchOffersBundle($unauthorized, $errors);
        $profile = $this->aggregateProfileCompletion($unauthorized, $errors);
        // Recommendations are optional Task-12 enrichment. Failures stay component-scoped
        // (e.g. missing candidate_hidden_jobs table) and must not raise the global banner
        // when core KPI sources succeed.
        $recommendations = $this->fetchRecommendations($unauthorized, $errors, softFail: true);

        $assessments = $this->metric(
            available: false,
            value: null,
            href: 'frontend.candidate.exams.index',
            metaKey: 'kpi.assessments_unavailable',
        );

        $appKpis = $this->applicationKpisFromCatalog($appCatalog);
        $interviewKpi = $this->interviewKpiFromCatalog($interviewCatalog, $now);
        $upcomingInterview = $this->buildUpcomingInterviewCard($interviewCatalog, $now);
        $applicationTracker = $this->buildApplicationTracker($appCatalog);
        $nextActions = $this->buildNextActions(
            $profile,
            $upcomingInterview,
            $applicationTracker,
            $offersBundle,
            $recommendations,
            $now,
        );

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

        // Sanitize each Backend message once. Never use the banner title as the SafeApiMessage
        // fallback — that previously produced "Some dashboard… Some dashboard…" in the Blade alert.
        $bannerTitle = __('candidate.kpi.partial_error');
        $safeErrors = [];
        foreach ($errors as $message) {
            if (! is_string($message) || trim($message) === '') {
                continue;
            }
            $safe = SafeApiMessage::forUser(
                $message,
                __('candidate.kpi.unavailable'),
                ['action' => 'candidate.dashboard']
            );
            $safe = trim($safe);
            if ($safe === '' || $safe === $bannerTitle) {
                continue;
            }
            $safeErrors[$safe] = $safe;
        }
        $safeErrors = array_values($safeErrors);

        return [
            'unauthorized' => $unauthorized,
            // Non-null means partial failure. Details are unique and never equal the banner title alone.
            'loadError' => $errors !== []
                ? ($safeErrors !== [] ? implode(' · ', $safeErrors) : $bannerTitle)
                : null,
            'kpis' => [
                'total_applications' => $appKpis['total'],
                'applications_in_progress' => $appKpis['in_progress'],
                'upcoming_interviews' => $interviewKpi,
                'offers_received' => $offersBundle['metric'],
                'pending_assessments' => $assessments,
                'profile_completion' => $profile,
            ],
            'analytics' => $analytics,
            'overview' => [
                'upcoming_interview' => $upcomingInterview,
                'application_tracker' => $applicationTracker,
                'recommendations' => $recommendations,
                'next_actions' => $nextActions,
                'profile_checklist' => $this->buildProfileChecklist($profile),
            ],
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
     * @return array{metric: array<string, mixed>, available: bool, rows: list<array<string, mixed>>}
     */
    private function fetchOffersBundle(bool &$unauthorized, array &$errors): array
    {
        $response = $this->gateway->offers();
        $status = (int) ($response['statusCode'] ?? 500);

        if ($status === 401) {
            $unauthorized = true;

            return [
                'metric' => $this->metric(false, null, 'frontend.candidate.offers.index', 'kpi.unavailable'),
                'available' => false,
                'rows' => [],
            ];
        }

        if ($status !== 200) {
            $errors[] = $this->httpErrorMessage($response, $status, 'offers');

            return [
                'metric' => $this->metric(false, null, 'frontend.candidate.offers.index', 'kpi.unavailable'),
                'available' => false,
                'rows' => [],
            ];
        }

        $rows = $this->rows($response['data'] ?? null);

        return [
            'metric' => $this->metric(true, count($rows), 'frontend.candidate.offers.index'),
            'available' => true,
            'rows' => $rows,
        ];
    }

    /**
     * @param  list<string>  $errors
     * @return array<string, mixed>
     */
    private function fetchRecommendations(bool &$unauthorized, array &$errors, bool $softFail = false): array
    {
        $response = $this->gateway->recommendedJobs(1, 6);
        $status = (int) ($response['statusCode'] ?? 500);

        if ($status === 401) {
            $unauthorized = true;

            return [
                'available' => false,
                'empty' => false,
                'items' => [],
                'error' => true,
                'error_kind' => 'unauthorized',
                'error_key' => 'candidate.dashboard.recommended_error',
                'error_hint_key' => 'candidate.dashboard.recommended_error_hint',
            ];
        }

        if ($status !== 200) {
            // Soft-fail: keep the dashboard usable; show the section error state only.
            if (! $softFail) {
                $errors[] = $this->httpErrorMessage($response, $status, 'recommendations');
            }

            $raw = $this->httpErrorMessage($response, $status, 'recommendations');
            $schemaMissing = is_string($raw) && str_contains(strtolower($raw), 'candidate_hidden_jobs');

            return [
                'available' => false,
                'empty' => false,
                'items' => [],
                'error' => true,
                // Distinct from genuine empty — never invent job rows.
                'error_kind' => $schemaMissing ? 'schema' : 'api',
                'error_key' => $schemaMissing
                    ? 'candidate.dashboard.recommended_backend_unavailable'
                    : 'candidate.dashboard.recommended_error',
                'error_hint_key' => $schemaMissing
                    ? 'candidate.dashboard.recommended_backend_unavailable_hint'
                    : 'candidate.dashboard.recommended_error_hint',
            ];
        }

        $items = [];
        foreach ($this->rows($response['data'] ?? null) as $row) {
            $id = data_get($row, 'id');
            if (! is_numeric($id)) {
                continue;
            }

            $why = data_get($row, 'why_it_fits');
            $whyList = [];
            if (is_array($why)) {
                foreach ($why as $reason) {
                    if (is_string($reason) && trim($reason) !== '') {
                        $whyList[] = trim($reason);
                    }
                }
            }

            $items[] = [
                'id' => (int) $id,
                'title' => data_get($row, 'title'),
                'company_name' => data_get($row, 'company.name') ?? data_get($row, 'company_name'),
                'work_mode' => data_get($row, 'work_mode'),
                'location' => data_get($row, 'location'),
                'employment_type' => data_get($row, 'employment_type'),
                'why_it_fits' => $whyList,
                'applied_by_me' => (bool) data_get($row, 'applied_by_me', false),
                'href' => route('frontend.candidate.vacancies.show', ['vacancyId' => (int) $id], false),
                'apply_href' => route('frontend.candidate.vacancies.apply', ['vacancyId' => (int) $id], false),
            ];
        }

        return [
            'available' => true,
            'empty' => $items === [],
            'items' => $items,
            'error' => false,
        ];
    }

    /**
     * @param  array{complete: bool, rows: list<array<string, mixed>>}  $catalog
     * @return array<string, mixed>
     */
    private function buildUpcomingInterviewCard(array $catalog, Carbon $now): array
    {
        if (! $catalog['complete']) {
            return [
                'available' => false,
                'empty' => false,
                'item' => null,
                'error' => true,
            ];
        }

        $nearest = null;
        $nearestTs = null;

        foreach ($catalog['rows'] as $row) {
            if (! $this->isUpcomingInterview($row, $now)) {
                continue;
            }

            try {
                $start = Carbon::parse((string) data_get($row, 'start_at'));
            } catch (Throwable) {
                continue;
            }

            if ($nearestTs === null || $start->lt($nearestTs)) {
                $nearestTs = $start;
                $nearest = [
                    'id' => data_get($row, 'id'),
                    'status' => strtolower(trim((string) data_get($row, 'status', ''))),
                    'start_at' => $start->toIso8601String(),
                    'end_at' => data_get($row, 'end_at'),
                    'job_title' => data_get($row, 'application.job_title')
                        ?? data_get($row, 'job_title'),
                    'company_name' => data_get($row, 'application.company_name')
                        ?? data_get($row, 'company_name'),
                    'interview_type' => data_get($row, 'interview_type'),
                    'is_online' => (bool) data_get($row, 'is_online', false),
                    'location' => data_get($row, 'location'),
                    // Candidate interview detail show-route is not wired; list is the real destination.
                    'href' => route('frontend.candidate.interviews.index', [], false),
                ];
            }
        }

        return [
            'available' => true,
            'empty' => $nearest === null,
            'item' => $nearest,
            'error' => false,
        ];
    }

    /**
     * @param  array{complete: bool, rows: list<array<string, mixed>>, meta_total: ?int}  $catalog
     * @return array<string, mixed>
     */
    private function buildApplicationTracker(array $catalog): array
    {
        if (! $catalog['complete'] && $catalog['rows'] === []) {
            return [
                'available' => false,
                'empty' => false,
                'items' => [],
                'partial' => false,
                'error' => true,
            ];
        }

        $sorted = $catalog['rows'];
        usort($sorted, static function (array $a, array $b): int {
            $aAt = data_get($a, 'applied_at');
            $bAt = data_get($b, 'applied_at');
            try {
                $aTs = is_string($aAt) && $aAt !== '' ? Carbon::parse($aAt)->getTimestamp() : 0;
            } catch (Throwable) {
                $aTs = 0;
            }
            try {
                $bTs = is_string($bAt) && $bAt !== '' ? Carbon::parse($bAt)->getTimestamp() : 0;
            } catch (Throwable) {
                $bTs = 0;
            }

            return $bTs <=> $aTs;
        });

        $items = [];
        foreach (array_slice($sorted, 0, 5) as $row) {
            $id = data_get($row, 'id');
            if (! is_numeric($id)) {
                continue;
            }

            $status = strtolower(trim((string) data_get($row, 'status', '')));
            $items[] = [
                'id' => (int) $id,
                'job_title' => data_get($row, 'job.title')
                    ?? data_get($row, 'job_title')
                    ?? data_get($row, 'vacancy_title'),
                'company_name' => data_get($row, 'job.company.name')
                    ?? data_get($row, 'company_name')
                    ?? data_get($row, 'company.name'),
                'status' => $status !== '' ? $status : 'unknown',
                'applied_at' => data_get($row, 'applied_at'),
                'href' => route('frontend.candidate.applications.show', ['applicationId' => (int) $id], false),
            ];
        }

        return [
            'available' => true,
            'empty' => $items === [],
            'items' => $items,
            'partial' => ! $catalog['complete'],
            'error' => false,
        ];
    }

    /**
     * @param  array<string, mixed>  $profile
     * @return array<string, mixed>
     */
    private function buildProfileChecklist(array $profile): array
    {
        if (! ($profile['available'] ?? false)) {
            return [
                'available' => false,
                'percent' => null,
                'filled' => null,
                'total_fields' => count(self::PROFILE_COMPLETION_FIELDS),
                'checks' => [],
                'links' => [],
            ];
        }

        $checks = is_array($profile['checks'] ?? null) ? $profile['checks'] : [];
        $links = [
            'phone' => route('frontend.candidate.profile.update', [], false),
            'location' => route('frontend.candidate.profile.update', [], false),
            'headline' => route('frontend.candidate.profile.update', [], false),
            'bio' => route('frontend.candidate.profile.update', [], false),
            'experience' => route('frontend.candidate.experience.add', [], false),
            'education' => route('frontend.candidate.education.add', [], false),
            'resume' => route('frontend.candidate.resume.upload', [], false),
        ];

        return [
            'available' => true,
            'percent' => (int) ($profile['value'] ?? 0),
            'filled' => (int) ($profile['filled'] ?? 0),
            'total_fields' => (int) ($profile['total_fields'] ?? count(self::PROFILE_COMPLETION_FIELDS)),
            'checks' => $checks,
            'links' => $links,
        ];
    }

    /**
     * @param  array<string, mixed>  $profile
     * @param  array<string, mixed>  $upcomingInterview
     * @param  array<string, mixed>  $applicationTracker
     * @param  array{metric: array<string, mixed>, available: bool, rows: list<array<string, mixed>>}  $offersBundle
     * @param  array<string, mixed>  $recommendations
     * @return array{items: list<array<string, mixed>>}
     */
    private function buildNextActions(
        array $profile,
        array $upcomingInterview,
        array $applicationTracker,
        array $offersBundle,
        array $recommendations,
        Carbon $now,
    ): array {
        unset($upcomingInterview); // Interview CTA lives only on the upcoming-interview card.

        $items = [];

        if (($profile['available'] ?? false) && (int) ($profile['value'] ?? 0) < 100) {
            $items[] = [
                'key' => 'complete_profile',
                'label_key' => 'candidate.dashboard.action_complete_profile',
                'meta_key' => 'candidate.dashboard.action_complete_profile_meta',
                'href' => route('frontend.candidate.profile', [], false),
            ];
        }

        if (($applicationTracker['available'] ?? false) && ! ($applicationTracker['empty'] ?? true)) {
            $first = $applicationTracker['items'][0] ?? null;
            $items[] = [
                'key' => 'track_application',
                'label_key' => 'candidate.dashboard.action_track_application',
                'meta_key' => 'candidate.dashboard.action_track_application_meta',
                'href' => is_array($first)
                    ? (string) ($first['href'] ?? route('frontend.candidate.applications.index', [], false))
                    : route('frontend.candidate.applications.index', [], false),
            ];
        }

        if ($offersBundle['available']) {
            foreach ($offersBundle['rows'] as $offer) {
                $expiresAt = data_get($offer, 'expires_at');
                if (! is_string($expiresAt) || trim($expiresAt) === '') {
                    continue;
                }

                try {
                    $deadline = Carbon::parse($expiresAt);
                } catch (Throwable) {
                    continue;
                }

                $offerId = data_get($offer, 'id');
                if (! is_numeric($offerId)) {
                    continue;
                }

                $status = strtolower(trim((string) data_get($offer, 'status', '')));
                if (! in_array($status, ['sent', 'pending', 'draft'], true) && $status !== '') {
                    // Still show open sent offers primarily; skip clearly closed ones.
                    if (in_array($status, ['accepted', 'rejected', 'expired', 'withdrawn'], true)) {
                        continue;
                    }
                }

                $overdue = $deadline->lt($now);
                $items[] = [
                    'key' => $overdue ? 'offer_deadline_overdue' : 'offer_deadline',
                    'label_key' => $overdue
                        ? 'candidate.dashboard.action_offer_overdue'
                        : 'candidate.dashboard.action_offer_deadline',
                    'meta_key' => null,
                    'meta' => $deadline->toIso8601String(),
                    'deadline_state' => $overdue ? 'overdue' : 'upcoming',
                    'href' => route('frontend.candidate.offers.show', ['offerId' => (int) $offerId], false),
                    'job_title' => data_get($offer, 'job_title'),
                ];
            }
        }

        if (($recommendations['available'] ?? false) && ! ($recommendations['empty'] ?? true) && ! ($recommendations['error'] ?? false)) {
            $items[] = [
                'key' => 'explore_recommendations',
                'label_key' => 'candidate.dashboard.action_explore_recommendations',
                'meta_key' => 'candidate.dashboard.action_explore_recommendations_meta',
                'href' => route('frontend.candidate.vacancies.index', [], false),
            ];
        } else {
            // Empty results OR soft Backend failure — still nudge Discover jobs (no invented matches).
            $items[] = [
                'key' => 'browse_jobs',
                'label_key' => 'candidate.dashboard.action_browse_jobs',
                'meta_key' => 'candidate.dashboard.action_browse_jobs_meta',
                'href' => route('frontend.candidate.vacancies.index', [], false),
            ];
        }

        return ['items' => array_slice($items, 0, 6)];
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
