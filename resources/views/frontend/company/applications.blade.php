@extends('frontend.layouts.app')

@section('title', __('Applications'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Applications') }}</h1>
@endsection

@section('content')
@php $items = \App\Support\Rows::from($applications ?? []); $meta = \App\Support\Rows::meta($applications ?? null); @endphp

<x-table>
    <x-slot:head>
        <th class="px-4 py-3 text-start">{{ __('Candidate') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Status') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Applied') }}</th>
        <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
    </x-slot:head>

    @forelse ($items as $row)
        <tr>
            <td class="px-4 py-3">{{ data_get($row, 'candidate.name') ?? data_get($row, 'candidate_name') ?? '—' }}</td>
            <td class="px-4 py-3"><x-badge :variant="\App\Support\Rows::badgeVariant(data_get($row, 'status'))" :dot="true">{{ data_get($row, 'status', '—') }}</x-badge></td>
            <td class="px-4 py-3">{{ \App\Support\Rows::date(data_get($row, 'applied_at')) }}</td>
            <td class="px-4 py-3"><div class="flex flex-wrap justify-end gap-2">
                <x-button variant="light" size="sm" :href="route('frontend.company.applications.status', ['applicationId' => data_get($row, 'id')])">{{ __('Change status') }}</x-button>
                <x-button variant="light" size="sm" :href="route('frontend.company.offers.create', ['application_id' => data_get($row, 'id')])">{{ __('Send offer') }}</x-button>
            </div></td>
        </tr>
    @empty
        <tr><td colspan="4" class="px-4 py-10 text-center text-hb-muted">{{ __('Nothing to show yet.') }}</td></tr>
    @endforelse
</x-table>
@include('frontend.partials.pagination', ['meta' => $meta])
@endsection
