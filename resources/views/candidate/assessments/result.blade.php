@extends('layouts.app')

@section('title', $pageTitle ?? __('candidate.assessments.result_page_title'))
@section('breadcrumb', __('candidate.assessments.breadcrumb_results'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.assessments.results') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ $assessment['title'] }} · {{ $assessment['company'] }} · {{ $assessment['code'] }}</p>
@endsection

@section('content')

    <div class="mx-auto max-w-2xl space-y-6" data-assessment-result data-passing="{{ $assessment['passing_score'] }}">
        <section class="hb-surface p-5 sm:p-6 text-center">
            <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('candidate.assessments.estimated_score') }}</p>
            <p class="mt-2 text-4xl font-semibold text-hb-navy" data-result-score>—</p>
            <p class="mt-2 text-sm text-hb-muted">
                {{ __('candidate.assessments.passing_threshold', ['score' => $assessment['passing_score']]) }}
                <span data-result-outcome>{{ __('candidate.assessments.awaiting_score') }}</span>
            </p>
        </section>

        <section class="hb-surface p-5 sm:p-6">
            <h2 class="text-lg font-semibold text-hb-navy">{{ __('candidate.assessments.results_mean') }}</h2>
            <ul class="mt-3 list-disc space-y-2 ps-5 text-sm text-hb-muted">
                <li>{{ __('candidate.assessments.results_bullet_mc') }}</li>
                <li>{{ __('candidate.assessments.results_bullet_short') }}</li>
                <li>{{ __('candidate.assessments.results_bullet_review') }}</li>
            </ul>
            <div class="mt-5 flex flex-wrap gap-2">
                <a href="{{ route('candidate.assessments.index') }}" class="hb-btn-primary">{{ __('candidate.assessments.back') }}</a>
                <a href="{{ route('candidate.applications.show', $assessment['application_id']) }}" class="hb-btn-outline">
                    {{ __('candidate.assessments.view_application') }}
                </a>
            </div>
        </section>
    </div>
@endsection
