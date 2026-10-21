@extends('layouts.app')

@section('title', $pageTitle ?? __('candidate.assessments.take_page_title'))
@section('breadcrumb', __('candidate.assessments.breadcrumb_in_progress'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ $assessment['title'] }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ $assessment['company'] }} · {{ $assessment['code'] }} · {{ __('candidate.assessments.in_progress') }}</p>
@endsection

@section('page_actions')
    <p class="rounded-lg border border-hb-border bg-hb-surface px-3 py-2 text-sm font-semibold text-hb-navy" aria-live="polite">
        {{ __('candidate.assessments.time_left') }}: <span data-assessment-timer>{{ $assessment['time_limit_min'] }}:00</span>
    </p>
@endsection

@section('content')

    <div
        class="mx-auto max-w-3xl"
        data-assessment-take
        data-assessment-id="{{ $assessment['id'] }}"
        data-time-limit="{{ $assessment['time_limit_min'] }}"
        data-result-url="{{ route('candidate.assessments.result', $assessment['id']) }}"
        data-passing-score="{{ $assessment['passing_score'] }}"
    >
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <p class="text-sm text-hb-muted">
                {{ __('candidate.assessments.question_label') }}
                <span data-question-position>1</span>
                {{ __('candidate.assessments.question_of_total', ['total' => count($assessment['questions'])]) }}
            </p>
            <div class="flex flex-wrap gap-1" role="tablist" aria-label="{{ __('candidate.assessments.question_nav_aria') }}" data-question-nav>
                @foreach ($assessment['questions'] as $index => $question)
                    <button
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-hb-border text-xs font-semibold {{ $index === 0 ? 'border-hb-blue bg-hb-info-soft text-hb-blue' : 'bg-hb-surface text-hb-muted' }}"
                        data-goto-question="{{ $index }}"
                        aria-label="{{ __('candidate.assessments.go_to_question', ['number' => $index + 1]) }}"
                    >{{ $index + 1 }}</button>
                @endforeach
            </div>
        </div>

        @foreach ($assessment['questions'] as $index => $question)
            <section
                class="hb-surface p-5 sm:p-6 {{ $index === 0 ? '' : 'hidden' }}"
                data-question-panel="{{ $index }}"
                @if ($index !== 0) hidden @endif
            >
                <h2 class="text-lg font-semibold text-hb-navy">{{ __('candidate.assessments.question_heading', ['number' => $index + 1]) }}</h2>
                <p class="mt-3 text-sm leading-relaxed text-hb-text">{{ $question['prompt'] }}</p>

                @if (($question['type'] ?? '') === 'short')
                    <label class="hb-label mt-5" for="q-{{ $question['id'] }}">{{ __('candidate.assessments.your_answer') }}</label>
                    <textarea
                        id="q-{{ $question['id'] }}"
                        class="hb-input"
                        rows="4"
                        data-answer-field="{{ $question['id'] }}"
                        placeholder="{{ __('candidate.assessments.short_answer_placeholder') }}"
                    ></textarea>
                @else
                    <fieldset class="mt-5 space-y-2">
                        <legend class="sr-only">{{ __('candidate.assessments.choices_legend', ['number' => $index + 1]) }}</legend>
                        @foreach ($question['choices'] as $choiceIndex => $choice)
                            <label class="flex items-start gap-3 rounded-lg border border-hb-border px-3 py-2.5 text-sm hover:bg-hb-page">
                                <input
                                    type="radio"
                                    class="mt-0.5"
                                    name="answer-{{ $question['id'] }}"
                                    value="{{ $choiceIndex }}"
                                    data-answer-field="{{ $question['id'] }}"
                                    data-correct="{{ ($question['answer'] ?? null) === $choiceIndex ? '1' : '0' }}"
                                >
                                <span>{{ $choice }}</span>
                            </label>
                        @endforeach
                    </fieldset>
                @endif
            </section>
        @endforeach

        <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
            <button type="button" class="hb-btn-outline" data-question-prev disabled>{{ __('common.actions.previous') }}</button>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="hb-btn-outline" data-question-next>{{ __('common.actions.next') }}</button>
                <button type="button" class="hb-btn-primary hidden" data-assessment-submit hidden>{{ __('candidate.assessments.review_submit') }}</button>
            </div>
        </div>
        <p class="mt-3 hidden text-sm text-hb-error" data-assessment-take-error role="alert"></p>
    </div>
@endsection
