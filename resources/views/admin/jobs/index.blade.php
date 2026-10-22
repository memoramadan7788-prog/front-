@extends('layouts.app')

@section('title', $pageTitle ?? __('admin.jobs.page_title'))
@section('breadcrumb', __('admin.jobs.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ __('admin.jobs.title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ __('admin.jobs.intro') }}</p>
@endsection

@section('content')
    @php
        $defaultStatus = request()->query('status', $jobsPage['default_status'] ?? 'pending');
        $allowedStatus = collect($jobsPage['status_filters'])->pluck('key')->all();
        if (! in_array($defaultStatus, $allowedStatus, true)) {
            $defaultStatus = 'pending';
        }
        $initialQuery = (string) request()->query('q', '');
        $reasonLabels = [
            'salary' => __('admin.jobs.reason_salary'),
            'verification' => __('admin.jobs.reason_verification'),
            'employment_type' => __('admin.jobs.reason_employment_type'),
        ];
        $jobsI18n = [
            'empty' => __('admin.jobs.empty'),
            'review_heading' => __('admin.jobs.review_heading'),
            'pending_badge' => __('admin.jobs.pending_badge'),
            'submitted_by' => __('admin.jobs.submitted_by'),
            'policy_prefix' => __('admin.jobs.policy_prefix'),
            'note_label' => __('admin.jobs.note_label'),
            'notify_owner' => __('admin.jobs.notify_owner'),
            'request_changes' => __('admin.jobs.request_changes'),
            'approve' => __('admin.jobs.approve'),
            'reject' => __('admin.jobs.reject'),
            'reject_footnote' => __('admin.jobs.reject_footnote'),
            'open_organization' => __('admin.jobs.open_organization'),
            'note_required' => __('admin.jobs.note_required'),
            'salary' => __('admin.jobs.policy_salary_transparency'),
            'verification' => __('admin.jobs.policy_employer_verification'),
            'employment_type' => __('admin.jobs.policy_employment_type'),
            'reason_salary' => __('admin.jobs.reason_salary'),
            'reason_verification' => __('admin.jobs.reason_verification'),
            'reason_employment_type' => __('admin.jobs.reason_employment_type'),
        ];
    @endphp

    <div
        data-admin-jobs
        data-default-status="{{ $defaultStatus }}"
        data-default-job="{{ $jobsPage['default_job_id'] }}"
        data-initial-q="{{ $initialQuery }}"
        data-i18n="{{ json_encode($jobsI18n, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
    >
        @if (! empty($jobsPage['moderation_contract_note']))
            <x-alert variant="info" class="mb-6" data-jobs-moderation-contract role="note">
                <p class="text-sm text-hb-text">{{ $jobsPage['moderation_contract_note'] }}</p>
            </x-alert>
        @endif

        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
            <label class="relative min-w-0 flex-1">
                <span class="sr-only">{{ __('admin.jobs.search_placeholder') }}</span>
                <span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-hb-muted" aria-hidden="true">⌕</span>
                <input
                    type="search"
                    class="hb-input ps-9"
                    placeholder="{{ __('admin.jobs.search_placeholder') }}"
                    data-jobs-q
                    value="{{ $initialQuery }}"
                    autocomplete="off"
                >
            </label>
            <label class="shrink-0">
                <span class="sr-only">{{ __('admin.jobs.filter_status') }}</span>
                <select class="hb-input min-w-44" data-jobs-status aria-label="{{ __('admin.jobs.filter_status') }}">
                    @foreach ($jobsPage['status_filters'] as $filter)
                        @php
                            $statusLabel = match ($filter['key']) {
                                'changes_requested' => __('admin.jobs.status_changes'),
                                'approved' => __('admin.jobs.status_approved'),
                                'all' => __('admin.jobs.status_all'),
                                default => __('admin.jobs.status_pending'),
                            };
                        @endphp
                        <option value="{{ $filter['key'] }}" @selected($filter['key'] === $defaultStatus)>
                            {{ $statusLabel }} ({{ $filter['count'] }})
                        </option>
                    @endforeach
                </select>
            </label>
            <label class="shrink-0">
                <span class="sr-only">{{ __('admin.jobs.filter_reason') }}</span>
                <select class="hb-input min-w-48" data-jobs-reason aria-label="{{ __('admin.jobs.filter_reason') }}">
                    <option value="all">{{ __('admin.jobs.reason_all') }}</option>
                    @foreach ($jobsPage['reason_filters'] as $reasonKey)
                        @continue($reasonKey === 'all')
                        <option value="{{ $reasonKey }}">{{ $reasonLabels[$reasonKey] ?? $reasonKey }}</option>
                    @endforeach
                </select>
            </label>
            <label class="shrink-0">
                <span class="sr-only">{{ __('admin.jobs.sort_label') }}</span>
                <select class="hb-input min-w-44" data-jobs-sort aria-label="{{ __('admin.jobs.sort_label') }}">
                    <option value="newest">{{ __('admin.jobs.sort_newest') }}</option>
                    <option value="oldest">{{ __('admin.jobs.sort_oldest') }}</option>
                    <option value="code">{{ __('admin.jobs.sort_code') }}</option>
                </select>
            </label>
            <button type="button" class="hb-btn-outline shrink-0" data-jobs-reset>
                {{ __('admin.jobs.clear_filters') }}
            </button>
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(18rem,24rem)]">
            <div class="min-w-0 space-y-4">
                <div class="overflow-hidden rounded-xl border border-hb-border bg-hb-surface" role="listbox" aria-label="{{ __('admin.jobs.queue_aria') }}">
                    <ul class="divide-y divide-hb-border" data-jobs-list>
                        @foreach ($jobsPage['jobs'] as $job)
                            <li>
                                <button
                                    type="button"
                                    role="option"
                                    class="w-full px-5 py-4 text-start transition hover:bg-hb-page focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-hb-blue/40"
                                    data-job-item
                                    data-id="{{ $job['id'] }}"
                                    data-status="{{ $job['status'] }}"
                                    data-reason="{{ $job['reason_key'] }}"
                                    data-search="{{ $job['search'] }}"
                                    data-sort-time="{{ $job['sort_time'] }}"
                                    data-code="{{ $job['code'] }}"
                                    data-title="{{ $job['title'] }}"
                                    data-organization="{{ $job['organization'] }}"
                                    data-org-id="{{ $job['org_id'] }}"
                                    data-org-href="{{ route('admin.organizations.show', $job['org_id']) }}"
                                    data-location="{{ $job['location'] }}"
                                    data-employment="{{ $job['employment_type'] }}"
                                    data-salary="{{ $job['salary_label'] }}"
                                    data-submitted-at="{{ $job['submitted_at'] }}"
                                    data-submitted-by="{{ $job['submitted_by'] }}"
                                    data-policy-key="{{ $job['policy_title_key'] }}"
                                    data-policy-body="{{ $job['policy_body'] }}"
                                    data-note="{{ $job['review_note_default'] }}"
                                    data-reason-label="{{ $reasonLabels[$job['reason_key']] ?? $job['reason_key'] }}"
                                    aria-selected="false"
                                    hidden
                                >
                                    <p class="font-semibold text-hb-navy">
                                        <span dir="ltr">{{ $job['code'] }}</span>
                                        · {{ $job['title'] }}
                                        · {{ $job['organization'] }}
                                    </p>
                                    <p class="mt-1 text-sm text-hb-muted">
                                        {{ __('admin.jobs.policy_reason_line', ['reason' => $reasonLabels[$job['reason_key']] ?? $job['reason_key']]) }}
                                    </p>
                                    <p class="mt-1 text-xs text-hb-muted">
                                        {{ __('admin.jobs.submitted_line', ['when' => $job['submitted_at']]) }}
                                    </p>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                    <p class="hidden px-5 py-8 text-center text-sm text-hb-muted" data-jobs-empty role="status">
                        {{ __('admin.jobs.empty') }}
                    </p>
                </div>

                <div class="rounded-xl border border-hb-info/30 bg-hb-info-soft/40 px-5 py-4 text-sm text-hb-muted" role="note">
                    {{ __('admin.jobs.pause_banner') }}
                </div>
            </div>

            <aside class="hb-surface p-5 sm:p-6" data-jobs-detail aria-live="polite">
                <div data-jobs-detail-empty class="text-sm text-hb-muted">
                    {{ __('admin.jobs.select_prompt') }}
                </div>
                <div data-jobs-detail-body class="hidden space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="text-base font-semibold text-hb-navy" data-jobs-detail-heading></h2>
                        <x-badge variant="pending" :dot="true">{{ __('admin.jobs.pending_badge') }}</x-badge>
                    </div>
                    <div>
                        <p class="text-lg font-semibold text-hb-navy" data-jobs-detail-title></p>
                        <p class="mt-1 text-sm text-hb-muted">
                            <a href="#" class="font-semibold text-hb-blue hover:underline" data-jobs-detail-org-link></a>
                            <span data-jobs-detail-location></span>
                        </p>
                        <p class="mt-1 text-sm text-hb-muted" data-jobs-detail-meta></p>
                        <p class="mt-2 text-sm text-hb-muted" data-jobs-detail-submitted></p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-hb-navy" data-jobs-detail-policy-title></h3>
                        <p class="mt-2 text-sm text-hb-muted" data-jobs-detail-policy-body></p>
                    </div>
                    <label class="block">
                        <span class="mb-1.5 block text-sm font-semibold text-hb-navy">
                            {{ __('admin.jobs.note_label') }}
                            <span class="text-hb-error" aria-hidden="true">*</span>
                        </span>
                        <textarea class="hb-input min-h-28 w-full" data-jobs-note rows="4"></textarea>
                        <span class="mt-1 hidden text-xs text-hb-error" data-jobs-note-error role="alert"></span>
                    </label>
                    <label class="flex items-start gap-2 text-sm text-hb-text">
                        <input type="checkbox" class="mt-1 rounded border-hb-border text-hb-blue focus:ring-hb-blue/40" data-jobs-notify checked>
                        <span>{{ __('admin.jobs.notify_owner') }}</span>
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="hb-btn-primary" data-jobs-action="changes">
                            {{ __('admin.jobs.request_changes') }}
                        </button>
                        <button type="button" class="hb-btn-outline" data-jobs-action="approve">
                            {{ __('admin.jobs.approve') }}
                        </button>
                    </div>
                    <button type="button" class="text-sm font-semibold text-hb-error hover:underline" data-jobs-action="reject">
                        {{ __('admin.jobs.reject') }}
                    </button>
                    <p class="text-xs text-hb-muted">{{ __('admin.jobs.reject_footnote') }}</p>
                </div>
            </aside>
        </div>
    </div>

    @include('layouts.modal', [
        'modalId' => 'admin-jobs-review-modal',
        'modalTitle' => __('admin.jobs.review_modal_title'),
        'modalBody' => '<p data-jobs-review-body>'.e(__('admin.jobs.review_modal_body')).'</p><p class="mt-3 text-sm text-hb-muted">'.e(__('admin.jobs.review_modal_unavailable')).'</p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button>',
    ])
@endsection
