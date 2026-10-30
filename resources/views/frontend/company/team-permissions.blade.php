@extends('frontend.layouts.app')

@section('title', __('company.employees.page_title'))

@section('page_header')
    <div>
        <p class="text-xs font-medium uppercase tracking-wide text-hb-muted">{{ __('company.employees.breadcrumb') }}</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-hb-navy">{{ __('company.employees.title') }}</h1>
        <p class="mt-1 max-w-2xl text-sm text-hb-muted">{{ __('company.employees.subtitle') }}</p>
    </div>
@endsection

@section('content')
@php
    $tab = $tab ?? 'members';
    $members = is_array($members ?? null) ? $members : [];
    $roles = is_array($roles ?? null) ? $roles : [];
    $assignableRoles = is_array($assignableRoles ?? null) ? $assignableRoles : [];
    $invitations = is_array($invitations ?? null) ? $invitations : [];
    $roleCapabilityNotes = is_array($roleCapabilityNotes ?? null) ? $roleCapabilityNotes : [];
    $currentUserRoles = is_array($currentUserRoles ?? null) ? $currentUserRoles : [];
    $currentUserId = (int) ($currentUserId ?? 0);
    $adminCount = (int) ($adminCount ?? 0);
    $memberCounts = is_array($memberCounts ?? null) ? $memberCounts : [];
    $statusFilter = $statusFilter ?? null;
    $searchQuery = $searchQuery ?? '';
    $roleFilter = $roleFilter ?? '';
    $roleLabel = function (?string $name): string {
        $key = strtolower((string) $name);
        return match ($key) {
            'company_admin' => __('company.employees.role_label_company_admin'),
            'hr' => __('company.employees.role_label_hr'),
            'interviewer' => __('company.employees.role_label_interviewer'),
            'employee' => __('company.employees.role_label_employee'),
            default => $name !== null && $name !== '' ? $name : '—',
        };
    };
    $statusLabel = function (?string $status): string {
        $key = strtolower((string) $status);
        return match ($key) {
            'pending' => __('company.employees.status_pending'),
            'approved' => __('company.employees.status_approved'),
            'rejected' => __('company.employees.status_rejected'),
            'removed' => __('company.employees.status_removed'),
            default => $status !== null && $status !== '' ? $status : '—',
        };
    };
    $inviteStatusLabel = function (?string $status): string {
        $key = strtolower((string) $status);
        return match ($key) {
            'pending' => __('company.employees.invite_status_pending'),
            'accepted' => __('company.employees.invite_status_accepted'),
            'expired' => __('company.employees.invite_status_expired'),
            'revoked' => __('company.employees.invite_status_revoked'),
            default => $status !== null && $status !== '' ? $status : __('company.employees.invite_status_pending'),
        };
    };
    $tabs = [
        'members' => ['label' => __('company.employees.tab_members_plain'), 'count' => $memberCounts['all'] ?? count($members)],
        'invitations' => ['label' => __('company.employees.tab_invites_plain'), 'count' => count($invitations)],
        'roles' => ['label' => __('company.employees.tab_roles_plain'), 'count' => count($roles)],
    ];
@endphp

