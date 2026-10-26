@extends('frontend.layouts.app')

@section('title', __('Vacancies'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Vacancies') }}</h1>
@endsection

@section('page_actions')
    <x-button variant="primary" size="md" :href="route('frontend.company.vacancies.create')">{{ __('New vacancy') }}</x-button>
@endsection

@section('content')
@php $items = \App\Support\Rows::from($vacancies ?? []); $meta = \App\Support\Rows::meta($vacancies ?? null); @endphp

<x-table>
    <x-slot:head>
        <th class="px-4 py-3 text-start">{{ __('Title') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Status') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Location') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Deadline') }}</th>
        <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
    </x-slot:head>

    @forelse ($items as $row)
        <tr>
            <td class="px-4 py-3">{{ data_get($row, 'title', '—') }}</td>
            <td class="px-4 py-3"><x-badge :variant="\App\Support\Rows::badgeVariant(data_get($row, 'status'))" :dot="true">{{ data_get($row, 'status', '—') }}</x-badge></td>
            <td class="px-4 py-3">{{ data_get($row, 'location', '—') }}</td>
            <td class="px-4 py-3">{{ \App\Support\Rows::date(data_get($row, 'deadline'), 'Y-m-d') }}</td>
            <td class="px-4 py-3"><div class="flex flex-wrap justify-end gap-2">
                <x-button variant="light" size="sm" :href="route('frontend.company.vacancies.applications', ['vacancyId' => data_get($row, 'id')])">{{ __('Applications') }}</x-button>
                <x-button variant="light" size="sm" :href="route('frontend.company.vacancies.update', ['vacancyId' => data_get($row, 'id')])">{{ __('Edit') }}</x-button>
                <form method="POST" action="{{ route('frontend.company.vacancies.delete', ['vacancyId' => data_get($row, 'id')]) }}" class="inline" onsubmit="return confirm('{{ __('Delete this vacancy?') }}')">@csrf @method('DELETE')<x-button type="submit" size="sm" variant="destructive">{{ __('Delete') }}</x-button></form>
            </div></td>
        </tr>
    @empty
        <tr><td colspan="5" class="px-4 py-10 text-center text-hb-muted">{{ __('Nothing to show yet.') }}</td></tr>
    @endforelse
</x-table>
@include('frontend.partials.pagination', ['meta' => $meta])
@endsection
