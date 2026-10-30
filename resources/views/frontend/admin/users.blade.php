@extends('frontend.layouts.app')

@section('title', __('Users'))

@section('page_header')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Users') }}</h1>
        <x-button variant="light" :href="route('frontend.admin.roles.index')">{{ __('Roles & permissions') }}</x-button>
    </div>
@endsection

@section('content')
@php $items = \App\Support\Rows::from($users ?? []); $meta = $pagination ?? null; @endphp
<form method="GET" action="{{ route('frontend.admin.users.index') }}" class="mb-4 flex flex-wrap items-end gap-3">
    <x-form.input name="search" :label="__('Search')" type="search" class="max-w-xs" value="{{ request('search') }}" placeholder="{{ __('Name or email') }}" />
    <div class="w-full max-w-xs"><x-form.select name="account_type" :label="__('Type')">
        <option value="">{{ __('All') }}</option>
        @foreach (['candidate' => __('common.role_labels.candidate'), 'company' => __('common.role_labels.company'), 'admin' => __('common.role_labels.admin')] as $v => $label)
            <option value="{{ $v }}" @selected(request('account_type') === $v)>{{ $label }}</option>
        @endforeach
    </x-form.select></div>
    <div class="w-full max-w-xs"><x-form.select name="account_status_id" :label="__('Status')">
        <option value="">{{ __('All') }}</option>
        @foreach ([1 => __('Active'), 2 => __('Deactivated'), 3 => __('Blocked'), 4 => __('Deleted')] as $v => $label)
            <option value="{{ $v }}" @selected((string) request('account_status_id') === (string) $v)>{{ $label }}</option>
        @endforeach
    </x-form.select></div>
    <x-button type="submit" variant="light">{{ __('Filter') }}</x-button>
    @if (request()->hasAny(['search', 'account_type', 'account_status_id']))
        <x-button variant="ghost" :href="route('frontend.admin.users.index')">{{ __('Reset') }}</x-button>
    @endif
</form>

<x-table>
    <x-slot:head>
        <th class="px-4 py-3 text-start">{{ __('Name') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Type') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Roles') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Status') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Joined') }}</th>
        <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
    </x-slot:head>

    @forelse ($items as $row)
        @php
            $isAdmin = data_get($row, 'account_type') === 'admin';
            $userRoles = collect(data_get($row, 'roles', []))->filter(fn ($r) => is_array($r) || is_object($r));
            $permissionCount = collect(data_get($row, 'permissions', []))->filter()->count();
        @endphp
        <tr>
            <td class="px-4 py-3">
                <p class="font-semibold text-hb-navy">{{ data_get($row, 'name', '—') }}</p>
                <p class="text-xs text-hb-muted">{{ data_get($row, 'email', '—') }}</p>
            </td>
            <td class="px-4 py-3">{{ ucfirst((string) data_get($row, 'account_type', '—')) }}</td>
            <td class="px-4 py-3">
                @if ($userRoles->isEmpty())
                    <span class="text-xs text-hb-muted">—</span>
                @else
                    <ul class="space-y-2">
                        @foreach ($userRoles as $role)
                            @php
                                $scope = (string) data_get($role, 'scope', '');
                                $companyId = data_get($role, 'company_id');
                                $scopeVariant = $scope === 'platform' ? 'info' : ($scope === 'company' ? 'pending' : 'neutral');
                            @endphp
                            <li class="text-xs">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="font-semibold text-hb-navy">{{ data_get($role, 'name', '—') }}</span>
                                    @if ($scope !== '')
                                        <x-badge :variant="$scopeVariant">{{ ucfirst($scope) }}</x-badge>
                                    @endif
                                </div>
                                <p class="mt-0.5 text-hb-muted">
                                    @if ($companyId)
                                        {{ __('common.role_labels.company') }} #{{ $companyId }}
                                    @elseif ($scope === 'company')
                                        {{ __('No company context') }}
                                    @else
                                        {{ __('Platform scope') }}
                                    @endif
                                    @if (data_get($role, 'assigned_at'))
                                        · {{ \App\Support\Rows::date(data_get($role, 'assigned_at'), 'Y-m-d') }}
                                    @endif
                                </p>
                            </li>
                        @endforeach
                    </ul>
                    @if ($permissionCount > 0)
                        <p class="mt-2 text-[11px] text-hb-muted">{{ __(':count aggregated permission(s)', ['count' => $permissionCount]) }}</p>
                    @endif
                @endif
            </td>
            <td class="px-4 py-3"><x-badge :variant="\App\Support\Rows::badgeVariant(data_get($row, 'account_status'))" :dot="true">{{ data_get($row, 'account_status', '—') }}</x-badge></td>
            <td class="px-4 py-3">{{ \App\Support\Rows::date(data_get($row, 'created_at'), 'Y-m-d') }}</td>
            <td class="px-4 py-3"><div class="flex flex-wrap justify-end gap-2">
                @unless ($isAdmin)
                    <x-button variant="light" size="sm" :href="route('frontend.admin.users.status', ['userId' => data_get($row, 'id')])">{{ __('Change status') }}</x-button>
                @endunless
            </div></td>
        </tr>
    @empty
        <tr><td colspan="6" class="px-4 py-10 text-center text-hb-muted">{{ __('Nothing to show yet.') }}</td></tr>
    @endforelse
</x-table>
@include('frontend.partials.pagination', ['meta' => $meta])
@endsection
