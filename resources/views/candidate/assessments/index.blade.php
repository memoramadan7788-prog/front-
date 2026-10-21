@extends('layouts.app')

@section('title', $pageTitle ?? 'Assessments · Hirebridge')
@section('breadcrumb', 'Assessments / My workspace / Assessments')

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">Assessments</h1>
    <p class="mt-1 text-sm text-hb-muted">
        Role-linked exams assigned by hiring teams. Frontend preview — no grading or proctoring runs here.
    </p>
@endsection

@section('content')
    <div class="mb-4 rounded-lg border border-hb-blue/20 bg-hb-info-soft px-4 py-3 text-sm text-hb-blue" role="status">
        No dedicated Figma frame for assessments. UI follows Platform workflow stubs
        (<code class="text-xs">intro</code> / <code class="text-xs">take</code> / <code class="text-xs">result</code>)
        and ERD exam/attempt fields. Camera consent is a local acknowledgment only.
    </div>

    <div class="hb-mock-banner mb-6 rounded-lg text-start">
        Frontend UI only — assessment progress is not persisted to Hirebridge.
    </div>

    <div class="space-y-4" data-candidate-assessments>
        @forelse ($assessments as $assessment)
            <article class="hb-surface p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold text-hb-navy">{{ $assessment['title'] }}</h2>
                        <p class="mt-1 text-sm text-hb-muted">
                            {{ $assessment['company'] }} · {{ $assessment['role'] }} · {{ $assessment['code'] }}
                        </p>
                        <p class="mt-2 text-sm text-hb-text">
                            {{ $assessment['questions_count'] }} questions · {{ $assessment['time_limit_min'] }} min ·
                            Pass {{ $assessment['passing_score'] }}% · Attempt limit {{ $assessment['attempt_limit'] }}
                        </p>
                        <p class="mt-1 text-sm text-hb-muted">{{ $assessment['due'] }}</p>
                    </div>
                    <x-badge
                        :variant="$assessment['status_key'] === 'ready' ? 'info' : 'pending'"
                        :dot="true"
                    >{{ $assessment['status'] }}</x-badge>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    @if ($assessment['status_key'] === 'ready')
                        <a href="{{ route('candidate.assessments.show', $assessment['id']) }}" class="hb-btn-primary">
                            Open instructions
                        </a>
                    @else
                        <button type="button" class="hb-btn-primary" disabled title="Opens after screening — mock state">
                            Not available yet
                        </button>
                        <a href="{{ route('candidate.assessments.show', $assessment['id']) }}" class="hb-btn-outline">
                            View details
                        </a>
                    @endif
                    <a href="{{ route('candidate.applications.show', $assessment['application_id']) }}" class="hb-btn-outline">
                        View application
                    </a>
                </div>
            </article>
        @empty
            <div class="hb-surface px-5 py-10 text-center" role="status">
                <p class="font-semibold text-hb-navy">No assessments assigned</p>
                <p class="mt-2 text-sm text-hb-muted">When an employer assigns an exam, it will appear here.</p>
            </div>
        @endforelse
    </div>

    <p class="mt-8 text-xs text-hb-muted">Hirebridge · Private workspace · All times Central unless noted · October 20, 2026</p>
@endsection
