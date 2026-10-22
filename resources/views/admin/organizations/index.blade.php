@extends('layouts.app')

@section('title', $pageTitle ?? __('admin.organizations.page_title'))
@section('breadcrumb', __('admin.organizations.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ __('admin.organizations.title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ __('admin.organizations.intro') }}</p>
@endsection

@section('page_actions')
    <button
        type="button"
        class="hb-btn-primary"
        data-open-modal="admin-org-add-modal"
        title="{{ __('admin.organizations.add_soon_title') }}"
    >
        {{ __('admin.organizations.add') }}
    </button>
@endsection

@section('content')
    @php
        $defaultTab = request()->query('tab', $orgsPage['default_tab'] ?? 'active');
        $allowedTabs = collect($orgsPage['tabs'])->pluck('key')->all();
        if (! in_array($defaultTab, $allowedTabs, true)) {
            $defaultTab = 'active';
        }
        $initialQuery = (string) request()->query('q', '');
        $orgsI18n = [
            'showing_range' => __('admin.organizations.js.showing_range'),
            'showing_zero' => __('admin.organizations.js.showing_zero'),
            'label_active' => __('admin.organizations.js.label_active'),
            'label_pending' => __('admin.organizations.js.label_pending'),
            'label_suspended' => __('admin.organizations.js.label_suspended'),
            'label_archived' => __('admin.organizations.js.label_archived'),
            'label_filtered' => __('admin.organizations.js.label_filtered'),
            'previous' => __('admin.organizations.js.previous'),
            'next' => __('admin.organizations.js.next'),
        ];
    @endphp

    <div
        data-admin-organizations
        data-page-size="{{ $orgsPage['page_size'] }}"
        data-default-tab="{{ $defaultTab }}"
        data-initial-q="{{ $initialQuery }}"
        data-i18n="{{ json_encode($orgsI18n, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
    >
        <div
            class="flex flex-wrap gap-4 border-b border-hb-border text-sm font-semibold"
            role="tablist"
            aria-label="{{ __('admin.organizations.tablist_aria') }}"
        >
            @foreach ($orgsPage['tabs'] as $tab)
                @php
                    $tabLabel = match ($tab['key']) {
                        'pending' => __('admin.organizations.tab_pending'),
                        'suspended' => __('admin.organizations.tab_suspended'),
                        'archived' => __('admin.organizations.tab_archived'),
                        default => __('admin.organizations.tab_active'),
                    };
                @endphp
                <button
                    type="button"
                    role="tab"
                    id="orgs-tab-{{ $tab['key'] }}"
                    aria-selected="{{ $tab['key'] === $defaultTab ? 'true' : 'false' }}"
                    data-orgs-tab="{{ $tab['key'] }}"
                    class="border-b-2 px-1 pb-3 {{ $tab['key'] === $defaultTab ? 'border-hb-blue text-hb-blue' : 'border-transparent text-hb-muted hover:text-hb-navy' }}"
                >
                    {{ $tabLabel }} ({{ $tab['count'] }})
                </button>
            @endforeach
        </div>

        <div class="mt-5 flex flex-col gap-3 lg:flex-row lg:items-center">
            <label class="relative min-w-0 flex-1">
                <span class="sr-only">{{ __('admin.organizations.search_placeholder') }}</span>
                <span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-hb-muted" aria-hidden="true">⌕</span>
                <input
                    type="search"
                    class="hb-input ps-9"
                    placeholder="{{ __('admin.organizations.search_placeholder') }}"
                    data-orgs-q
                    value="{{ $initialQuery }}"
                    autocomplete="off"
                >
            </label>
            <label class="shrink-0">
                <span class="sr-only">{{ __('admin.organizations.filter_type') }}</span>
                <select class="hb-input min-w-44" data-orgs-type aria-label="{{ __('admin.organizations.filter_type') }}">
                    @foreach ($orgsPage['type_filters'] as $filter)
                        <option value="{{ $filter['key'] }}">{{ __('admin.organizations.'.$filter['label_key']) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="shrink-0">
                <span class="sr-only">{{ __('admin.organizations.filter_plan') }}</span>
                <select class="hb-input min-w-40" data-orgs-plan aria-label="{{ __('admin.organizations.filter_plan') }}">
                    @foreach ($orgsPage['plan_filters'] as $filter)
                        <option value="{{ $filter['key'] }}">{{ __('admin.organizations.'.$filter['label_key']) }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <div class="mt-5 overflow-hidden rounded-xl border border-hb-border bg-hb-surface">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[52rem] text-start text-sm">
                    <caption class="sr-only">{{ __('admin.organizations.title') }}</caption>
                    <thead class="bg-hb-page text-xs font-semibold tracking-wide text-hb-muted uppercase">
                        <tr>
                            <th scope="col" class="px-5 py-3">{{ __('admin.organizations.col_org') }}</th>
                            <th scope="col" class="px-4 py-3">{{ __('admin.organizations.col_plan') }}</th>
                            <th scope="col" class="px-4 py-3">{{ __('admin.organizations.col_verification') }}</th>
                            <th scope="col" class="px-4 py-3">{{ __('admin.organizations.col_owner') }}</th>
                            <th scope="col" class="px-5 py-3">{{ __('admin.organizations.col_action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-hb-border" data-orgs-tbody>
                        @foreach ($orgsPage['organizations'] as $org)
                            @php
                                $typeLabel = $org['type'] === 'agency'
                                    ? __('admin.organizations.type_agency')
                                    : __('admin.organizations.type_company');
                                $planLabel = match ($org['plan']) {
                                    'growth' => __('admin.organizations.plan_label_growth'),
                                    'agency_partner' => __('admin.organizations.plan_label_agency_partner'),
                                    default => __('admin.organizations.plan_label_business'),
                                };
                                $verificationLabel = match ($org['verification']) {
                                    'pending' => __('admin.organizations.verification_pending'),
                                    'rejected' => __('admin.organizations.verification_rejected'),
                                    'suspended' => __('admin.organizations.verification_suspended'),
                                    'archived' => __('admin.organizations.verification_archived'),
                                    default => __('admin.organizations.verification_verified_active'),
                                };
                                $actionLabel = $org['action_kind'] === 'roles'
                                    ? __('admin.organizations.roles_count', ['count' => $org['active_count']])
                                    : __('admin.organizations.jobs_count', ['count' => $org['active_count']]);
                                $viewLabel = $org['action_kind'] === 'roles'
                                    ? __('admin.organizations.view_roles')
                                    : __('admin.organizations.view_organization');
                                $showUrl = route('admin.organizations.show', $org['id']);
                            @endphp
                            <tr
                                data-org-row
                                data-tab="{{ $org['tab'] }}"
                                data-type="{{ $org['type'] }}"
                                data-plan="{{ $org['plan'] }}"
                                data-search="{{ $org['search'] }}"
                                data-name="{{ $org['name'] }}"
                                data-id="{{ $org['id'] }}"
                                hidden
                            >
                                <td class="px-5 py-4">
                                    <a href="{{ $showUrl }}" class="font-semibold text-hb-navy hover:text-hb-blue hover:underline">
                                        {{ $org['name'] }}
                                    </a>
                                    <p class="text-xs text-hb-muted">{{ $typeLabel }}</p>
                                </td>
                                <td class="px-4 py-4 text-hb-text">{{ $planLabel }}</td>
                                <td class="px-4 py-4 text-hb-text">{{ $verificationLabel }}</td>
                                <td class="px-4 py-4 text-hb-text">{{ $org['owner'] }}</td>
                                <td class="px-5 py-4">
                                    <span class="text-hb-muted" dir="ltr">{{ $actionLabel }}</span>
                                    <span class="text-hb-muted" aria-hidden="true"> · </span>
                                    <a href="{{ $showUrl }}" class="font-semibold text-hb-blue hover:underline">
                                        {{ $viewLabel }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="hidden border-t border-hb-border px-5 py-8 text-center text-sm text-hb-muted" data-orgs-empty role="status">
                {{ __('admin.organizations.empty') }}
            </p>

            <div
                class="flex flex-col gap-3 border-t border-hb-border px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                data-orgs-pagination
                data-status-template="{{ __('admin.organizations.js.showing_range') }}"
            >
                <p class="text-sm text-hb-muted" data-orgs-page-status role="status"></p>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="hb-btn-outline" data-orgs-page-prev disabled>
                        {{ __('common.actions.previous') }}
                    </button>
                    <div class="flex items-center gap-1" data-orgs-page-numbers></div>
                    <button type="button" class="hb-btn-outline" data-orgs-page-next>
                        {{ __('common.actions.next') }}
                    </button>
                </div>
            </div>
        </div>

        <section class="mt-6 rounded-xl border border-hb-border bg-hb-surface p-5 sm:p-6" aria-labelledby="orgs-lifecycle-heading">
            <h2 id="orgs-lifecycle-heading" class="text-base font-semibold text-hb-navy">
                {{ __('admin.organizations.lifecycle_heading') }}
            </h2>
            <p class="mt-2 text-sm text-hb-muted">
                {{ __('admin.organizations.lifecycle_body', [
                    'pending' => $orgsPage['lifecycle']['pending_names'],
                    'suspended' => $orgsPage['lifecycle']['suspended_names'],
                ]) }}
            </p>
            <div class="mt-4 flex flex-wrap gap-3">
                <button type="button" class="hb-btn-outline" data-orgs-goto-tab="pending">
                    {{ __('admin.organizations.review_pending') }}
                </button>
                <button type="button" class="text-sm font-semibold text-hb-blue hover:underline" data-orgs-goto-tab="suspended">
                    {{ __('admin.organizations.view_suspended') }}
                </button>
            </div>
        </section>
    </div>

    @include('layouts.modal', [
        'modalId' => 'admin-org-add-modal',
        'modalTitle' => __('admin.organizations.add_soon_title'),
        'modalBody' => '<p>'.e(__('admin.organizations.add_soon_body')).'</p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button>',
    ])
@endsection
