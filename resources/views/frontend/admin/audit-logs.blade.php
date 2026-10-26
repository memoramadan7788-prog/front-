@extends('frontend.layouts.app')

@section('title', __('Audit logs'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Audit logs') }}</h1>
@endsection

@section('content')
@php $items = \App\Support\Rows::from($logs ?? []); $meta = $pagination ?? null; @endphp
<form method="GET" action="{{ route('frontend.admin.audit-logs.index') }}" class="mb-4 flex flex-wrap items-end gap-3">
    <x-form.input name="action" :label="__('Action')" class="max-w-xs" value="{{ request('action') }}" placeholder="user.status_changed" />
    <x-form.input name="auditable_type" :label="__('Entity')" class="max-w-xs" value="{{ request('auditable_type') }}" placeholder="Company" />
    <x-button type="submit" variant="light">{{ __('Filter') }}</x-button>
    @if (request()->hasAny(['action', 'auditable_type']))
        <x-button variant="ghost" :href="route('frontend.admin.audit-logs.index')">{{ __('Reset') }}</x-button>
    @endif
</form>

<x-table>
    <x-slot:head>
        <th class="px-4 py-3 text-start">{{ __('When') }}</th>
        <th class="px-4 py-3 text-start">{{ __('User') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Action') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Entity') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Reason') }}</th>
        <th class="px-4 py-3 text-start">{{ __('IP') }}</th>
    </x-slot:head>

    @forelse ($items as $row)
        <tr>
            <td class="px-4 py-3 whitespace-nowrap">{{ \App\Support\Rows::date(data_get($row, 'created_at')) }}</td>
            <td class="px-4 py-3">{{ data_get($row, 'user.name') ?? __('System') }}</td>
            <td class="px-4 py-3"><x-badge variant="info">{{ data_get($row, 'action', '—') }}</x-badge></td>
            <td class="px-4 py-3">{{ data_get($row, 'auditable_type', '—') }}@if (data_get($row, 'auditable_id')) <span class="text-hb-muted">#{{ data_get($row, 'auditable_id') }}</span>@endif</td>
            <td class="px-4 py-3 max-w-[280px] truncate text-hb-muted" title="{{ data_get($row, 'reason') }}">{{ data_get($row, 'reason') ?: '—' }}</td>
            <td class="px-4 py-3 text-xs text-hb-muted">{{ data_get($row, 'ip_address') ?: '—' }}</td>
        </tr>
    @empty
        <tr><td colspan="6" class="px-4 py-10 text-center text-hb-muted">{{ __('Nothing to show yet.') }}</td></tr>
    @endforelse
</x-table>
@include('frontend.partials.pagination', ['meta' => $meta])
@endsection
