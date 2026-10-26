@extends('frontend.layouts.app')

@section('title', __('Team'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Team') }}</h1>
@endsection

@section('content')
@php $items = \App\Support\Rows::from($employees ?? []); $meta = \App\Support\Rows::meta($employees ?? null); @endphp

<x-table>
    <x-slot:head>
        <th class="px-4 py-3 text-start">{{ __('Name') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Job title') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Department') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Status') }}</th>
        <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
    </x-slot:head>

    @forelse ($items as $row)
        <tr>
            <td class="px-4 py-3">{{ data_get($row, 'user.name') ?? data_get($row, 'name') ?? '—' }}</td>
            <td class="px-4 py-3">{{ data_get($row, 'requested_job_title', '—') }}</td>
            <td class="px-4 py-3">{{ data_get($row, 'requested_department', '—') }}</td>
            <td class="px-4 py-3"><x-badge :variant="\App\Support\Rows::badgeVariant(data_get($row, 'status'))" :dot="true">{{ data_get($row, 'status', '—') }}</x-badge></td>
            <td class="px-4 py-3"><div class="flex flex-wrap justify-end gap-2">
                @if (data_get($row, 'status') === 'pending')
                <form method="POST" action="{{ route('frontend.company.employees.approve', ['employeeId' => data_get($row, 'id')]) }}" class="inline">@csrf <x-button type="submit" size="sm" variant="primary">{{ __('Approve') }}</x-button></form>
                @endif
                @if (data_get($row, 'status') === 'pending')
                <x-button variant="destructive" size="sm" :href="route('frontend.company.employees.reject', ['employeeId' => data_get($row, 'id')])">{{ __('Reject') }}</x-button>
                @endif
                @if (data_get($row, 'status') !== 'pending')
                <form method="POST" action="{{ route('frontend.company.employees.remove', ['employeeId' => data_get($row, 'id')]) }}" class="inline" onsubmit="return confirm('{{ __('Remove this employee?') }}')">@csrf @method('DELETE')<x-button type="submit" size="sm" variant="destructive">{{ __('Remove') }}</x-button></form>
                @endif
            </div></td>
        </tr>
    @empty
        <tr><td colspan="5" class="px-4 py-10 text-center text-hb-muted">{{ __('Nothing to show yet.') }}</td></tr>
    @endforelse
</x-table>
@include('frontend.partials.pagination', ['meta' => $meta])
@endsection
