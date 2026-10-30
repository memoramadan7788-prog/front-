@extends('frontend.layouts.app')

@section('title', __('My interviews'))

@section('page_header')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('My interviews') }}</h1>
        <x-button variant="primary" size="sm" :href="route('frontend.interviewer.availability.add')">{{ __('Add availability') }}</x-button>
    </div>
@endsection

@section('content')
@php $items = \App\Support\Rows::from($interviews ?? []); $meta = \App\Support\Rows::meta($interviews ?? null); @endphp

<x-table>
    <x-slot:head>
        <th class="px-4 py-3 text-start">{{ __('common.role_labels.candidate') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Job') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Round') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Status') }}</th>
        <th class="px-4 py-3 text-start">{{ __('When') }}</th>
        <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
    </x-slot:head>

    @forelse ($items as $row)
        <tr>
            <td class="px-4 py-3">{{ data_get($row, 'application.candidate.name') ?? data_get($row, 'candidate_name') ?? '—' }}</td>
            <td class="px-4 py-3">{{ data_get($row, 'application.job.title') ?? data_get($row, 'job_title') ?? '—' }}</td>
            <td class="px-4 py-3">{{ data_get($row, 'round_no', '—') }}</td>
            <td class="px-4 py-3"><x-badge :variant="\App\Support\Rows::badgeVariant(data_get($row, 'status'))" :dot="true">{{ data_get($row, 'status', '—') }}</x-badge></td>
            <td class="px-4 py-3">{{ \App\Support\Rows::date(data_get($row, 'scheduled_at')) }}</td>
            <td class="px-4 py-3"><div class="flex flex-wrap justify-end gap-2">
                <x-button variant="light" size="sm" :href="route('frontend.interviewer.interviews.show', ['interviewId' => data_get($row, 'id')])">{{ __('View') }}</x-button>
                <x-button variant="primary" size="sm" :href="route('frontend.interviewer.interviews.live', ['interviewId' => data_get($row, 'id')])">{{ __('interviewer.live.open_workspace') }}</x-button>
                <x-button variant="light" size="sm" :href="route('frontend.interviewer.interviews.evaluation', ['interviewId' => data_get($row, 'id')])">{{ __('Evaluate') }}</x-button>
            </div></td>
        </tr>
    @empty
        <tr><td colspan="6" class="px-4 py-10 text-center text-hb-muted">{{ __('Nothing to show yet.') }}</td></tr>
    @endforelse
</x-table>
@include('frontend.partials.pagination', ['meta' => $meta])
@endsection
