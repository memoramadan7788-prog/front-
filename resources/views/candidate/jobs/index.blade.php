@extends('layouts.app')

@section('title', $pageTitle ?? 'Discover jobs · Hirebridge')
@section('breadcrumb', '14 / My workspace / Discover jobs')

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">Work that fits your next chapter</h1>
    <p class="mt-1 text-sm text-hb-muted">
        Recommendations based on your design experience, salary range, and work preferences.
    </p>
@endsection

@section('page_actions')
    <a href="{{ route('candidate.profile') }}" class="hb-btn-primary">Edit preferences</a>
@endsection

@section('content')
    <div
        data-candidate-jobs
        data-candidate-jobs-mode="discover"
        data-default-saved='@json($defaultSavedIds)'
    >
        <div class="hb-mock-banner mb-6 rounded-lg text-start">
            Frontend UI only — recommendations, filters, and save/unsave use mock data in this browser. Nothing is stored on your account.
        </div>

        {{-- Search / filter bar --}}
        <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-center">
            <label class="relative min-w-0 flex-1">
                <span class="sr-only">Search roles</span>
                <input
                    type="search"
                    name="q"
                    value="{{ $preferences['search_default'] }}"
                    class="hb-input"
                    placeholder="Senior product design"
                    data-discover-q
                    autocomplete="off"
                >
            </label>
            <label class="shrink-0">
                <span class="sr-only">Location filter</span>
                <select class="hb-input min-w-44" data-discover-location aria-label="Location filter">
                    <option value="austin_remote" @selected($preferences['location_default'] === 'austin_remote')>Austin + Remote</option>
                    <option value="austin">Austin only</option>
                    <option value="remote">Remote only</option>
                    <option value="all">All locations</option>
                </select>
            </label>
            <label class="shrink-0">
                <span class="sr-only">Sort</span>
                <select class="hb-input min-w-40" data-discover-sort aria-label="Sort roles">
                    <option value="recommended" @selected($preferences['sort_default'] === 'recommended')>Recommended</option>
                    <option value="salary_desc">Salary · high to low</option>
                    <option value="newest">Newest first</option>
                    <option value="company">Company A–Z</option>
                </select>
            </label>
            <button type="button" class="shrink-0 text-sm font-semibold text-hb-blue hover:underline" data-save-search>
                Save this search
            </button>
        </div>

        <div
            class="mb-6 hidden rounded-lg border border-hb-info-soft bg-hb-info-soft px-3 py-2.5 text-sm text-hb-navy"
            data-save-search-note
            role="status"
        >
            Search criteria were not saved to your account — Backend Team alerts integration is required.
        </div>

        <div class="grid gap-6 xl:grid-cols-[280px_minmax(0,1fr)]">
            {{-- Your preferences --}}
            <aside class="hb-surface h-fit p-5" aria-label="Your preferences">
                <h2 class="text-sm font-semibold text-hb-navy">Your preferences</h2>
                <ul class="mt-4 space-y-2 text-sm text-hb-text">
                    @foreach ($preferences['work_models'] as $item)
                        <li class="flex gap-2">
                            <span class="text-hb-success" aria-hidden="true">✓</span>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>

                <div class="mt-4 rounded-lg border border-hb-border bg-hb-page px-3 py-2.5">
                    <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">Base salary</p>
                    <p class="mt-1 text-sm font-semibold text-hb-navy">{{ $preferences['salary'] }}</p>
                </div>

                <div class="mt-4">
                    <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">Core skills</p>
                    <ul class="mt-2 space-y-1.5 text-sm text-hb-text">
                        @foreach ($preferences['skills'] as $skill)
                            <li class="flex gap-2">
                                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-hb-blue" aria-hidden="true"></span>
                                {{ $skill }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <p class="mt-5 text-xs leading-relaxed text-hb-muted">{{ $preferences['note'] }}</p>
            </aside>

            {{-- Recommended roles --}}
            <div>
                <p class="mb-3 text-sm text-hb-muted">
                    <span data-jobs-count-label>{{ $preferences['recommended_count_label'] }}</span>
                    <span class="sr-only">(<span data-jobs-count>{{ count($jobs) }}</span> shown)</span>
                </p>

                <div class="hb-surface overflow-hidden" data-jobs-results>
                    @foreach ($jobs as $job)
                        @include('candidate.jobs._card', ['job' => $job])
                    @endforeach
                </div>

                <div class="hb-surface mt-4 hidden px-5 py-10 text-center" data-jobs-empty role="status">
                    <p class="font-semibold text-hb-navy">No roles match this search</p>
                    <p class="mt-2 text-sm text-hb-muted">Try clearing the keyword or broadening location.</p>
                    <button type="button" class="hb-btn-outline mt-4" data-clear-discover>Clear search</button>
                </div>

                <div class="hb-surface mt-4 p-5" aria-label="Recommendation feedback">
                    <h2 class="text-base font-semibold text-hb-navy">Help us improve your recommendations</h2>
                    <p class="mt-1 text-sm text-hb-muted">
                        Tell us what feels right so future matches stay closer to your preferences.
                    </p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ route('candidate.profile') }}" class="hb-btn-outline">Update preferences</a>
                        <button type="button" class="text-sm font-semibold text-hb-blue hover:underline" disabled title="Hide roles — Backend Team">
                            Hide irrelevant roles
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <p class="mt-8 text-xs text-hb-muted">
            Hirebridge · Private workspace · All times Central unless noted · October 20, 2026
        </p>
    </div>

    @isset($applyFlow)
        @include('candidate.applications.apply-modal', ['flow' => $applyFlow])
        <div class="sr-only">
            <button type="button" data-open-modal="candidate-apply-modal">Open apply modal</button>
        </div>
    @endisset
@endsection
