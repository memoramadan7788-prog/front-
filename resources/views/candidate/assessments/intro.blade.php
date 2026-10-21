@extends('layouts.app')

@section('title', $pageTitle ?? __('candidate.assessments.intro_page_title'))
@section('breadcrumb', __('candidate.assessments.breadcrumb_instructions'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ $assessment['title'] }}</h1>
    <p class="mt-1 text-sm text-hb-muted">
        {{ __('candidate.assessments.attempt_of', ['company' => $assessment['company'], 'code' => $assessment['code'], 'current' => max(1, $assessment['attempt_no']), 'limit' => $assessment['attempt_limit']]) }}
    </p>
@endsection

@section('page_actions')
    <a href="{{ route('candidate.assessments.index') }}" class="hb-btn-outline">{{ __('candidate.assessments.all_assessments') }}</a>
@endsection

@section('content')

    <div class="mx-auto max-w-2xl space-y-6" data-assessment-intro data-assessment-id="{{ $assessment['id'] }}">
        <section class="hb-surface p-5 sm:p-6">
            <h2 class="text-lg font-semibold text-hb-navy">{{ __('candidate.assessments.before_begin') }}</h2>
            <ul class="mt-4 list-disc space-y-2 ps-5 text-sm text-hb-text">
                @foreach ($assessment['instructions'] as $line)
                    <li>{{ $line }}</li>
                @endforeach
            </ul>
            <dl class="mt-5 grid gap-3 sm:grid-cols-3 text-sm">
                <div class="rounded-lg border border-hb-border bg-hb-page px-3 py-3">
                    <dt class="text-hb-muted">{{ __('candidate.assessments.time_limit') }}</dt>
                    <dd class="mt-1 font-semibold text-hb-navy">{{ __('candidate.assessments.minutes', ['count' => $assessment['time_limit_min']]) }}</dd>
                </div>
                <div class="rounded-lg border border-hb-border bg-hb-page px-3 py-3">
                    <dt class="text-hb-muted">{{ __('candidate.assessments.questions') }}</dt>
                    <dd class="mt-1 font-semibold text-hb-navy">{{ $assessment['questions_count'] }}</dd>
                </div>
                <div class="rounded-lg border border-hb-border bg-hb-page px-3 py-3">
                    <dt class="text-hb-muted">{{ __('candidate.assessments.passing_score') }}</dt>
                    <dd class="mt-1 font-semibold text-hb-navy">{{ $assessment['passing_score'] }}%</dd>
                </div>
            </dl>
        </section>

        <section class="hb-surface p-5 sm:p-6">
            <h2 class="text-lg font-semibold text-hb-navy">{{ __('candidate.assessments.acknowledgment_heading') }}</h2>
            <label class="mt-4 flex items-start gap-3 text-sm text-hb-text">
                <input type="checkbox" class="hb-checkbox mt-0.5" data-assessment-consent required>
                <span>
                    {{ __('candidate.assessments.acknowledge') }}
                </span>
            </label>
            <p class="hb-field-error hidden" data-consent-error role="alert"></p>

            <div class="mt-5 flex flex-wrap gap-2">
                @if (($assessment['status_key'] ?? '') === 'ready')
                    <a
                        href="{{ route('candidate.assessments.take', $assessment['id']) }}"
                        class="hb-btn-primary"
                        data-assessment-start
                    >
                        {{ __('candidate.assessments.start') }}
                    </a>
                @else
                    <button type="button" class="hb-btn-primary" disabled>
                        {{ __('candidate.assessments.start_unavailable', ['status' => $assessment['status']]) }}
                    </button>
                @endif
                <a href="{{ route('candidate.assessments.index') }}" class="hb-btn-outline">{{ __('common.actions.cancel') }}</a>
            </div>
        </section>
    </div>
@endsection
