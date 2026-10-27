@extends('frontend.layouts.app')

@section('title', __('Overview'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Overview') }}</h1>
@endsection

@section('content')
@php
    $k = is_array($kpis ?? null) ? $kpis : [];
    $a = is_array($analytics ?? null) ? $analytics : [];
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
@endphp

<x-welcome-banner :subtitle="__('Pick up where you left off: jobs, exams, interviews and offers in one place.')" />

@if (! empty($loadError))
    <div class="mb-6 rounded-2xl border border-hb-error/30 bg-hb-error-soft px-5 py-4 text-sm text-hb-error" role="alert">
        {{ __('candidate.kpi.partial_error') }}
        <span class="opacity-80">{{ trim($loadError) }}</span>
    </div>
@endif

<section class="mb-8" aria-labelledby="candidate-kpi-heading">
    <div class="mb-5 flex flex-wrap items-end justify-between gap-2">
        <div>
            <h2 id="candidate-kpi-heading" class="text-lg font-semibold tracking-tight text-hb-navy">{{ __('candidate.kpi.section_title') }}</h2>
            <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.kpi.section_subtitle') }}</p>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <x-kpi-card
            kpi-key="total_applications"
            :label="__('candidate.kpi.total_applications')"
            :value="$totalApps['display']"
            :meta="$metaFor($totalApps, 'candidate.kpi.total_applications_meta')"
            :href="$totalApps['href'] ? route($totalApps['href']) : null"
            :available="$totalApps['available']"
            icon="applications"
        />
        <x-kpi-card
            kpi-key="applications_in_progress"
            :label="__('candidate.kpi.applications_in_progress')"
            :value="$inProgress['display']"
            :meta="$metaFor($inProgress, 'candidate.kpi.applications_in_progress_meta')"
            :href="$inProgress['href'] ? route($inProgress['href']) : null"
            :available="$inProgress['available']"
            icon="progress"
        />
        <x-kpi-card
            kpi-key="upcoming_interviews"
            :label="__('candidate.kpi.upcoming_interviews')"
            :value="$interviews['display']"
            :meta="$metaFor($interviews, 'candidate.kpi.upcoming_interviews_meta')"
            :href="$interviews['href'] ? route($interviews['href']) : null"
            :available="$interviews['available']"
            icon="interviews"
        />
        <x-kpi-card
            kpi-key="offers_received"
            :label="__('candidate.kpi.offers_received')"
            :value="$offers['display']"
            :meta="$metaFor($offers, 'candidate.kpi.offers_received_meta')"
            :href="$offers['href'] ? route($offers['href']) : null"
            :available="$offers['available']"
            icon="offers"
        />
        <x-kpi-card
            kpi-key="pending_assessments"
            :label="__('candidate.kpi.pending_assessments')"
            :value="$assessments['display']"
            :meta="$metaFor($assessments, 'candidate.kpi.pending_assessments_meta')"
            :href="$assessments['href'] ? route($assessments['href']) : null"
            :available="$assessments['available']"
            icon="assessments"
        />
        <x-kpi-card
            kpi-key="profile_completion"
            :label="__('candidate.kpi.profile_completion')"
            :value="$profile['display']"
            :meta="$metaFor($profile, 'candidate.kpi.profile_completion_meta')"
            :href="$profile['href'] ? route($profile['href']) : null"
            :available="$profile['available']"
            icon="profile"
        />
    </div>
</section>

<section
    class="mb-10"
    aria-labelledby="candidate-analytics-heading"
    data-candidate-analytics="{{ e(json_encode($analyticsPayload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) }}"
>
    <div class="mb-5">
        <h2 id="candidate-analytics-heading" class="text-lg font-semibold tracking-tight text-hb-navy">{{ __('candidate.analytics.section_title') }}</h2>
        <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.analytics.section_subtitle') }}</p>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        {{-- Application activity --}}
        <x-card class="lg:col-span-2" data-analytics-panel="activity">
            <x-slot:header>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-hb-navy">{{ __('candidate.analytics.activity_title') }}</h3>
                        <p class="mt-0.5 text-xs text-hb-muted">{{ __('candidate.analytics.activity_subtitle') }}</p>
                    </div>
                    <div class="inline-flex rounded-xl border border-hb-border bg-hb-page/60 p-1" role="group" aria-label="{{ __('candidate.analytics.sr_period_group') }}">
                        @foreach ([7, 30, 90] as $days)
                            <button
                                type="button"
                                data-activity-period="{{ $days }}"
                                class="rounded-lg px-2.5 py-1 text-xs font-semibold transition"
                                aria-pressed="false"
                            >{{ __('candidate.analytics.period_'.$days) }}</button>
                        @endforeach
                    </div>
                </div>
            </x-slot:header>

            <div data-activity-unavailable @class(['py-12 text-center', 'hidden' => (bool) ($activity['available'] ?? false)]) role="status">
                <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.analytics.historical_unavailable') }}</p>
                <p class="mt-1 text-sm text-hb-muted">{{ $activityUnavailableMsg }}</p>
            </div>
            <div data-activity-empty @class(['py-12 text-center', 'hidden' => ! (($activity['available'] ?? false) && ($activity['empty'] ?? false))]) role="status">
                <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.analytics.activity_empty') }}</p>
                <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.analytics.activity_empty_hint') }}</p>
                <a href="{{ route('frontend.candidate.vacancies.index') }}" class="mt-4 inline-flex text-sm font-semibold text-hb-blue hover:underline">{{ __('Discover jobs') }}</a>
            </div>
            <div data-activity-chart-wrap @class(['hidden' => ! (($activity['available'] ?? false) && ! ($activity['empty'] ?? true))])>
                <p data-activity-summary class="mb-3 text-xs text-hb-muted" aria-live="polite"></p>
                <div data-activity-chart class="min-h-[220px] w-full"></div>
                <noscript>
                    <p class="text-sm text-hb-muted">{{ __('candidate.analytics.activity_subtitle') }}</p>
                </noscript>
            </div>
        </x-card>

        {{-- Profile completion ring --}}
        <x-card data-analytics-panel="profile">
            <x-slot:header>
                <h3 class="text-base font-bold text-hb-navy">{{ __('candidate.analytics.profile_ring_title') }}</h3>
                <p class="mt-0.5 text-xs text-hb-muted">{{ __('candidate.analytics.profile_ring_subtitle') }}</p>
            </x-slot:header>

            @if (! ($profileRing['available'] ?? false))
                <p class="py-10 text-center text-sm text-hb-muted" role="status">{{ __('candidate.analytics.profile_ring_unavailable') }}</p>
            @else
                @php
                    $pct = (int) ($profileRing['percent'] ?? 0);
                    $filled = (int) ($profileRing['filled'] ?? 0);
                    $fields = (int) ($profileRing['total_fields'] ?? 7);
                    $checks = is_array($profileRing['checks'] ?? null) ? $profileRing['checks'] : [];
                    $r = 54;
                    $circ = 2 * M_PI * $r;
                @endphp
                <div
                    class="flex flex-col items-center"
                    data-profile-ring
                    data-percent="{{ $pct }}"
                >
                    <svg class="h-40 w-40" viewBox="0 0 140 140" role="img" aria-label="{{ __('candidate.kpi.profile_completion') }}: {{ $pct }}%">
                        <circle cx="70" cy="70" r="{{ $r }}" fill="none" stroke="currentColor" class="text-hb-page" stroke-width="12" />
                        <circle
                            data-profile-ring-progress
                            cx="70" cy="70" r="{{ $r }}"
                            fill="none"
                            stroke="currentColor"
                            class="text-hb-blue"
                            stroke-width="12"
                            stroke-linecap="round"
                            transform="rotate(-90 70 70)"
                            style="stroke-dasharray: {{ $circ }}; stroke-dashoffset: {{ $circ }};"
                        />
                        <text x="70" y="66" text-anchor="middle" class="fill-hb-navy text-2xl font-semibold" style="font-size: 28px; font-weight: 600;">{{ $pct }}%</text>
                        <text x="70" y="88" text-anchor="middle" class="fill-hb-muted" style="font-size: 11px;">{{ __('candidate.analytics.profile_fields_meta', ['filled' => $filled, 'total' => $fields]) }}</text>
                    </svg>
                    <ul class="mt-4 w-full space-y-1.5 text-sm">
                        @foreach (\App\Frontend\Candidate\CandidateDashboardAdapter::PROFILE_COMPLETION_FIELDS as $field)
                            @php $ok = (bool) ($checks[$field] ?? false); @endphp
                            <li class="flex items-center justify-between gap-2">
                                <span class="text-hb-text">{{ __('candidate.analytics.field.'.$field) }}</span>
                                <span class="{{ $ok ? 'text-hb-success' : 'text-hb-muted' }}" aria-hidden="true">{{ $ok ? '✓' : '–' }}</span>
                                <span class="sr-only">{{ $ok ? __('Approved') : __('Pending') }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('frontend.candidate.profile') }}" class="mt-4 text-sm font-semibold text-hb-blue hover:underline">{{ __('candidate.dashboard.finish_profile') }}</a>
                </div>
            @endif
        </x-card>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        {{-- Pipeline donut --}}
        <x-card data-analytics-panel="pipeline">
            <x-slot:header>
                <h3 class="text-base font-bold text-hb-navy">{{ __('candidate.analytics.pipeline_title') }}</h3>
                <p class="mt-0.5 text-xs text-hb-muted">{{ __('candidate.analytics.pipeline_subtitle') }}</p>
            </x-slot:header>

            <div data-pipeline-unavailable @class(['py-10 text-center text-sm text-hb-muted', 'hidden' => (bool) ($pipeline['available'] ?? false)]) role="status">
                {{ __('candidate.analytics.pipeline_unavailable') }}
            </div>
            <div data-pipeline-empty @class(['py-10 text-center', 'hidden' => ! (($pipeline['available'] ?? false) && ($pipeline['empty'] ?? false))]) role="status">
                <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.analytics.pipeline_empty') }}</p>
                <a href="{{ route('frontend.candidate.vacancies.index') }}" class="mt-3 inline-flex text-sm font-semibold text-hb-blue hover:underline">{{ __('Discover jobs') }}</a>
            </div>
            <div data-pipeline-chart-wrap @class(['hidden' => ! (($pipeline['available'] ?? false) && ! ($pipeline['empty'] ?? true))])>
                <div data-pipeline-donut class="min-h-[200px]"></div>
                <ul class="mt-4 space-y-2 text-sm" data-pipeline-legend>
                    @foreach (($pipeline['segments'] ?? []) as $seg)
                        @php
                            $key = (string) ($seg['key'] ?? 'unknown');
                            $count = (int) ($seg['count'] ?? 0);
                            $label = $statusLabels[$key] ?? $key;
                        @endphp
                        <li class="flex items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-2 text-hb-text">
                                <span class="h-2.5 w-2.5 rounded-full bg-hb-blue" aria-hidden="true"></span>
                                {{ $label }}
                                @if (! ($seg['known'] ?? true) && ! empty($seg['statuses']))
                                    <span class="text-xs text-hb-muted">({{ implode(', ', $seg['statuses']) }})</span>
                                @endif
                            </span>
                            <span class="font-semibold text-hb-navy tabular-nums">{{ $count }}</span>
                        </li>
                    @endforeach
                </ul>
                @if (($pipeline['available'] ?? false) && ! ($pipeline['reconciled'] ?? true))
                    <p class="mt-3 text-xs text-hb-pending" role="status">{{ __('candidate.analytics.pipeline_reconcile_note') }}</p>
                @endif
                {{-- Accessible table alternative --}}
                <table class="sr-only">
                    <caption>{{ __('candidate.analytics.pipeline_title') }}</caption>
                    <thead><tr><th>{{ __('candidate.analytics.pipeline_subtitle') }}</th><th>{{ __('candidate.analytics.pipeline_total') }}</th></tr></thead>
                    <tbody>
                        @foreach (($pipeline['segments'] ?? []) as $seg)
                            <tr>
                                <td>{{ $statusLabels[$seg['key'] ?? 'unknown'] ?? ($seg['key'] ?? '') }}</td>
                                <td>{{ (int) ($seg['count'] ?? 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>

        {{-- Interview timeline --}}
        <x-card class="lg:col-span-2" data-analytics-panel="timeline">
            <x-slot:header>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h3 class="text-base font-bold text-hb-navy">{{ __('candidate.analytics.timeline_title') }}</h3>
                        <p class="mt-0.5 text-xs text-hb-muted">{{ __('candidate.analytics.timeline_subtitle') }}</p>
                    </div>
                    <a href="{{ route('frontend.candidate.interviews.index') }}" class="text-sm font-semibold text-hb-blue hover:underline">{{ __('Interviews') }}</a>
                </div>
            </x-slot:header>

            @if (! ($timeline['available'] ?? false))
                <p class="py-10 text-center text-sm text-hb-muted" role="status">{{ __('candidate.analytics.timeline_unavailable') }}</p>
            @elseif ($timeline['empty'] ?? true)
                <div class="py-10 text-center" role="status">
                    <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.analytics.timeline_empty') }}</p>
                    <a href="{{ route('frontend.candidate.interviews.index') }}" class="mt-3 inline-flex text-sm font-semibold text-hb-blue hover:underline">{{ __('Interviews') }}</a>
                </div>
            @else
                <ol class="relative space-y-0 border-s border-hb-border ms-3" data-interview-timeline>
                    @foreach (($timeline['items'] ?? []) as $item)
                        @php
                            $start = data_get($item, 'start_at');
                            $when = is_string($start) ? \App\Support\Rows::date($start) : '—';
                            $title = data_get($item, 'job_title') ?: __('Interviews');
                            $company = data_get($item, 'company_name');
                            $online = (bool) data_get($item, 'is_online', false);
                        @endphp
                        <li class="relative pb-6 ps-6 last:pb-0">
                            <span class="absolute -start-[5px] top-1.5 h-2.5 w-2.5 rounded-full bg-hb-blue ring-4 ring-hb-surface" aria-hidden="true"></span>
                            <p class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ $when }}</p>
                            <p class="mt-1 text-sm font-semibold text-hb-navy">{{ $title }}</p>
                            <p class="mt-0.5 text-sm text-hb-muted">
                                @if ($company){{ $company }} · @endif
                                {{ $online ? __('candidate.analytics.timeline_online') : __('candidate.analytics.timeline_onsite') }}
                                · {{ data_get($item, 'status') }}
                            </p>
                        </li>
                    @endforeach
                </ol>
            @endif
        </x-card>
    </div>
</section>

<section aria-labelledby="candidate-quick-actions-heading">
    <h2 id="candidate-quick-actions-heading" class="mb-4 text-lg font-semibold tracking-tight text-hb-navy">{{ __('candidate.kpi.quick_actions') }}</h2>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <x-stat-card :label="__('Discover jobs')" value="→" :meta="__('Browse open vacancies')" :href="route('frontend.candidate.vacancies.index')" />
        <x-stat-card :label="__('My applications')" value="→" :meta="__('Track your applications')" :href="route('frontend.candidate.applications.index')" />
        <x-stat-card :label="__('Assessments')" value="→" :meta="__('Take your exams')" :href="route('frontend.candidate.exams.index')" />
        <x-stat-card :label="__('Interviews')" value="→" :meta="__('Book and manage interviews')" :href="route('frontend.candidate.interviews.index')" />
        <x-stat-card :label="__('Offers')" value="→" :meta="__('Review your offers')" :href="route('frontend.candidate.offers.index')" />
        <x-stat-card :label="__('Profile & resume')" value="→" :meta="__('Keep your profile up to date')" :href="route('frontend.candidate.profile')" />
    </div>
</section>
@endsection
