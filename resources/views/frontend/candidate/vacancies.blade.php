@extends('frontend.layouts.app')

@section('title', __('Discover jobs'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Discover jobs') }}</h1>
@endsection

@section('content')
@php $items = \App\Support\Rows::from($vacancies ?? []); $meta = \App\Support\Rows::meta($vacancies ?? null); @endphp

<x-table>
    <x-slot:head>
        <th class="px-4 py-3 text-start">{{ __('Title') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Company') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Location') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Work mode') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Deadline') }}</th>
        <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
    </x-slot:head>

    @forelse ($items as $row)
        <tr>
            <td class="px-4 py-3">{{ data_get($row, 'title', '—') }}</td>
            <td class="px-4 py-3">{{ data_get($row, 'company.name') ?? data_get($row, 'company_name') ?? '—' }}</td>
            <td class="px-4 py-3">{{ data_get($row, 'location', '—') }}</td>
            <td class="px-4 py-3">{{ data_get($row, 'work_mode', '—') }}</td>
            <td class="px-4 py-3">{{ \App\Support\Rows::date(data_get($row, 'deadline'), 'Y-m-d') }}</td>
            <td class="px-4 py-3"><div class="flex flex-wrap justify-end gap-2">
                <x-button variant="light" size="sm" :href="route('frontend.candidate.vacancies.show', ['vacancyId' => data_get($row, 'id')])">{{ __('View') }}</x-button>
                <x-button variant="primary" size="sm" :href="route('frontend.candidate.vacancies.apply', ['vacancyId' => data_get($row, 'id')])">{{ __('Apply') }}</x-button>
            </div></td>
        </tr>
    @empty
        <tr><td colspan="6" class="px-4 py-10 text-center text-hb-muted">{{ __('Nothing to show yet.') }}</td></tr>
    @endforelse
</x-table>
@include('frontend.partials.pagination', ['meta' => $meta])
@endsection
