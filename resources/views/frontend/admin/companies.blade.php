@extends('frontend.layouts.app')

@section('title', __('Companies'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Companies') }}</h1>
@endsection

@section('content')
@php $items = \App\Support\Rows::from($companies ?? []); $meta = $pagination ?? null; @endphp
<form method="GET" action="{{ route('frontend.admin.companies.index') }}" class="mb-4 flex flex-wrap items-end gap-3">
    <x-form.input name="search" :label="__('Search')" type="search" class="max-w-xs" value="{{ request('search') }}" placeholder="{{ __('Company name') }}" />
    <div class="w-full max-w-xs"><x-form.select name="status" :label="__('Status')">
        <option value="">{{ __('All') }}</option>
        @foreach (['pending' => __('Pending'), 'approved' => __('Approved'), 'rejected' => __('Rejected'), 'suspended' => __('Suspended')] as $st => $label)
            <option value="{{ $st }}" @selected(request('status') === $st)>{{ $label }}</option>
        @endforeach
    </x-form.select></div>
    <x-button type="submit" variant="light">{{ __('Filter') }}</x-button>
    @if (request()->hasAny(['search', 'status']))
        <x-button variant="ghost" :href="route('frontend.admin.companies.index')">{{ __('Reset') }}</x-button>
    @endif
</form>

<x-table>
    <x-slot:head>
        <th class="px-4 py-3 text-start">{{ __('Name') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Industry') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Contact') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Jobs') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Status') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Registered') }}</th>
        <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
    </x-slot:head>

    @forelse ($items as $row)
        @php $st = data_get($row, 'status'); @endphp
        <tr>
            <td class="px-4 py-3">
                <p class="font-semibold text-hb-navy">{{ data_get($row, 'name', '—') }}</p>
                @if (data_get($row, 'owner.email'))<p class="text-xs text-hb-muted">{{ data_get($row, 'owner.email') }}</p>@endif
            </td>
            <td class="px-4 py-3">{{ data_get($row, 'industry') ?: '—' }}</td>
            <td class="px-4 py-3">{{ data_get($row, 'contact_email') ?: '—' }}</td>
            <td class="px-4 py-3">{{ data_get($row, 'jobs_count', 0) }}</td>
            <td class="px-4 py-3">
                <x-badge :variant="\App\Support\Rows::badgeVariant($st)" :dot="true">{{ $st ?? '—' }}</x-badge>
                @if ($st === 'rejected' && data_get($row, 'rejection_reason'))
                    <p class="mt-1 max-w-[220px] truncate text-xs text-hb-muted" title="{{ data_get($row, 'rejection_reason') }}">{{ data_get($row, 'rejection_reason') }}</p>
                @endif
            </td>
            <td class="px-4 py-3">{{ \App\Support\Rows::date(data_get($row, 'created_at'), 'Y-m-d') }}</td>
            <td class="px-4 py-3"><div class="flex flex-wrap justify-end gap-2">
                @if ($st === 'pending')
                    <form method="POST" action="{{ route('frontend.admin.companies.approve', ['companyId' => data_get($row, 'id')]) }}" class="inline">@csrf <x-button type="submit" size="sm" variant="primary">{{ __('Approve') }}</x-button></form>
                    <x-button variant="destructive" size="sm" :href="route('frontend.admin.companies.reject', ['companyId' => data_get($row, 'id')])">{{ __('Reject') }}</x-button>
                @elseif ($st === 'approved')
                    <form method="POST" action="{{ route('frontend.admin.companies.suspend', ['companyId' => data_get($row, 'id')]) }}" class="inline" onsubmit="return confirm('{{ __('Suspend this company?') }}')">@csrf <x-button type="submit" size="sm" variant="light">{{ __('Suspend') }}</x-button></form>
                @elseif ($st === 'suspended')
                    <form method="POST" action="{{ route('frontend.admin.companies.approve', ['companyId' => data_get($row, 'id')]) }}" class="inline">@csrf <x-button type="submit" size="sm" variant="primary">{{ __('Reactivate') }}</x-button></form>
                @endif
            </div></td>
        </tr>
    @empty
        <tr><td colspan="7" class="px-4 py-10 text-center text-hb-muted">{{ __('Nothing to show yet.') }}</td></tr>
    @endforelse
</x-table>
@include('frontend.partials.pagination', ['meta' => $meta])
@endsection
