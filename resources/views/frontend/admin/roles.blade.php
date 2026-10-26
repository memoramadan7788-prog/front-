@extends('frontend.layouts.app')

@section('title', __('Roles & permissions'))

@section('page_header')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Roles & permissions') }}</h1>
            <p class="mt-1 text-sm text-hb-muted">{{ __('Read-only catalog of platform and company roles from the Backend API.') }}</p>
        </div>
        <x-button variant="light" :href="route('frontend.admin.users.index')">{{ __('Back to users') }}</x-button>
    </div>
@endsection

@section('content')
@php $items = \App\Support\Rows::from($roles ?? []); @endphp

@if (! empty($loadError))
    <div class="mb-6 rounded-2xl border border-hb-error/30 bg-hb-error-soft px-5 py-4 text-sm text-hb-error" role="alert">
        {{ __('Could not load roles and permissions.') }}
        <span class="opacity-80">{{ trim($loadError) }}</span>
    </div>
@endif

<div class="mb-4 rounded-2xl border border-hb-border bg-hb-surface px-5 py-3 text-sm text-hb-muted">
    {{ __('Assigning and removing roles is not available in this phase. Backend authorization remains authoritative.') }}
</div>

<x-table>
    <x-slot:head>
        <th class="px-4 py-3 text-start">{{ __('Role') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Scope') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Description') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Permissions') }}</th>
    </x-slot:head>

    @forelse ($items as $row)
        @php
            $scope = (string) data_get($row, 'scope', '');
            $permissions = collect(data_get($row, 'permissions', []))->filter()->values();
            $scopeVariant = $scope === 'platform' ? 'info' : ($scope === 'company' ? 'pending' : 'neutral');
        @endphp
        <tr>
            <td class="px-4 py-3">
                <p class="font-semibold text-hb-navy">{{ data_get($row, 'name', '—') }}</p>
                @if (data_get($row, 'id'))
                    <p class="text-xs text-hb-muted">#{{ data_get($row, 'id') }}</p>
                @endif
            </td>
            <td class="px-4 py-3">
                @if ($scope !== '')
                    <x-badge :variant="$scopeVariant" :dot="true">{{ ucfirst($scope) }}</x-badge>
                @else
                    <span class="text-hb-muted">—</span>
                @endif
            </td>
            <td class="px-4 py-3 max-w-[280px] text-sm text-hb-muted">{{ data_get($row, 'description') ?: '—' }}</td>
            <td class="px-4 py-3">
                @if ($permissions->isEmpty())
                    <span class="text-sm text-hb-muted">{{ __('No permissions listed') }}</span>
                @else
                    <div class="flex max-w-xl flex-wrap gap-1.5">
                        @foreach ($permissions as $key)
                            <span class="inline-flex rounded-md bg-hb-page px-2 py-0.5 text-[11px] font-medium text-hb-navy">{{ $key }}</span>
                        @endforeach
                    </div>
                    <p class="mt-1.5 text-xs text-hb-muted">{{ __(':count permission(s)', ['count' => $permissions->count()]) }}</p>
                @endif
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="4" class="px-4 py-10 text-center text-hb-muted">
                @if (! empty($loadError))
                    {{ __('Data unavailable') }}
                @else
                    {{ __('No roles were returned by the API.') }}
                @endif
            </td>
        </tr>
    @endforelse
</x-table>
@endsection
