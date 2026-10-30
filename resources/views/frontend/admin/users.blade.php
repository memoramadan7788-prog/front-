@extends('frontend.layouts.app')

@php
    $statusTab = $statusTab ?? 'all';
    $search = $search ?? '';
    $accountType = $accountType ?? '';
    $pageRoleFilter = $pageRoleFilter ?? '';
    $pageOrgFilter = $pageOrgFilter ?? '';
    $users = is_array($users ?? null) ? $users : [];
    $meta = $pagination ?? null;
    $privilegedOnPage = is_array($privilegedOnPage ?? null) ? $privilegedOnPage : [];
    $auditSnippet = is_array($auditSnippet ?? null) ? $auditSnippet : [];
    $roleAssignmentEnabled = (bool) ($roleAssignmentEnabled ?? false);
    $invitationsSupported = (bool) ($invitationsSupported ?? false);
    $exportFullSupported = (bool) ($exportFullSupported ?? false);
    $tabQuery = function (string $tab) use ($search, $accountType, $pageRoleFilter, $pageOrgFilter): array {
        return array_filter([
            'status_tab' => $tab === 'all' ? null : $tab,
            'search' => $search !== '' ? $search : null,
            'account_type' => $accountType !== '' ? $accountType : null,
            'page_role' => $pageRoleFilter !== '' ? $pageRoleFilter : null,
            'page_org' => $pageOrgFilter !== '' ? $pageOrgFilter : null,
        ], fn ($v) => $v !== null && $v !== '');
    };
@endphp

@section('title', __('admin.users.page_title'))

