@extends('layouts.app')

@section('title', $pageTitle ?? __('candidate.applications.confirmation_page_title'))
@section('breadcrumb', __('candidate.workspace') . ' / ' . __('navigation.candidate.applications'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.applications.confirmation_title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">
        {{ __('candidate.applications.submitted_to', ['company' => $preview['company'], 'when' => $preview['submitted_at']]) }}
    </p>
@endsection

@section('content')

    <x-alert variant="info" class="mb-4">
        {{ $preview['journey_banner'] }}
    </x-alert>

    <x-alert variant="success" class="mb-6">
        {{ __('candidate.applications.confirmation_sent', ['email' => $preview['email']]) }}
        {{ __('candidate.applications.application_reference', ['reference' => $preview['reference']]) }}
    </x-alert>

    <section class="hb-surface p-5 sm:p-6" aria-labelledby="submitted-summary-heading">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 id="submitted-summary-heading" class="text-lg font-semibold text-hb-navy">
                    {{ $preview['title'] }} · {{ $preview['code'] }}
                </h2>
                <p class="mt-1 text-sm text-hb-muted">{{ $preview['company'] }} · {{ $preview['discipline'] }}</p>
            </div>
            <x-badge variant="info" :dot="true">{{ __('candidate.applications.status_applied') }}</x-badge>
        </div>

        <div class="mt-5">
            <x-timeline :steps="$preview['pipeline']" />
        </div>

        <dl class="mt-5 space-y-3 rounded-lg border border-hb-border bg-hb-page px-4 py-4 text-sm">
            <div class="flex flex-wrap justify-between gap-2">
                <dt class="font-medium text-hb-muted">{{ __('candidate.applications.review_profile') }}</dt>
                <dd class="text-end text-hb-navy">{{ $preview['summary']['profile'] }}</dd>
            </div>
            <div class="flex flex-wrap justify-between gap-2">
                <dt class="font-medium text-hb-muted">{{ __('candidate.applications.review_resume') }}</dt>
                <dd class="text-end text-hb-navy">{{ $preview['summary']['resume'] }}</dd>
            </div>
            <div class="flex flex-wrap justify-between gap-2">
                <dt class="font-medium text-hb-muted">{{ __('candidate.applications.review_portfolio') }}</dt>
                <dd class="text-end text-hb-navy">{{ $preview['summary']['portfolio'] }}</dd>
            </div>
            <div class="flex flex-wrap justify-between gap-2">
                <dt class="font-medium text-hb-muted">{{ __('candidate.applications.screening_answers') }}</dt>
                <dd class="text-end text-hb-navy">{{ $preview['summary']['screening'] }}</dd>
            </div>
            <div class="flex flex-wrap justify-between gap-2">
                <dt class="font-medium text-hb-muted">{{ __('candidate.applications.consent') }}</dt>
                <dd class="text-end text-hb-navy">{{ $preview['summary']['consent'] }}</dd>
            </div>
        </dl>
    </section>

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        <section class="hb-surface p-5 sm:p-6" aria-labelledby="next-heading">
            <h2 id="next-heading" class="text-base font-semibold text-hb-navy">{{ __('candidate.applications.what_next') }}</h2>
            <ol class="mt-4 space-y-4">
                @foreach ($preview['next_steps'] as $step)
                    <li class="flex gap-3">
                        <span class="text-sm font-semibold text-hb-blue">{{ $step['number'] }}</span>
                        <div>
                            <p class="text-sm font-semibold text-hb-navy">{{ $step['title'] }}</p>
                            <p class="mt-1 text-sm text-hb-muted">{{ $step['body'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
            @if (! empty($preview['application_id']))
                <a href="{{ route('candidate.applications.show', $preview['application_id']) }}" class="hb-btn-primary mt-5 inline-flex">
                    {{ __('candidate.applications.track') }}
                </a>
            @else
                <a href="{{ route('candidate.applications.index') }}" class="hb-btn-primary mt-5 inline-flex">
                    {{ __('candidate.applications.go_to_applications') }}
                </a>
            @endif
        </section>

        <section class="hb-surface p-5 sm:p-6" aria-labelledby="momentum-heading">
            <h2 id="momentum-heading" class="text-base font-semibold text-hb-navy">{{ __('candidate.applications.momentum') }}</h2>
            <p class="mt-2 text-sm leading-relaxed text-hb-muted">
                {{ __('candidate.applications.momentum_body') }}
            </p>
            <a href="{{ route('candidate.jobs.index') }}" class="hb-btn-outline mt-5 inline-flex text-hb-blue">
                {{ __('candidate.applications.browse_jobs') }}
            </a>
            <p class="mt-4 text-xs text-hb-muted">
                {{ __('candidate.applications.correct_materials') }}
            </p>
        </section>
    </div>

    <p class="mt-8 text-xs text-hb-muted">{{ $preview['footer'] }}</p>
@endsection
