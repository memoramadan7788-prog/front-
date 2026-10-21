@extends('layouts.app')

@section('title', $pageTitle ?? 'Results preview · Hirebridge')
@section('breadcrumb', 'Assessments / My workspace / Results')

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">Results preview</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ $assessment['title'] }} · {{ $assessment['company'] }} · {{ $assessment['code'] }}</p>
@endsection

@section('content')
    <div class="hb-mock-banner mb-6 rounded-lg text-start">
        Demonstration score only — no attempt was graded or stored by Hirebridge.
    </div>

    <div class="mx-auto max-w-2xl space-y-6" data-assessment-result data-passing="{{ $assessment['passing_score'] }}">
        <section class="hb-surface p-5 sm:p-6 text-center">
            <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">Estimated score</p>
            <p class="mt-2 text-4xl font-semibold text-hb-navy" data-result-score>—</p>
            <p class="mt-2 text-sm text-hb-muted">
                Passing threshold {{ $assessment['passing_score'] }}% ·
                <span data-result-outcome>Awaiting local calculation</span>
            </p>
        </section>

        <section class="hb-surface p-5 sm:p-6">
            <h2 class="text-lg font-semibold text-hb-navy">What this preview means</h2>
            <ul class="mt-3 list-disc space-y-2 ps-5 text-sm text-hb-muted">
                <li>Multiple-choice items are scored locally against mock answer keys.</li>
                <li>Short-answer items are marked as reviewed, not auto-graded.</li>
                <li>Employers do not receive this preview. Backend Team owns real attempts.</li>
            </ul>
            <div class="mt-5 flex flex-wrap gap-2">
                <a href="{{ route('candidate.assessments.index') }}" class="hb-btn-primary">Back to assessments</a>
                <a href="{{ route('candidate.applications.show', $assessment['application_id']) }}" class="hb-btn-outline">
                    View application
                </a>
            </div>
        </section>
    </div>
@endsection
