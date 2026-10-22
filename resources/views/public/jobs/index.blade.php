@extends('layouts.public')

@section('title', $pageTitle ?? __('public.jobs.index_page_title'))

@section('content')
    <section class="bg-hb-page" data-jobs-directory data-page-size="5">
        <div class="hb-container py-10 lg:py-14">
            <p class="text-xs font-semibold tracking-[0.12em] text-hb-muted uppercase">{{ __('public.jobs.index_eyebrow') }}</p>
            <div class="mt-3 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="text-3xl font-semibold text-hb-navy sm:text-4xl">{{ __('public.jobs.index_title') }}</h1>
                    <p class="mt-2 max-w-2xl text-sm text-hb-muted">
                        {{ __('public.jobs.index_intro') }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="hb-btn-outline lg:hidden"
                        data-jobs-filters-open
                        aria-controls="jobs-filters-panel"
                        aria-expanded="false"
                    >
                        {{ __('public.jobs.filters_open') }}
                    </button>
                    <span class="rounded-full bg-hb-pending-soft px-3 py-1 text-[10px] font-semibold tracking-wide text-hb-pending uppercase">
                        {{ __('public.jobs.open_roles_badge') }}
                    </span>
                </div>
            </div>

            <div
                class="fixed inset-0 z-40 hidden bg-hb-navy/40 lg:hidden"
                data-jobs-filters-backdrop
                hidden
            ></div>

            <div class="mt-8 grid gap-8 lg:grid-cols-[280px_minmax(0,1fr)]">
                <aside
                    id="jobs-filters-panel"
                    class="hb-surface fixed inset-y-0 start-0 z-50 h-full w-[min(100%,20rem)] -translate-x-full overflow-y-auto p-5 transition-transform duration-200 ease-out rtl:translate-x-full lg:static lg:z-auto lg:h-fit lg:w-auto lg:translate-x-0 lg:overflow-visible lg:rtl:translate-x-0"
                    data-jobs-filters
                    aria-label="{{ __('public.jobs.refine_aria') }}"
                    aria-hidden="true"
                    inert
                >
                    <div class="mb-4 flex items-center justify-between gap-3 lg:mb-0 lg:block">
                        <h2 class="text-sm font-semibold text-hb-navy">{{ __('public.jobs.refine_title') }}</h2>
                        <button type="button" class="hb-btn-ghost lg:hidden" data-jobs-filters-close>
                            {{ __('public.jobs.filters_close') }}
                        </button>
                    </div>

                    <form id="jobs-filter-form" class="mt-5 space-y-6" onsubmit="return false;" data-jobs-filter-form>
                        <div>
                            <label for="filter-q" class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('public.jobs.keyword') }}</label>
                            <input id="filter-q" name="q" type="search" value="{{ request('q') }}" placeholder="Product designer"
                                   class="mt-2 w-full rounded-lg border border-hb-border bg-hb-surface px-3 py-2.5 text-sm text-hb-text">
                        </div>

                        <div>
                            <label for="filter-location" class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('public.jobs.location') }}</label>
                            <input id="filter-location" name="location" type="text" value="{{ request('location') }}" placeholder="Austin, TX"
                                   class="mt-2 w-full rounded-lg border border-hb-border bg-hb-surface px-3 py-2.5 text-sm text-hb-text">
                        </div>

                        <fieldset>
                            <legend class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('public.jobs.work_model') }}</legend>
                            <div class="mt-2 space-y-2 text-sm text-hb-text">
                                @foreach (['Hybrid', 'Remote', 'On-site'] as $model)
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="work_model[]" value="{{ $model }}" class="rounded border-hb-border text-hb-blue"
                                            @checked(is_array(request('work_model')) && in_array($model, request('work_model'), true))>
                                        {{ \App\Support\Frontend\MockCandidateData::workModelLabel($model) }}
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('public.jobs.employment') }}</legend>
                            <div class="mt-2 space-y-2 text-sm text-hb-text">
                                @foreach (['Full-time', 'Contract'] as $type)
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="employment[]" value="{{ $type }}" class="rounded border-hb-border text-hb-blue"
                                            @checked(is_array(request('employment')) && in_array($type, request('employment'), true))>
                                        {{ \App\Support\Frontend\MockCandidateData::employmentLabel($type) }}
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <div>
                            <label class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('public.jobs.salary_usd') }}</label>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <input id="filter-salary-min" type="number" name="salary_min" value="{{ request('salary_min') }}" min="0" step="5000"
                                       class="w-full rounded-lg border border-hb-border bg-hb-surface px-3 py-2 text-sm text-hb-text" aria-label="{{ __('public.jobs.salary_min_aria') }}" placeholder="Min">
                                <input id="filter-salary-max" type="number" name="salary_max" value="{{ request('salary_max') }}" min="0" step="5000"
                                       class="w-full rounded-lg border border-hb-border bg-hb-surface px-3 py-2 text-sm text-hb-text" aria-label="{{ __('public.jobs.salary_max_aria') }}" placeholder="Max">
                            </div>
                        </div>

                        <fieldset>
                            <legend class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('public.jobs.discipline') }}</legend>
                            <div class="mt-2 space-y-2 text-sm text-hb-text">
                                @foreach (['Product & Design', 'Engineering & Data', 'Marketing & Growth', 'People & Operations'] as $discipline)
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="discipline[]" value="{{ $discipline }}" class="rounded border-hb-border text-hb-blue"
                                            @checked(request('discipline') === $discipline || (is_array(request('discipline')) && in_array($discipline, request('discipline'), true)))>
                                        {{ $discipline }}
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <div class="flex flex-col gap-2">
                            <x-button type="button" variant="secondary" class="w-full" data-clear-filters>{{ __('public.jobs.clear_filters') }}</x-button>
                            <x-button type="button" variant="primary" class="w-full lg:hidden" data-jobs-filters-apply>{{ __('public.jobs.filters_apply') }}</x-button>
                        </div>
                    </form>
                </aside>

                <div>
                    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-hb-muted" aria-live="polite">
                            <span data-jobs-count>{{ count($jobs) }}</span> {{ __('public.jobs.match_count_suffix') }}
                        </p>
                        <label class="text-sm text-hb-muted">
                            <span class="sr-only">{{ __('public.jobs.sort_label') }}</span>
                            <select class="rounded-lg border border-hb-border bg-hb-surface px-3 py-2 text-sm text-hb-text" data-jobs-sort aria-label="{{ __('public.jobs.sort_label') }}">
                                <option value="relevance">{{ __('public.jobs.sort_relevance') }}</option>
                                <option value="newest" selected>{{ __('public.jobs.sort_newest') }}</option>
                                <option value="salary">{{ __('public.jobs.sort_salary') }}</option>
                                <option value="company">{{ __('public.jobs.sort_company') }}</option>
                            </select>
                        </label>
                    </div>

                    <div class="space-y-4" data-jobs-results>
                        @foreach ($jobs as $index => $job)
                            @include('public.jobs._card', ['job' => $job, 'postedRank' => count($jobs) - $index])
                        @endforeach
                    </div>

                    <div class="hb-surface mt-4 hidden px-5 py-10 text-center" data-jobs-empty>
                        <p class="font-semibold text-hb-navy">{{ __('public.jobs.empty_title') }}</p>
                        <p class="mt-2 text-sm text-hb-muted">{{ __('public.jobs.empty_body') }}</p>
                        <x-button class="mt-4" variant="secondary" data-clear-filters>{{ __('public.jobs.clear_filters') }}</x-button>
                    </div>

                    <div
                        class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                        data-jobs-pagination
                        data-status-template="{{ __('public.jobs.pagination_status') }}"
                        hidden
                    >
                        <p class="text-sm text-hb-muted" data-jobs-page-status aria-live="polite"></p>
                        <nav class="flex flex-wrap items-center gap-2" aria-label="{{ __('public.jobs.pagination_aria') }}">
                            <button type="button" class="hb-btn-outline" data-jobs-page-prev>{{ __('public.jobs.pagination_prev') }}</button>
                            <div class="flex flex-wrap items-center gap-1" data-jobs-page-numbers></div>
                            <button type="button" class="hb-btn-outline" data-jobs-page-next>{{ __('public.jobs.pagination_next') }}</button>
                        </nav>
                    </div>

                    <div class="mt-8 hb-surface flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-semibold text-hb-navy">{{ __('public.jobs.alert_title') }}</p>
                            <p class="mt-1 text-sm text-hb-muted">{{ __('public.jobs.alert_body') }}</p>
                        </div>
                        <a href="{{ route('register') }}" class="hb-btn-primary">{{ __('public.jobs.create_account') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
