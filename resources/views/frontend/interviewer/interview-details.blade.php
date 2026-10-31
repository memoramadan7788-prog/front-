@extends('frontend.layouts.app')

@php
    $state = $state ?? 'error';
    $details = is_array($details ?? null) ? $details : [];
    $bookings = is_array($details['bookings'] ?? null) ? $details['bookings'] : [];
    $prompts = is_array($prompts ?? null) ? $prompts : [];
@endphp

@section('title', __('interviewer.details.title'))

@section('page_header')
    <div>
        <p class="text-sm font-semibold text-hb-muted">{{ __('interviewer.live.breadcrumb') }}</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-hb-navy">{{ __('interviewer.details.title') }}</h1>
        @if (! empty($details['job_title']))
            <p class="mt-1 text-sm text-hb-muted">{{ $details['job_title'] }}</p>
        @endif
    </div>
@endsection

@section('content')
<div class="mx-auto max-w-6xl space-y-5" data-interview-details data-state="{{ $state }}" data-interview-id="{{ $interviewId }}">
    @if (in_array($state, ['forbidden', 'error'], true))
        <x-alert variant="error">{{ $message }}</x-alert>
    @else
        <section class="grid gap-5 lg:grid-cols-2" aria-label="{{ __('interviewer.details.prepare_heading') }}">
            <x-card>
                <x-slot:header>
                    <h2 class="text-lg font-bold text-hb-navy">{{ __('interviewer.details.prepare_heading') }}</h2>
                </x-slot:header>

                <dl class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('interviewer.live.label_interview_id') }}</dt>
                        <dd class="mt-1 font-medium text-hb-navy">#{{ $details['id'] ?? $interviewId }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('interviewer.live.label_type') }}</dt>
                        <dd class="mt-1 font-medium text-hb-navy">{{ $details['interview_type'] ?? __('interviewer.details.unavailable') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('Status') }}</dt>
                        <dd class="mt-1">
                            @if (! empty($details['status']))
                                <x-badge :variant="\App\Support\Rows::badgeVariant($details['status'])" :dot="true">{{ $details['status'] }}</x-badge>
                            @else
                                <span class="text-hb-muted">{{ __('interviewer.details.unavailable') }}</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('interviewer.live.label_round') }}</dt>
                        <dd class="mt-1 font-medium text-hb-navy">{{ $details['round_no'] ?? __('interviewer.details.unavailable') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('interviewer.details.location_label') }}</dt>
                        <dd class="mt-1 font-medium text-hb-navy">{{ $details['location'] ?? __('interviewer.details.no_location') }}</dd>
                    </div>
                </dl>

                <div class="mt-4 border-t border-hb-border pt-4">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('interviewer.details.instructions_label') }}</h3>
                    <p class="mt-1 whitespace-pre-line text-sm leading-6 text-hb-navy">{{ $details['instructions'] ?? __('interviewer.details.no_instructions') }}</p>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    @if (! empty($details['meeting_link']))
                        <x-button variant="light" size="sm" :href="$details['meeting_link']" target="_blank" rel="noopener noreferrer">{{ __('interviewer.live.open_meeting') }}</x-button>
                    @else
                        <span class="self-center text-sm text-hb-muted">{{ __('interviewer.live.meeting_unavailable') }}</span>
                    @endif
                    <x-button variant="primary" size="sm" :href="route('frontend.interviewer.interviews.live', ['interviewId' => $interviewId])">{{ __('interviewer.live.open_workspace') }}</x-button>
                    <x-button variant="light" size="sm" :href="route('frontend.interviewer.interviews.evaluation', ['interviewId' => $interviewId])">{{ __('Evaluate') }}</x-button>
                </div>
            </x-card>

            <x-card>
                <x-slot:header>
                    <h2 class="text-lg font-bold text-hb-navy">{{ __('interviewer.details.interviewer_heading') }}</h2>
                </x-slot:header>

                @forelse ($bookings as $booking)
                    <div @class(['py-3 first:pt-0', 'border-t border-hb-border' => ! $loop->first])>
                        <p class="font-medium text-hb-navy">{{ $booking['interviewer_name'] ?? __('interviewer.details.no_assigned_interviewers') }}</p>
                        @if (! empty($booking['start_at']) || ! empty($booking['end_at']))
                            <p class="mt-1 text-sm text-hb-muted">
                                @if (! empty($booking['start_at']))
                                    {{ \App\Support\Rows::date($booking['start_at']) }}
                                @endif
                                @if (! empty($booking['end_at']))
                                    <span aria-hidden="true">·</span> {{ \App\Support\Rows::date($booking['end_at']) }}
                                @endif
                            </p>
                            <p class="mt-1 text-xs text-hb-muted">{{ __('interviewer.live.timezone_unavailable') }}</p>
                        @endif
                    </div>
                @empty
                    <x-empty-state :title="__('interviewer.details.no_assigned_interviewers')" />
                @endforelse
            </x-card>

            <x-card>
                <x-slot:header>
                    <h2 class="text-lg font-bold text-hb-navy">{{ __('interviewer.details.candidate_brief_heading') }}</h2>
                </x-slot:header>

                @if (! empty($details['candidate_name']) || ! empty($details['candidate_headline']))
                    @if (! empty($details['candidate_name']))
                        <p class="font-semibold text-hb-navy">{{ $details['candidate_name'] }}</p>
                    @endif
                    @if (! empty($details['candidate_headline']))
                        <p class="mt-1 text-sm text-hb-muted">{{ $details['candidate_headline'] }}</p>
                    @endif
                    <p class="mt-3 text-xs text-hb-muted">{{ __('interviewer.live.pii_note') }}</p>
                @else
                    <x-empty-state :title="__('interviewer.details.candidate_brief_empty')" />
                @endif
            </x-card>

            <x-card>
                <x-slot:header>
                    <h2 class="text-lg font-bold text-hb-navy">{{ __('interviewer.details.competencies_heading') }}</h2>
                </x-slot:header>
                <p class="text-sm leading-6 text-hb-muted">{{ __('interviewer.details.competencies_unavailable') }}</p>
                <x-button class="mt-4" variant="light" size="sm" :href="route('frontend.interviewer.interviews.evaluation', ['interviewId' => $interviewId])">{{ __('Evaluate') }}</x-button>
            </x-card>

            <x-card>
                <x-slot:header>
                    <h2 class="text-lg font-bold text-hb-navy">{{ __('interviewer.details.prompts_heading') }}</h2>
                </x-slot:header>
                <p class="text-xs leading-5 text-hb-muted">{{ __('interviewer.details.prompts_generic_note') }}</p>
                <ul class="mt-3 list-disc space-y-2 ps-5 text-sm leading-6 text-hb-navy">
                    @foreach ($prompts as $prompt)
                        <li>{{ $prompt }}</li>
                    @endforeach
                </ul>
                <p class="mt-3 text-xs text-hb-muted">{{ __('interviewer.live.questions_not_persisted') }}</p>
            </x-card>

            <x-card>
                <x-slot:header>
                    <h2 class="text-lg font-bold text-hb-navy">{{ __('interviewer.details.agenda_heading') }}</h2>
                </x-slot:header>
                <p class="text-sm leading-6 text-hb-muted">{{ __('interviewer.details.agenda_unavailable') }}</p>
                <x-button class="mt-4" variant="light" size="sm" :href="route('frontend.interviewer.interviews.live', ['interviewId' => $interviewId])">{{ __('interviewer.live.open_workspace') }}</x-button>
            </x-card>

            <x-card class="lg:col-span-2">
                <x-slot:header>
                    <h2 class="text-lg font-bold text-hb-navy">{{ __('interviewer.details.scoped_heading') }}</h2>
                </x-slot:header>
                <p class="text-sm leading-6 text-hb-muted">{{ __('interviewer.details.scoped_note') }}</p>
            </x-card>
        </section>
    @endif

    <div class="flex flex-wrap gap-2">
        <x-button variant="light" :href="route('frontend.interviewer.interviews.index')">{{ __('Back') }}</x-button>
    </div>
</div>
@endsection
