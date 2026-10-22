@extends('layouts.app')

@section('title', $pageTitle ?? __('admin.settings.page_title'))
@section('breadcrumb', __('admin.settings.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ __('admin.settings.title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ __('admin.settings.intro') }}</p>
@endsection

@section('content')
    @php
        $allowedTabs = ['audit', 'security', 'permissions', 'integrations'];
        $defaultTab = request()->query('tab', $settingsPage['default_tab'] ?? 'audit');
        if (! in_array($defaultTab, $allowedTabs, true)) {
            $defaultTab = 'audit';
        }
        $initialQuery = (string) request()->query('q', '');
        $settingsI18n = [
            'showing_range' => __('admin.settings.js.showing_range'),
            'showing_zero' => __('admin.settings.js.showing_zero'),
            'empty' => __('admin.settings.empty'),
            'result_success' => __('admin.settings.result_success'),
            'result_blocked' => __('admin.settings.result_blocked'),
            'preview_id' => __('admin.settings.preview_id'),
            'preview_actor' => __('admin.settings.preview_actor'),
            'preview_action' => __('admin.settings.preview_action'),
            'preview_object' => __('admin.settings.preview_object'),
            'preview_result' => __('admin.settings.preview_result'),
            'preview_time' => __('admin.settings.preview_time'),
            'preview_description' => __('admin.settings.preview_description'),
            'status_connected' => __('admin.settings.status_connected'),
            'status_unavailable' => __('admin.settings.status_unavailable'),
            'previous' => __('common.actions.previous'),
            'next' => __('common.actions.next'),
        ];
        $resultLabels = [
            'success' => __('admin.settings.result_success'),
            'blocked' => __('admin.settings.result_blocked'),
        ];
        $typeLabels = [
            'moderation' => __('admin.settings.type_moderation'),
            'access' => __('admin.settings.type_access'),
            'agency' => __('admin.settings.type_agency'),
            'integrations' => __('admin.settings.type_integrations'),
            'identity' => __('admin.settings.type_identity'),
            'offers' => __('admin.settings.type_offers'),
            'interviews' => __('admin.settings.type_interviews'),
            'security' => __('admin.settings.type_security'),
        ];
        $policyStateLabels = [
            'enforced' => __('admin.settings.state_enforced'),
            'documented' => __('admin.settings.state_documented'),
            'routed' => __('admin.settings.state_routed'),
        ];
    @endphp

    <div
        data-admin-settings
        data-tabs
        id="admin-settings-tabs"
        data-page-size="{{ $settingsPage['page_size'] }}"
        data-default-tab="{{ $defaultTab }}"
        data-default-range="{{ $settingsPage['default_range'] }}"
        data-default-result="{{ $settingsPage['default_result'] }}"
        data-default-sort="{{ $settingsPage['default_sort'] }}"
        data-initial-q="{{ $initialQuery }}"
        data-i18n="{{ json_encode($settingsI18n, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
    >
        <div
            class="flex flex-wrap gap-4 border-b border-hb-border text-sm font-semibold"
            role="tablist"
            aria-label="{{ __('admin.settings.tablist_aria') }}"
        >
            @foreach ([
                ['audit', __('admin.settings.tab_audit')],
                ['security', __('admin.settings.tab_security')],
                ['permissions', __('admin.settings.tab_permissions')],
                ['integrations', __('admin.settings.tab_integrations')],
            ] as [$key, $label])
                <button
                    type="button"
                    role="tab"
                    id="admin-settings-tabs-tab-{{ $key }}"
                    aria-controls="admin-settings-tabs-panel-{{ $key }}"
                    aria-selected="{{ $key === $defaultTab ? 'true' : 'false' }}"
                    tabindex="{{ $key === $defaultTab ? '0' : '-1' }}"
                    data-settings-tab="{{ $key }}"
                    class="border-b-2 px-1 pb-3 {{ $key === $defaultTab ? 'border-hb-blue text-hb-blue' : 'border-transparent text-hb-muted hover:text-hb-navy' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Audit log --}}
        <div
            id="admin-settings-tabs-panel-audit"
            role="tabpanel"
            aria-labelledby="admin-settings-tabs-tab-audit"
            class="{{ $defaultTab === 'audit' ? '' : 'hidden' }}"
            @if ($defaultTab !== 'audit') hidden @endif
        >
            <div class="mt-6 grid gap-4 lg:grid-cols-2">
                <section class="hb-surface p-5 sm:p-6" aria-labelledby="settings-security-card-heading">
                    <div class="flex items-center gap-2">
                        <span class="inline-block h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true"></span>
                        <p id="settings-security-card-heading" class="text-sm font-semibold text-hb-navy">
                            {{ __('admin.settings.state_enforced') }}
                        </p>
                    </div>
                    <h2 class="sr-only">{{ __('admin.settings.card_security_title') }}</h2>
                    <ul class="mt-4 list-disc space-y-2 ps-5 text-sm text-hb-text">
                        <li>{{ __('admin.settings.bullet_mfa_required') }}</li>
                        <li>{{ __('admin.settings.bullet_session_expiry') }}</li>
                        <li>{{ __('admin.settings.bullet_retention') }}</li>
                        <li>{{ __('admin.settings.bullet_data_requests') }}</li>
                    </ul>
                    <button
                        type="button"
                        class="hb-btn-outline mt-5"
                        data-settings-goto-tab="security"
                    >
                        {{ __('admin.settings.review_policies') }}
                    </button>
                </section>

                <section class="hb-surface p-5 sm:p-6" aria-labelledby="settings-permissions-card-heading">
                    <h2 id="settings-permissions-card-heading" class="text-sm font-semibold text-hb-navy">
                        {{ __('admin.settings.card_permissions_title') }}
                    </h2>
                    <ul class="mt-4 list-disc space-y-2 ps-5 text-sm text-hb-text">
                        <li>{{ __('admin.settings.bullet_super_admin_accounts') }}</li>
                        <li>{{ __('admin.settings.bullet_email_connected') }}</li>
                        <li>{{ __('admin.settings.bullet_calendar_connected') }}</li>
                        <li>{{ __('admin.settings.bullet_video_connected') }}</li>
                    </ul>
                    <p class="mt-4 flex items-center gap-2 text-sm text-hb-muted">
                        <span class="inline-block h-2 w-2 rounded-full bg-hb-muted" aria-hidden="true"></span>
                        {{ __('admin.settings.last_sync', ['when' => $settingsPage['permissions_card']['last_sync']]) }}
                    </p>
                    <button
                        type="button"
                        class="hb-btn-outline mt-5"
                        data-settings-goto-tab="integrations"
                    >
                        {{ __('admin.settings.manage_integrations') }}
                    </button>
                </section>
            </div>

            <section class="hb-surface mt-6 overflow-hidden" aria-labelledby="settings-audit-heading">
                <div class="border-b border-hb-border px-5 py-4 sm:px-6">
                    <h2 id="settings-audit-heading" class="text-base font-semibold text-hb-navy">
                        {{ __('admin.settings.audit_heading') }}
                    </h2>
                    <p class="mt-1 text-sm text-hb-muted">{{ __('admin.settings.audit_intro') }}</p>
                </div>

                <div class="flex flex-col gap-3 border-b border-hb-border px-5 py-4 sm:flex-row sm:flex-wrap sm:items-center sm:px-6">
                    <label class="relative min-w-0 flex-1">
                        <span class="sr-only">{{ __('admin.settings.search_placeholder') }}</span>
                        <span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-hb-muted" aria-hidden="true">⌕</span>
                        <input
                            type="search"
                            class="hb-input ps-9"
                            placeholder="{{ __('admin.settings.search_placeholder') }}"
                            data-settings-q
                            value="{{ $initialQuery }}"
                            autocomplete="off"
                        >
                    </label>
                    <label class="shrink-0">
                        <span class="sr-only">{{ __('admin.settings.filter_range') }}</span>
                        <select class="hb-input min-w-44" data-settings-range aria-label="{{ __('admin.settings.filter_range') }}">
                            <option value="oct19_20" selected>{{ __('admin.settings.range_oct19_20') }}</option>
                            <option value="earlier">{{ __('admin.settings.range_earlier') }}</option>
                            <option value="all">{{ __('admin.settings.range_all') }}</option>
                        </select>
                    </label>
                    <label class="shrink-0">
                        <span class="sr-only">{{ __('admin.settings.filter_result') }}</span>
                        <select class="hb-input min-w-40" data-settings-result aria-label="{{ __('admin.settings.filter_result') }}">
                            <option value="all" selected>{{ __('admin.settings.result_all') }}</option>
                            <option value="success">{{ __('admin.settings.result_success') }}</option>
                            <option value="blocked">{{ __('admin.settings.result_blocked') }}</option>
                        </select>
                    </label>
                    <label class="shrink-0">
                        <span class="sr-only">{{ __('admin.settings.filter_type') }}</span>
                        <select class="hb-input min-w-40" data-settings-type aria-label="{{ __('admin.settings.filter_type') }}">
                            <option value="all" selected>{{ __('admin.settings.type_all') }}</option>
                            @foreach ($typeLabels as $typeKey => $typeLabel)
                                <option value="{{ $typeKey }}">{{ $typeLabel }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="shrink-0">
                        <span class="sr-only">{{ __('admin.settings.sort_label') }}</span>
                        <select class="hb-input min-w-40" data-settings-sort aria-label="{{ __('admin.settings.sort_label') }}">
                            <option value="newest" selected>{{ __('admin.settings.sort_newest') }}</option>
                            <option value="oldest">{{ __('admin.settings.sort_oldest') }}</option>
                        </select>
                    </label>
                    <button type="button" class="hb-btn-outline shrink-0" data-settings-reset>
                        {{ __('admin.settings.clear_filters') }}
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-start text-sm">
                        <caption class="sr-only">{{ __('admin.settings.audit_caption') }}</caption>
                        <thead class="border-b border-hb-border bg-hb-surface-soft text-xs font-semibold tracking-wide text-hb-muted uppercase">
                            <tr>
                                <th scope="col" class="px-5 py-3">{{ __('admin.settings.col_time') }}</th>
                                <th scope="col" class="px-4 py-3">{{ __('admin.settings.col_actor') }}</th>
                                <th scope="col" class="px-4 py-3">{{ __('admin.settings.col_action') }}</th>
                                <th scope="col" class="px-4 py-3">{{ __('admin.settings.col_object') }}</th>
                                <th scope="col" class="px-5 py-3">{{ __('admin.settings.col_result') }}</th>
                            </tr>
                        </thead>
                        <tbody data-settings-tbody>
                            @foreach ($settingsPage['events'] as $event)
                                <tr
                                    class="cursor-pointer border-b border-hb-border last:border-0 hover:bg-hb-info-soft/40 focus-within:bg-hb-info-soft/40"
                                    data-audit-row
                                    data-id="{{ $event['id'] }}"
                                    data-search="{{ $event['search'] }}"
                                    data-range="{{ $event['range'] }}"
                                    data-result="{{ $event['result'] }}"
                                    data-type="{{ $event['type'] }}"
                                    data-sort-time="{{ $event['sort_time'] }}"
                                    data-actor="{{ $event['actor'] }}"
                                    data-action="{{ $event['action'] }}"
                                    data-object="{{ $event['object'] }}"
                                    data-time="{{ $event['time'] }}"
                                    data-description="{{ $event['description'] }}"
                                    data-result-label="{{ $resultLabels[$event['result']] ?? $event['result'] }}"
                                    tabindex="0"
                                >
                                    <td class="px-5 py-3 whitespace-nowrap text-hb-muted">
                                        <time dir="ltr">{{ $event['time'] }}</time>
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-hb-navy">{{ $event['actor'] }}</td>
                                    <td class="px-4 py-3 text-hb-text">{{ $event['action'] }}</td>
                                    <td class="px-4 py-3 text-hb-muted">{{ $event['object'] }}</td>
                                    <td class="px-5 py-3">
                                        @if ($event['result'] === 'blocked')
                                            <span class="font-semibold text-hb-error">{{ __('admin.settings.result_blocked_label') }}</span>
                                        @else
                                            <span class="font-semibold text-emerald-700 dark:text-emerald-400">{{ __('admin.settings.result_success') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="hidden border-t border-hb-border px-5 py-8 text-center text-sm text-hb-muted" data-settings-empty role="status">
                    {{ __('admin.settings.empty') }}
                </p>

                <div
                    class="flex flex-col gap-3 border-t border-hb-border px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                    data-settings-pagination
                    data-status-template="{{ __('admin.settings.js.showing_range') }}"
                >
                    <p class="text-sm text-hb-muted" data-settings-page-status role="status"></p>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" class="hb-btn-outline" data-settings-page-prev disabled>
                            {{ __('common.actions.previous') }}
                        </button>
                        <div class="flex items-center gap-1" data-settings-page-numbers></div>
                        <button type="button" class="hb-btn-outline" data-settings-page-next>
                            {{ __('common.actions.next') }}
                        </button>
                    </div>
                </div>
            </section>

            <section class="hb-surface mt-6 flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6" aria-labelledby="settings-restricted-heading">
                <div class="min-w-0">
                    <h2 id="settings-restricted-heading" class="text-base font-semibold text-hb-navy">
                        {{ __('admin.settings.restricted_heading') }}
                    </h2>
                    <p class="mt-2 text-sm text-hb-muted">{{ __('admin.settings.restricted_body') }}</p>
                </div>
                <button
                    type="button"
                    class="hb-btn-destructive shrink-0"
                    data-open-modal="admin-settings-restricted-modal"
                >
                    {{ __('admin.settings.restricted_action') }}
                </button>
            </section>
        </div>

        {{-- Security & privacy --}}
        <div
            id="admin-settings-tabs-panel-security"
            role="tabpanel"
            aria-labelledby="admin-settings-tabs-tab-security"
            class="{{ $defaultTab === 'security' ? '' : 'hidden' }}"
            @if ($defaultTab !== 'security') hidden @endif
        >
            <div class="mt-6 space-y-4">
                <p class="text-sm text-hb-muted">{{ __('admin.settings.security_intro') }}</p>
                @foreach ($settingsPage['security_policies'] as $policy)
                    <section class="hb-surface p-5 sm:p-6" aria-labelledby="policy-{{ $policy['id'] }}-heading">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 id="policy-{{ $policy['id'] }}-heading" class="text-base font-semibold text-hb-navy">
                                    {{ __('admin.settings.'.$policy['title_key']) }}
                                </h2>
                                <p class="mt-2 text-sm text-hb-muted">{{ __('admin.settings.'.$policy['body_key']) }}</p>
                            </div>
                            <x-badge variant="{{ $policy['state_key'] === 'enforced' ? 'success' : 'neutral' }}" :dot="true">
                                {{ $policyStateLabels[$policy['state_key']] ?? $policy['state_key'] }}
                            </x-badge>
                        </div>
                        <button
                            type="button"
                            class="hb-btn-outline mt-4"
                            data-open-modal="admin-settings-policy-modal"
                            data-policy-title="{{ __('admin.settings.'.$policy['title_key']) }}"
                        >
                            {{ __('admin.settings.review_policy') }}
                        </button>
                    </section>
                @endforeach
            </div>
        </div>

        {{-- Permissions --}}
        <div
            id="admin-settings-tabs-panel-permissions"
            role="tabpanel"
            aria-labelledby="admin-settings-tabs-tab-permissions"
            class="{{ $defaultTab === 'permissions' ? '' : 'hidden' }}"
            @if ($defaultTab !== 'permissions') hidden @endif
        >
            <div class="mt-6">
                <p class="text-sm text-hb-muted">{{ __('admin.settings.permissions_intro') }}</p>
                <div class="mt-4 overflow-hidden rounded-xl border border-hb-border bg-hb-surface">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-start text-sm">
                            <caption class="sr-only">{{ __('admin.settings.permissions_caption') }}</caption>
                            <thead class="border-b border-hb-border bg-hb-surface-soft text-xs font-semibold tracking-wide text-hb-muted uppercase">
                                <tr>
                                    <th scope="col" class="px-5 py-3">{{ __('admin.settings.col_role') }}</th>
                                    <th scope="col" class="px-4 py-3">{{ __('admin.settings.col_accounts') }}</th>
                                    <th scope="col" class="px-4 py-3">{{ __('admin.settings.col_scopes') }}</th>
                                    <th scope="col" class="px-5 py-3">{{ __('admin.settings.col_notes') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($settingsPage['roles'] as $role)
                                    <tr class="border-b border-hb-border last:border-0" data-role-row data-role-id="{{ $role['id'] }}">
                                        <td class="px-5 py-4 font-semibold text-hb-navy">{{ $role['name'] }}</td>
                                        <td class="px-4 py-4 text-hb-text" dir="ltr">{{ $role['accounts'] }}</td>
                                        <td class="px-4 py-4 text-hb-muted">{{ implode(' · ', $role['scopes']) }}</td>
                                        <td class="px-5 py-4">
                                            <button
                                                type="button"
                                                class="font-semibold text-hb-blue hover:underline"
                                                data-open-modal="admin-settings-role-modal"
                                                data-role-name="{{ $role['name'] }}"
                                                data-role-notes="{{ __('admin.settings.'.$role['notes_key']) }}"
                                            >
                                                {{ __('admin.settings.inspect_role') }}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <p class="mt-4 text-sm text-hb-muted">{{ __('admin.settings.permissions_readonly') }}</p>
            </div>
        </div>

        {{-- Integrations --}}
        <div
            id="admin-settings-tabs-panel-integrations"
            role="tabpanel"
            aria-labelledby="admin-settings-tabs-tab-integrations"
            class="{{ $defaultTab === 'integrations' ? '' : 'hidden' }}"
            @if ($defaultTab !== 'integrations') hidden @endif
        >
            <div class="mt-6">
                <p class="text-sm text-hb-muted">{{ __('admin.settings.integrations_intro') }}</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    @foreach ($settingsPage['integrations'] as $integration)
                        <section class="hb-surface p-5 sm:p-6" aria-labelledby="integration-{{ $integration['id'] }}-heading">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h2 id="integration-{{ $integration['id'] }}-heading" class="text-base font-semibold text-hb-navy">
                                        {{ $integration['name'] }}
                                    </h2>
                                    <p class="mt-1 text-sm text-hb-muted">{{ $integration['provider'] }}</p>
                                </div>
                                @if ($integration['status'] === 'connected')
                                    <x-badge variant="success" :dot="true">{{ __('admin.settings.status_connected') }}</x-badge>
                                @else
                                    <x-badge variant="neutral" :dot="true">{{ __('admin.settings.status_unavailable') }}</x-badge>
                                @endif
                            </div>
                            <p class="mt-3 text-sm text-hb-text">{{ __('admin.settings.'.$integration['capability_key']) }}</p>
                            <p class="mt-2 text-xs text-hb-muted">
                                {{ __('admin.settings.last_sync', ['when' => $integration['last_sync']]) }}
                            </p>
                            <button
                                type="button"
                                class="hb-btn-outline mt-4"
                                data-open-modal="admin-settings-integration-modal"
                                data-integration-name="{{ $integration['name'] }}"
                                data-integration-status="{{ $integration['status'] }}"
                                data-integration-provider="{{ $integration['provider'] }}"
                            >
                                {{ __('admin.settings.review_integration') }}
                            </button>
                        </section>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @include('layouts.modal', [
        'modalId' => 'admin-settings-event-modal',
        'modalTitle' => __('admin.settings.event_modal_title'),
        'modalSize' => 'md',
        'modalBody' => '<dl class="space-y-3" data-settings-event-dl></dl><p class="mt-4 text-sm text-hb-muted">'.e(__('admin.settings.event_modal_note')).'</p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button>',
    ])

    @include('layouts.modal', [
        'modalId' => 'admin-settings-restricted-modal',
        'modalTitle' => __('admin.settings.restricted_modal_title'),
        'modalBody' => '<p>'.e(__('admin.settings.restricted_modal_body')).'</p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button>',
    ])

    @include('layouts.modal', [
        'modalId' => 'admin-settings-policy-modal',
        'modalTitle' => __('admin.settings.policy_modal_title'),
        'modalBody' => '<p data-settings-policy-body>'.e(__('admin.settings.policy_modal_body')).'</p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button>',
    ])

    @include('layouts.modal', [
        'modalId' => 'admin-settings-role-modal',
        'modalTitle' => __('admin.settings.role_modal_title'),
        'modalBody' => '<p data-settings-role-body class="text-sm text-hb-text"></p><p class="mt-4 text-sm text-hb-muted">'.e(__('admin.settings.role_modal_note')).'</p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button>',
    ])

    @include('layouts.modal', [
        'modalId' => 'admin-settings-integration-modal',
        'modalTitle' => __('admin.settings.integration_modal_title'),
        'modalBody' => '<p data-settings-integration-body class="text-sm text-hb-text"></p><p class="mt-4 text-sm text-hb-muted">'.e(__('admin.settings.integration_modal_note')).'</p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button>',
    ])
@endsection
