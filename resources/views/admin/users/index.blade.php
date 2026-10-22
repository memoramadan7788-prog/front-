@extends('layouts.app')

@section('title', $pageTitle ?? __('admin.users.page_title'))
@section('breadcrumb', __('admin.users.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ __('admin.users.title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ __('admin.users.intro') }}</p>
@endsection

@section('page_actions')
    <button
        type="button"
        class="hb-btn-primary"
        data-open-modal="admin-users-invite-modal"
        title="{{ __('admin.users.invite_soon_title') }}"
    >
        {{ __('admin.users.invite') }}
    </button>
@endsection

@section('content')
    @php
        $defaultTab = request()->query('tab', $usersPage['default_tab'] ?? 'all');
        $allowedTabs = collect($usersPage['tabs'])->pluck('key')->all();
        if (! in_array($defaultTab, $allowedTabs, true)) {
            $defaultTab = 'all';
        }
        $initialQuery = (string) request()->query('q', '');
        $roleLabels = [
            'candidate' => __('admin.users.role_candidate'),
            'company_admin' => __('admin.users.role_company_admin'),
            'hiring_manager' => __('admin.users.role_hiring_manager'),
            'interviewer' => __('admin.users.role_interviewer'),
            'agency_recruiter' => __('admin.users.role_agency_recruiter'),
            'agency_admin' => __('admin.users.role_agency_admin'),
            'super_admin' => __('admin.users.role_super_admin'),
        ];
        $stateLabels = [
            'active' => __('admin.users.state_active'),
            'invited' => __('admin.users.state_invited'),
            'suspended' => __('admin.users.state_suspended'),
        ];
        $actionLabels = [
            'view_account' => __('admin.users.action_view_account'),
            'view_access' => __('admin.users.action_view_access'),
            'view_own' => __('admin.users.action_view_own'),
            'invite_actions' => __('admin.users.action_invite'),
            'support_case' => __('admin.users.action_support_case'),
        ];
        $usersI18n = [
            'showing_range' => __('admin.users.js.showing_range'),
            'showing_zero' => __('admin.users.js.showing_zero'),
            'label_all' => __('admin.users.js.label_all'),
            'label_active' => __('admin.users.js.label_active'),
            'label_invited' => __('admin.users.js.label_invited'),
            'label_suspended' => __('admin.users.js.label_suspended'),
            'label_filtered' => __('admin.users.js.label_filtered'),
            'previous' => __('admin.users.js.previous'),
            'next' => __('admin.users.js.next'),
            'preview_email' => __('admin.users.preview_email'),
            'preview_organization' => __('admin.users.preview_organization'),
            'preview_role' => __('admin.users.preview_role'),
            'preview_account_type' => __('admin.users.preview_account_type'),
            'preview_platform_role' => __('admin.users.preview_platform_role'),
            'preview_company_role' => __('admin.users.preview_company_role'),
            'preview_membership' => __('admin.users.preview_membership'),
            'preview_state' => __('admin.users.preview_state'),
            'preview_last_active' => __('admin.users.preview_last_active'),
            'membership_none' => __('status_vocab.membership.none'),
            'membership_pending' => __('status_vocab.membership.pending'),
            'membership_approved' => __('status_vocab.membership.approved'),
            'membership_rejected' => __('status_vocab.membership.rejected'),
            'membership_restricted' => __('admin.users.membership_restricted'),
            'sort_name_asc' => __('admin.users.sort_name_asc'),
            'sort_name_desc' => __('admin.users.sort_name_desc'),
            'sort_active_asc' => __('admin.users.sort_active_asc'),
            'sort_active_desc' => __('admin.users.sort_active_desc'),
        ];
    @endphp

    <div
        data-admin-users
        data-page-size="{{ $usersPage['page_size'] }}"
        data-default-tab="{{ $defaultTab }}"
        data-initial-q="{{ $initialQuery }}"
        data-i18n="{{ json_encode($usersI18n, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
    >
        <div
            class="flex flex-wrap gap-4 border-b border-hb-border text-sm font-semibold"
            role="tablist"
            aria-label="{{ __('admin.users.tablist_aria') }}"
        >
            @foreach ($usersPage['tabs'] as $tab)
                @php
                    $tabLabel = match ($tab['key']) {
                        'active' => __('admin.users.tab_active'),
                        'invited' => __('admin.users.tab_invited'),
                        'suspended' => __('admin.users.tab_suspended'),
                        default => __('admin.users.tab_all'),
                    };
                @endphp
                <button
                    type="button"
                    role="tab"
                    id="users-tab-{{ $tab['key'] }}"
                    aria-selected="{{ $tab['key'] === $defaultTab ? 'true' : 'false' }}"
                    data-users-tab="{{ $tab['key'] }}"
                    class="border-b-2 px-1 pb-3 {{ $tab['key'] === $defaultTab ? 'border-hb-blue text-hb-blue' : 'border-transparent text-hb-muted hover:text-hb-navy' }}"
                >
                    {{ $tabLabel }} ({{ $tab['count'] }})
                </button>
            @endforeach
        </div>

        <div class="mt-5 flex flex-col gap-3 lg:flex-row lg:items-center">
            <label class="relative min-w-0 flex-1">
                <span class="sr-only">{{ __('admin.users.search_placeholder') }}</span>
                <span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-hb-muted" aria-hidden="true">⌕</span>
                <input
                    type="search"
                    class="hb-input ps-9"
                    placeholder="{{ __('admin.users.search_placeholder') }}"
                    data-users-q
                    value="{{ $initialQuery }}"
                    autocomplete="off"
                >
            </label>
            <label class="shrink-0">
                <span class="sr-only">{{ __('admin.users.filter_role') }}</span>
                <select class="hb-input min-w-44" data-users-role aria-label="{{ __('admin.users.filter_role') }}">
                    <option value="all">{{ __('admin.users.role_all') }}</option>
                    @foreach ($usersPage['role_filters'] as $roleKey)
                        <option value="{{ $roleKey }}">{{ $roleLabels[$roleKey] ?? $roleKey }}</option>
                    @endforeach
                </select>
            </label>
            <label class="shrink-0">
                <span class="sr-only">{{ __('admin.users.filter_organization') }}</span>
                <select class="hb-input min-w-48" data-users-org aria-label="{{ __('admin.users.filter_organization') }}">
                    <option value="all">{{ __('admin.users.org_all') }}</option>
                    @foreach ($usersPage['organization_filters'] as $orgName)
                        <option value="{{ $orgName }}">{{ $orgName }}</option>
                    @endforeach
                </select>
            </label>
            <label class="shrink-0">
                <span class="sr-only">{{ __('admin.users.sort_label') }}</span>
                <select class="hb-input min-w-44" data-users-sort aria-label="{{ __('admin.users.sort_label') }}">
                    <option value="name_asc">{{ __('admin.users.sort_name_asc') }}</option>
                    <option value="name_desc">{{ __('admin.users.sort_name_desc') }}</option>
                    <option value="active_desc">{{ __('admin.users.sort_active_desc') }}</option>
                    <option value="active_asc">{{ __('admin.users.sort_active_asc') }}</option>
                </select>
            </label>
            <button type="button" class="hb-btn-outline shrink-0" data-users-reset>
                {{ __('admin.users.clear_filters') }}
            </button>
        </div>

        <div class="mt-5 overflow-hidden rounded-xl border border-hb-border bg-hb-surface">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[52rem] text-start text-sm">
                    <caption class="sr-only">{{ __('admin.users.title') }}</caption>
                    <thead class="bg-hb-page text-xs font-semibold tracking-wide text-hb-muted uppercase">
                        <tr>
                            <th scope="col" class="px-5 py-3">{{ __('admin.users.col_user') }}</th>
                            <th scope="col" class="px-4 py-3">{{ __('admin.users.col_organization') }}</th>
                            <th scope="col" class="px-4 py-3">{{ __('admin.users.col_role') }}</th>
                            <th scope="col" class="px-4 py-3">{{ __('admin.users.col_state') }}</th>
                            <th scope="col" class="px-5 py-3">{{ __('admin.users.col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-hb-border" data-users-tbody>
                        @foreach ($usersPage['users'] as $user)
                            @php
                                $roleLabel = $roleLabels[$user['role_key']] ?? $user['role_key'];
                                $stateLabel = $stateLabels[$user['state']] ?? $user['state'];
                                $actionLabel = $actionLabels[$user['action_key']] ?? __('admin.users.action_view_access');
                                $stateLine = $stateLabel.' · '.$user['last_active'];
                                $orgHref = ! empty($user['org_id'])
                                    ? route('admin.organizations.show', $user['org_id'])
                                    : null;
                                $membershipKey = (string) ($user['membership_status'] ?? 'none');
                                $membershipLabel = match ($membershipKey) {
                                    'pending' => __('status_vocab.membership.pending'),
                                    'approved' => __('status_vocab.membership.approved'),
                                    'rejected' => __('status_vocab.membership.rejected'),
                                    'restricted' => __('admin.users.membership_restricted'),
                                    default => __('status_vocab.membership.none'),
                                };
                                $accountTypeLabel = match ($user['account_type'] ?? '') {
                                    'candidate' => __('admin.users.account_type_candidate'),
                                    'company' => __('admin.users.account_type_company'),
                                    default => ($user['account_type'] ?? '—'),
                                };
                                $platformRoleLabel = ! empty($user['platform_role'])
                                    ? ($roleLabels[$user['platform_role']] ?? $user['platform_role'])
                                    : __('admin.users.platform_role_none');
                                $companyRoleLabel = ! empty($user['company_role'])
                                    ? ($roleLabels[$user['company_role']] ?? $user['company_role'])
                                    : __('admin.users.company_role_none');
                            @endphp
                            <tr
                                data-user-row
                                data-state="{{ $user['state'] }}"
                                data-role="{{ $user['role_key'] }}"
                                data-org="{{ $user['organization'] }}"
                                data-org-href="{{ $orgHref ?? '' }}"
                                data-search="{{ $user['search'] }}"
                                data-name="{{ $user['name'] }}"
                                data-email="{{ $user['email'] }}"
                                data-role-label="{{ $roleLabel }}"
                                data-account-type="{{ $accountTypeLabel }}"
                                data-platform-role="{{ $platformRoleLabel }}"
                                data-company-role="{{ $companyRoleLabel }}"
                                data-membership="{{ $membershipLabel }}"
                                data-membership-key="{{ $membershipKey }}"
                                data-state-label="{{ $stateLabel }}"
                                data-last-active="{{ $user['last_active'] }}"
                                data-sort-name="{{ strtolower($user['name']) }}"
                                data-sort-active="{{ $user['last_active'] }}"
                                data-action-key="{{ $user['action_key'] }}"
                                @if (! empty($user['membership_note'])) data-membership-note="{{ $user['membership_note'] }}" @endif
                                hidden
                            >
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-hb-navy">{{ $user['name'] }}</p>
                                    <p class="text-xs text-hb-muted" dir="ltr">{{ $user['email'] }}</p>
                                </td>
                                <td class="px-4 py-4 text-hb-text">
                                    @if ($orgHref)
                                        <a href="{{ $orgHref }}" class="font-semibold text-hb-blue hover:underline">
                                            {{ $user['organization'] }}
                                        </a>
                                    @else
                                        {{ $user['organization'] }}
                                    @endif
                                    @if ($membershipKey === 'pending')
                                        <p class="mt-1 text-xs text-hb-muted">{{ $membershipLabel }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-hb-text">
                                    <p>{{ $roleLabel }}</p>
                                    <p class="mt-1 text-xs text-hb-muted">{{ __('admin.users.account_type_line', ['type' => $accountTypeLabel]) }}</p>
                                </td>
                                <td class="px-4 py-4 text-hb-text">{{ $stateLine }}</td>
                                <td class="px-5 py-4">
                                    @if (($user['action_key'] ?? '') === 'invite_actions')
                                        <button type="button" class="font-semibold text-hb-blue hover:underline" data-user-preview data-open-review="invite">
                                            {{ __('admin.users.action_resend') }}
                                        </button>
                                        <span class="text-hb-muted" aria-hidden="true"> · </span>
                                        <button type="button" class="font-semibold text-hb-blue hover:underline" data-open-modal="admin-users-revoke-modal">
                                            {{ __('admin.users.action_revoke') }}
                                        </button>
                                    @elseif (($user['action_key'] ?? '') === 'support_case')
                                        <button type="button" class="font-semibold text-hb-blue hover:underline" data-open-modal="admin-users-support-modal">
                                            {{ $actionLabel }}
                                        </button>
                                    @else
                                        <button type="button" class="font-semibold text-hb-blue hover:underline" data-user-preview>
                                            {{ $actionLabel }}
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="hidden border-t border-hb-border px-5 py-8 text-center text-sm text-hb-muted" data-users-empty role="status">
                {{ __('admin.users.empty') }}
            </p>

            <div
                class="flex flex-col gap-3 border-t border-hb-border px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                data-users-pagination
                data-status-template="{{ __('admin.users.js.showing_range') }}"
            >
                <p class="text-sm text-hb-muted" data-users-page-status role="status"></p>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="hb-btn-outline" data-users-page-prev disabled>
                        {{ __('common.actions.previous') }}
                    </button>
                    <div class="flex items-center gap-1" data-users-page-numbers></div>
                    <button type="button" class="hb-btn-outline" data-users-page-next>
                        {{ __('common.actions.next') }}
                    </button>
                </div>
            </div>
        </div>

        <section class="mt-6 rounded-xl border border-hb-info/30 bg-hb-info-soft/40 p-5 sm:p-6" aria-labelledby="users-safety-heading">
            <h2 id="users-safety-heading" class="text-base font-semibold text-hb-navy">
                {{ __('admin.users.safety_heading') }}
            </h2>
            <p class="mt-2 text-sm text-hb-muted">{{ __('admin.users.safety_body') }}</p>
            <div class="mt-4 flex flex-wrap gap-3">
                <button type="button" class="hb-btn-outline" data-open-modal="admin-users-privileged-modal">
                    {{ __('admin.users.review_privileged') }}
                </button>
                <button type="button" class="hb-btn-outline" data-open-modal="admin-users-export-modal">
                    {{ __('admin.users.export_report') }}
                </button>
            </div>
        </section>
    </div>

    @include('layouts.modal', [
        'modalId' => 'admin-users-invite-modal',
        'modalTitle' => __('admin.users.invite_soon_title'),
        'modalBody' => '<p>'.e(__('admin.users.invite_soon_body')).'</p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button>',
    ])

    @include('layouts.modal', [
        'modalId' => 'admin-users-preview-modal',
        'modalTitle' => __('admin.users.preview_title'),
        'modalSize' => 'md',
        'modalBody' => '<p class="text-hb-muted" data-user-preview-intro>'.e(__('admin.users.preview_body')).'</p><dl class="mt-4 space-y-3" data-user-preview-dl></dl><p class="mt-4 text-sm text-hb-muted">'.e(__('admin.users.preview_no_impersonate')).'</p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button><a href="#" class="hb-btn-primary hidden" data-user-preview-org>'.e(__('admin.users.open_organization')).'</a>',
    ])

    @include('layouts.modal', [
        'modalId' => 'admin-users-revoke-modal',
        'modalTitle' => __('admin.users.revoke_modal_title'),
        'modalBody' => '<p>'.e(__('admin.users.revoke_modal_body')).'</p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button>',
    ])

    @include('layouts.modal', [
        'modalId' => 'admin-users-support-modal',
        'modalTitle' => __('admin.users.support_modal_title'),
        'modalBody' => '<p>'.e(__('admin.users.support_modal_body')).'</p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button>',
    ])

    @include('layouts.modal', [
        'modalId' => 'admin-users-privileged-modal',
        'modalTitle' => __('admin.users.privileged_modal_title'),
        'modalBody' => '<p>'.e(__('admin.users.privileged_modal_body')).'</p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button>',
    ])

    @include('layouts.modal', [
        'modalId' => 'admin-users-export-modal',
        'modalTitle' => __('admin.users.export_modal_title'),
        'modalBody' => '<p>'.e(__('admin.users.export_modal_body')).'</p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button>',
    ])
@endsection
