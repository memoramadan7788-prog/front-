@extends('layouts.app')

@section('title', $pageTitle ?? __('interviewer.evaluations.index_page_title'))
@section('breadcrumb', __('interviewer.evaluations.index_breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ $index['title'] }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ $index['intro'] }}</p>
@endsection

@section('content')
    <div class="hb-surface overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[36rem] text-start text-sm">
                <caption class="sr-only">{{ $index['title'] }}</caption>
                <thead class="bg-hb-page text-xs font-semibold tracking-wide text-hb-muted uppercase">
                    <tr>
                        <th scope="col" class="px-5 py-3">{{ __('interviewer.evaluations.col_candidate') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('interviewer.evaluations.col_interview') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('interviewer.evaluations.col_when') }}</th>
                        <th scope="col" class="px-4 py-3">{{ __('interviewer.evaluations.col_status') }}</th>
                        <th scope="col" class="px-5 py-3">{{ __('interviewer.evaluations.col_action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hb-border">
                    @forelse ($index['rows'] as $row)
                        <tr>
                            <td class="px-5 py-4">
                                <p class="font-semibold text-hb-navy">{{ $row['applicant'] }}</p>
                                <p class="mt-0.5 text-xs text-hb-muted">{{ $row['job_title'] }} · {{ $row['job_code'] }}</p>
                            </td>
                            <td class="px-4 py-4 text-hb-text">{{ $row['title'] }}</td>
                            <td class="px-4 py-4 text-hb-muted">{{ $row['when'] }}</td>
                            <td class="px-4 py-4">
                                @php
                                    $variant = match ($row['status_key']) {
                                        'overdue' => 'pending',
                                        'draft' => 'info',
                                        'ready' => 'success',
                                        default => 'neutral',
                                    };
                                @endphp
                                <x-badge :variant="$variant" :dot="true">{{ $row['status_label'] }}</x-badge>
                            </td>
                            <td class="px-5 py-4">
                                <a
                                    href="{{ route('interviewer.evaluations.create', $row['id']) }}"
                                    class="hb-link-hit font-semibold text-hb-blue hover:underline"
                                >
                                    {{ __('interviewer.evaluations.open_scorecard') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-hb-muted">{{ __('interviewer.evaluations.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <p class="mt-8 text-center text-xs text-hb-muted">{{ $index['footer'] }}</p>
@endsection
