@extends('frontend.layouts.app')

@section('title', __('candidate.interviews.list_title'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.interviews.list_title') }}</h1>
@endsection

@section('content')
@php
    $items = \App\Support\Rows::from($interviews ?? []);
    $meta = \App\Support\Rows::meta($interviews ?? null);
    $hasError = filled($loadError ?? null);
@endphp

@if ($hasError || $items === [])
    @include('frontend.partials.pipeline-state', [
        'loadError' => $loadError ?? null,
        'loadStatus' => $loadStatus ?? null,
        'emptyTitle' => __('candidate.pipeline.interviews_empty_title'),
        'emptyBody' => __('candidate.pipeline.interviews_empty_body'),
        'ctaLabel' => __('candidate.pipeline.interviews_empty_cta'),
        'ctaHref' => route('frontend.candidate.applications.index'),
    ])
@else
    <x-table>
        <x-slot:head>
            <th class="px-4 py-3 text-start">{{ __('Job') }}</th>
            <th class="px-4 py-3 text-start">{{ __('Round') }}</th>
            <th class="px-4 py-3 text-start">{{ __('Type') }}</th>
            <th class="px-4 py-3 text-start">{{ __('Status') }}</th>
            <th class="px-4 py-3 text-start">{{ __('When') }}</th>
            <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
        </x-slot:head>

        @foreach ($items as $row)
            <tr>
                <td class="px-4 py-3">{{ data_get($row, 'application.job.title') ?? data_get($row, 'job_title') ?? '—' }}</td>
                <td class="px-4 py-3">{{ data_get($row, 'round_no', '—') }}</td>
                <td class="px-4 py-3">{{ data_get($row, 'interview_type', '—') }}</td>
                <td class="px-4 py-3"><x-badge :variant="\App\Support\Rows::badgeVariant(data_get($row, 'status'))" :dot="true">{{ data_get($row, 'status', '—') }}</x-badge></td>
                <td class="px-4 py-3">{{ \App\Support\Rows::date(data_get($row, 'scheduled_at')) }}</td>
                <td class="px-4 py-3"><div class="flex flex-wrap justify-end gap-2">
                    <x-button variant="light" size="sm" :href="route('frontend.candidate.interviews.slots', ['application_id' => data_get($row, 'application_id')])">{{ __('Choose a slot') }}</x-button>
                    <x-button variant="light" size="sm" :href="route('frontend.candidate.interviews.reschedule', ['interviewId' => data_get($row, 'id')])">{{ __('Reschedule') }}</x-button>
                    <form method="POST" action="{{ route('frontend.candidate.interviews.cancel', ['interviewId' => data_get($row, 'id')]) }}" class="inline" onsubmit="return confirm('{{ __('Cancel this interview?') }}')">@csrf <x-button type="submit" size="sm" variant="destructive">{{ __('Cancel') }}</x-button></form>
                </div></td>
            </tr>
        @endforeach
    </x-table>
    @include('frontend.partials.pagination', ['meta' => $meta])
@endif
@endsection
