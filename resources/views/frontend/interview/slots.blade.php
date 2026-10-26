@extends('frontend.layouts.app')

@section('title', __('Available slots'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Available slots') }}</h1>
@endsection

@section('content')
@php $items = \App\Support\Rows::from($slots ?? []); $meta = \App\Support\Rows::meta($slots ?? null); @endphp

<x-table>
    <x-slot:head>
        <th class="px-4 py-3 text-start">{{ __('From') }}</th>
        <th class="px-4 py-3 text-start">{{ __('To') }}</th>
        <th class="px-4 py-3 text-start">{{ __('Status') }}</th>
        <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
    </x-slot:head>

    @forelse ($items as $row)
        <tr>
            <td class="px-4 py-3">{{ \App\Support\Rows::date(data_get($row, 'start_at')) }}</td>
            <td class="px-4 py-3">{{ \App\Support\Rows::date(data_get($row, 'end_at')) }}</td>
            <td class="px-4 py-3"><x-badge :variant="\App\Support\Rows::badgeVariant(data_get($row, 'availability_status'))" :dot="true">{{ data_get($row, 'availability_status', '—') }}</x-badge></td>
            <td class="px-4 py-3 text-end">
                @if (request('interview_id') || request('application_id'))
                    <form method="POST" action="{{ route(request('reschedule') ? 'frontend.candidate.interviews.reschedule.submit' : 'frontend.candidate.interviews.book.submit', request('reschedule') ? ['interviewId' => request('interview_id')] : []) }}" class="inline">
                        @csrf
                        @unless (request('reschedule'))
                            <input type="hidden" name="application_id" value="{{ request('application_id') }}">
                        @endunless
                        <input type="hidden" name="availability_id" value="{{ data_get($row, 'id') }}">
                        <x-button type="submit" size="sm" variant="primary">{{ __('Book') }}</x-button>
                    </form>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="4" class="px-4 py-10 text-center text-hb-muted">{{ __('Nothing to show yet.') }}</td></tr>
    @endforelse
</x-table>
@include('frontend.partials.pagination', ['meta' => $meta])
@endsection
