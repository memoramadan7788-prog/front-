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
<tr data-member-id="{{ $employeeId }}" data-member-status="{{ $status }}">
    <td class="px-4 py-3">
        <div class="font-medium text-hb-navy">{{ data_get($row, 'user.name') ?? '—' }}</div>
        <div class="text-xs text-hb-muted">{{ data_get($row, 'user.email') ?? '—' }}</div>
        @if (filled(data_get($row, 'requested_job_title')) || filled(data_get($row, 'requested_department')))
            <div class="mt-1 text-xs text-hb-muted">
                {{ data_get($row, 'requested_job_title') ?: '—' }}
                · {{ data_get($row, 'requested_department') ?: '—' }}
            </div>
        @endif
    </td>
    <td class="px-4 py-3">
        <x-badge variant="info">{{ $roleLabel($roleName !== '' ? $roleName : null) }}</x-badge>
    </td>
    <td class="px-4 py-3">
        <x-badge :variant="\App\Support\Rows::badgeVariant($status)" :dot="true">{{ $statusLabel($status) }}</x-badge>
    </td>
    <td class="px-4 py-3 text-sm text-hb-muted">{{ \App\Support\Rows::date($joined) }}</td>
    <td class="px-4 py-3">
        <div class="flex flex-wrap justify-end gap-2">
            @if ($status === 'pending')
                <form method="POST" action="{{ route('frontend.company.employees.approve', ['employeeId' => $employeeId]) }}" class="inline-flex flex-wrap items-center gap-2" data-submit-once data-approve-form>
                    @csrf
                    <label class="sr-only" for="approve-role-{{ $employeeId }}">{{ __('company.employees.assign_role_label') }}</label>
                    <select id="approve-role-{{ $employeeId }}" name="role_id" class="hb-input !w-auto min-w-[9rem] py-1 text-sm" required>
                        <option value="">{{ __('company.employees.assign_role_placeholder') }}</option>
                        @foreach ($assignableRoles as $role)
                            <option value="{{ data_get($role, 'id') }}">{{ $roleLabel((string) data_get($role, 'name')) }}</option>
                        @endforeach
                    </select>
                    <x-button type="submit" size="sm" variant="primary">{{ __('company.employees.approve') }}</x-button>
                </form>
                <x-button variant="destructive" size="sm" :href="route('frontend.company.employees.reject', ['employeeId' => $employeeId])">{{ __('company.employees.decline') }}</x-button>
            @endif

            @if ($status === 'approved')
                @if (! ($roleUpdatesEnabled ?? false))
                    <span class="text-xs text-hb-muted" data-role-update-locked>{{ __('company.employees.role_update_locked_hint') }}</span>
                @elseif ($isSelf)
                    <span class="text-xs text-hb-muted" data-self-role-locked>{{ __('company.employees.self_role_hint') }}</span>
                @elseif ($assignableRoles !== [])
                    <form method="POST" action="{{ route('frontend.company.employees.role.update', ['employeeId' => $employeeId]) }}" class="inline-flex flex-wrap items-center gap-2" data-submit-once data-role-form>
                        @csrf
                        @method('PUT')
                        <label class="sr-only" for="role-{{ $employeeId }}">{{ __('company.employees.change_role') }}</label>
                        <select id="role-{{ $employeeId }}" name="role_id" class="hb-input !w-auto min-w-[9rem] py-1 text-sm" required>
                            @foreach ($assignableRoles as $role)
                                <option value="{{ data_get($role, 'id') }}" @selected((int) data_get($role, 'id') === (int) data_get($row, 'role.id'))>
                                    {{ $roleLabel((string) data_get($role, 'name')) }}
                                </option>
                            @endforeach
                        </select>
                        <x-button type="submit" size="sm" variant="light">{{ __('company.employees.save_role') }}</x-button>
                    </form>
                @endif
            @endif

            @if (in_array($status, ['approved', 'rejected'], true))
                @if ($isSelf)
                    <span class="text-xs text-hb-muted" data-self-remove-locked>{{ __('company.employees.self_remove_hint') }}</span>
                @elseif ($isLastAdmin)
                    <span class="text-xs text-hb-muted" data-last-admin-locked>{{ __('company.employees.last_admin_hint') }}</span>
                @else
                    <form method="POST" action="{{ route('frontend.company.employees.remove', ['employeeId' => $employeeId]) }}" class="inline" onsubmit="return confirm(@json(__('company.employees.confirm_remove')))" data-submit-once>
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" size="sm" variant="destructive">{{ __('company.employees.remove') }}</x-button>
                    </form>
                @endif
            @endif
        </div>
    </td>
</tr>
