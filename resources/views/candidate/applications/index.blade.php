@extends('layouts.app')

@section('title', $pageTitle ?? 'My applications · Hirebridge')
@section('breadcrumb', '18 / My workspace / My applications')

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">My applications</h1>
    <p class="mt-1 text-sm text-hb-muted">Every conversation, document, and next step in one place.</p>
@endsection

@section('content')
    @php
        $featured = collect($activeApplications)->firstWhere('featured', true);
        $compactActive = collect($activeApplications)->where('featured', false)->values();
    @endphp

    <div class="hb-mock-banner mb-6 rounded-lg text-start">
        Frontend UI only — application stages and activity are mock presentation data.
    </div>

    <div data-candidate-applications data-tabs>
        <div class="border-b border-hb-border" role="tablist" aria-label="Application lists">
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
                    Active ({{ count($activeApplications) }})
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
                    Archived ({{ count($archivedApplications) }})
                </button>
            </div>
        </div>

        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center">
            <label class="relative min-w-0 flex-1">
                <span class="sr-only">Search role or company</span>
                <span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-hb-muted" aria-hidden="true">⌕</span>
                <input
                    type="search"
                    class="hb-input ps-9"
                    placeholder="Search role or company"
                    data-apps-search
                    autocomplete="off"
                >
            </label>
            <div class="flex flex-wrap gap-2">
                <label class="sr-only" for="apps-stage-filter">Filter by stage</label>
                <select id="apps-stage-filter" class="hb-input w-auto min-w-[9rem]" data-apps-stage>
                    <option value="all">All stages</option>
                    <option value="applied">Applied</option>
                    <option value="screening">Screening</option>
                    <option value="interview">Interview</option>
                    <option value="offer">Offer</option>
                    <option value="withdrawn">Withdrawn</option>
                </select>
                <label class="sr-only" for="apps-sort">Sort applications</label>
                <select id="apps-sort" class="hb-input w-auto min-w-[10rem]" data-apps-sort>
                    <option value="latest">Latest activity</option>
                    <option value="company">Company A–Z</option>
                    <option value="stage">Stage</option>
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
                                {{ $featured['code'] }} · {{ $featured['source'] }} · Applied {{ $featured['applied_on'] }}
                            </p>
                        </div>
                        <x-badge variant="info" :dot="true">{{ $featured['stage'] }}</x-badge>
                    </div>

                    <div class="mt-5">
                        <x-timeline :steps="$featured['pipeline']" />
                    </div>

                    <p class="mt-4 text-sm text-hb-text">
                        <span class="font-medium text-hb-navy">Next:</span>
                        {{ $featured['next_step'] }}
                    </p>

                    <div class="mt-5">
                        <a href="{{ route('candidate.applications.show', $featured['id']) }}" class="hb-btn-outline text-hb-blue">
                            View application
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
                                        Stage: {{ $app['stage'] }} · {{ $app['source'] }}
                                    </p>
                                    <p class="mt-1 text-sm text-hb-muted">Last activity: {{ $app['last_activity'] }}</p>
                                    <p class="mt-1 text-sm text-hb-text">Next step: {{ $app['next_step'] }}</p>
                                </div>
                                @if (($app['stage_key'] ?? '') === 'offer')
                                    <a href="{{ route('candidate.offers.show', $app['id']) }}" class="text-sm font-semibold text-hb-blue hover:underline">
                                        Review offer
                                    </a>
                                @else
                                    <a href="{{ route('candidate.applications.show', $app['id']) }}" class="text-sm font-semibold text-hb-blue hover:underline">
                                        View
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

            <div class="hb-surface hidden px-5 py-10 text-center" data-apps-empty="active" role="status" hidden>
                <p class="font-semibold text-hb-navy">No active applications match</p>
                <p class="mt-2 text-sm text-hb-muted">Try another stage filter or clear your search.</p>
                <a href="{{ route('candidate.jobs.index') }}" class="hb-btn-primary mt-4 inline-flex">Discover jobs</a>
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
                <h2 class="text-base font-semibold text-hb-navy">Archived application</h2>
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
                                View application
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>

            <div class="hb-surface mt-4 hidden px-5 py-10 text-center" data-apps-empty="archived" role="status" hidden>
                <p class="font-semibold text-hb-navy">No archived applications match</p>
                <p class="mt-2 text-sm text-hb-muted">Adjust your search or stage filter.</p>
            </div>
        </div>
    </div>

    <p class="mt-6 text-sm text-hb-muted">
        Stages are updated by each employer or assigned recruiter. An offer from one employer does not change your other applications.
    </p>
    <p class="mt-4 text-xs text-hb-muted">Hirebridge · Private workspace · All times Central unless noted · October 20, 2026</p>
@endsection