@section('page_header')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-hb-muted">{{ __('admin.users.breadcrumb') }}</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-hb-navy">{{ __('admin.users.title') }}</h1>
            <p class="mt-1 max-w-2xl text-sm text-hb-muted">{{ __('admin.users.intro') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-button variant="light" size="sm" :href="route('frontend.admin.roles.index')">{{ __('admin.users.link_roles') }}</x-button>
            <x-button variant="light" size="sm" :href="route('frontend.admin.audit-logs.index')">{{ __('admin.users.link_audit') }}</x-button>
        </div>
    </div>
@endsection

@section('content')
<div class="mx-auto max-w-6xl space-y-5" data-admin-users-access data-status-tab="{{ $statusTab }}">
    @if ($errors->any())
        <div class="rounded-xl border border-hb-error/30 bg-hb-error/5 px-4 py-3 text-sm text-hb-navy" role="alert">
            <ul class="list-disc ps-5">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (! empty($loadError))
        <div class="rounded-xl border border-hb-error/30 bg-hb-error-soft px-4 py-3 text-sm text-hb-error" role="alert" data-users-load-error>
            {{ $loadError }}
        </div>
    @endif

    {{-- Status tabs (Backend account_status_id) --}}
    <div class="flex flex-wrap gap-2 border-b border-hb-border pb-3" role="tablist" aria-label="{{ __('admin.users.tablist_aria') }}" data-users-status-tabs>
        @foreach ([
            'all' => __('admin.users.tab_all'),
            'active' => __('admin.users.tab_active'),
            'deactivated' => __('admin.users.tab_deactivated'),
            'blocked' => __('admin.users.tab_blocked'),
            'deleted' => __('admin.users.tab_deleted'),
        ] as $key => $label)
            @php $active = $statusTab === $key; @endphp
            <a
                href="{{ route('frontend.admin.users.index', $tabQuery($key)) }}"
                role="tab"
                aria-selected="{{ $active ? 'true' : 'false' }}"
                data-status-tab-link="{{ $key }}"
                class="rounded-lg px-3 py-1.5 text-sm font-medium {{ $active ? 'bg-hb-navy text-white' : 'text-hb-muted hover:bg-hb-page hover:text-hb-navy' }}"
            >{{ $label }}</a>
        @endforeach
    </div>

    {{-- Search / filters --}}
    <form method="GET" action="{{ route('frontend.admin.users.index') }}" class="hb-surface rounded-2xl p-4 sm:p-5" data-users-filters>
        <input type="hidden" name="status_tab" value="{{ $statusTab }}">
        <div class="flex flex-wrap items-end gap-3">
            <div class="min-w-[12rem] flex-1">
                <x-form.input name="search" :label="__('admin.users.filter_search')" type="search" value="{{ $search }}" :placeholder="__('admin.users.search_placeholder')" />
            </div>
            <div class="w-full max-w-xs">
                <x-form.select name="account_type" :label="__('admin.users.filter_type')">
                    <option value="">{{ __('admin.users.type_all') }}</option>
                    @foreach (['candidate' => __('admin.users.type_candidate'), 'company' => __('admin.users.type_company'), 'admin' => __('admin.users.type_admin')] as $v => $label)
                        <option value="{{ $v }}" @selected($accountType === $v)>{{ $label }}</option>
                    @endforeach
                </x-form.select>
            </div>
            <div class="w-full max-w-xs">
                <x-form.input name="page_role" :label="__('admin.users.filter_role_page')" value="{{ $pageRoleFilter }}" :placeholder="__('admin.users.filter_role_page_hint')" />
            </div>
            <div class="w-full max-w-xs">
                <x-form.input name="page_org" :label="__('admin.users.filter_org_page')" value="{{ $pageOrgFilter }}" :placeholder="__('admin.users.filter_org_page_hint')" />
            </div>
            <x-button type="submit" variant="primary" size="sm">{{ __('admin.users.apply_filters') }}</x-button>
            @if ($search !== '' || $accountType !== '' || $statusTab !== 'all' || $pageRoleFilter !== '' || $pageOrgFilter !== '')
                <x-button variant="ghost" size="sm" :href="route('frontend.admin.users.index')">{{ __('admin.users.clear_filters') }}</x-button>
            @endif
        </div>
        <p class="mt-3 text-xs text-hb-muted" data-users-filter-honesty>
            {{ __('admin.users.filter_honesty') }}
        </p>
        @if (! empty($pageFilterNote))
            <p class="mt-1 text-xs text-hb-pending" data-users-page-filter-note>{{ __('admin.users.page_filter_active') }}</p>
        @endif
    </form>

    <div class="grid gap-5 lg:grid-cols-2">
        {{-- Invitations (Backend blocked) --}}
        <section class="hb-surface rounded-2xl p-5" aria-labelledby="invite-heading" data-users-invitations data-invitations-supported="0">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 id="invite-heading" class="text-base font-semibold text-hb-navy">{{ __('admin.users.invite_heading') }}</h2>
                <x-badge variant="pending">{{ __('admin.users.backend_blocked_badge') }}</x-badge>
            </div>
            <p class="mt-2 text-sm text-hb-muted">{{ __('admin.users.invite_blocked_body') }}</p>
            <button type="button" class="mt-3 inline-flex cursor-not-allowed items-center rounded-lg border border-hb-border bg-hb-page px-3 py-2 text-sm font-medium text-hb-muted opacity-70" disabled aria-disabled="true" data-invite-blocked>
                {{ __('admin.users.invite') }}
            </button>
        </section>

        {{-- Export --}}
        <section class="hb-surface rounded-2xl p-5" aria-labelledby="export-heading" data-users-export data-export-full="0">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 id="export-heading" class="text-base font-semibold text-hb-navy">{{ __('admin.users.export_heading') }}</h2>
                <x-badge variant="pending">{{ __('admin.users.no_full_export_badge') }}</x-badge>
            </div>
            <p class="mt-2 text-sm text-hb-muted">{{ __('admin.users.export_full_blocked') }}</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <x-button
                    variant="light"
                    size="sm"
                    :href="route('frontend.admin.users.export-page', request()->query())"
                    data-export-page
                >{{ __('admin.users.export_page_csv') }}</x-button>
            </div>
            <p class="mt-2 text-xs text-hb-muted">{{ __('admin.users.export_page_note') }}</p>
        </section>
    </div>

    {{-- Privileged access review --}}
    <section class="hb-surface rounded-2xl p-5 sm:p-6" aria-labelledby="privileged-heading" data-users-privileged>
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 id="privileged-heading" class="text-base font-semibold text-hb-navy">{{ __('admin.users.privileged_heading') }}</h2>
                <p class="mt-1 text-sm text-hb-muted">{{ __('admin.users.privileged_intro') }}</p>
            </div>
            <x-button variant="light" size="sm" :href="route('frontend.admin.users.index', array_filter(['account_type' => 'admin', 'status_tab' => $statusTab !== 'all' ? $statusTab : null, 'search' => $search !== '' ? $search : null]))">
                {{ __('admin.users.filter_privileged_type') }}
            </x-button>
        </div>
        <p class="mt-3 text-sm text-hb-navy" data-privileged-count>
            {{ __('admin.users.privileged_on_page', ['count' => count($privilegedOnPage)]) }}
        </p>
        @if (count($privilegedOnPage) === 0)
            <p class="mt-2 text-sm text-hb-muted">{{ __('admin.users.privileged_empty_page') }}</p>
        @else
            <ul class="mt-3 space-y-2" data-privileged-list>
                @foreach ($privilegedOnPage as $priv)
                    <li class="rounded-xl border border-hb-border px-3 py-2 text-sm" data-privilege-status="privileged">
                        <p class="font-semibold text-hb-navy">{{ $priv['name'] }}</p>
                        <p class="text-xs text-hb-muted">{{ $priv['email'] }} · {{ $priv['account_type'] }}</p>
                        <p class="mt-1 text-xs text-hb-muted">{{ implode(' · ', $priv['role_labels'] ?: ['—']) }}</p>
                    </li>
                @endforeach
            </ul>
        @endif

        @php $unknownPrivilegeOnPage = is_array($unknownPrivilegeOnPage ?? null) ? $unknownPrivilegeOnPage : []; @endphp
        @if (count($unknownPrivilegeOnPage) > 0)
            <div class="mt-3 rounded-xl border border-hb-pending/40 bg-hb-pending-soft/30 px-3 py-2 text-xs text-hb-navy" data-privilege-unknown role="status">
                {{ __('admin.users.privilege_unknown_note', ['count' => count($unknownPrivilegeOnPage)]) }}
            </div>
        @endif

        <div class="mt-5 border-t border-hb-border pt-4" data-users-audit-snippet>
            <h3 class="text-sm font-semibold text-hb-navy">{{ __('admin.users.audit_heading') }}</h3>
            @if (! empty($auditSnippetAvailable) && count($auditSnippet) > 0)
                <ul class="mt-2 space-y-2">
                    @foreach ($auditSnippet as $log)
                        <li class="text-xs text-hb-muted" data-audit-row>
                            <span class="font-medium text-hb-navy">{{ $log['action'] }}</span>
                            · #{{ $log['auditable_id'] ?? '—' }}
                            · {{ $log['actor'] }}
                            @if (! empty($log['created_at']))
                                · {{ \App\Support\Rows::date($log['created_at'], 'Y-m-d H:i') }}
                            @endif
                        </li>
                    @endforeach
                </ul>
                <p class="mt-2 text-xs text-hb-muted">{{ __('admin.users.audit_snippet_note') }}</p>
            @else
                <p class="mt-2 text-sm text-hb-muted" data-audit-unavailable>{{ $auditSnippetError ?? __('admin.users.audit_unavailable') }}</p>
            @endif
            <p class="mt-2 text-xs text-hb-muted" data-no-certification>{{ __('admin.users.no_fake_certification') }}</p>
        </div>
    </section>

    {{-- Role assignment safety --}}
    <div class="rounded-xl border border-hb-pending/40 bg-hb-pending-soft/40 px-4 py-3 text-sm text-hb-navy" data-role-assignment-blocked role="status">
        @if ($roleAssignmentEnabled)
            {{ __('admin.users.role_assign_enabled') }}
        @else
            {{ __('admin.users.role_assign_blocked') }}
        @endif
    </div>

    {{-- Directory table (desktop) + cards (mobile) --}}
    <div class="hidden md:block" data-users-table>
        <x-table>
            <x-slot:head>
                <th class="px-4 py-3 text-start">{{ __('admin.users.col_user') }}</th>
                <th class="px-4 py-3 text-start">{{ __('admin.users.col_organization') }}</th>
                <th class="px-4 py-3 text-start">{{ __('admin.users.col_role') }}</th>
                <th class="px-4 py-3 text-start">{{ __('admin.users.col_state') }}</th>
                <th class="px-4 py-3 text-start">{{ __('admin.users.col_joined') }}</th>
                <th class="px-4 py-3 text-end">{{ __('admin.users.col_actions') }}</th>
            </x-slot:head>
            @forelse ($users as $row)
                <tr data-user-row="{{ data_get($row, 'id') }}" @if (! empty($row['is_privileged'])) data-privileged-row="1" @endif>
                    <td class="px-4 py-3">
                        <p class="font-semibold text-hb-navy">{{ $row['name'] }}</p>
                        <p class="text-xs text-hb-muted">{{ $row['email'] }}</p>
                        <p class="mt-0.5 text-[11px] text-hb-muted">{{ __('admin.users.account_type_line', ['type' => $row['account_type']]) }}</p>
                    </td>
                    <td class="px-4 py-3 text-sm text-hb-navy">
                        @if (empty($row['org_labels']))
                            <span class="text-hb-muted">—</span>
                        @else
                            <ul class="space-y-1 text-xs">
                                @foreach ($row['org_labels'] as $org)
                                    <li>{{ $org }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if (empty($row['roles_raw']))
                            <span class="text-xs text-hb-muted">—</span>
                        @else
                            <ul class="space-y-1.5">
                                @foreach ($row['roles_raw'] as $role)
                                    @php
                                        $scope = (string) data_get($role, 'scope', '');
                                        $scopeVariant = $scope === 'platform' ? 'info' : ($scope === 'company' ? 'pending' : 'neutral');
                                    @endphp
                                    <li class="text-xs">
                                        <span class="font-semibold text-hb-navy">{{ data_get($role, 'name', '—') }}</span>
                                        @if ($scope !== '')
                                            <x-badge :variant="$scopeVariant">{{ $scope }}</x-badge>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <x-badge :variant="\App\Support\Rows::badgeVariant($row['account_status'] ?? null)" :dot="true">{{ $row['account_status'] ?? '—' }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-sm">{{ \App\Support\Rows::date($row['created_at'] ?? null, 'Y-m-d') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap justify-end gap-2">
                            @if (! empty($row['can_change_status']))
                                <x-button variant="light" size="sm" :href="route('frontend.admin.users.status', ['userId' => $row['id']])">{{ __('admin.users.action_change_status') }}</x-button>
                            @else
                                <span class="text-xs text-hb-muted">{{ __('admin.users.action_status_blocked_admin') }}</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-hb-muted" data-users-empty>
                        @if (! empty($loadError))
                            {{ __('admin.users.data_unavailable') }}
                        @else
                            {{ __('admin.users.empty') }}
                        @endif
                    </td>
                </tr>
            @endforelse
        </x-table>
    </div>

    <div class="space-y-3 md:hidden" data-users-cards>
        @forelse ($users as $row)
            <article class="hb-surface rounded-2xl p-4" data-user-card="{{ data_get($row, 'id') }}">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="font-semibold text-hb-navy">{{ $row['name'] }}</p>
                        <p class="text-xs text-hb-muted">{{ $row['email'] }}</p>
                    </div>
                    <x-badge :variant="\App\Support\Rows::badgeVariant($row['account_status'] ?? null)" :dot="true">{{ $row['account_status'] ?? '—' }}</x-badge>
                </div>
                <dl class="mt-3 grid gap-2 text-xs">
                    <div>
                        <dt class="text-hb-muted">{{ __('admin.users.col_role') }}</dt>
                        <dd class="text-hb-navy">{{ implode(', ', $row['role_labels'] ?: ['—']) }}</dd>
                    </div>
                    <div>
                        <dt class="text-hb-muted">{{ __('admin.users.col_organization') }}</dt>
                        <dd class="text-hb-navy">{{ implode(', ', $row['org_labels'] ?: ['—']) }}</dd>
                    </div>
                    <div>
                        <dt class="text-hb-muted">{{ __('admin.users.col_joined') }}</dt>
                        <dd class="text-hb-navy">{{ \App\Support\Rows::date($row['created_at'] ?? null, 'Y-m-d') }}</dd>
                    </div>
                </dl>
                @if (! empty($row['can_change_status']))
                    <div class="mt-3">
                        <x-button variant="light" size="sm" :href="route('frontend.admin.users.status', ['userId' => $row['id']])">{{ __('admin.users.action_change_status') }}</x-button>
                    </div>
                @endif
            </article>
        @empty
            <p class="py-8 text-center text-sm text-hb-muted">{{ ! empty($loadError) ? __('admin.users.data_unavailable') : __('admin.users.empty') }}</p>
        @endforelse
    </div>

    @include('frontend.partials.pagination', ['meta' => $meta])

    <p class="text-xs text-hb-muted" data-users-safety>{{ __('admin.users.safety_body') }}</p>
</div>
@endsection
