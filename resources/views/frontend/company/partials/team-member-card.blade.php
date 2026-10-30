@php
    $row = is_array($row ?? null) ? $row : [];
    $status = strtolower((string) data_get($row, 'status', ''));
    $roleName = strtolower((string) data_get($row, 'role.name', ''));
    $userId = (int) data_get($row, 'user.id', 0);
    $employeeId = (int) data_get($row, 'id', 0);
    $isSelf = $currentUserId > 0 && $userId === $currentUserId;
    $isLastAdmin = $status === 'approved' && $roleName === 'company_admin' && (int) $adminCount <= 1;
    $joined = data_get($row, 'joined_at') ?? data_get($row, 'reviewed_at') ?? data_get($row, 'requested_at') ?? data_get($row, 'created_at');
@endphp
<article class="hb-surface rounded-2xl p-4" data-member-card="{{ $employeeId }}" data-member-status="{{ $status }}">
    <div class="flex flex-wrap items-start justify-between gap-2">
        <div>
            <p class="font-medium text-hb-navy">{{ data_get($row, 'user.name') ?? '—' }}</p>
            <p class="text-xs text-hb-muted">{{ data_get($row, 'user.email') ?? '—' }}</p>
        </div>
        <div class="flex flex-wrap gap-1.5">
            <x-badge variant="info">{{ $roleLabel($roleName !== '' ? $roleName : null) }}</x-badge>
            <x-badge :variant="\App\Support\Rows::badgeVariant($status)" :dot="true">{{ $statusLabel($status) }}</x-badge>
        </div>
    </div>
    @if (filled(data_get($row, 'requested_job_title')) || filled(data_get($row, 'requested_department')))
        <p class="mt-2 text-xs text-hb-muted">
            {{ data_get($row, 'requested_job_title') ?: '—' }} · {{ data_get($row, 'requested_department') ?: '—' }}
        </p>
    @endif
    <p class="mt-2 text-xs text-hb-muted">{{ __('company.employees.col_joined') }}: {{ \App\Support\Rows::date($joined) }}</p>

    <div class="mt-3 flex flex-col gap-2 border-t border-hb-border pt-3">
        @if ($status === 'pending')
            <form method="POST" action="{{ route('frontend.company.employees.approve', ['employeeId' => $employeeId]) }}" class="space-y-2" data-submit-once data-approve-form>
                @csrf
                <label class="hb-label" for="m-approve-role-{{ $employeeId }}">{{ __('company.employees.assign_role_label') }}</label>
                <select id="m-approve-role-{{ $employeeId }}" name="role_id" class="hb-input" required>
                    <option value="">{{ __('company.employees.assign_role_placeholder') }}</option>
                    @foreach ($assignableRoles as $role)
                        <option value="{{ data_get($role, 'id') }}">{{ $roleLabel((string) data_get($role, 'name')) }}</option>
                    @endforeach
                </select>
                <div class="flex flex-wrap gap-2">
                    <x-button type="submit" size="sm" variant="primary">{{ __('company.employees.approve') }}</x-button>
                    <x-button variant="destructive" size="sm" :href="route('frontend.company.employees.reject', ['employeeId' => $employeeId])">{{ __('company.employees.decline') }}</x-button>
                </div>
            </form>
        @endif

        @if ($status === 'approved' && ! ($roleUpdatesEnabled ?? false))
            <p class="text-xs text-hb-muted" data-role-update-locked>{{ __('company.employees.role_update_locked_hint') }}</p>
        @elseif ($status === 'approved' && $assignableRoles !== [] && ! $isSelf)
            <form method="POST" action="{{ route('frontend.company.employees.role.update', ['employeeId' => $employeeId]) }}" class="space-y-2" data-submit-once data-role-form>
                @csrf
                @method('PUT')
                <label class="hb-label" for="m-role-{{ $employeeId }}">{{ __('company.employees.change_role') }}</label>
                <select id="m-role-{{ $employeeId }}" name="role_id" class="hb-input" required>
                    @foreach ($assignableRoles as $role)
                        <option value="{{ data_get($role, 'id') }}" @selected((int) data_get($role, 'id') === (int) data_get($row, 'role.id'))>
                            {{ $roleLabel((string) data_get($role, 'name')) }}
                        </option>
                    @endforeach
                </select>
                <x-button type="submit" size="sm" variant="light">{{ __('company.employees.save_role') }}</x-button>
            </form>
        @elseif ($status === 'approved' && $isSelf)
            <p class="text-xs text-hb-muted" data-self-role-locked>{{ __('company.employees.self_role_hint') }}</p>
        @endif

        @if (in_array($status, ['approved', 'rejected'], true))
            @if ($isSelf)
                <p class="text-xs text-hb-muted" data-self-remove-locked>{{ __('company.employees.self_remove_hint') }}</p>
            @elseif ($isLastAdmin)
                <p class="text-xs text-hb-muted" data-last-admin-locked>{{ __('company.employees.last_admin_hint') }}</p>
            @else
                <form method="POST" action="{{ route('frontend.company.employees.remove', ['employeeId' => $employeeId]) }}" onsubmit="return confirm(@json(__('company.employees.confirm_remove')))" data-submit-once>
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" size="sm" variant="destructive">{{ __('company.employees.remove') }}</x-button>
                </form>
            @endif
        @endif
    </div>
</article>
