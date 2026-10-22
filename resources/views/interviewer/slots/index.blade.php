@extends('layouts.app')

@section('title', $pageTitle ?? __('interviewer.schedule.page_title'))
@section('breadcrumb', __('interviewer.schedule.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ __('interviewer.schedule.title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ $schedule['interviewer'] }} · {{ $schedule['subtitle_meta'] }}</p>
@endsection

@section('page_actions')
    <button
        type="button"
        class="hb-btn-primary"
        disabled
        title="{{ __('interviewer.schedule.availability_soon') }}"
        aria-disabled="true"
    >
        {{ __('interviewer.schedule.edit_availability') }}
    </button>
@endsection

@section('content')
    @php
        $scheduleI18n = [
            'week_nav_status' => __('interviewer.schedule.js.week_nav_status'),
            'empty_week' => __('interviewer.schedule.js.empty_week'),
            'empty_filter' => __('interviewer.schedule.js.empty_filter'),
            'available' => __('interviewer.schedule.js.available'),
            'selected' => __('interviewer.schedule.js.selected'),
            'brief_soon' => __('interviewer.schedule.brief_soon'),
            'meeting_soon' => __('interviewer.schedule.meeting_soon'),
            'scorecard_soon' => __('interviewer.schedule.scorecard_soon'),
            'open_scorecard' => __('interviewer.evaluations.open_scorecard'),
        ];
        $selected = $schedule['selected'] ?? null;
    @endphp

    <div
        data-interviewer-schedule
        data-i18n="{{ json_encode($scheduleI18n, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-interviews="{{ json_encode($schedule['interviews'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-upcoming="{{ json_encode($schedule['upcoming'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-past="{{ json_encode($schedule['past'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-detail-rows="{{ json_encode($schedule['detail_rows'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-weeks="{{ json_encode($schedule['weeks'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-current-week-id="{{ $schedule['current_week_id'] }}"
        data-selected-id="{{ $selected['id'] ?? '' }}"
        data-availability-key="hb_interviewer_availability_prefs_v1"
    >
        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center">
            <label class="relative min-w-0 flex-1">
                <span class="sr-only">{{ __('interviewer.schedule.search_placeholder') }}</span>
                <input type="search" data-sched-q class="hb-input w-full" placeholder="{{ __('interviewer.schedule.search_placeholder') }}" autocomplete="off">
            </label>
            <div class="flex flex-wrap gap-2">
                <label class="sr-only" for="sched-job">{{ __('interviewer.schedule.filter_job') }}</label>
                <select id="sched-job" data-sched-job class="hb-input w-auto min-w-[8rem]">
                    <option value="">{{ __('interviewer.schedule.filter_job') }}</option>
                    <option value="ns-204">NS-204</option>
                    <option value="ns-205">NS-205</option>
                </select>
                <label class="sr-only" for="sched-type">{{ __('interviewer.schedule.filter_type') }}</label>
                <select id="sched-type" data-sched-type class="hb-input w-auto min-w-[9rem]">
                    <option value="">{{ __('interviewer.schedule.filter_type') }}</option>
                    <option value="portfolio">Portfolio</option>
                    <option value="team">Team</option>
                    <option value="collaboration">Collaboration</option>
                </select>
                <button type="button" class="hb-btn-outline" data-sched-clear>{{ __('interviewer.schedule.clear_filters') }}</button>
            </div>
        </div>

        <div class="mb-5 border-b border-hb-border" role="tablist" aria-label="{{ __('interviewer.schedule.title') }}">
            <div class="flex flex-wrap gap-1">
                <button type="button" class="border-b-2 border-hb-blue px-3 py-2 text-sm font-semibold text-hb-blue" role="tab" aria-selected="true" data-sched-tab="week" id="tab-week">{{ __('interviewer.schedule.tab_week') }}</button>
                <button type="button" class="border-b-2 border-transparent px-3 py-2 text-sm font-medium text-hb-muted hover:text-hb-navy" role="tab" aria-selected="false" data-sched-tab="upcoming" id="tab-upcoming">{{ __('interviewer.schedule.tab_upcoming') }}</button>
                <button type="button" class="border-b-2 border-transparent px-3 py-2 text-sm font-medium text-hb-muted hover:text-hb-navy" role="tab" aria-selected="false" data-sched-tab="past" id="tab-past">{{ __('interviewer.schedule.tab_past') }}</button>
            </div>
        </div>

        <p class="mb-4 hidden text-sm text-hb-muted" data-sched-empty role="status"></p>

        <div data-sched-panel="week" role="tabpanel" aria-labelledby="tab-week">
            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_280px]">
                <div class="min-w-0 space-y-6">
                    <section class="hb-surface p-5 sm:p-6" aria-labelledby="week-heading">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h2 id="week-heading" class="text-base font-semibold text-hb-navy" data-week-heading>{{ $schedule['week_label'] }}</h2>
                                <p class="mt-1 text-xs text-hb-muted">{{ $schedule['timezone'] }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2" role="group" aria-label="{{ __('interviewer.schedule.week_nav_aria') }}">
                                <button type="button" class="hb-btn-outline px-3 py-2" data-week-nav="prev" aria-label="{{ __('interviewer.schedule.previous_week') }}">‹</button>
                                <button type="button" class="hb-btn-outline px-3 py-2" data-week-today>{{ __('interviewer.schedule.today') }}</button>
                                <button type="button" class="hb-btn-outline px-3 py-2" data-week-nav="next" aria-label="{{ __('interviewer.schedule.next_week') }}">›</button>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-hb-muted" data-week-nav-status role="status"></p>
                        <div class="mt-5 grid gap-2 sm:grid-cols-5" data-sched-grid></div>
                        <p class="mt-4 hidden text-sm text-hb-muted" data-week-empty role="status"></p>
                    </section>

                    <section class="hb-surface p-5 sm:p-6" aria-labelledby="detail-list-heading">
                        <h2 id="detail-list-heading" class="text-base font-semibold text-hb-navy">{{ __('interviewer.schedule.detail_heading') }}</h2>
                        <ul class="mt-4 divide-y divide-hb-border" data-detail-list>
                            @foreach ($schedule['detail_rows'] as $row)
                                <li class="py-4 first:pt-0 last:pb-0" data-detail-row data-interview-id="{{ $row['id'] }}" data-job-id="{{ $row['job_id'] }}" data-type-key="{{ $row['type_key'] }}" data-applicant="{{ strtolower($row['applicant']) }}">
                                    <p class="text-sm font-semibold text-hb-navy">
                                        {{ $row['day_key'] === '2026-10-22' ? 'Oct 22' : 'Oct 23' }} · {{ $row['time_short'] }}
                                    </p>
                                    <dl class="mt-2 space-y-1 text-sm">
                                        <div>
                                            <dt class="inline text-hb-muted">{{ __('interviewer.schedule.candidate_role') }}:</dt>
                                            <dd class="inline text-hb-text"> {{ $row['applicant'] }} · {{ $row['job_code'] }} · {{ $row['job_title'] }}</dd>
                                        </div>
                                        <div>
                                            <dt class="inline text-hb-muted">{{ __('interviewer.schedule.type_location') }}:</dt>
                                            <dd class="inline text-hb-text"> {{ ($row['type_key'] === 'portfolio' ? 'Portfolio' : $row['title']) }} · {{ $row['location'] }}</dd>
                                        </div>
                                        <div>
                                            <dt class="inline text-hb-muted">{{ __('interviewer.schedule.preparation') }}:</dt>
                                            <dd class="inline text-hb-text"> {{ $row['preparation'] }}</dd>
                                        </div>
                                    </dl>
                                    <div class="mt-3 flex flex-wrap gap-3 text-sm">
                                        <a href="{{ route('interviewer.interviews.prepare', $row['id']) }}" class="font-semibold text-hb-blue hover:underline">{{ __('interviewer.schedule.open_brief') }}</a>
                                        <button type="button" class="font-semibold text-hb-blue hover:underline" disabled title="{{ __('interviewer.schedule.meeting_soon') }}">{{ __('interviewer.schedule.meeting_details') }}</button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </section>

                    <section class="hb-surface p-5 sm:p-6" aria-labelledby="meeting-access-heading">
                        <h2 id="meeting-access-heading" class="text-base font-semibold text-hb-navy">{{ __('interviewer.schedule.meeting_access_title') }}</h2>
                        <p class="mt-2 text-sm text-hb-muted">{{ $schedule['meeting_access']['body'] }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <button type="button" class="hb-btn-outline" disabled title="{{ __('interviewer.schedule.contact_soon') }}">{{ __('interviewer.schedule.contact_coordinator') }}</button>
                            <button type="button" class="hb-btn-outline" disabled title="{{ __('interviewer.schedule.calendar_soon') }}">{{ __('interviewer.schedule.review_calendar') }}</button>
                        </div>
                    </section>
                </div>

                <aside>
                    <section class="hb-surface p-5" aria-labelledby="selected-interview-heading" data-selected-panel>
                        <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('interviewer.schedule.selected_heading') }}</p>
                        @if ($selected)
                            <div class="mt-3">
                                <x-badge variant="info" :dot="true" data-sel-status>{{ $selected['status'] }}</x-badge>
                            </div>
                            <h2 id="selected-interview-heading" class="mt-3 text-lg font-semibold text-hb-navy" data-sel-name>{{ $selected['applicant'] }}</h2>
                            <dl class="mt-4 space-y-2 text-sm">
                                <div>
                                    <dt class="text-xs text-hb-muted">{{ __('interviewer.schedule.job_label') }}</dt>
                                    <dd class="font-medium text-hb-text" data-sel-job>{{ $selected['job_title'] }} · {{ $selected['job_code'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-hb-muted">{{ __('interviewer.schedule.when_label') }}</dt>
                                    <dd class="text-hb-text" data-sel-when>{{ $selected['when'] }} · {{ $selected['time'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-hb-muted">{{ __('interviewer.schedule.type_label') }}</dt>
                                    <dd class="text-hb-text" data-sel-type>{{ $selected['title'] }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-hb-muted">{{ __('interviewer.schedule.preparation') }}</dt>
                                    <dd class="text-hb-text" data-sel-prep>{{ $selected['preparation'] }}</dd>
                                </div>
                            </dl>
                            <div class="mt-5 flex flex-wrap gap-2">
                                <a
                                    href="{{ route('interviewer.interviews.prepare', $selected['id']) }}"
                                    class="hb-btn-primary"
                                    data-sel-prepare
                                >
                                    {{ __('interviewer.dashboard.action_prepare') }}
                                </a>
                                <a
                                    href="{{ route('interviewer.interviews.live', $selected['id']) }}"
                                    class="hb-btn-outline"
                                    data-sel-live
                                >
                                    {{ __('interviewer.prepare.start_interview') }}
                                </a>
                            </div>
                        @endif
                    </section>

                    <section class="hb-surface mt-6 p-5" aria-labelledby="local-availability-heading">
                        <h2 id="local-availability-heading" class="text-base font-semibold text-hb-navy">{{ __('interviewer.dashboard.availability_heading') }}</h2>
                        <ul class="mt-4 space-y-3" data-local-availability>
                            @foreach ($schedule['availability_local'] as $window)
                                <li>
                                    <label class="flex cursor-pointer items-start gap-3 text-sm">
                                        <input
                                            type="checkbox"
                                            class="mt-0.5 h-4 w-4 rounded border-hb-border text-hb-blue focus:ring-hb-blue/40"
                                            data-avail-day="{{ $window['day'] }}"
                                            @checked(! empty($window['selected']))
                                            aria-label="{{ __('interviewer.schedule.availability_toggle') }} · {{ $window['day'] }}"
                                        >
                                        <span>
                                            <span class="font-semibold text-hb-navy">{{ $window['day'] }}</span>
                                            <span class="text-hb-muted"> · {{ $window['windows'] }}</span>
                                        </span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                        <p class="mt-3 text-xs text-hb-muted" data-avail-note role="status">{{ __('interviewer.schedule.availability_local_note') }}</p>
                    </section>
                </aside>
            </div>
        </div>

        <div class="hidden" data-sched-panel="upcoming" role="tabpanel" aria-labelledby="tab-upcoming" hidden>
            <section class="hb-surface p-5 sm:p-6">
                <ul class="divide-y divide-hb-border" data-upcoming-list></ul>
                <p class="mt-4 hidden text-sm text-hb-muted" data-upcoming-empty role="status">{{ __('interviewer.schedule.empty_upcoming') }}</p>
            </section>
        </div>

        <div class="hidden" data-sched-panel="past" role="tabpanel" aria-labelledby="tab-past" hidden>
            <section class="hb-surface p-5 sm:p-6">
                <ul class="divide-y divide-hb-border" data-past-list></ul>
                <p class="mt-4 hidden text-sm text-hb-muted" data-past-empty role="status">{{ __('interviewer.schedule.empty_past') }}</p>
            </section>
        </div>

        <p class="mt-8 text-center text-xs text-hb-muted">{{ $schedule['footer'] }}</p>
    </div>
@endsection
