@extends('frontend.layouts.app')

@section('title', __('candidate.applications.index_title'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.applications.index_title') }}</h1>
@endsection

@section('content')
@php
    $items = \App\Support\Rows::from($applications ?? []);
    $meta = \App\Support\Rows::meta($applications ?? null);
    $hasError = filled($loadError ?? null);
@endphp

@if ($hasError || $items === [])
    @include('frontend.partials.pipeline-state', [
        'loadError' => $loadError ?? null,
        'loadStatus' => $loadStatus ?? null,
        'emptyTitle' => __('candidate.pipeline.applications_empty_title'),
        'emptyBody' => __('candidate.pipeline.applications_empty_body'),
        'ctaLabel' => __('candidate.pipeline.applications_empty_cta'),
        'ctaHref' => route('frontend.candidate.vacancies.index'),
    ])
@else
    <x-table>
        <x-slot:head>
            <th class="px-4 py-3 text-start">{{ __('Job') }}</th>
            <th class="px-4 py-3 text-start">{{ __('Status') }}</th>
            <th class="px-4 py-3 text-start">{{ __('Applied') }}</th>
            <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
        </x-slot:head>

        @foreach ($items as $row)
            <tr>
                <td class="px-4 py-3">{{ data_get($row, 'job.title') ?? data_get($row, 'job_title') ?? data_get($row, 'title') ?? '—' }}</td>
                <td class="px-4 py-3"><x-badge :variant="\App\Support\Rows::badgeVariant(data_get($row, 'status'))" :dot="true">{{ data_get($row, 'status', '—') }}</x-badge></td>
                <td class="px-4 py-3">{{ \App\Support\Rows::date(data_get($row, 'applied_at')) }}</td>
                <td class="px-4 py-3"><div class="flex flex-wrap justify-end gap-2">
                    <x-button variant="light" size="sm" :href="route('frontend.candidate.applications.show', ['applicationId' => data_get($row, 'id')])">{{ __('View') }}</x-button>
                    @if (! in_array(data_get($row, 'status'), ['withdrawn', 'rejected', 'hired'], true))
                    <form method="POST" action="{{ route('frontend.candidate.applications.withdraw', ['applicationId' => data_get($row, 'id')]) }}" class="inline" onsubmit="return confirm('{{ __('Withdraw this application?') }}')">@csrf <x-button type="submit" size="sm" variant="destructive">{{ __('Withdraw') }}</x-button></form>
                    @endif
                </div></td>
            </tr>
        @endforeach
    </x-table>
    @include('frontend.partials.pagination', ['meta' => $meta])
@endif
@endsection
