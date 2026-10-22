@extends('layouts.app')

@section('title', $pageTitle ?? __('candidate.assessments.page_title'))
@section('breadcrumb', __('candidate.assessments.breadcrumb_index'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.assessments.title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">
        {{ __('candidate.assessments.intro') }}
    </p>
@endsection

@section('content')
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
                            {{ __('candidate.assessments.questions_meta', ['count' => $assessment['questions_count'], 'minutes' => $assessment['time_limit_min'], 'pass' => $assessment['passing_score'], 'attempts' => $assessment['attempt_limit']]) }}
                        </p>
                        <p class="mt-1 text-sm text-hb-muted">{{ $assessment['due'] }}</p>
                    </div>
                    <x-badge
                        :variant="match ($assessment['status_key'] ?? '') {
                            'ready' => 'info',
                            'exhausted', 'flagged' => 'pending',
                            default => 'neutral',
                        }"
                        :dot="true"
                    >{{ $assessment['status'] }}</x-badge>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    @if (($assessment['status_key'] ?? '') === 'ready' && ! empty($assessment['can_start']))
                        <a href="{{ route('candidate.assessments.show', $assessment['id']) }}" class="hb-btn-primary">
                            {{ __('candidate.assessments.open_instructions') }}
                        </a>
                    @elseif (($assessment['status_key'] ?? '') === 'exhausted')
                        <a href="{{ route('candidate.assessments.show', $assessment['id']) }}" class="hb-btn-outline">
                            {{ __('candidate.assessments.view_exhausted') }}
                        </a>
                    @else
                        <button type="button" class="hb-btn-primary" disabled title="{{ $assessment['due'] ?? __('candidate.assessments.available_after_screening') }}">
                            {{ __('candidate.assessments.not_available_yet') }}
                        </button>
                        <a href="{{ route('candidate.assessments.show', $assessment['id']) }}" class="hb-btn-outline">
                            {{ __('candidate.assessments.view_details') }}
                        </a>
                    @endif
                    <a href="{{ route('candidate.applications.show', $assessment['application_id']) }}" class="hb-btn-outline">
                        {{ __('candidate.assessments.view_application') }}
                    </a>
                </div>
                @if (! empty($assessment['attempts_remaining']) || isset($assessment['attempt_no']))
                    <p class="mt-3 text-xs text-hb-muted" dir="ltr">
                        {{ __('candidate.assessments.attempt_summary', [
                            'current' => $assessment['attempt_no'] ?? 0,
                            'limit' => $assessment['attempt_limit'] ?? 1,
                            'remaining' => $assessment['attempts_remaining'] ?? 0,
                        ]) }}
                    </p>
                @endif
            </article>
        @empty
            <div class="hb-surface" role="status">
                <x-empty-state :title="__('candidate.assessments.empty_title')">
                    {{ __('candidate.assessments.empty_body') }}
                </x-empty-state>
            </div>
        @endforelse
    </div>

    <p class="mt-8 text-xs text-hb-muted">{{ __('common.footer.workspace_note', ['date' => __('candidate.footer_date')]) }}</p>
@endsection
