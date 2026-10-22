@extends('layouts.app')

@section('title', $pageTitle ?? __('company.assessments.show_page_title', ['title' => $assessment['title']]))
@section('breadcrumb', __('company.assessments.show_breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ $assessment['title'] }}</h1>
    <p class="mt-1 text-sm text-hb-muted">
        {{ __('company.assessments.meta_line', [
            'code' => $assessment['job_code'],
            'job' => $assessment['job_title'],
            'questions' => $assessment['questions_count'],
            'minutes' => $assessment['time_limit_min'],
            'pass' => $assessment['passing_score'],
        ]) }}
    </p>
@endsection

@section('page_actions')
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('company.assessments.results', $assessment['id']) }}" class="hb-btn-outline">{{ __('company.assessments.view_results') }}</a>
        <a href="{{ route('company.assessments.index') }}" class="hb-btn-outline">{{ __('company.assessments.back') }}</a>
    </div>
@endsection

@section('content')
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_280px]">
        <div class="space-y-6">
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="config-heading">
                <h2 id="config-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.assessments.details_heading') }}</h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2 text-sm">
                    <div>
                        <dt class="hb-label">{{ __('company.assessments.owner') }}</dt>
                        <dd class="mt-1 text-hb-text">{{ $assessment['owner'] }}</dd>
                    </div>
                    <div>
                        <dt class="hb-label">{{ __('company.assessments.updated') }}</dt>
                        <dd class="mt-1 text-hb-text">{{ $assessment['updated'] }}</dd>
                    </div>
                    <div>
                        <dt class="hb-label">{{ __('company.assessments.time_limit') }}</dt>
                        <dd class="mt-1 text-hb-text" dir="ltr">{{ $assessment['time_limit_min'] }}</dd>
                    </div>
                    <div>
                        <dt class="hb-label">{{ __('company.assessments.passing_score') }}</dt>
                        <dd class="mt-1 text-hb-text" dir="ltr">{{ $assessment['passing_score'] }}%</dd>
                    </div>
                </dl>
            </section>

            <section class="hb-surface p-5 sm:p-6" aria-labelledby="questions-heading">
                <h2 id="questions-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.assessments.questions_heading') }}</h2>
                <ol class="mt-4 list-decimal space-y-4 ps-5 text-sm text-hb-text">
                    @foreach ($assessment['questions'] as $question)
                        <li>
                            <p class="font-medium text-hb-navy">{{ $question['prompt'] }}</p>
                            <p class="mt-1 text-hb-muted">
                                {{ ($question['type'] ?? 'mc') === 'short' ? __('company.assessments.type_short') : __('company.assessments.type_mc') }}
                            </p>
                        </li>
                    @endforeach
                </ol>
            </section>

            <section class="hb-surface overflow-hidden" aria-labelledby="assignees-heading">
                <div class="border-b border-hb-border px-5 py-4">
                    <h2 id="assignees-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.assessments.assignees_heading') }}</h2>
                </div>
                @if (count($assessment['assignees']) === 0)
                    <p class="px-5 py-8 text-center text-sm text-hb-muted">{{ __('company.assessments.no_assignees') }}</p>
                @else
                    <ul class="divide-y divide-hb-border">
                        @foreach ($assessment['assignees'] as $assignee)
                            <li class="flex flex-wrap items-center justify-between gap-2 px-5 py-3 text-sm">
                                @if (! empty($assignee['id']))
                                    <a href="{{ route('company.applicants.show', $assignee['id']) }}" class="font-semibold text-hb-blue hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40">
                                        {{ $assignee['name'] }}
                                    </a>
                                @else
                                    <span class="font-semibold text-hb-navy">{{ $assignee['name'] }}</span>
                                @endif
                                <span class="text-hb-muted">
                                    {{ $assignee['status'] }}
                                    @if ($assignee['score'] !== null)
                                        · {{ __('company.assessments.score') }}: <span dir="ltr">{{ $assignee['score'] }}%</span>
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            @if (! empty($assessment['proctoring_queue']))
                <section class="hb-surface p-5 sm:p-6" aria-labelledby="proctoring-queue-heading" data-proctoring-review-queue>
                    <h2 id="proctoring-queue-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.assessments.proctoring_queue_heading') }}</h2>
                    <p class="mt-1 text-sm text-hb-muted">{{ __('company.assessments.proctoring_queue_intro') }}</p>
                    <ul class="mt-4 space-y-3">
                        @foreach ($assessment['proctoring_queue'] as $flag)
                            <li class="rounded-lg border border-hb-border bg-hb-page px-4 py-3 text-sm" data-proctoring-flag="{{ $flag['id'] }}">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div>
                                        <p class="font-semibold text-hb-navy">{{ $flag['candidate'] }}</p>
                                        <p class="mt-0.5 text-hb-text">{{ $flag['label'] }}</p>
                                        <p class="mt-1 text-xs text-hb-muted">{{ $flag['recorded_at'] }} · {{ $flag['note'] }}</p>
                                    </div>
                                    <x-badge variant="pending" :dot="true">{{ __('company.assessments.proctoring_pending') }}</x-badge>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <a href="{{ route('company.applicants.show', $flag['candidate_id']) }}" class="text-sm font-semibold text-hb-blue hover:underline">
                                        {{ __('company.assessments.open_applicant') }}
                                    </a>
                                    <button
                                        type="button"
                                        class="text-sm font-semibold text-hb-blue hover:underline"
                                        data-proctoring-review-preview
                                        data-flag-id="{{ $flag['id'] }}"
                                    >
                                        {{ __('company.assessments.proctoring_review_preview') }}
                                    </button>
                                </div>
                                <p class="mt-2 hidden text-xs text-hb-muted" data-proctoring-review-status role="status"></p>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        <aside class="hb-surface h-fit p-5">
            <x-badge :variant="match ($assessment['status_key'] ?? '') {
                'active' => 'success',
                'pending_approval' => 'pending',
                default => 'neutral',
            }" :dot="true">
                {{ $assessment['status'] }}
            </x-badge>
            @if (($assessment['approval_status'] ?? '') === 'pending_review')
                <p class="mt-3 text-sm text-hb-muted" data-ai-question-approval>
                    {{ $assessment['approval_note'] ?? __('company.assessments.ai_approval_required') }}
                </p>
            @endif
            <p class="mt-4 text-sm text-hb-muted">
                {{ __('company.assessments.assigned', ['count' => $assessment['assigned_count']]) }} ·
                {{ __('company.assessments.completed', ['count' => $assessment['completed_count']]) }}
            </p>
            <a href="{{ route('company.jobs.show', $assessment['job_id']) }}" class="mt-4 inline-block text-sm font-semibold text-hb-blue hover:underline">
                {{ $assessment['job_code'] }} · {{ $assessment['job_title'] }}
            </a>
        </aside>
    </div>

    <p class="mt-8 text-center text-xs text-hb-muted">{{ $catalog['footer'] ?? 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026' }}</p>
@endsection

@push('scripts')
    <script>
        (() => {
            document.querySelectorAll('[data-proctoring-review-preview]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const row = btn.closest('[data-proctoring-flag]');
                    const status = row?.querySelector('[data-proctoring-review-status]');
                    if (status) {
                        status.textContent = @json(__('company.assessments.proctoring_preview_only'));
                        status.classList.remove('hidden');
                    }
                });
            });
        })();
    </script>
@endpush