<div class="mx-auto max-w-6xl space-y-5" data-team-permissions data-tab="{{ $tab }}">
    @if ($errors->any())
        <div class="rounded-xl border border-hb-error/30 bg-hb-error/5 px-4 py-3 text-sm text-hb-navy" role="alert" data-team-errors>
            <ul class="list-disc ps-5">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-wrap gap-2 border-b border-hb-border pb-3" role="tablist" aria-label="{{ __('company.employees.tabs_aria') }}" data-team-tabs>
        @foreach ($tabs as $key => $meta)
            @php $active = $tab === $key; @endphp
            <a
                href="{{ route('frontend.company.employees.index', array_filter(['tab' => $key === 'members' ? null : $key])) }}"
                role="tab"
                aria-selected="{{ $active ? 'true' : 'false' }}"
                data-team-tab="{{ $key }}"
                class="inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-sm font-medium transition {{ $active ? 'bg-hb-navy text-white shadow-sm' : 'bg-hb-page text-hb-muted hover:text-hb-navy' }}"
            >
                <span>{{ $meta['label'] }}</span>
                <span class="rounded-full px-1.5 text-xs {{ $active ? 'bg-white/20 text-white' : 'bg-hb-surface text-hb-muted' }}">{{ $meta['count'] }}</span>
            </a>
        @endforeach
    </div>

    <p class="text-xs text-hb-muted" data-hiring-team-note>{{ __('company.employees.hiring_team_separate') }}</p>

    @if ($tab === 'members')
        <section class="space-y-4" data-team-panel="members" aria-labelledby="members-heading">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 id="members-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.employees.members_heading') }}</h2>
                    <p class="mt-1 text-sm text-hb-muted">{{ __('company.employees.members_intro') }}</p>
                </div>
            </div>

            @unless ($roleUpdatesEnabled ?? false)
                <div class="rounded-xl border border-hb-pending/40 bg-hb-pending-soft/40 px-4 py-3 text-sm text-hb-navy" role="status" data-role-updates-blocked>
                    <p class="font-medium">{{ __('company.employees.role_update_blocked_title') }}</p>
                    <p class="mt-1">{{ __('company.employees.role_update_disabled') }}</p>
                </div>
            @endunless

            <form method="GET" action="{{ route('frontend.company.employees.index') }}" class="grid gap-3 rounded-2xl border border-hb-border bg-hb-surface p-4 sm:grid-cols-2 lg:grid-cols-4 lg:items-end" data-members-filters>
                <input type="hidden" name="tab" value="members">
                <div class="sm:col-span-2">
                    <label class="hb-label" for="team-q">{{ __('company.employees.search_label') }}</label>
                    <input id="team-q" name="q" type="search" class="hb-input" value="{{ $searchQuery }}" placeholder="{{ __('company.employees.search_placeholder') }}" autocomplete="off">
                    <p class="mt-1 text-xs text-hb-muted">{{ __('company.employees.search_client_side_hint') }}</p>
                </div>
                <div>
                    <label class="hb-label" for="team-status">{{ __('company.employees.filter_status') }}</label>
                    <select id="team-status" name="status" class="hb-input">
                        <option value="">{{ __('company.employees.all_status') }}</option>
                        @foreach (['pending', 'approved', 'rejected', 'removed'] as $st)
                            <option value="{{ $st }}" @selected($statusFilter === $st)>{{ $statusLabel($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="hb-label" for="team-role">{{ __('company.employees.filter_role') }}</label>
                    <select id="team-role" name="role" class="hb-input">
                        <option value="">{{ __('company.employees.all_roles') }}</option>
                        @foreach ($roles as $role)
                            @php $rn = strtolower((string) data_get($role, 'name', '')); @endphp
                            <option value="{{ $rn }}" @selected($roleFilter === $rn)>{{ $roleLabel($rn) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-wrap gap-2 lg:col-span-4">
                    <x-button type="submit" size="sm" variant="primary">{{ __('company.employees.apply_filters') }}</x-button>
                    <x-button variant="light" size="sm" :href="route('frontend.company.employees.index', ['tab' => 'members'])">{{ __('company.employees.clear_filters') }}</x-button>
                </div>
            </form>

            @if (! ($membersAvailable ?? true))
                <div class="rounded-xl border border-hb-error/30 bg-hb-error/5 px-4 py-3 text-sm text-hb-navy" role="alert" data-members-unavailable>
                    {{ $membersMessage ?? __('company.employees.members_unavailable') }}
                </div>
            @elseif ($members === [])
                <div class="hb-surface rounded-2xl px-5 py-10 text-center text-sm text-hb-muted" data-members-empty>
                    {{ __('company.employees.empty_members') }}
                </div>
            @else
                <div class="hidden md:block">
                    <x-table>
                        <x-slot:head>
                            <th class="px-4 py-3 text-start">{{ __('company.employees.col_member') }}</th>
                            <th class="px-4 py-3 text-start">{{ __('company.employees.col_role') }}</th>
                            <th class="px-4 py-3 text-start">{{ __('company.employees.col_status') }}</th>
                            <th class="px-4 py-3 text-start">{{ __('company.employees.col_joined') }}</th>
                            <th class="px-4 py-3 text-end">{{ __('company.employees.col_actions') }}</th>
                        </x-slot:head>
                        @foreach ($members as $row)
                            @include('frontend.company.partials.team-member-row', [
                                'row' => $row,
                                'compact' => false,
                                'roleLabel' => $roleLabel,
                                'statusLabel' => $statusLabel,
                                'assignableRoles' => $assignableRoles,
                                'currentUserId' => $currentUserId,
                                'adminCount' => $adminCount,
                                'roleUpdatesEnabled' => $roleUpdatesEnabled ?? false,
                            ])
                        @endforeach
                    </x-table>
                </div>

                <div class="space-y-3 md:hidden" data-members-mobile>
                    @foreach ($members as $row)
                        @include('frontend.company.partials.team-member-card', [
                            'row' => $row,
                            'roleLabel' => $roleLabel,
                            'statusLabel' => $statusLabel,
                            'assignableRoles' => $assignableRoles,
                            'currentUserId' => $currentUserId,
                            'adminCount' => $adminCount,
                            'roleUpdatesEnabled' => $roleUpdatesEnabled ?? false,
                        ])
                    @endforeach
                </div>

                @include('frontend.partials.pagination', ['meta' => $membersMeta ?? null, 'showStatus' => true])
            @endif
        </section>
    @endif

    @if ($tab === 'invitations')
        <section class="space-y-4" data-team-panel="invitations" aria-labelledby="invites-heading">
            <div>
                <h2 id="invites-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.employees.invites_heading') }}</h2>
                <p class="mt-1 text-sm text-hb-muted">{{ __('company.employees.invites_intro') }}</p>
            </div>

            @if ($invitationsBlocked ?? false)
                <div class="rounded-xl border border-hb-pending/40 bg-hb-pending-soft/40 px-4 py-4 text-sm text-hb-navy" role="status" data-invites-blocked>
                    <p class="font-medium">{{ __('company.employees.invites_blocked_title') }}</p>
                    <p class="mt-2">{{ $invitationsMessage ?? __('company.employees.invites_migration_blocked') }}</p>
                    <p class="mt-2 text-xs text-hb-muted">{{ __('company.employees.invites_no_email_claim') }}</p>
                </div>
            @elseif (($invitationsAvailable ?? true) === false)
                <div class="rounded-xl border border-hb-error/30 bg-hb-error/5 px-4 py-3 text-sm text-hb-navy" role="alert" data-invites-unavailable>
                    {{ $invitationsMessage ?? __('company.employees.invites_unavailable') }}
                </div>
            @else
                <div class="hb-surface rounded-2xl p-5 sm:p-6" data-invite-create>
                    <h3 class="text-base font-semibold text-hb-navy">{{ __('company.employees.invite_heading') }}</h3>
                    <p class="mt-1 text-xs text-hb-muted">{{ __('company.employees.invite_record_only') }}</p>
                    <form method="POST" action="{{ route('frontend.company.employees.invitations.store') }}" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 lg:items-end" data-submit-once>
                        @csrf
                        <div class="sm:col-span-2 lg:col-span-1">
                            <label class="hb-label" for="invite-email">{{ __('company.employees.work_email') }} <span class="text-hb-error">*</span></label>
                            <input id="invite-email" name="email" type="email" class="hb-input" value="{{ old('email') }}" required maxlength="255" autocomplete="email">
                            @error('email')<p class="hb-field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="hb-label" for="invite-role">{{ __('company.employees.role') }}</label>
                            <select id="invite-role" name="role_id" class="hb-input">
                                <option value="">{{ __('company.employees.assign_role_optional') }}</option>
                                @foreach ($assignableRoles as $role)
                                    <option value="{{ data_get($role, 'id') }}" @selected((string) old('role_id') === (string) data_get($role, 'id'))>
                                        {{ $roleLabel((string) data_get($role, 'name')) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')<p class="hb-field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <x-button type="submit" variant="primary">{{ __('company.employees.create_invite_record') }}</x-button>
                        </div>
                    </form>
                    <p class="mt-3 text-xs text-hb-muted" data-no-resend>{{ __('company.employees.no_resend_endpoint') }}</p>
                </div>

                @if ($invitations === [])
                    <div class="hb-surface rounded-2xl px-5 py-10 text-center text-sm text-hb-muted" data-invites-empty>
                        {{ __('company.employees.empty_invites') }}
                    </div>
                @else
                    <x-table>
                        <x-slot:head>
                            <th class="px-4 py-3 text-start">{{ __('company.employees.work_email') }}</th>
                            <th class="px-4 py-3 text-start">{{ __('company.employees.role') }}</th>
                            <th class="px-4 py-3 text-start">{{ __('company.employees.col_status') }}</th>
                            <th class="px-4 py-3 text-start">{{ __('company.employees.invite_expires') }}</th>
                            <th class="px-4 py-3 text-end">{{ __('company.employees.col_actions') }}</th>
                        </x-slot:head>
                        @foreach ($invitations as $invite)
                            @php
                                $expires = data_get($invite, 'expires_at');
                                $expired = filled($expires) && \Illuminate\Support\Carbon::parse($expires)->isPast();
                                $st = strtolower((string) data_get($invite, 'status', $expired ? 'expired' : 'pending'));
                            @endphp
                            <tr data-invite-row="{{ data_get($invite, 'id') }}">
                                <td class="px-4 py-3 text-sm text-hb-navy">{{ data_get($invite, 'email', '—') }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @php
                                        $rid = data_get($invite, 'role_id');
                                        $matched = collect($roles)->firstWhere('id', $rid);
                                    @endphp
                                    {{ $roleLabel($matched ? (string) data_get($matched, 'name') : null) }}
                                </td>
                                <td class="px-4 py-3">
                                    <x-badge :variant="\App\Support\Rows::badgeVariant($st)" :dot="true">{{ $inviteStatusLabel($st) }}</x-badge>
                                </td>
                                <td class="px-4 py-3 text-sm text-hb-muted">{{ \App\Support\Rows::date($expires) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <form method="POST" action="{{ route('frontend.company.employees.invitations.destroy', ['invitationId' => data_get($invite, 'id')]) }}" class="inline" onsubmit="return confirm(@json(__('company.employees.confirm_revoke_invite')))" data-submit-once>
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" size="sm" variant="destructive">{{ __('company.employees.revoke_invite') }}</x-button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </x-table>
                    @include('frontend.partials.pagination', ['meta' => $invitationsMeta ?? null])
                @endif
            @endif
        </section>
    @endif

    @if ($tab === 'roles')
        <section class="space-y-4" data-team-panel="roles" aria-labelledby="roles-heading">
            <div>
                <h2 id="roles-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.employees.roles_heading') }}</h2>
                <p class="mt-1 text-sm text-hb-muted">{{ __('company.employees.roles_intro') }}</p>
            </div>

            <div class="rounded-xl border border-hb-border bg-hb-page/60 px-4 py-3 text-sm text-hb-navy" data-current-user-roles>
                <p class="font-medium">{{ __('company.employees.your_roles_heading') }}</p>
                @if ($currentUserRoles === [])
                    <p class="mt-1 text-hb-muted">{{ __('company.employees.your_roles_unknown') }}</p>
                @else
                    <ul class="mt-2 flex flex-wrap gap-2">
                        @foreach ($currentUserRoles as $rn)
                            <li><x-badge variant="info">{{ $roleLabel($rn) }}</x-badge></li>
                        @endforeach
                    </ul>
                @endif
                <p class="mt-2 text-xs text-hb-muted">{{ __('company.employees.your_roles_source') }}</p>
            </div>

            <div class="rounded-xl border border-dashed border-hb-border px-4 py-3 text-xs text-hb-muted" data-permission-catalog-gap>
                {{ __('company.employees.permission_catalog_gap') }}
            </div>

            @if (! ($rolesAvailable ?? true))
                <div class="rounded-xl border border-hb-error/30 bg-hb-error/5 px-4 py-3 text-sm text-hb-navy" role="alert">
                    {{ $rolesMessage ?? __('company.employees.roles_unavailable') }}
                </div>
            @elseif ($roles === [])
                <div class="hb-surface rounded-2xl px-5 py-10 text-center text-sm text-hb-muted" data-roles-empty>
                    {{ __('company.employees.empty_roles') }}
                </div>
            @else
                <div class="grid gap-4 md:grid-cols-2" data-roles-grid>
                    @foreach ($roles as $role)
                        @php
                            $name = strtolower((string) data_get($role, 'name', ''));
                            $desc = (string) (data_get($role, 'description') ?: __('company.employees.role_no_description'));
                            $caps = $roleCapabilityNotes[$name] ?? null;
                        @endphp
                        <article class="hb-surface rounded-2xl p-5" data-role-card="{{ $name }}">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-base font-semibold text-hb-navy">{{ $roleLabel($name) }}</h3>
                                <x-badge variant="neutral">{{ data_get($role, 'scope', 'company') }}</x-badge>
                            </div>
                            <p class="mt-2 text-sm text-hb-muted">{{ $desc }}</p>
                            @if (is_array($caps))
                                <p class="mt-3 text-xs font-medium uppercase tracking-wide text-hb-muted">{{ __('company.employees.seed_capabilities_heading') }}</p>
                                @if ($caps === [])
                                    <p class="mt-1 text-sm text-hb-muted">{{ __('company.employees.seed_capabilities_none') }}</p>
                                @else
                                    <ul class="mt-2 flex flex-wrap gap-1.5">
                                        @foreach ($caps as $cap)
                                            <li><span class="inline-flex rounded-md bg-hb-page px-2 py-0.5 text-xs text-hb-navy">{{ $cap }}</span></li>
                                        @endforeach
                                    </ul>
                                @endif
                                <p class="mt-2 text-xs text-hb-muted">{{ __('company.employees.seed_capabilities_note') }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif

            <div class="rounded-xl border border-hb-border px-4 py-3 text-sm text-hb-muted" data-scope-boundaries>
                <p class="font-medium text-hb-navy">{{ __('company.employees.scope_boundaries_heading') }}</p>
                <ul class="mt-2 list-disc space-y-1 ps-5">
                    <li>{{ __('company.employees.scope_company_members') }}</li>
                    <li>{{ __('company.employees.scope_hiring_team') }}</li>
                    <li>{{ __('company.employees.scope_platform_admin') }}</li>
                </ul>
            </div>
        </section>
    @endif
</div>
@endsection
