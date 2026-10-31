@extends('frontend.layouts.app')

@section('title', __('company.applicant_profile.title'))

@section('content')
@php
    $application = is_array($application ?? null) ? $application : [];
    $candidateName = data_get($application, 'candidate.user.name') ?? data_get($application, 'candidate.name') ?? data_get($application, 'candidate_name');
    $jobTitle = data_get($application, 'job.title') ?? data_get($application, 'job_title');
    $status = data_get($application, 'status');
    $tabs = [
        'profile' => __('company.applicant_profile.tabs.profile'),
        'interview_plan' => __('company.applicant_profile.tabs.interview_plan'),
        'notes_activity' => __('company.applicant_profile.tabs.notes_activity'),
        'contact_consent' => __('company.applicant_profile.tabs.contact_consent'),
    ];
@endphp

<div class="mx-auto max-w-5xl space-y-5" data-applicant-profile data-state="{{ $state }}">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-hb-muted">{{ __('company.applicant_profile.eyebrow') }}</p>
            <h1 class="mt-1 text-2xl font-bold text-hb-navy">{{ __('company.applicant_profile.title') }}</h1>
            @if ($state === 'loaded')
                <p class="mt-2 text-sm text-hb-muted">
                    {{ $candidateName ?: __('company.applicant_profile.applicant_label') }}
                    @if ($jobTitle)
                        <span aria-hidden="true">&middot;</span> {{ $jobTitle }}
                    @endif
                </p>
            @endif
        </div>
        <x-button variant="light" :href="route('frontend.company.vacancies.index')">{{ __('company.applicant_profile.back') }}</x-button>
    </header>

    @if ($state === 'forbidden')
        <x-alert variant="error">{{ __('company.applicant_profile.forbidden') }}</x-alert>
    @elseif ($state === 'error')
        <x-alert variant="error">{{ __('company.applicant_profile.load_error') }}</x-alert>
    @else
        <nav class="flex gap-2 overflow-x-auto border-b border-hb-border" aria-label="{{ __('company.applicant_profile.tabs_label') }}">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('frontend.company.applications.show', ['applicationId' => $applicationId, 'tab' => $key]) }}" @class(['shrink-0 border-b-2 px-4 py-3 text-sm font-semibold', 'border-hb-blue text-hb-blue' => $tab === $key, 'border-transparent text-hb-muted hover:text-hb-navy' => $tab !== $key]) @if ($tab === $key) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>

        @if ($tab === 'profile')
            <section class="grid gap-5 lg:grid-cols-2">
                <x-card>
                    <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('company.applicant_profile.profile_heading') }}</h2></x-slot:header>
                    @if ($candidateName || $jobTitle || $status)
                        <dl class="grid gap-4 sm:grid-cols-2">
                            <div><dt class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('company.applicant_profile.applicant_label') }}</dt><dd class="mt-1 font-medium text-hb-navy">{{ $candidateName ?: '—' }}</dd></div>
                            <div><dt class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('company.applicant_profile.application_id') }}</dt><dd class="mt-1 font-medium text-hb-navy">#{{ $applicationId }}</dd></div>
                            <div><dt class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('company.applicant_profile.job_label') }}</dt><dd class="mt-1 font-medium text-hb-navy">{{ $jobTitle ?: '—' }}</dd></div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('company.applicant_profile.status_label') }}</dt>
                                <dd class="mt-1">
                                    @if ($status)
                                        <x-badge :variant="\App\Support\Rows::badgeVariant((string) $status)" :dot="true">{{ $status }}</x-badge>
                                    @else
                                        &mdash;
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    @else
                        <x-empty-state :title="__('company.applicant_profile.profile_empty')">{{ __('company.applicant_profile.profile_empty_body') }}</x-empty-state>
                    @endif
                </x-card>
                <x-card>
                    <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('company.applicant_profile.application_heading') }}</h2></x-slot:header>
                    <p class="text-sm leading-6 text-hb-muted">{{ __('company.applicant_profile.application_body') }}</p>
                    <x-button class="mt-4" variant="light" :href="route('frontend.company.applications.ats', ['applicationId' => $applicationId])">{{ __('ats.open') }}</x-button>
                </x-card>
            </section>
        @elseif ($tab === 'interview_plan')
            <x-card>
                <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('company.applicant_profile.tabs.interview_plan') }}</h2></x-slot:header>
                <p class="text-sm leading-6 text-hb-muted">{{ __('company.applicant_profile.interview_body') }}</p>
                <x-button class="mt-4" variant="primary" :href="route('frontend.company.interviews.schedule', ['application_id' => $applicationId])">{{ __('company.applicant_profile.schedule_interview') }}</x-button>
            </x-card>
        @elseif ($tab === 'notes_activity')
            <x-card>
                <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('company.applicant_profile.tabs.notes_activity') }}</h2></x-slot:header>
                <p class="text-sm leading-6 text-hb-muted">{{ __('company.applicant_profile.notes_unavailable') }}</p>
            </x-card>
        @else
            <x-card>
                <x-slot:header>
                    <h2 class="text-lg font-bold text-hb-navy">{{ __('company.applicant_profile.tabs.contact_consent') }}</h2>
                </x-slot:header>

                <p class="text-sm leading-6 text-hb-muted">
                    {{ __('company.applicant_profile.contact_consent_unavailable') }}
                </p>
            </x-card>
        @endif
    @endif
</div>
@endsection
