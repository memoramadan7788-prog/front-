@extends('layouts.app')

@section('title', $pageTitle ?? __('candidate.jobs.discover_page_title'))
@section('breadcrumb', '14 / ' . __('candidate.workspace') . ' / ' . __('navigation.candidate.discover_jobs'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.jobs.discover_headline') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">
        {{ __('candidate.jobs.discover_intro') }}
    </p>
@endsection

@section('page_actions')
    <a href="{{ route('candidate.profile') }}" class="hb-btn-primary">{{ __('candidate.jobs.edit_preferences') }}</a>
@endsection

@section('content')
    <div
        data-candidate-jobs
        data-candidate-jobs-mode="discover"
        data-default-saved='@json($defaultSavedIds)'
    >

        {{-- Search / filter bar --}}
        <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-center">
            <label class="relative min-w-0 flex-1">
                <span class="sr-only">{{ __('candidate.jobs.search_roles') }}</span>
                <input
                    type="search"
                    name="q"
                    value="{{ $preferences['search_default'] }}"
                    class="hb-input"
                    placeholder="{{ __('candidate.jobs.search_roles_placeholder') }}"
                    data-discover-q
                    autocomplete="off"
                >
            </label>
            <label class="shrink-0">
                <span class="sr-only">{{ __('candidate.jobs.location_filter') }}</span>
                <select class="hb-input min-w-44" data-discover-location aria-label="{{ __('candidate.jobs.location_filter') }}">
                    <option value="austin_remote" @selected($preferences['location_default'] === 'austin_remote')>{{ __('candidate.jobs.location_austin_remote') }}</option>
                    <option value="austin">{{ __('candidate.jobs.location_austin') }}</option>
                    <option value="remote">{{ __('candidate.jobs.location_remote') }}</option>
                    <option value="all">{{ __('candidate.jobs.location_all') }}</option>
                </select>
            </label>
            <label class="shrink-0">
                <span class="sr-only">{{ __('candidate.jobs.sort') }}</span>
                <select class="hb-input min-w-40" data-discover-sort aria-label="{{ __('candidate.jobs.sort_roles') }}">
                    <option value="recommended" @selected($preferences['sort_default'] === 'recommended')>{{ __('candidate.jobs.sort_recommended') }}</option>
                    <option value="salary_desc">{{ __('candidate.jobs.sort_salary_desc') }}</option>
                    <option value="newest">{{ __('candidate.jobs.sort_newest') }}</option>
                    <option value="company">{{ __('candidate.jobs.sort_company') }}</option>
                </select>
            </label>
            <button type="button" class="shrink-0 text-sm font-semibold text-hb-blue hover:underline" data-save-search>
                {{ __('candidate.jobs.save_this_search') }}
            </button>
        </div>

        <div
            class="mb-6 hidden rounded-lg border border-hb-info-soft bg-hb-info-soft px-3 py-2.5 text-sm text-hb-navy"
            data-save-search-note
            role="status"
        >
            {{ __('candidate.jobs.search_saved_device') }}
        </div>

        <div class="grid gap-6 xl:grid-cols-[280px_minmax(0,1fr)]">
            {{-- Your preferences --}}
            <aside class="hb-surface h-fit p-5" aria-label="{{ __('candidate.jobs.your_preferences_aria') }}">
                <h2 class="text-sm font-semibold text-hb-navy">{{ __('candidate.jobs.your_preferences') }}</h2>
                <ul class="mt-4 space-y-2 text-sm text-hb-text">
                    @foreach ($preferences['work_models'] as $item)
                        <li class="flex gap-2">
                            <span class="text-hb-success" aria-hidden="true">✓</span>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>

                <div class="mt-4 rounded-lg border border-hb-border bg-hb-page px-3 py-2.5">
                    <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('candidate.jobs.base_salary') }}</p>
                    <p class="mt-1 text-sm font-semibold text-hb-navy">{{ $preferences['salary'] }}</p>
                </div>

                <div class="mt-4">
                    <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('candidate.jobs.core_skills') }}</p>
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
                    <span class="sr-only">(<span data-jobs-count>{{ count($jobs) }}</span> {{ __('candidate.jobs.shown_label') }})</span>
                </p>

                <div class="hb-surface overflow-hidden" data-jobs-results>
                    @foreach ($jobs as $job)
                        @include('candidate.jobs._card', ['job' => $job])
                    @endforeach
                </div>

                <div class="hb-surface mt-4 hidden px-5 py-10 text-center" data-jobs-empty role="status">
                    <p class="font-semibold text-hb-navy">{{ __('candidate.jobs.empty_title') }}</p>
                    <p class="mt-2 text-sm text-hb-muted">{{ __('candidate.jobs.empty_body') }}</p>
                    <button type="button" class="hb-btn-outline mt-4" data-clear-discover>{{ __('candidate.jobs.clear_search') }}</button>
                </div>

                <div class="hb-surface mt-4 p-5" aria-label="{{ __('candidate.jobs.feedback_aria') }}">
                    <h2 class="text-base font-semibold text-hb-navy">{{ __('candidate.jobs.feedback_title') }}</h2>
                    <p class="mt-1 text-sm text-hb-muted">
                        {{ __('candidate.jobs.feedback_body') }}
                    </p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ route('candidate.profile') }}" class="hb-btn-outline">{{ __('candidate.jobs.update_preferences') }}</a>
                        <button type="button" class="text-sm font-semibold text-hb-blue hover:underline" disabled title="{{ __('candidate.jobs.hide_roles_soon') }}">
                            {{ __('candidate.jobs.hide_roles') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <p class="mt-8 text-xs text-hb-muted">
            {{ __('common.footer.workspace_note', ['date' => __('candidate.footer_date')]) }}
        </p>
    </div>

    @isset($applyFlow)
        @include('candidate.applications.apply-modal', ['flow' => $applyFlow])
        <div class="sr-only">
            <button type="button" data-open-modal="candidate-apply-modal">{{ __('candidate.jobs.open_apply_modal') }}</button>
        </div>
    @endisset
@endsection
