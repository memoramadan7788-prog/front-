@extends('layouts.app')

@section('title', $pageTitle ?? 'Saved jobs · Hirebridge')
@section('breadcrumb', '15 / My workspace / Saved jobs')

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">Saved jobs</h1>
    <p class="mt-1 text-sm text-hb-muted">
        A considered shortlist. Compare the details before you take the next step.
    </p>
@endsection

@section('page_actions')
    <a href="{{ route('candidate.jobs.index') }}" class="hb-btn-primary">Discover more roles</a>
@endsection

@section('content')
    <div
        data-candidate-jobs
        data-candidate-jobs-mode="saved"
        data-default-saved='@json($defaultSavedIds)'
    >
        <div class="hb-mock-banner mb-6 rounded-lg text-start">
            Frontend UI only — saved jobs are not stored on your account. Clearing browser storage resets this list.
        </div>

        <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center">
            <label class="relative min-w-0 flex-1">
                <span class="sr-only">Search saved roles</span>
                <input
                    type="search"
                    class="hb-input"
                    placeholder="Search saved roles"
                    data-saved-q
                    autocomplete="off"
                >
            </label>
            <label class="shrink-0">
                <span class="sr-only">Work model</span>
                <select class="hb-input min-w-44" data-saved-work-model aria-label="Filter by work model">
                    <option value="all">All work models</option>
                    <option value="Hybrid">Hybrid</option>
                    <option value="Remote">Remote</option>
                    <option value="On-site">On-site</option>
                </select>
            </label>
            <label class="shrink-0">
                <span class="sr-only">Sort saved roles</span>
                <select class="hb-input min-w-40" data-saved-sort aria-label="Sort saved roles">
                    <option value="recent">Recently saved</option>
                    <option value="salary_desc">Salary · high to low</option>
                    <option value="company">Company A–Z</option>
                </select>
            </label>
        </div>

        <div class="mb-4 rounded-lg border border-hb-info-soft bg-hb-info-soft px-4 py-3" role="status">
            <p class="text-sm font-semibold text-hb-navy">{{ $savedSearch['updates_title'] }}</p>
            <p class="mt-1 text-sm text-hb-text">{{ $savedSearch['updates_body'] }}</p>
        </div>

        <p class="mb-3 text-sm text-hb-muted">
            <span data-saved-count>{{ count($savedJobs) }}</span>
            <span data-saved-count-label>{{ count($savedJobs) === 1 ? 'saved role' : 'saved roles' }}</span>
        </p>

        <div class="hb-surface overflow-hidden" data-saved-results>
            <div class="hidden border-b border-hb-border bg-hb-page px-5 py-2 text-xs font-semibold tracking-wide text-hb-muted uppercase sm:grid sm:grid-cols-[minmax(0,1.4fr)_minmax(0,0.8fr)_minmax(0,0.7fr)_minmax(0,0.7fr)_auto] sm:gap-3">
                <span>Role</span>
                <span>Location / model</span>
                <span>Base salary</span>
                <span>Saved / status</span>
                <span class="text-end">Actions</span>
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
                        <p class="text-sm text-hb-muted">{{ $job['saved_on'] ?? '—' }} · {{ $job['saved_status'] ?? 'Open' }}</p>
                        <div class="flex flex-wrap items-center gap-3 sm:justify-end">
                            @if (($job['saved_action'] ?? '') === 'view_application')
                                <a href="{{ route('candidate.applications.show', $job['id']) }}" class="text-sm font-semibold text-hb-blue hover:underline">
                                    {{ $job['saved_action_label'] ?? 'View application' }}
                                </a>
                            @else
                                <a href="{{ route('candidate.applications.apply', $job['id']) }}" class="text-sm font-semibold text-hb-blue hover:underline">
                                    {{ $job['saved_action_label'] ?? 'Review role' }}
                                </a>
                            @endif
                            <button
                                type="button"
                                class="text-sm font-semibold text-hb-muted hover:text-hb-error"
                                data-save-job="{{ $job['id'] }}"
                                data-remove-saved
                                aria-pressed="true"
                            >
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="hb-surface mt-4 hidden px-5 py-10 text-center" data-saved-empty role="status" hidden>
            <p class="font-semibold text-hb-navy">No saved jobs yet</p>
            <p class="mt-2 text-sm text-hb-muted">
                Save roles from Discover jobs to keep them here while you compare options.
            </p>
            <a href="{{ route('candidate.jobs.index') }}" class="hb-btn-primary mt-4 inline-flex">Discover more roles</a>
        </div>

        <div class="mt-6 grid gap-4 lg:grid-cols-2">
            <div class="hb-surface p-5">
                <h2 class="text-base font-semibold text-hb-navy">Keep a shortlist that works for you</h2>
                <p class="mt-2 text-sm leading-relaxed text-hb-muted">
                    Saved jobs are private. Removing a saved role does not withdraw an application you’ve already submitted.
                </p>
                <button type="button" class="hb-btn-outline mt-4" disabled title="Saved-search alerts — Backend Team">
                    Manage saved-search alerts
                </button>
            </div>

            <div class="hb-surface p-5">
                <h2 class="text-base font-semibold text-hb-navy">Your saved search</h2>
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
                    <button type="button" class="text-sm font-semibold text-hb-blue hover:underline" disabled title="Edit search — Backend Team">
                        Edit search
                    </button>
                    <button type="button" class="text-sm font-semibold text-hb-blue hover:underline" disabled title="Pause alerts — Backend Team">
                        Pause alerts
                    </button>
                </div>
            </div>
        </div>

        <p class="mt-8 text-xs text-hb-muted">
            Hirebridge · Private workspace · All times Central unless noted · October 20, 2026
        </p>
    </div>
@endsection
