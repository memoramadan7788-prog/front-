@extends('layouts.app')

@section('title', $pageTitle ?? __('admin.organizations.show_page_title', ['name' => $organization['name']]))
@section('breadcrumb', __('admin.organizations.show_breadcrumb', ['name' => $organization['name']]))

@section('page_header')
    @php
        $workspaceLabel = ($organization['workspace_label_key'] ?? '') === 'agency_workspace'
            ? __('admin.organizations.agency_workspace')
            : __('admin.organizations.company_workspace');
        $lifecycleLabel = match ($organization['lifecycle_key'] ?? 'active') {
            'pending' => __('admin.organizations.lifecycle_pending'),
            'suspended' => __('admin.organizations.lifecycle_suspended'),
            'archived' => __('admin.organizations.lifecycle_archived'),
            default => __('admin.organizations.lifecycle_active'),
        };
        $lifecycleVariant = match ($organization['lifecycle_key'] ?? 'active') {
            'pending' => 'pending',
            'suspended' => 'error',
            'archived' => 'neutral',
            default => 'success',
        };
        $verifiedLabel = match ($organization['verified_badge_key'] ?? '') {
            'verification_pending' => __('admin.organizations.badge_verification_pending'),
            'not_verified' => __('admin.organizations.badge_not_verified'),
            default => __('admin.organizations.badge_business_verified'),
        };
        $verifiedVariant = ($organization['verified_badge_key'] ?? '') === 'business_verified' ? 'info' : 'neutral';
    @endphp
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ $organization['name'] }}</h1>
    <p class="mt-1 text-sm text-hb-muted">
        <span dir="ltr">{{ $organization['code'] }}</span>
        · {{ $workspaceLabel }}
        · {{ __('admin.organizations.created_on', ['date' => $organization['created_on']]) }}
    </p>
    <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm">
        <x-badge :variant="$lifecycleVariant" :dot="true">{{ $lifecycleLabel }}</x-badge>
        <x-badge :variant="$verifiedVariant" :dot="true">{{ $verifiedLabel }}</x-badge>
        <span class="text-hb-muted">
            {{ __('admin.organizations.owner_meta', [
                'owner' => $organization['owner'],
                'domain' => $organization['domain'],
            ]) }}
        </span>
    </div>
@endsection

@section('page_actions')
    <a href="{{ route('admin.organizations.index') }}" class="hb-btn-outline">
        {{ __('admin.organizations.back_to_directory') }}
    </a>
@endsection

