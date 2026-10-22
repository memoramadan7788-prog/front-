@extends('layouts.app')

@section('title', $pageTitle ?? __('candidate.applications.index_page_title'))
@section('breadcrumb', __('candidate.workspace') . ' / ' . __('navigation.candidate.applications'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.applications.index_title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.applications.index_intro') }}</p>
@endsection

@section('content')
    @php
        $featured = collect($activeApplications)->firstWhere('featured', true);
        $compactActive = collect($activeApplications)->where('featured', false)->values();
    @endphp


    <div data-candidate-applications data-tabs>
        <div class="border-b border-hb-border" role="tablist" aria-label="{{ __('candidate.applications.tablist_aria') }}">
            <div class="flex flex-wrap gap-1">
                <button
                    type="button"
                    id="apps-tab-active"
                    role="tab"
                    aria-selected="true"
                    aria-controls="apps-panel-active"
                    tabindex="0"
                    class="border-b-2 border-hb-blue px-3 py-2.5 text-sm font-semibold text-hb-blue"
                    data-apps-tab="active"
                >
                    {{ __('candidate.applications.active', ['count' => count($activeApplications)]) }}
                </button>
                <button
                    type="button"
                    id="apps-tab-archived"
                    role="tab"
                    aria-selected="false"
                    aria-controls="apps-panel-archived"
                    tabindex="-1"
                    class="border-b-2 border-transparent px-3 py-2.5 text-sm font-semibold text-hb-muted"
                    data-apps-tab="archived"
                >
                    {{ __('candidate.applications.archived', ['count' => count($archivedApplications)]) }}
                </button>
            </div>
        </div>

        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center">
            <label class="relative min-w-0 flex-1">
                <span class="sr-only">{{ __('candidate.applications.search') }}</span>
                <span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-hb-muted" aria-hidden="true">⌕</span>
                <input
                    type="search"
                    class="hb-input ps-9"
                    placeholder="{{ __('candidate.applications.search') }}"
                    data-apps-search
                    autocomplete="off"
                >
            </label>
            <div class="flex flex-wrap gap-2">
                <label class="sr-only" for="apps-stage-filter">{{ __('candidate.applications.filter_stage') }}</label>
                <select id="apps-stage-filter" class="hb-input w-auto min-w-[9rem]" data-apps-stage>
                    <option value="all">{{ __('candidate.applications.all_stages') }}</option>
                    <option value="applied">{{ __('candidate.applications.stage_applied') }}</option>
                    <option value="screening">{{ __('candidate.applications.stage_screening') }}</option>
                    <option value="interview">{{ __('candidate.applications.stage_interview') }}</option>
                    <option value="offer">{{ __('candidate.applications.stage_offer') }}</option>
                    <option value="withdrawn">{{ __('candidate.applications.stage_withdrawn') }}</option>
                </select>
                <label class="sr-only" for="apps-sort">{{ __('candidate.applications.sort_apps') }}</label>
                <select id="apps-sort" class="hb-input w-auto min-w-[10rem]" data-apps-sort>
                    <option value="latest">{{ __('candidate.applications.latest_activity') }}</option>
                    <option value="company">{{ __('candidate.applications.sort_company') }}</option>
                    <option value="stage">{{ __('candidate.applications.sort_stage') }}</option>
                </select>
            </div>
        </div>

        <div
            id="apps-panel-active"
            role="tabpanel"
            aria-labelledby="apps-tab-active"
            class="mt-5 space-y-4"
            data-apps-panel="active"
        >
            @if ($featured)
                <article
                    class="hb-surface p-5 sm:p-6"
                    data-app-card
                    data-app-scope="active"
                    data-title="{{ strtolower($featured['title'].' '.$featured['company'].' '.$featured['code']) }}"
                    data-stage="{{ $featured['stage_key'] }}"
                    data-company="{{ strtolower($featured['company']) }}"
                    data-activity="{{ $featured['last_activity_sort'] }}"
                >
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="text-lg font-semibold text-hb-navy">
                                {{ $featured['title'] }} · {{ $featured['company'] }}
                            </h2>
                            <p class="mt-1 text-sm text-hb-muted">
                                {{ $featured['code'] }} · {{ $featured['source'] }} · {{ __('candidate.applications.applied_on', ['date' => $featured['applied_on']]) }}
                            </p>
                        </div>
                        <x-badge variant="info" :dot="true">{{ $featured['stage'] }}</x-badge>
                    </div>

                    <div class="mt-5">
                        <x-timeline :steps="$featured['pipeline']" />
                    </div>

                    <p class="mt-4 text-sm text-hb-text">
                        <span class="font-medium text-hb-navy">{{ __('candidate.applications.next_label') }}</span>
                        {{ $featured['next_step'] }}
                    </p>

                    <div class="mt-5">
                        <a href="{{ route('candidate.applications.show', $featured['id']) }}" class="hb-btn-outline text-hb-blue">
                            {{ __('candidate.applications.view_application') }}
                        </a>
                    </div>
                </article>
            @endif

            @if ($compactActive->isNotEmpty())
                <div class="hb-surface overflow-hidden" data-apps-compact-list>
                    @foreach ($compactActive as $app)
                        <article
                            class="border-b border-hb-border px-5 py-4 last:border-b-0"
                            data-app-card
                            data-app-scope="active"
                            data-title="{{ strtolower($app['title'].' '.$app['company'].' '.$app['code']) }}"
                            data-stage="{{ $app['stage_key'] }}"
                            data-company="{{ strtolower($app['company']) }}"
                            data-activity="{{ $app['last_activity_sort'] }}"
                        >
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="text-base font-semibold text-hb-navy">
                                        <a href="{{ route('candidate.applications.show', $app['id']) }}" class="hover:text-hb-blue">
                                            {{ $app['title'] }} · {{ $app['company'] }} · {{ $app['code'] }}
                                        </a>
                                    </h3>
                                    <p class="mt-1 text-sm text-hb-muted">
                                        {{ __('candidate.applications.stage_label', ['stage' => $app['stage']]) }} · {{ $app['source'] }}
                                    </p>
                                    <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.applications.last_activity', ['date' => $app['last_activity']]) }}</p>
                                    <p class="mt-1 text-sm text-hb-text">{{ __('candidate.applications.next_step', ['step' => $app['next_step']]) }}</p>
                                </div>
                                @if (($app['stage_key'] ?? '') === 'offer')
                                    <a href="{{ route('candidate.offers.show', $app['offer_id'] ?? $app['id']) }}" class="text-sm font-semibold text-hb-blue hover:underline">
                                        {{ __('candidate.applications.review_offer') }}
                                    </a>
                                @else
                                    <a href="{{ route('candidate.applications.show', $app['id']) }}" class="text-sm font-semibold text-hb-blue hover:underline">
                                        {{ __('candidate.applications.view') }}
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

            <div class="hb-surface hidden px-5 py-10 text-center" data-apps-empty="active" role="status" hidden>
                <p class="font-semibold text-hb-navy">{{ __('candidate.applications.empty_active_title') }}</p>
                <p class="mt-2 text-sm text-hb-muted">{{ __('candidate.applications.empty_active_body') }}</p>
                <a href="{{ route('candidate.jobs.index') }}" class="hb-btn-primary mt-4 inline-flex">{{ __('candidate.dashboard.discover_jobs') }}</a>
            </div>
        </div>

        <div
            id="apps-panel-archived"
            role="tabpanel"
            aria-labelledby="apps-tab-archived"
            class="mt-5 hidden"
            data-apps-panel="archived"
            hidden
        >
            <div class="hb-surface p-5 sm:p-6">
                <h2 class="text-base font-semibold text-hb-navy">{{ __('candidate.applications.archived_heading') }}</h2>
                <div class="mt-4 space-y-4">
                    @foreach ($archivedApplications as $app)
                        <article
                            class="rounded-lg border border-hb-border px-4 py-4"
                            data-app-card
                            data-app-scope="archived"
                            data-title="{{ strtolower($app['title'].' '.$app['company'].' '.$app['code']) }}"
                            data-stage="{{ $app['stage_key'] }}"
                            data-company="{{ strtolower($app['company']) }}"
                            data-activity="{{ $app['last_activity_sort'] }}"
                        >
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-semibold text-hb-navy">
                                        {{ $app['title'] }} · {{ $app['company'] }} · {{ $app['code'] }}
                                    </h3>
                                    <p class="mt-2 text-sm text-hb-muted">{{ $app['archived_note'] ?? $app['next_step'] }}</p>
                                </div>
                                <x-badge variant="neutral" :dot="true">{{ $app['stage'] }}</x-badge>
                            </div>
                            <a href="{{ route('candidate.applications.show', $app['id']) }}" class="mt-3 inline-flex text-sm font-semibold text-hb-blue hover:underline">
                                {{ __('candidate.applications.view_application') }}
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>

            <div class="hb-surface mt-4 hidden px-5 py-10 text-center" data-apps-empty="archived" role="status" hidden>
                <p class="font-semibold text-hb-navy">{{ __('candidate.applications.empty_archived_title') }}</p>
                <p class="mt-2 text-sm text-hb-muted">{{ __('candidate.applications.empty_archived_body') }}</p>
            </div>
        </div>
    </div>

    <p class="mt-6 text-sm text-hb-muted">
        {{ __('candidate.applications.footer_note') }}
    </p>
    <p class="mt-4 text-xs text-hb-muted">{{ __('common.footer.workspace_note', ['date' => __('candidate.footer_date')]) }}</p>
@endsection
