@extends('layouts.app')

@section('title', $pageTitle ?? 'Assessment · Hirebridge')
@section('breadcrumb', 'Assessments / My workspace / Instructions')

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ $assessment['title'] }}</h1>
    <p class="mt-1 text-sm text-hb-muted">
        {{ $assessment['company'] }} · {{ $assessment['code'] }} · Attempt {{ max(1, $assessment['attempt_no']) }} of {{ $assessment['attempt_limit'] }}
    </p>
@endsection

@section('page_actions')
    <a href="{{ route('candidate.assessments.index') }}" class="hb-btn-outline">All assessments</a>
@endsection

@section('content')
    <div class="hb-mock-banner mb-6 rounded-lg text-start">
        Instructions preview — starting does not create a real exam attempt.
    </div>

    <div class="mx-auto max-w-2xl space-y-6" data-assessment-intro data-assessment-id="{{ $assessment['id'] }}">
        <section class="hb-surface p-5 sm:p-6">
            <h2 class="text-lg font-semibold text-hb-navy">Before you begin</h2>
            <ul class="mt-4 list-disc space-y-2 ps-5 text-sm text-hb-text">
                @foreach ($assessment['instructions'] as $line)
                    <li>{{ $line }}</li>
                @endforeach
            </ul>
            <dl class="mt-5 grid gap-3 sm:grid-cols-3 text-sm">
                <div class="rounded-lg border border-hb-border bg-hb-page px-3 py-3">
                    <dt class="text-hb-muted">Time limit</dt>
                    <dd class="mt-1 font-semibold text-hb-navy">{{ $assessment['time_limit_min'] }} minutes</dd>
                </div>
                <div class="rounded-lg border border-hb-border bg-hb-page px-3 py-3">
                    <dt class="text-hb-muted">Questions</dt>
                    <dd class="mt-1 font-semibold text-hb-navy">{{ $assessment['questions_count'] }}</dd>
                </div>
                <div class="rounded-lg border border-hb-border bg-hb-page px-3 py-3">
                    <dt class="text-hb-muted">Passing score</dt>
                    <dd class="mt-1 font-semibold text-hb-navy">{{ $assessment['passing_score'] }}%</dd>
                </div>
            </dl>
        </section>

        <section class="hb-surface p-5 sm:p-6">
            <h2 class="text-lg font-semibold text-hb-navy">Workspace acknowledgment</h2>
            <label class="mt-4 flex items-start gap-3 text-sm text-hb-text">
                <input type="checkbox" class="hb-checkbox mt-0.5" data-assessment-consent required>
                <span>
                    I understand this is a frontend demonstration. No webcam stream is started, and answers are not submitted to a server.
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
                        Start assessment preview
                    </a>
                @else
                    <button type="button" class="hb-btn-primary" disabled>
                        Start unavailable ({{ $assessment['status'] }})
                    </button>
                @endif
                <a href="{{ route('candidate.assessments.index') }}" class="hb-btn-outline">Cancel</a>
            </div>
        </section>
    </div>
@endsection