@section('content')
    @php
        $profile = $organization['profile'] ?? [];
        $subscription = $organization['subscription_tab'] ?? [];
        $usage = $organization['usage_tab'] ?? [];
        $members = $organization['members'] ?? [];
        $suspendReasons = $organization['suspend_reasons'] ?? [];
        $isLimited = ($organization['detail_level'] ?? 'limited') === 'limited';
        $isSuspended = ($organization['lifecycle_key'] ?? '') === 'suspended';
        $isArchived = ($organization['lifecycle_key'] ?? '') === 'archived';
    @endphp

    <div data-admin-org-show data-tabs id="admin-org-detail-tabs">
        <div
            class="mb-6 flex flex-wrap gap-4 border-b border-hb-border text-sm font-semibold"
            role="tablist"
            aria-label="{{ __('admin.organizations.detail_tablist_aria') }}"
        >
            @foreach ([
                ['overview', __('admin.organizations.tab_overview')],
                ['subscription', __('admin.organizations.tab_subscription')],
                ['members', __('admin.organizations.tab_members')],
                ['usage', __('admin.organizations.tab_usage')],
                ['admin', __('admin.organizations.tab_admin_actions')],
            ] as [$key, $label])
                <button
                    type="button"
                    role="tab"
                    id="admin-org-detail-tabs-tab-{{ $key }}"
                    aria-controls="admin-org-detail-tabs-panel-{{ $key }}"
                    aria-selected="{{ $key === 'overview' ? 'true' : 'false' }}"
                    tabindex="{{ $key === 'overview' ? '0' : '-1' }}"
                    class="border-b-2 px-1 pb-3 {{ $key === 'overview' ? 'border-hb-blue text-hb-blue' : 'border-transparent text-hb-muted hover:text-hb-navy' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Overview --}}
        <div
            id="admin-org-detail-tabs-panel-overview"
            role="tabpanel"
            aria-labelledby="admin-org-detail-tabs-tab-overview"
        >
            @if ($isLimited)
                <p class="mb-4 rounded-lg border border-hb-border bg-hb-page px-4 py-3 text-sm text-hb-muted" role="status">
                    {{ __('admin.organizations.limited_overview_note') }}
                </p>
            @endif

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(16rem,18rem)]">
                <div class="min-w-0 space-y-6">
                    <section class="hb-surface p-5 sm:p-6" aria-labelledby="org-profile-heading">
                        <h2 id="org-profile-heading" class="text-base font-semibold text-hb-navy">
                            {{ __('admin.organizations.workspace_profile') }}
                        </h2>
                        <dl class="mt-4 space-y-4 text-sm">
                            @foreach ([
                                'industry_location' => __('admin.organizations.profile_industry'),
                                'hiring_models' => __('admin.organizations.profile_hiring'),
                                'subscription' => __('admin.organizations.profile_subscription'),
                                'verification' => __('admin.organizations.profile_verification'),
                                'usage' => __('admin.organizations.profile_usage'),
                                'agency_relationship' => __('admin.organizations.profile_agency'),
                            ] as $field => $label)
                                <div>
                                    <dt class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ $label }}</dt>
                                    <dd class="mt-1 text-hb-navy">{{ $profile[$field] ?? '—' }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </section>

                    <section class="hb-surface overflow-hidden" aria-labelledby="org-members-overview-heading">
                        <div class="border-b border-hb-border px-5 py-4 sm:px-6">
                            <h2 id="org-members-overview-heading" class="text-base font-semibold text-hb-navy">
                                {{ __('admin.organizations.members_access') }}
                            </h2>
                        </div>
                        <ul class="divide-y divide-hb-border">
                            @forelse ($members as $member)
                                <li class="px-5 py-4 sm:px-6">
                                    <p class="font-semibold text-hb-navy">{{ $member['name'] }}</p>
                                    <p class="mt-1 text-sm text-hb-muted">
                                        {{ $member['role'] }} · {{ __('admin.organizations.scope_label', ['scope' => $member['scope']]) }}
                                    </p>
                                </li>
                            @empty
                                <li class="px-5 py-8 text-sm text-hb-muted sm:px-6">
                                    {{ __('admin.organizations.members_empty') }}
                                </li>
                            @endforelse
                        </ul>
                    </section>
                </div>

                <aside class="space-y-6">
                    <section class="hb-surface p-5 sm:p-6" aria-labelledby="org-scoped-actions-heading">
                        <h2 id="org-scoped-actions-heading" class="text-base font-semibold text-hb-navy">
                            {{ __('admin.organizations.scoped_actions') }}
                        </h2>
                        <div class="mt-4 flex flex-col gap-2">
                            <button type="button" class="hb-btn-outline w-full justify-center" data-open-modal="admin-org-verify-modal">
                                {{ __('admin.organizations.review_verification') }}
                            </button>
                            <button type="button" class="hb-btn-outline w-full justify-center" data-org-goto-tab="subscription">
                                {{ __('admin.organizations.manage_subscription') }}
                            </button>
                            <button type="button" class="hb-btn-outline w-full justify-center" data-open-modal="admin-org-message-modal">
                                {{ __('admin.organizations.send_owner_message') }}
                            </button>
                        </div>
                        <p class="mt-4 text-xs text-hb-muted">
                            {{ __('admin.organizations.candidate_docs_note') }}
                        </p>
                    </section>

                    @unless ($isArchived)
                        <section class="hb-surface border-hb-error/30 p-5 sm:p-6" aria-labelledby="org-suspend-heading">
                            <h2 id="org-suspend-heading" class="text-base font-semibold text-hb-navy">
                                {{ __('admin.organizations.suspend_heading') }}
                            </h2>
                            <p class="mt-2 text-sm text-hb-muted">
                                {{ __('admin.organizations.suspend_warning') }}
                            </p>
                            @if ($isSuspended)
                                <p class="mt-4 rounded-lg bg-hb-page px-3 py-2 text-sm text-hb-muted" role="status">
                                    {{ __('admin.organizations.already_suspended') }}
                                </p>
                            @else
                                <div class="mt-4 space-y-3" data-org-suspend-form>
                                    <label class="block">
                                        <span class="mb-1.5 block text-sm font-semibold text-hb-navy">
                                            {{ __('admin.organizations.suspend_reason_label') }}
                                            <span class="text-hb-error" aria-hidden="true">*</span>
                                        </span>
                                        <select class="hb-input w-full" data-org-suspend-reason aria-required="true">
                                            <option value="">{{ __('admin.organizations.suspend_reason_placeholder') }}</option>
                                            @foreach ($suspendReasons as $reasonKey => $reasonLabel)
                                                <option value="{{ $reasonKey }}">{{ __('admin.organizations.suspend_reason_'.$reasonKey) }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="flex items-start gap-2 text-sm text-hb-text">
                                        <input
                                            type="checkbox"
                                            class="mt-1 rounded border-hb-border text-hb-blue focus:ring-hb-blue/40"
                                            data-org-suspend-ack
                                        >
                                        <span>{{ __('admin.organizations.suspend_ack') }}</span>
                                    </label>
                                    <button
                                        type="button"
                                        class="inline-flex w-full items-center justify-center rounded-lg bg-hb-error px-4 py-2.5 text-sm font-semibold text-white hover:bg-hb-error/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-error/40 disabled:cursor-not-allowed disabled:opacity-50"
                                        data-org-suspend-review
                                        disabled
                                    >
                                        {{ __('admin.organizations.review_suspension') }}
                                    </button>
                                    <p class="text-xs text-hb-muted" data-org-suspend-hint>
                                        {{ __('admin.organizations.suspend_footer') }}
                                    </p>
                                </div>
                            @endif
                        </section>
                    @endunless
                </aside>
            </div>
        </div>

        {{-- Subscription --}}
        <div
            id="admin-org-detail-tabs-panel-subscription"
            role="tabpanel"
            aria-labelledby="admin-org-detail-tabs-tab-subscription"
            class="hidden"
            hidden
        >
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="org-subscription-heading">
                <h2 id="org-subscription-heading" class="text-base font-semibold text-hb-navy">
                    {{ __('admin.organizations.subscription_heading') }}
                </h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    @foreach ([
                        'plan' => __('admin.organizations.sub_plan'),
                        'billing' => __('admin.organizations.sub_billing'),
                        'renews' => __('admin.organizations.sub_renews'),
                        'seats' => __('admin.organizations.sub_seats'),
                    ] as $field => $label)
                        <div>
                            <dt class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ $label }}</dt>
                            <dd class="mt-1 text-sm text-hb-navy">{{ $subscription[$field] ?? '—' }}</dd>
                        </div>
                    @endforeach
                </dl>
                <p class="mt-4 text-sm text-hb-muted">{{ $subscription['notes'] ?? '' }}</p>
                <button type="button" class="hb-btn-outline mt-5" data-open-modal="admin-org-subscription-modal">
                    {{ __('admin.organizations.change_plan_review') }}
                </button>
            </section>
        </div>

        {{-- Members --}}
        <div
            id="admin-org-detail-tabs-panel-members"
            role="tabpanel"
            aria-labelledby="admin-org-detail-tabs-tab-members"
            class="hidden"
            hidden
        >
            <section class="hb-surface overflow-hidden" aria-labelledby="org-members-tab-heading">
                <div class="border-b border-hb-border px-5 py-4 sm:px-6">
                    <h2 id="org-members-tab-heading" class="text-base font-semibold text-hb-navy">
                        {{ __('admin.organizations.members_heading') }}
                    </h2>
                    <p class="mt-1 text-sm text-hb-muted">{{ __('admin.organizations.members_intro') }}</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[36rem] text-start text-sm">
                        <caption class="sr-only">{{ __('admin.organizations.members_heading') }}</caption>
                        <thead class="bg-hb-page text-xs font-semibold tracking-wide text-hb-muted uppercase">
                            <tr>
                                <th scope="col" class="px-5 py-3">{{ __('admin.organizations.col_member') }}</th>
                                <th scope="col" class="px-4 py-3">{{ __('admin.organizations.col_role') }}</th>
                                <th scope="col" class="px-5 py-3">{{ __('admin.organizations.col_scope') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hb-border">
                            @forelse ($members as $member)
                                <tr>
                                    <td class="px-5 py-4 font-semibold text-hb-navy">{{ $member['name'] }}</td>
                                    <td class="px-4 py-4 text-hb-text">{{ $member['role'] }}</td>
                                    <td class="px-5 py-4 text-hb-muted">{{ $member['scope'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-8 text-center text-hb-muted">
                                        {{ __('admin.organizations.members_empty') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-hb-border px-5 py-4 sm:px-6">
                    <a href="{{ route('admin.users.index', ['q' => $organization['name']]) }}" class="text-sm font-semibold text-hb-blue hover:underline">
                        {{ __('admin.organizations.open_users_for_org') }}
                    </a>
                </div>
            </section>
        </div>

        {{-- Usage --}}
        <div
            id="admin-org-detail-tabs-panel-usage"
            role="tabpanel"
            aria-labelledby="admin-org-detail-tabs-tab-usage"
            class="hidden"
            hidden
        >
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="org-usage-heading">
                <h2 id="org-usage-heading" class="text-base font-semibold text-hb-navy">
                    {{ __('admin.organizations.usage_heading') }}
                </h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ([
                        'open_jobs' => __('admin.organizations.usage_jobs'),
                        'candidates' => __('admin.organizations.usage_candidates'),
                        'members' => __('admin.organizations.usage_members'),
                        'interviews_week' => __('admin.organizations.usage_interviews'),
                        'scorecards_pending' => __('admin.organizations.usage_scorecards'),
                    ] as $field => $label)
                        <div class="rounded-lg border border-hb-border bg-hb-page p-4">
                            <dt class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ $label }}</dt>
                            <dd class="mt-2 text-sm font-semibold text-hb-navy">{{ $usage[$field] ?? '—' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        </div>

        {{-- Admin actions --}}
        <div
            id="admin-org-detail-tabs-panel-admin"
            role="tabpanel"
            aria-labelledby="admin-org-detail-tabs-tab-admin"
            class="hidden"
            hidden
        >
            <div class="grid gap-6 lg:grid-cols-2">
                <section class="hb-surface p-5 sm:p-6" aria-labelledby="org-admin-review-heading">
                    <h2 id="org-admin-review-heading" class="text-base font-semibold text-hb-navy">
                        {{ __('admin.organizations.admin_review_heading') }}
                    </h2>
                    <p class="mt-2 text-sm text-hb-muted">{{ __('admin.organizations.admin_review_body') }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <button type="button" class="hb-btn-outline" data-open-modal="admin-org-verify-modal">
                            {{ __('admin.organizations.review_verification') }}
                        </button>
                        <button type="button" class="hb-btn-outline" data-open-modal="admin-org-subscription-modal">
                            {{ __('admin.organizations.manage_subscription') }}
                        </button>
                        <button type="button" class="hb-btn-outline" data-open-modal="admin-org-message-modal">
                            {{ __('admin.organizations.send_owner_message') }}
                        </button>
                    </div>
                </section>
                <section class="hb-surface p-5 sm:p-6" aria-labelledby="org-admin-boundary-heading">
                    <h2 id="org-admin-boundary-heading" class="text-base font-semibold text-hb-navy">
                        {{ __('admin.organizations.admin_boundary_heading') }}
                    </h2>
                    <p class="mt-2 text-sm text-hb-muted">{{ __('admin.organizations.admin_boundary_body') }}</p>
                    <ul class="mt-4 list-disc space-y-1 ps-5 text-sm text-hb-muted">
                        <li>{{ __('admin.organizations.boundary_no_write') }}</li>
                        <li>{{ __('admin.organizations.boundary_no_docs') }}</li>
                        <li>{{ __('admin.organizations.boundary_audit') }}</li>
                    </ul>
                </section>
            </div>
        </div>
    </div>

    @include('layouts.modal', [
        'modalId' => 'admin-org-verify-modal',
        'modalTitle' => __('admin.organizations.verify_modal_title'),
        'modalBody' => '<p>'.e(__('admin.organizations.verify_modal_body')).'</p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button>',
    ])

    @include('layouts.modal', [
        'modalId' => 'admin-org-subscription-modal',
        'modalTitle' => __('admin.organizations.subscription_modal_title'),
        'modalBody' => '<p>'.e(__('admin.organizations.subscription_modal_body')).'</p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button>',
    ])

    @include('layouts.modal', [
        'modalId' => 'admin-org-message-modal',
        'modalTitle' => __('admin.organizations.message_modal_title'),
        'modalBody' => '<p>'.e(__('admin.organizations.message_modal_body')).'</p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button>',
    ])

    @include('layouts.modal', [
        'modalId' => 'admin-org-suspend-modal',
        'modalTitle' => __('admin.organizations.suspend_modal_title'),
        'modalBody' => '<p data-org-suspend-modal-body>'.e(__('admin.organizations.suspend_modal_body')).'</p><p class="mt-3 text-sm text-hb-muted">'.e(__('admin.organizations.suspend_modal_unavailable')).'</p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button>',
    ])
@endsection
