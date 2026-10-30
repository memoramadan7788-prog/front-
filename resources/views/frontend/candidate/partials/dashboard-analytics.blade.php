{{-- Candidate dashboard analytics — KPI-first companion charts (real data only). --}}
<section
    class="hb-dashboard-analytics"
    aria-labelledby="candidate-analytics-heading"
    data-candidate-analytics="{{ e(json_encode($analyticsPayload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) }}"
>
    <div class="mb-2 flex flex-wrap items-end justify-between gap-2">
        <div>
            <h2 id="candidate-analytics-heading" class="text-base font-semibold tracking-tight text-hb-navy">{{ __('candidate.analytics.section_title') }}</h2>
            <p class="mt-0.5 text-xs text-hb-muted">{{ __('candidate.analytics.section_subtitle') }}</p>
        </div>
    </div>

    {{-- Row 1: major trend + status breakdown (reference composition ~2/3 + 1/3) --}}
    <div class="grid gap-3 lg:grid-cols-3">
        <x-card class="lg:col-span-2" data-analytics-panel="activity">
            <x-slot:header>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-hb-navy">{{ __('candidate.analytics.activity_title') }}</h3>
                        <p class="mt-0.5 text-xs text-hb-muted">{{ __('candidate.analytics.activity_subtitle') }}</p>
                    </div>
                    <div class="inline-flex rounded-lg border border-hb-border bg-hb-page/60 p-0.5" role="group" aria-label="{{ __('candidate.analytics.sr_period_group') }}">
                        @foreach ([7, 30, 90] as $days)
                            <button
                                type="button"
                                data-activity-period="{{ $days }}"
                                class="rounded-md px-2.5 py-1 text-xs font-semibold transition"
                                aria-pressed="false"
                            >{{ __('candidate.analytics.period_'.$days) }}</button>
                        @endforeach
                    </div>
                </div>
            </x-slot:header>

            <div data-activity-unavailable @class(['py-4 text-center', 'hidden' => (bool) ($activity['available'] ?? false)]) role="status">
                <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.analytics.historical_unavailable') }}</p>
                @if ($activityUnavailableMsg !== __('candidate.analytics.historical_unavailable'))
                    <p class="mt-1 text-xs text-hb-muted">{{ $activityUnavailableMsg }}</p>
                @endif
            </div>
            <div data-activity-empty @class(['py-4 text-center', 'hidden' => ! (($activity['available'] ?? false) && ($activity['empty'] ?? false))]) role="status">
                <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.analytics.activity_empty') }}</p>
                <p class="mt-1 text-xs text-hb-muted">{{ __('candidate.analytics.activity_empty_hint') }}</p>
                <a href="{{ route('frontend.candidate.vacancies.index') }}" class="mt-2 inline-flex text-sm font-semibold text-hb-blue hover:underline">{{ __('Discover jobs') }}</a>
            </div>
            <div data-activity-chart-wrap @class(['hidden' => ! (($activity['available'] ?? false) && ! ($activity['empty'] ?? true))])>
                <p data-activity-summary class="mb-2 text-xs text-hb-muted" aria-live="polite"></p>
                <div data-activity-chart class="min-h-[200px] w-full sm:min-h-[240px]"></div>
                <noscript>
                    <p class="text-sm text-hb-muted">{{ __('candidate.analytics.activity_subtitle') }}</p>
                </noscript>
            </div>
        </x-card>

        <x-card data-analytics-panel="pipeline">
            <x-slot:header>
                <h3 class="text-sm font-bold text-hb-navy">{{ __('candidate.analytics.pipeline_title') }}</h3>
                <p class="mt-0.5 text-xs text-hb-muted">{{ __('candidate.analytics.pipeline_subtitle') }}</p>
            </x-slot:header>

            <div data-pipeline-unavailable @class(['py-4 text-center text-sm text-hb-muted', 'hidden' => (bool) ($pipeline['available'] ?? false)]) role="status">
                {{ __('candidate.analytics.pipeline_unavailable') }}
            </div>
            <div data-pipeline-empty @class(['py-4 text-center', 'hidden' => ! (($pipeline['available'] ?? false) && ($pipeline['empty'] ?? false))]) role="status">
                <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.analytics.pipeline_empty') }}</p>
                <a href="{{ route('frontend.candidate.vacancies.index') }}" class="mt-2 inline-flex text-sm font-semibold text-hb-blue hover:underline">{{ __('Discover jobs') }}</a>
            </div>
            <div data-pipeline-chart-wrap @class(['hidden' => ! (($pipeline['available'] ?? false) && ! ($pipeline['empty'] ?? true))])>
                <div data-pipeline-donut class="min-h-[180px]"></div>
                <ul class="mt-3 space-y-1.5 text-sm" data-pipeline-legend>
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
                    <p class="mt-2 text-xs text-hb-pending" role="status">{{ __('candidate.analytics.pipeline_reconcile_note') }}</p>
                @endif
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
    </div>

    {{-- Profile completion only here — upcoming interviews live in the single Next Interview card (Task #12). --}}
    <div class="mt-3" data-analytics-panel="profile">
        <x-card>
            <x-slot:header>
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-bold text-hb-navy">{{ __('candidate.analytics.profile_ring_title') }}</h3>
                        <p class="mt-0.5 text-xs text-hb-muted">{{ __('candidate.analytics.profile_ring_subtitle') }}</p>
                    </div>
                    <a href="{{ route('frontend.candidate.profile') }}" class="text-sm font-semibold text-hb-blue hover:underline">{{ __('candidate.dashboard.finish_profile') }}</a>
                </div>
            </x-slot:header>

            @if (! ($profileRing['available'] ?? false))
                <p class="py-4 text-center text-sm text-hb-muted" role="status">{{ __('candidate.analytics.profile_ring_unavailable') }}</p>
            @else
                @php
                    $pct = (int) ($profileRing['percent'] ?? 0);
                    $filled = (int) ($profileRing['filled'] ?? 0);
                    $fields = (int) ($profileRing['total_fields'] ?? 7);
                    $checks = is_array($profileRing['checks'] ?? null) ? $profileRing['checks'] : [];
                    $r = 42;
                    $circ = 2 * M_PI * $r;
                @endphp
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center"
                    data-profile-ring
                    data-percent="{{ $pct }}"
                >
                    <svg class="mx-auto h-28 w-28 shrink-0 sm:mx-0" viewBox="0 0 120 120" role="img" aria-label="{{ __('candidate.kpi.profile_completion') }}: {{ $pct }}%">
                        <circle cx="60" cy="60" r="{{ $r }}" fill="none" stroke="currentColor" class="text-hb-page" stroke-width="10" />
                        <circle
                            data-profile-ring-progress
                            cx="60" cy="60" r="{{ $r }}"
                            fill="none"
                            stroke="currentColor"
                            class="text-hb-blue"
                            stroke-width="10"
                            stroke-linecap="round"
                            transform="rotate(-90 60 60)"
                            style="stroke-dasharray: {{ $circ }}; stroke-dashoffset: {{ $circ }};"
                        />
                        <text x="60" y="56" text-anchor="middle" class="fill-hb-navy" style="font-size: 22px; font-weight: 600;">{{ $pct }}%</text>
                        <text x="60" y="74" text-anchor="middle" class="fill-hb-muted" style="font-size: 10px;">{{ __('candidate.analytics.profile_fields_meta', ['filled' => $filled, 'total' => $fields]) }}</text>
                    </svg>
                    <ul class="grid w-full grid-cols-1 gap-x-4 gap-y-1 sm:grid-cols-2" data-profile-checklist>
                        @foreach (\App\Frontend\Candidate\CandidateDashboardAdapter::PROFILE_COMPLETION_FIELDS as $field)
                            @php
                                $ok = (bool) ($checks[$field] ?? false);
                                $fieldHref = $checklistLinks[$field] ?? route('frontend.candidate.profile');
                            @endphp
                            <li class="flex items-center justify-between gap-2 text-sm">
                                @if ($ok)
                                    <span class="text-hb-text">{{ __('candidate.analytics.field.'.$field) }}</span>
                                    <span class="text-hb-success" aria-hidden="true">✓</span>
                                    <span class="sr-only">{{ __('candidate.dashboard.checklist_complete') }}</span>
                                @else
                                    <a href="{{ $fieldHref }}" class="font-medium text-hb-blue hover:underline" data-profile-checklist-link="{{ $field }}">
                                        {{ __('candidate.analytics.field.'.$field) }}
                                    </a>
                                    <span class="text-hb-muted" aria-hidden="true">–</span>
                                    <span class="sr-only">{{ __('candidate.dashboard.checklist_incomplete') }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </x-card>
    </div>
</section>
