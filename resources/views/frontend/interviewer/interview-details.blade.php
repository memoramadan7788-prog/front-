@extends('frontend.layouts.app')

@section('title', __('Interview details'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Interview details') }}</h1>
@endsection

@section('content')
@php $row = \App\Support\Rows::get($interview ?? [], 'data', $interview ?? []); @endphp
<x-card class="max-w-2xl">
    <x-slot:header>
        <h2 class="text-lg font-bold text-hb-navy">{{ data_get($row, 'application.job.title') ?? data_get($row, 'job_title') ?? __('Interview details') }}</h2>
    </x-slot:header>

    <dl class="grid gap-4 sm:grid-cols-2">
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('Candidate') }}</dt>
            <dd class="mt-1 text-hb-navy">{{ data_get($row, 'application.candidate.name') ?? data_get($row, 'candidate_name') ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('Status') }}</dt>
            <dd class="mt-1"><x-badge :variant="\App\Support\Rows::badgeVariant(data_get($row, 'status'))" :dot="true">{{ data_get($row, 'status', '—') }}</x-badge></dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('Round') }}</dt>
            <dd class="mt-1 text-hb-navy">{{ data_get($row, 'round_no', '—') }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('Type') }}</dt>
            <dd class="mt-1 text-hb-navy">{{ data_get($row, 'interview_type', '—') }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('When') }}</dt>
            <dd class="mt-1 text-hb-navy">{{ \App\Support\Rows::date(data_get($row, 'scheduled_at')) }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('Meeting link') }}</dt>
            <dd class="mt-1 text-hb-navy">
                @if (data_get($row, 'meeting_url'))
                    <a class="font-semibold text-hb-blue hover:underline" href="{{ data_get($row, 'meeting_url') }}" target="_blank" rel="noopener">{{ __('Open') }}</a>
                @else
                    —
                @endif
            </dd>
        </div>
    </dl>

    <div class="flex flex-wrap items-center gap-3 pt-6">
        <x-button :href="route('frontend.interviewer.interviews.evaluation', ['interviewId' => $interviewId])">{{ __('Evaluate') }}</x-button>
        <x-button variant="light" :href="route('frontend.interviewer.interviews.index')">{{ __('Back') }}</x-button>
    </div>
</x-card>
@endsection
