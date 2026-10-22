@extends('layouts.app')

@section('title', $pageTitle ?? __('candidate.jobs.saved_page_title'))
@section('breadcrumb', __('candidate.workspace') . ' / ' . __('navigation.candidate.saved_jobs'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.jobs.saved_title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">
        {{ __('candidate.jobs.saved_intro') }}
    </p>
@endsection

@section('page_actions')
    <a href="{{ route('candidate.jobs.index') }}" class="hb-btn-primary">{{ __('candidate.jobs.discover_more') }}</a>
@endsection

@section('content')
    <div
        data-candidate-jobs
        data-candidate-jobs-mode="saved"
        data-default-saved='@json($defaultSavedIds)'
    >

        <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center">
            <label class="relative min-w-0 flex-1">
                <span class="sr-only">{{ __('candidate.jobs.search_saved_roles') }}</span>
                <input
                    type="search"
                    class="hb-input"
                    placeholder="{{ __('candidate.jobs.search_saved_placeholder') }}"
                    data-saved-q
                    autocomplete="off"
                >
            </label>
            <label class="shrink-0">
                <span class="sr-only">{{ __('candidate.jobs.work_model') }}</span>
                <select class="hb-input min-w-44" data-saved-work-model aria-label="{{ __('candidate.jobs.filter_work_model') }}">
                    <option value="all">{{ __('candidate.jobs.all_work_models') }}</option>
                    <option value="Hybrid">Hybrid</option>
                    <option value="Remote">Remote</option>
                    <option value="On-site">On-site</option>
                </select>
            </label>
            <label class="shrink-0">
                <span class="sr-only">{{ __('candidate.jobs.sort_saved_roles') }}</span>
                <select class="hb-input min-w-40" data-saved-sort aria-label="{{ __('candidate.jobs.sort_saved_roles') }}">
                    <option value="recent">{{ __('candidate.jobs.sort_recently_saved') }}</option>
                    <option value="salary_desc">{{ __('candidate.jobs.sort_salary_desc') }}</option>
                    <option value="company">{{ __('candidate.jobs.sort_company') }}</option>
                </select>
            </label>
        </div>

        <div class="mb-4 rounded-lg border border-hb-info-soft bg-hb-info-soft px-4 py-3" role="status">
            <p class="text-sm font-semibold text-hb-navy">{{ $savedSearch['updates_title'] }}</p>
            <p class="mt-1 text-sm text-hb-text">{{ $savedSearch['updates_body'] }}</p>
        </div>

        <p class="mb-3 text-sm text-hb-muted">
            <span data-saved-count>{{ count($savedJobs) }}</span>
            <span data-saved-count-label>{{ count($savedJobs) === 1 ? __('candidate.jobs.saved_role') : __('candidate.jobs.saved_roles') }}</span>
        </p>

        <div class="hb-surface overflow-hidden" data-saved-results>
            <div class="hidden border-b border-hb-border bg-hb-page px-5 py-2 text-xs font-semibold tracking-wide text-hb-muted uppercase sm:grid sm:grid-cols-[minmax(0,1.4fr)_minmax(0,0.8fr)_minmax(0,0.7fr)_minmax(0,0.7fr)_auto] sm:gap-3">
                <span>{{ __('candidate.jobs.col_role') }}</span>
                <span>{{ __('candidate.jobs.col_location') }}</span>
                <span>{{ __('candidate.jobs.col_salary') }}</span>
                <span>{{ __('candidate.jobs.col_saved_status') }}</span>
                <span class="text-end">{{ __('candidate.jobs.col_actions') }}</span>
            </div>

            @foreach ($jobs as $job)
                <div
                    class="border-b border-hb-border px-5 py-4 last:border-b-0"
                    data-saved-row
                    data-job-id="{{ $job['id'] }}"
                    data-title="{{ strtolower($job['title'].' '.$job['company'].' '.$job['code']) }}"
                    data-work-model="{{ $job['work_model'] }}"
                    data-salary-max="{{ $job['salary_max'] }}"
                    data-company="{{ strtolower($job['company']) }}"
                    data-saved-rank="{{ $job['saved_on'] ?? '' }}"
                    @class(['hidden' => ! in_array($job['id'], $defaultSavedIds, true)])
                >
                    <div class="grid gap-2 sm:grid-cols-[minmax(0,1.4fr)_minmax(0,0.8fr)_minmax(0,0.7fr)_minmax(0,0.7fr)_auto] sm:items-center sm:gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-hb-navy">
                                <a href="{{ route('jobs.show', $job['id']) }}" class="hover:text-hb-blue">{{ $job['title'] }}</a>
                            </p>
                            <p class="mt-0.5 text-sm text-hb-muted">{{ $job['company'] }} · {{ $job['code'] }}</p>
                        </div>
                        <p class="text-sm text-hb-text">{{ $job['location_short'] ?? $job['location'] }} · {{ $job['work_model'] }}</p>
                        <p class="text-sm text-hb-text">{{ $job['salary_label'] }}</p>
                        <p class="text-sm text-hb-muted">{{ $job['saved_on'] ?? '—' }} · {{ $job['saved_status'] ?? __('candidate.jobs.status_open') }}</p>
                        <div class="flex flex-wrap items-center gap-3 sm:justify-end">
                            @if (($job['saved_action'] ?? '') === 'view_application')
                                <a href="{{ route('candidate.applications.show', $job['id']) }}" class="text-sm font-semibold text-hb-blue hover:underline">
                                    {{ $job['saved_action_label'] ?? __('candidate.applications.view_application') }}
                                </a>
                            @else
                                <a href="{{ route('candidate.applications.apply', $job['id']) }}" class="text-sm font-semibold text-hb-blue hover:underline">
                                    {{ $job['saved_action_label'] ?? __('candidate.jobs.review_role') }}
                                </a>
                            @endif
                            <button
                                type="button"
                                class="text-sm font-semibold text-hb-muted hover:text-hb-error"
                                data-save-job="{{ $job['id'] }}"
                                data-remove-saved
                                aria-pressed="true"
                            >
                                {{ __('candidate.jobs.remove') }}
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="hb-surface mt-4 hidden px-5 py-10 text-center" data-saved-empty role="status" hidden>
            <p class="font-semibold text-hb-navy">{{ __('candidate.jobs.empty_saved_title') }}</p>
            <p class="mt-2 text-sm text-hb-muted">
                {{ __('candidate.jobs.empty_saved_body') }}
            </p>
            <a href="{{ route('candidate.jobs.index') }}" class="hb-btn-primary mt-4 inline-flex">{{ __('candidate.jobs.discover_more') }}</a>
        </div>

        <div class="mt-6 grid gap-4 lg:grid-cols-2">
            <div class="hb-surface p-5">
                <h2 class="text-base font-semibold text-hb-navy">{{ __('candidate.jobs.shortlist_title') }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-hb-muted">
                    {{ __('candidate.jobs.shortlist_body') }}
                </p>
                <button type="button" class="hb-btn-outline mt-4" disabled title="{{ __('candidate.jobs.manage_alerts_soon') }}">
                    {{ __('candidate.jobs.manage_alerts') }}
                </button>
            </div>

            <div class="hb-surface p-5">
                <h2 class="text-base font-semibold text-hb-navy">{{ __('candidate.jobs.your_saved_search') }}</h2>
                <p class="mt-3 flex items-center gap-2 text-sm font-medium text-hb-success">
                    <span class="h-2 w-2 rounded-full bg-hb-success" aria-hidden="true"></span>
                    {{ $savedSearch['status'] }}
                </p>
                <ul class="mt-3 space-y-1 text-sm text-hb-text">
                    @foreach ($savedSearch['lines'] as $line)
                        <li>{{ $line }}</li>
                    @endforeach
                </ul>
                <div class="mt-4 flex flex-wrap gap-4">
                    <button type="button" class="text-sm font-semibold text-hb-blue hover:underline" disabled title="{{ __('candidate.jobs.edit_search_soon') }}">
                        {{ __('candidate.jobs.edit_search') }}
                    </button>
                    <button type="button" class="text-sm font-semibold text-hb-blue hover:underline" disabled title="{{ __('candidate.jobs.pause_alerts_soon') }}">
                        {{ __('candidate.jobs.pause_alerts') }}
                    </button>
                </div>
            </div>
        </div>

        <p class="mt-8 text-xs text-hb-muted">
            {{ __('common.footer.workspace_note', ['date' => __('candidate.footer_date')]) }}
        </p>
    </div>
@endsection
