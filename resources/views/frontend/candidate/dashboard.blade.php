@extends('frontend.layouts.app')

@section('title', __('Overview'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Overview') }}</h1>
@endsection

@section('content')
@php
    $k = is_array($kpis ?? null) ? $kpis : [];
    $a = is_array($analytics ?? null) ? $analytics : [];
    $o = is_array($overview ?? null) ? $overview : [];
    $metric = function (string $key) use ($k): array {
        $row = is_array($k[$key] ?? null) ? $k[$key] : [];

        return [
            'available' => (bool) ($row['available'] ?? false),
            'value' => $row['value'] ?? null,
            'display' => (string) ($row['display'] ?? '—'),
            'href' => $row['href'] ?? null,
            'meta_key' => $row['meta_key'] ?? null,
        ];
    };

    $totalApps = $metric('total_applications');
    $inProgress = $metric('applications_in_progress');
    $interviews = $metric('upcoming_interviews');
    $offers = $metric('offers_received');
    $assessments = $metric('pending_assessments');
    $profile = $metric('profile_completion');

    $metaFor = function (array $m, string $availableMeta): string {
        if (! ($m['available'] ?? false)) {
            $key = $m['meta_key'] ?? 'kpi.unavailable';
            $full = str_starts_with((string) $key, 'candidate.') ? $key : 'candidate.'.$key;

            return __($full);
        }

        return __($availableMeta);
    };

    $activity = is_array($a['activity'] ?? null) ? $a['activity'] : [];
    $pipeline = is_array($a['pipeline'] ?? null) ? $a['pipeline'] : [];
    $profileRing = is_array($a['profile_ring'] ?? null) ? $a['profile_ring'] : [];
    $timeline = is_array($a['interview_timeline'] ?? null) ? $a['interview_timeline'] : [];

    $upcomingCard = is_array($o['upcoming_interview'] ?? null) ? $o['upcoming_interview'] : [];
    $tracker = is_array($o['application_tracker'] ?? null) ? $o['application_tracker'] : [];
    $recommended = is_array($o['recommendations'] ?? null) ? $o['recommendations'] : [];
    $nextActions = is_array($o['next_actions']['items'] ?? null) ? $o['next_actions']['items'] : [];
    $profileChecklist = is_array($o['profile_checklist'] ?? null) ? $o['profile_checklist'] : [];
    $checklistLinks = is_array($profileChecklist['links'] ?? null) ? $profileChecklist['links'] : [];

    $statusLabels = [
        'submitted' => __('candidate.analytics.status.submitted'),
        'screening' => __('candidate.analytics.status.screening'),
        'shortlisted' => __('candidate.analytics.status.shortlisted'),
        'interviewing' => __('candidate.analytics.status.interviewing'),
        'offered' => __('candidate.analytics.status.offered'),
        'hired' => __('candidate.analytics.status.hired'),
        'rejected' => __('candidate.analytics.status.rejected'),
        'withdrawn' => __('candidate.analytics.status.withdrawn'),
        'unknown' => __('candidate.analytics.status.unknown'),
    ];

    $activityReason = (string) ($activity['reason'] ?? '');
    $activityUnavailableMsg = match ($activityReason) {
        'missing_dates' => __('candidate.analytics.historical_missing_dates'),
        'incomplete' => __('candidate.analytics.historical_incomplete'),
        default => __('candidate.analytics.historical_unavailable'),
    };

    $analyticsPayload = [
        'locale' => app()->getLocale(),
        'activity' => $activity,
        'pipeline' => $pipeline,
        'statusLabels' => $statusLabels,
        'i18n' => [
            'activityAria' => __('candidate.analytics.activity_aria'),
            'pipelineAria' => __('candidate.analytics.pipeline_aria'),
            'pipelineTotal' => __('candidate.analytics.pipeline_total'),
            'activitySummary' => __('candidate.analytics.activity_summary'),
        ],
    ];

    // Genuine spark values from the verified 7-day activity series only (never invented).
    $activitySpark = [];
    if (($activity['available'] ?? false) && is_array($activity['periods']['7']['values'] ?? null)) {
        $activitySpark = array_slice(array_map('floatval', $activity['periods']['7']['values']), -10);
        if (! array_filter($activitySpark, static fn ($v) => $v > 0)) {
            $activitySpark = [];
        }
    }

    $statTotal = ($totalApps['available'] && $inProgress['available'])
        ? __('candidate.kpi.stat_in_progress', ['count' => $inProgress['display']])
        : null;
    $statProgress = $inProgress['available']
        ? __('candidate.kpi.stat_open_pipeline')
        : null;
    $statInterviews = $interviews['available']
        ? __('candidate.kpi.stat_upcoming')
        : null;
    $statOffers = $offers['available']
        ? __('candidate.kpi.stat_offers')
        : null;
    $statProfile = $profile['available']
        ? __('candidate.analytics.profile_fields_meta', [
            'filled' => (int) ($k['profile_completion']['filled'] ?? 0),
            'total' => (int) ($k['profile_completion']['total_fields'] ?? 7),
        ])
        : null;

    $recommendedState = ($recommended['error'] ?? false)
        ? 'error'
        : ((($recommended['empty'] ?? true) ? 'empty' : 'ready'));
@endphp

<div class="hb-candidate-dashboard flex flex-col gap-3" data-candidate-dashboard>
{{-- Compact welcome — reference uses title + one line; keep banner without dominating --}}
<x-welcome-banner
    compact
    :subtitle="__('candidate.dashboard.subtitle')"
/>

@if (! empty($loadError))
    @php
        $partialTitle = __('candidate.kpi.partial_error');
        $errorDetail = trim((string) $loadError);
        $genericUnavailable = __('candidate.kpi.unavailable');
        // Prefer a specific detail; never echo the generic "Unavailable" alone under the same title.
        $showDetail = $errorDetail !== ''
            && $errorDetail !== $partialTitle
            && $errorDetail !== $genericUnavailable;
    @endphp
    <div class="rounded-xl border border-hb-error/25 bg-hb-error-soft px-4 py-3 text-sm text-hb-error" role="alert" data-dashboard-partial-error>
        <p class="font-semibold">{{ $partialTitle }}</p>
        @if ($showDetail)
            <p class="mt-1 opacity-90">{{ $errorDetail }}</p>
        @endif
    </div>
@endif

{{-- KPI-first summary strip — compact 4+2 grid matching reference density --}}
<section aria-labelledby="candidate-kpi-heading" data-dashboard-kpi-strip>
    <div class="mb-2">
        <h2 id="candidate-kpi-heading" class="text-base font-semibold tracking-tight text-hb-navy">{{ __('candidate.kpi.section_title') }}</h2>
        <p class="mt-0.5 text-xs text-hb-muted">{{ __('candidate.kpi.section_subtitle') }}</p>
    </div>

    <div class="grid grid-cols-2 gap-2.5 lg:grid-cols-4">
        <x-kpi-card
            kpi-key="total_applications"
            :label="__('candidate.kpi.total_applications')"
            :value="$totalApps['display']"
            :stat="$statTotal"
            :meta="$metaFor($totalApps, 'candidate.kpi.total_applications_meta')"
            :href="$totalApps['href'] ? route($totalApps['href']) : null"
            :available="$totalApps['available']"
            :spark="$activitySpark"
            icon="applications"
            tone="blue"
        />
        <x-kpi-card
            kpi-key="applications_in_progress"
            :label="__('candidate.kpi.applications_in_progress')"
            :value="$inProgress['display']"
            :stat="$statProgress"
            :meta="$metaFor($inProgress, 'candidate.kpi.applications_in_progress_meta')"
            :href="$inProgress['href'] ? route($inProgress['href']) : null"
            :available="$inProgress['available']"
            icon="progress"
            tone="amber"
        />
        <x-kpi-card
            kpi-key="upcoming_interviews"
            :label="__('candidate.kpi.upcoming_interviews')"
            :value="$interviews['display']"
            :stat="$statInterviews"
            :meta="$metaFor($interviews, 'candidate.kpi.upcoming_interviews_meta')"
            :href="$interviews['href'] ? route($interviews['href']) : null"
            :available="$interviews['available']"
            icon="interviews"
            tone="violet"
        />
        <x-kpi-card
            kpi-key="offers_received"
            :label="__('candidate.kpi.offers_received')"
            :value="$offers['display']"
            :stat="$statOffers"
            :meta="$metaFor($offers, 'candidate.kpi.offers_received_meta')"
            :href="$offers['href'] ? route($offers['href']) : null"
            :available="$offers['available']"
            icon="offers"
            tone="emerald"
        />
    </div>
    <div class="mt-2.5 grid grid-cols-2 gap-2.5 lg:grid-cols-4">
        <x-kpi-card
            class="lg:col-span-2"
            kpi-key="pending_assessments"
            :label="__('candidate.kpi.pending_assessments')"
            :value="$assessments['display']"
            :meta="$metaFor($assessments, 'candidate.kpi.assessments_unavailable')"
            :href="$assessments['href'] ? route($assessments['href']) : null"
            :available="$assessments['available']"
            icon="assessments"
            tone="rose"
        />
        <x-kpi-card
            class="lg:col-span-2"
            kpi-key="profile_completion"
            :label="__('candidate.kpi.profile_completion')"
            :value="$profile['display']"
            :stat="$statProfile"
            :meta="$metaFor($profile, 'candidate.kpi.profile_completion_meta')"
            :href="$profile['href'] ? route($profile['href']) : null"
            :available="$profile['available']"
            icon="profile"
            tone="teal"
        />
    </div>
</section>

{{-- Analytics immediately after KPIs (reference composition) --}}
@include('frontend.candidate.partials.dashboard-analytics')

{{-- Task #12 operational blocks — single upcoming-interview card (no duplicate timeline) --}}
<section aria-labelledby="candidate-overview-heading" data-dashboard-overview>
    <div class="mb-2">
        <h2 id="candidate-overview-heading" class="text-base font-semibold tracking-tight text-hb-navy">{{ __('candidate.dashboard.next_steps') }}</h2>
        <p class="mt-0.5 text-xs text-hb-muted">{{ __('candidate.dashboard.progress_aside') }}</p>
    </div>

    <div class="grid gap-3 lg:grid-cols-2">
        {{-- Sole upcoming interview presentation (Task #12) --}}
        <x-card data-upcoming-interview-card>
            <x-slot:header>
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-bold text-hb-navy">{{ __('candidate.dashboard.upcoming_card_title') }}</h3>
                        <p class="mt-0.5 text-xs text-hb-muted">{{ __('candidate.dashboard.upcoming_card_subtitle') }}</p>
                    </div>
                    <a href="{{ route('frontend.candidate.interviews.index') }}" class="text-sm font-semibold text-hb-blue hover:underline">{{ __('candidate.dashboard.upcoming_view') }}</a>
                </div>
            </x-slot:header>

            @if ($upcomingCard['error'] ?? false)
                <p class="py-4 text-center text-sm text-hb-muted" role="alert" data-upcoming-interview-error>{{ __('candidate.dashboard.upcoming_error') }}</p>
            @elseif ($upcomingCard['empty'] ?? true)
                <div class="py-4 text-center" role="status" data-upcoming-interview-empty>
                    <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.dashboard.upcoming_empty') }}</p>
                    <p class="mt-1 text-xs text-hb-muted">{{ __('candidate.dashboard.upcoming_empty_hint') }}</p>
                </div>
            @else
                @php $iv = is_array($upcomingCard['item'] ?? null) ? $upcomingCard['item'] : []; @endphp
                <article class="rounded-xl border border-hb-border bg-hb-page/50 p-3.5" data-upcoming-interview-item>
                    <p class="text-xs font-semibold uppercase tracking-wide text-hb-muted">
                        {{ \App\Support\Rows::date(data_get($iv, 'start_at'), 'D, M j · H:i T') }}
                    </p>
                    <h4 class="mt-1.5 text-sm font-bold text-hb-navy">{{ data_get($iv, 'job_title') ?: __('Interviews') }}</h4>
                    <p class="mt-1 text-xs text-hb-muted">
                        @if (data_get($iv, 'company_name'))
                            {{ data_get($iv, 'company_name') }} ·
                        @endif
                        {{ data_get($iv, 'is_online') ? __('candidate.dashboard.online') : __('candidate.dashboard.onsite') }}
                        @if (data_get($iv, 'interview_type'))
                            · {{ data_get($iv, 'interview_type') }}
                        @endif
                        · {{ data_get($iv, 'status') }}
                    </p>
                    <a href="{{ data_get($iv, 'href') ?: route('frontend.candidate.interviews.index') }}" class="mt-3 inline-flex text-sm font-semibold text-hb-blue hover:underline">
                        {{ __('candidate.dashboard.upcoming_view') }}
                    </a>
                </article>
            @endif
        </x-card>

        {{-- Next actions — actionable only (no duplicate interview review) --}}
        <x-card data-next-actions-card>
            <x-slot:header>
                <h3 class="text-sm font-bold text-hb-navy">{{ __('candidate.dashboard.next_actions') }}</h3>
                <p class="mt-0.5 text-xs text-hb-muted">{{ __('candidate.dashboard.next_actions_subtitle') }}</p>
            </x-slot:header>

            @if ($nextActions === [])
                <p class="py-4 text-center text-sm text-hb-muted" role="status" data-next-actions-empty>{{ __('candidate.dashboard.next_actions_empty') }}</p>
            @else
                <ul class="space-y-2" data-next-actions-list>
                    @foreach ($nextActions as $action)
                        @php
                            $label = ! empty($action['label_key']) ? __($action['label_key']) : (string) ($action['label'] ?? '');
                            $meta = null;
                            if (! empty($action['meta_key'])) {
                                $meta = __($action['meta_key']);
                            } elseif (! empty($action['meta']) && is_string($action['meta'])) {
                                $when = \App\Support\Rows::date($action['meta'], 'M j, Y H:i T');
                                $meta = ($action['deadline_state'] ?? '') === 'overdue'
                                    ? __('candidate.dashboard.deadline_overdue', ['when' => $when])
                                    : __('candidate.dashboard.deadline_upcoming', ['when' => $when]);
                            }
                            $href = (string) ($action['href'] ?? '#');
                        @endphp
                        <li>
                            <a href="{{ $href }}" class="block rounded-xl border border-hb-border px-3.5 py-2.5 transition hover:border-hb-blue/40 hover:bg-hb-page/60 focus:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/30" data-next-action="{{ $action['key'] ?? '' }}">
                                <span class="block text-sm font-semibold text-hb-navy">{{ $label }}</span>
                                @if ($meta)
                                    <span class="mt-0.5 block text-xs text-hb-muted">{{ $meta }}</span>
                                @endif
                                @if (! empty($action['job_title']))
                                    <span class="mt-0.5 block text-xs text-hb-muted">{{ $action['job_title'] }}</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>
    </div>

    {{-- Compact application tracker --}}
    <x-card class="mt-3" data-application-tracker>
        <x-slot:header>
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <h3 class="text-sm font-bold text-hb-navy">{{ __('candidate.dashboard.tracker_title') }}</h3>
                    <p class="mt-0.5 text-xs text-hb-muted">{{ __('candidate.dashboard.tracker_subtitle') }}</p>
                </div>
                <a href="{{ route('frontend.candidate.applications.index') }}" class="text-sm font-semibold text-hb-blue hover:underline">{{ __('candidate.dashboard.tracker_view_all') }}</a>
            </div>
        </x-slot:header>

        <p class="mb-2 text-xs text-hb-muted" data-tracker-snapshot-note>{{ __('candidate.dashboard.tracker_snapshot_note') }}</p>

        @if ($tracker['error'] ?? false)
            <p class="py-4 text-center text-sm text-hb-muted" role="alert" data-tracker-error>{{ __('candidate.dashboard.tracker_error') }}</p>
        @elseif ($tracker['empty'] ?? true)
            <div class="py-4 text-center" role="status" data-tracker-empty>
                <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.dashboard.tracker_empty') }}</p>
                <a href="{{ route('frontend.candidate.vacancies.index') }}" class="mt-2 inline-flex text-sm font-semibold text-hb-blue hover:underline">{{ __('Discover jobs') }}</a>
            </div>
        @else
            @if ($tracker['partial'] ?? false)
                <p class="mb-2 rounded-xl border border-hb-pending/30 bg-hb-pending-soft px-3 py-2 text-xs text-hb-pending" role="status" data-tracker-partial>
                    {{ __('candidate.dashboard.tracker_partial_note') }}
                </p>
            @endif
            <ul class="divide-y divide-hb-border" data-tracker-list>
                @foreach (($tracker['items'] ?? []) as $app)
                    @php
                        $statusKey = (string) ($app['status'] ?? 'unknown');
                        $statusLabel = $statusLabels[$statusKey] ?? $statusKey;
                    @endphp
                    <li class="flex flex-wrap items-center justify-between gap-3 py-2.5 first:pt-0 last:pb-0">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-hb-navy">{{ $app['job_title'] ?: __('My applications') }}</p>
                            <p class="mt-0.5 text-xs text-hb-muted">
                                @if (! empty($app['company_name'])){{ $app['company_name'] }} · @endif
                                {{ \App\Support\Rows::date($app['applied_at'] ?? null, 'M j, Y') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="rounded-full bg-hb-info-soft px-2.5 py-1 text-xs font-semibold text-hb-blue" data-tracker-status="{{ $statusKey }}">{{ $statusLabel }}</span>
                            <a href="{{ $app['href'] }}" class="text-sm font-semibold text-hb-blue hover:underline">{{ __('candidate.dashboard.view_application') }}</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-card>

    {{-- Recommended jobs — empty vs backend failure are distinct --}}
    <x-card class="mt-3" data-recommended-jobs data-recommended-state="{{ $recommendedState }}">
        <x-slot:header>
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <h3 class="text-sm font-bold text-hb-navy">{{ __('candidate.dashboard.recommended') }}</h3>
                    <p class="mt-0.5 text-xs text-hb-muted">{{ __('candidate.dashboard.recommended_subtitle') }}</p>
                </div>
                <a href="{{ route('frontend.candidate.vacancies.index') }}" class="text-sm font-semibold text-hb-blue hover:underline">{{ __('Discover jobs') }}</a>
            </div>
        </x-slot:header>

        @if ($recommended['error'] ?? false)
            <div class="py-4 text-center" role="alert" data-recommended-error data-recommended-error-kind="{{ $recommended['error_kind'] ?? 'api' }}">
                <p class="text-sm font-semibold text-hb-navy">{{ __($recommended['error_key'] ?? 'candidate.dashboard.recommended_error') }}</p>
                @if (! empty($recommended['error_hint_key']))
                    <p class="mt-1 text-xs text-hb-muted">{{ __($recommended['error_hint_key']) }}</p>
                @endif
                <a href="{{ route('frontend.candidate.vacancies.index') }}" class="mt-2 inline-flex text-sm font-semibold text-hb-blue hover:underline">{{ __('Discover jobs') }}</a>
            </div>
        @elseif ($recommended['empty'] ?? true)
            <div class="py-4 text-center" role="status" data-recommended-empty>
                <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.dashboard.recommended_empty') }}</p>
                <p class="mt-1 text-xs text-hb-muted">{{ __('candidate.dashboard.recommended_empty_hint') }}</p>
            </div>
        @else
            <ul class="grid gap-2.5 sm:grid-cols-2 xl:grid-cols-3" data-recommended-list>
                @foreach (($recommended['items'] ?? []) as $job)
                    <li class="rounded-xl border border-hb-border bg-hb-surface p-3.5">
                        <p class="text-sm font-bold text-hb-navy">{{ $job['title'] ?: __('Jobs') }}</p>
                        <p class="mt-1 text-xs text-hb-muted">
                            @if (! empty($job['company_name'])){{ $job['company_name'] }}@endif
                            @if (! empty($job['work_mode'])) · {{ $job['work_mode'] }}@endif
                            @if (! empty($job['location'])) · {{ $job['location'] }}@endif
                        </p>
                        @if (! empty($job['why_it_fits']) && is_array($job['why_it_fits']))
                            <p class="mt-2 text-xs font-semibold text-hb-navy">{{ __('candidate.dashboard.why_it_fits') }}</p>
                            <ul class="mt-1 space-y-0.5 text-xs text-hb-muted">
                                @foreach ($job['why_it_fits'] as $reason)
                                    <li>{{ $reason }}</li>
                                @endforeach
                            </ul>
                        @endif
                        <div class="mt-2.5 flex flex-wrap gap-3">
                            <a href="{{ $job['href'] }}" class="text-sm font-semibold text-hb-blue hover:underline">{{ __('candidate.dashboard.recommended_view') }}</a>
                            @unless ($job['applied_by_me'] ?? false)
                                <a href="{{ $job['apply_href'] }}" class="text-sm font-semibold text-hb-blue hover:underline">{{ __('candidate.dashboard.recommended_apply') }}</a>
                            @endunless
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-card>
</section>

<section aria-labelledby="candidate-quick-actions-heading">
    <h2 id="candidate-quick-actions-heading" class="mb-2 text-base font-semibold tracking-tight text-hb-navy">{{ __('candidate.kpi.quick_actions') }}</h2>
    <div class="grid gap-2.5 sm:grid-cols-2 xl:grid-cols-3">
        <x-stat-card :label="__('Discover jobs')" value="→" :meta="__('Browse open vacancies')" :href="route('frontend.candidate.vacancies.index')" />
        <x-stat-card :label="__('My applications')" value="→" :meta="__('Track your applications')" :href="route('frontend.candidate.applications.index')" />
        <x-stat-card :label="__('Assessments')" value="→" :meta="__('Take your exams')" :href="route('frontend.candidate.exams.index')" />
        <x-stat-card :label="__('Interviews')" value="→" :meta="__('Book and manage interviews')" :href="route('frontend.candidate.interviews.index')" />
        <x-stat-card :label="__('Offers')" value="→" :meta="__('Review your offers')" :href="route('frontend.candidate.offers.index')" />
        <x-stat-card :label="__('Profile & resume')" value="→" :meta="__('Keep your profile up to date')" :href="route('frontend.candidate.profile')" />
    </div>
</section>
</div>
@endsection
