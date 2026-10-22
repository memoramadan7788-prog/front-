@extends('layouts.app')

@section('title', $pageTitle ?? __('admin.agencies.page_title'))
@section('breadcrumb', __('admin.agencies.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ __('admin.agencies.title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ __('admin.agencies.intro') }}</p>
@endsection

@section('page_actions')
    <button
        type="button"
        class="hb-btn-primary"
        data-open-modal="admin-agencies-create-modal"
        title="{{ __('admin.agencies.create_soon_title') }}"
    >
        {{ __('admin.agencies.create') }}
    </button>
@endsection

@section('content')
    @php
        $defaultStatus = request()->query('status', $agenciesPage['default_status'] ?? 'active');
        $allowedStatus = collect($agenciesPage['status_filters'])->pluck('key')->all();
        if (! in_array($defaultStatus, $allowedStatus, true)) {
            $defaultStatus = 'active';
        }
        $initialQuery = (string) request()->query('q', '');
        $agenciesI18n = [
            'empty' => __('admin.agencies.empty'),
            'selected' => __('admin.agencies.selected'),
            'active_badge' => __('admin.agencies.active_badge'),
            'agency_managed' => __('admin.agencies.agency_managed'),
            'col_candidate' => __('admin.agencies.col_candidate'),
            'col_stage' => __('admin.agencies.col_stage'),
            'col_consent' => __('admin.agencies.col_consent'),
            'assigned_recruiter' => __('admin.agencies.assigned_recruiter'),
            'client_owner' => __('admin.agencies.client_owner'),
            'open_record' => __('admin.agencies.open_record'),
            'model_agency' => __('admin.agencies.model_agency'),
            'recruiter' => __('admin.agencies.recruiter'),
            'submissions' => __('admin.agencies.submissions'),
            'service_status' => __('admin.agencies.service_status'),
        ];
    @endphp

    <div
        data-admin-agencies
        data-default-status="{{ $defaultStatus }}"
        data-default-engagement="{{ $agenciesPage['default_engagement_id'] }}"
        data-initial-q="{{ $initialQuery }}"
        data-i18n="{{ json_encode($agenciesI18n, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
    >
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="hb-surface p-5">
                <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('admin.agencies.stat_active') }}</p>
                <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ $agenciesPage['stats']['active_engagements'] }}</p>
                <p class="mt-2 text-sm text-hb-muted">{{ $agenciesPage['stats']['active_meta'] }}</p>
            </div>
            <div class="hb-surface p-5">
                <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('admin.agencies.stat_pending') }}</p>
                <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ $agenciesPage['stats']['pending_review'] }}</p>
                <p class="mt-2 text-sm text-hb-muted">{{ $agenciesPage['stats']['pending_meta'] }}</p>
            </div>
            <div class="hb-surface p-5">
                <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('admin.agencies.stat_coordination') }}</p>
                <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ $agenciesPage['stats']['needs_coordination'] }}</p>
                <p class="mt-2 text-sm text-hb-muted">{{ $agenciesPage['stats']['coordination_meta'] }}</p>
            </div>
        </div>

        <div class="mt-6 flex flex-col gap-3 lg:flex-row lg:items-center">
            <label class="relative min-w-0 flex-1">
                <span class="sr-only">{{ __('admin.agencies.search_placeholder') }}</span>
                <span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-hb-muted" aria-hidden="true">⌕</span>
                <input
                    type="search"
                    class="hb-input ps-9"
                    placeholder="{{ __('admin.agencies.search_placeholder') }}"
                    data-agencies-q
                    value="{{ $initialQuery }}"
                    autocomplete="off"
                >
            </label>
            <label class="shrink-0">
                <span class="sr-only">{{ __('admin.agencies.filter_status') }}</span>
                <select class="hb-input min-w-48" data-agencies-status aria-label="{{ __('admin.agencies.filter_status') }}">
                    @foreach ($agenciesPage['status_filters'] as $filter)
                        <option value="{{ $filter['key'] }}" @selected($filter['key'] === $defaultStatus)>
                            {{ $filter['key'] === 'all' ? __('admin.agencies.status_all') : __('admin.agencies.status_active') }}
                            ({{ $filter['count'] }})
                        </option>
                    @endforeach
                </select>
            </label>
            <label class="shrink-0">
                <span class="sr-only">{{ __('admin.agencies.filter_agency') }}</span>
                <select class="hb-input min-w-48" data-agencies-agency aria-label="{{ __('admin.agencies.filter_agency') }}">
                    <option value="all">{{ __('admin.agencies.agency_all') }}</option>
                    @foreach ($agenciesPage['agency_filters'] as $agencyName)
                        <option value="{{ $agencyName }}">{{ $agencyName }}</option>
                    @endforeach
                </select>
            </label>
            <label class="shrink-0">
                <span class="sr-only">{{ __('admin.agencies.sort_label') }}</span>
                <select class="hb-input min-w-40" data-agencies-sort aria-label="{{ __('admin.agencies.sort_label') }}">
                    <option value="code_asc">{{ __('admin.agencies.sort_code_asc') }}</option>
                    <option value="code_desc">{{ __('admin.agencies.sort_code_desc') }}</option>
                    <option value="client">{{ __('admin.agencies.sort_client') }}</option>
                </select>
            </label>
            <button type="button" class="hb-btn-outline shrink-0" data-agencies-reset>
                {{ __('admin.agencies.clear_filters') }}
            </button>
        </div>

        <div class="mt-5 overflow-hidden rounded-xl border border-hb-border bg-hb-surface" role="listbox" aria-label="{{ __('admin.agencies.list_aria') }}">
            <ul class="divide-y divide-hb-border" data-agencies-list>
                @foreach ($agenciesPage['engagements'] as $engagement)
                    <li>
                        <button
                            type="button"
                            role="option"
                            class="w-full px-5 py-4 text-start transition hover:bg-hb-page focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-hb-blue/40"
                            data-engagement-item
                            data-id="{{ $engagement['id'] }}"
                            data-status="{{ $engagement['status'] }}"
                            data-agency="{{ $engagement['agency'] }}"
                            data-search="{{ $engagement['search'] }}"
                            data-sort-code="{{ $engagement['sort_code'] }}"
                            data-client="{{ $engagement['client'] }}"
                            data-payload="{{ json_encode($engagement, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
                            data-client-href="{{ route('admin.organizations.show', $engagement['client_org_id']) }}"
                            data-agency-href="{{ route('admin.organizations.show', $engagement['agency_org_id']) }}"
                            aria-selected="false"
                            hidden
                        >
                            <p class="font-semibold text-hb-navy">
                                <span dir="ltr">{{ $engagement['code'] }}</span>
                                · {{ $engagement['client'] }}
                                · <span dir="ltr">{{ $engagement['job_code'] }}</span>
                                · {{ $engagement['job_title'] }}
                            </p>
                            <p class="mt-1 text-sm text-hb-muted">
                                {{ __('admin.agencies.model_agency') }}: {{ $engagement['model'] }} · {{ $engagement['agency'] }}
                            </p>
                            <p class="mt-1 text-sm text-hb-muted">
                                {{ __('admin.agencies.recruiter') }}: {{ $engagement['recruiter'] }}
                                · {{ __('admin.agencies.submissions') }}: {{ $engagement['submissions_label'] }}
                                · {{ __('admin.agencies.service_status') }}: {{ $engagement['service_status'] }}
                            </p>
                        </button>
                    </li>
                @endforeach
            </ul>
            <p class="hidden px-5 py-8 text-center text-sm text-hb-muted" data-agencies-empty role="status">
                {{ __('admin.agencies.empty') }}
            </p>
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(16rem,18rem)]" data-agencies-detail aria-live="polite">
            <div class="hb-surface overflow-hidden">
                <div class="border-b border-hb-border px-5 py-4 sm:px-6">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-base font-semibold text-hb-navy" data-agencies-detail-heading>
                            {{ __('admin.agencies.select_prompt') }}
                        </h2>
                        <span class="hidden" data-agencies-detail-badges>
                            <x-badge variant="success" :dot="true">{{ __('admin.agencies.active_badge') }}</x-badge>
                            <x-badge variant="neutral" :dot="true">{{ __('admin.agencies.agency_managed') }}</x-badge>
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-hb-muted" data-agencies-detail-scope></p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[32rem] text-start text-sm">
                        <caption class="sr-only">{{ __('admin.agencies.candidates_caption') }}</caption>
                        <thead class="bg-hb-page text-xs font-semibold tracking-wide text-hb-muted uppercase">
                            <tr>
                                <th scope="col" class="px-5 py-3">{{ __('admin.agencies.col_candidate') }}</th>
                                <th scope="col" class="px-4 py-3">{{ __('admin.agencies.col_stage') }}</th>
                                <th scope="col" class="px-5 py-3">{{ __('admin.agencies.col_consent') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hb-border" data-agencies-candidates>
                            <tr>
                                <td colspan="3" class="px-5 py-8 text-center text-hb-muted">{{ __('admin.agencies.select_prompt') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <aside class="hb-surface p-5 sm:p-6 space-y-4" data-agencies-ownership>
                <div>
                    <p class="font-semibold text-hb-navy" data-agencies-recruiter-name>—</p>
                    <p class="text-sm text-hb-muted">
                        {{ __('admin.agencies.assigned_recruiter') }} ·
                        <a href="#" class="text-hb-blue hover:underline" data-agencies-agency-link>—</a>
                    </p>
                </div>
                <div>
                    <p class="font-semibold text-hb-navy" data-agencies-owner-name>—</p>
                    <p class="text-sm text-hb-muted">
                        {{ __('admin.agencies.client_owner') }} ·
                        <a href="#" class="text-hb-blue hover:underline" data-agencies-client-link>—</a>
                    </p>
                </div>
                <ul class="space-y-1 text-sm text-hb-muted" data-agencies-status-log></ul>
                <button type="button" class="hb-btn-outline w-full justify-center" data-open-modal="admin-agencies-record-modal">
                    {{ __('admin.agencies.open_record') }}
                </button>
            </aside>
        </div>
    </div>

    @include('layouts.modal', [
        'modalId' => 'admin-agencies-create-modal',
        'modalTitle' => __('admin.agencies.create_soon_title'),
        'modalBody' => '<p>'.e(__('admin.agencies.create_soon_body')).'</p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button>',
    ])

    @include('layouts.modal', [
        'modalId' => 'admin-agencies-record-modal',
        'modalTitle' => __('admin.agencies.record_modal_title'),
        'modalBody' => '<p>'.e(__('admin.agencies.record_modal_body')).'</p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button>',
    ])
@endsection
