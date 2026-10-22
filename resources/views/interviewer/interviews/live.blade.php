@extends('layouts.app')

@section('title', $pageTitle ?? __('interviewer.live.page_title', ['name' => $live['interview']['applicant']]))
@section('breadcrumb', __('interviewer.live.breadcrumb'))

@section('page_header')
    <div class="flex flex-wrap items-center gap-2">
        <x-badge variant="info" :dot="true">{{ __('interviewer.live.status_in_progress') }}</x-badge>
        <span class="text-xs text-hb-muted" data-live-timer-label aria-live="polite"></span>
    </div>
    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ $live['title'] }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ $live['meta'] }}</p>
@endsection

@section('page_actions')
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ $live['prepare_url'] }}" class="hb-btn-outline">
            {{ __('interviewer.live.back_prepare') }}
        </a>
        <button
            type="button"
            class="hb-btn-outline"
            disabled
            title="{{ __('interviewer.live.meeting_soon') }}"
            aria-disabled="true"
        >
            {{ __('interviewer.live.open_meeting') }}
        </button>
        <a href="{{ $live['scorecard_url'] }}" class="hb-btn-primary">
            {{ __('interviewer.live.continue_scorecard') }}
        </a>
    </div>
@endsection

@section('content')
    @php
        $interview = $live['interview'];
        $storagePrefix = $live['storage_prefix'];
    @endphp

    <div
        data-interviewer-live
        data-interview-id="{{ $interview['id'] }}"
        data-storage-prefix="{{ $storagePrefix }}"
        data-seed-notes="{{ json_encode($live['seed_notes'] ?? [], JSON_UNESCAPED_UNICODE) }}"
        data-i18n="{{ json_encode([
            'timer_idle' => __('interviewer.live.timer_idle'),
            'timer_running' => __('interviewer.live.timer_running'),
            'timer_paused' => __('interviewer.live.timer_paused'),
            'notes_saved' => __('interviewer.live.notes_saved'),
            'notes_unsaved' => __('interviewer.live.notes_unsaved'),
            'agenda_progress' => __('interviewer.live.agenda_progress'),
            'complete_confirm_title' => __('interviewer.live.complete_confirm_title'),
            'complete_confirm_body' => __('interviewer.live.complete_confirm_body'),
            'start' => __('interviewer.live.timer_start'),
            'pause' => __('interviewer.live.timer_pause'),
            'resume' => __('interviewer.live.timer_resume'),
            'reset' => __('interviewer.live.timer_reset'),
            'show_question' => __('interviewer.live.show_question'),
            'hide_question' => __('interviewer.live.hide_question'),
        ], JSON_UNESCAPED_UNICODE) }}"
    >
        <div class="mb-6 flex flex-wrap items-center gap-3 rounded-xl border border-hb-border bg-hb-surface p-4">
            <p class="font-mono text-2xl font-semibold tabular-nums tracking-tight text-hb-navy" data-live-timer aria-label="{{ __('interviewer.live.timer_aria') }}">00:00</p>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="hb-btn-primary" data-live-timer-action="start">{{ __('interviewer.live.timer_start') }}</button>
                <button type="button" class="hb-btn-outline" data-live-timer-action="pause" disabled>{{ __('interviewer.live.timer_pause') }}</button>
                <button type="button" class="hb-btn-outline" data-live-timer-action="resume" disabled>{{ __('interviewer.live.timer_resume') }}</button>
                <button type="button" class="hb-btn-outline" data-live-timer-action="reset">{{ __('interviewer.live.timer_reset') }}</button>
            </div>
            <p class="w-full text-xs text-hb-muted sm:w-auto">{{ __('interviewer.live.timer_footnote') }}</p>
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">
            <div class="min-w-0 space-y-6">
                <section class="hb-surface p-5 sm:p-6" aria-labelledby="live-agenda-heading">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 id="live-agenda-heading" class="text-lg font-semibold text-hb-navy">{{ __('interviewer.live.agenda_heading') }}</h2>
                        <p class="text-xs font-medium text-hb-blue" data-live-agenda-progress role="status" aria-live="polite"></p>
                    </div>
                    <ul class="mt-4 space-y-2">
                        @foreach ($live['agenda'] as $item)
                            <li>
                                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-hb-border px-3 py-2.5 text-sm hover:bg-hb-page/50 has-[:checked]:border-hb-blue/40 has-[:checked]:bg-hb-info-soft/40">
                                    <input
                                        type="checkbox"
                                        class="mt-0.5 h-4 w-4 rounded border-hb-border text-hb-blue focus-visible:ring-2 focus-visible:ring-hb-blue/40"
                                        data-live-agenda="{{ $item['id'] }}"
                                        @checked(($item['state'] ?? '') === 'done')
                                    >
                                    <span class="min-w-0">
                                        <span class="font-medium tabular-nums text-hb-muted">{{ $item['range'] }}</span>
                                        <span class="ms-2 text-hb-text">{{ $item['label'] }}</span>
                                    </span>
                                </label>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="hb-surface p-5 sm:p-6" aria-labelledby="live-questions-heading">
                    <h2 id="live-questions-heading" class="text-lg font-semibold text-hb-navy">{{ __('interviewer.live.questions_heading') }}</h2>
                    <p class="mt-1 text-sm text-hb-muted">{{ __('interviewer.live.questions_intro') }}</p>
                    <ul class="mt-4 space-y-3">
                        @foreach ($live['question_bank'] as $q)
                            <li class="rounded-xl border border-hb-border p-4">
                                <div class="flex items-start gap-3">
                                    <input
                                        type="checkbox"
                                        class="mt-1 h-4 w-4 shrink-0 rounded border-hb-border text-hb-blue focus-visible:ring-2 focus-visible:ring-hb-blue/40"
                                        data-live-question="{{ $q['id'] }}"
                                        id="live-q-{{ $q['id'] }}"
                                        aria-describedby="live-q-body-{{ $q['id'] }}"
                                    >
                                    <div class="min-w-0 flex-1">
                                        <button
                                            type="button"
                                            class="flex w-full items-start justify-between gap-2 text-start focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40"
                                            data-live-expand="q-{{ $q['id'] }}"
                                            aria-expanded="true"
                                            aria-controls="live-q-body-{{ $q['id'] }}"
                                        >
                                            <label for="live-q-{{ $q['id'] }}" class="cursor-pointer font-semibold text-hb-navy">{{ $q['label'] }}</label>
                                            <span class="text-xs font-medium text-hb-blue" data-expand-label>{{ __('interviewer.live.hide_question') }}</span>
                                        </button>
                                        <div id="live-q-body-{{ $q['id'] }}" class="mt-2 text-sm text-hb-text" data-live-q-panel>
                                            {{ $q['question'] }}
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            </div>

            <aside class="min-w-0">
                <section class="hb-surface p-5 sm:p-6" aria-labelledby="live-notes-heading">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 id="live-notes-heading" class="text-lg font-semibold text-hb-navy">{{ __('interviewer.live.notes_heading') }}</h2>
                        <p class="text-xs text-hb-muted" data-live-notes-status role="status" aria-live="polite">{{ __('interviewer.live.notes_unsaved') }}</p>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2 border-b border-hb-border pb-3" role="tablist" aria-label="{{ __('interviewer.live.notes_tabs_aria') }}">
                        @foreach ($live['note_tabs'] as $index => $tab)
                            <button
                                type="button"
                                role="tab"
                                id="live-note-tab-{{ $tab['id'] }}"
                                data-live-note-tab="{{ $tab['id'] }}"
                                aria-controls="live-note-panel-{{ $tab['id'] }}"
                                aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                                class="rounded-lg px-3 py-1.5 text-sm {{ $index === 0 ? 'bg-hb-info-soft font-semibold text-hb-blue' : 'font-medium text-hb-muted hover:text-hb-navy' }} focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40"
                            >
                                {{ $tab['label'] }}
                            </button>
                        @endforeach
                    </div>

                    @foreach ($live['note_tabs'] as $index => $tab)
                        <div
                            id="live-note-panel-{{ $tab['id'] }}"
                            role="tabpanel"
                            aria-labelledby="live-note-tab-{{ $tab['id'] }}"
                            data-live-note-panel="{{ $tab['id'] }}"
                            class="mt-4 space-y-4 {{ $index === 0 ? '' : 'hidden' }}"
                            @if ($index !== 0) hidden @endif
                        >
                            @foreach ($tab['fields'] as $field)
                                <label class="block">
                                    <span class="mb-1.5 block text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ $field['label'] }}</span>
                                    <textarea
                                        class="hb-input min-h-[5rem] w-full resize-y"
                                        data-live-note-field
                                        data-tab="{{ $tab['id'] }}"
                                        data-field="{{ $field['id'] }}"
                                        rows="3"
                                        dir="auto"
                                        placeholder="{{ $field['placeholder'] }}"
                                    ></textarea>
                                </label>
                            @endforeach
                        </div>
                    @endforeach

                    <div class="mt-5 flex flex-wrap items-center gap-2">
                        <button type="button" class="hb-btn-primary" data-live-save-notes>
                            {{ __('interviewer.live.save_draft') }}
                        </button>
                        <button
                            type="button"
                            class="hb-btn-outline"
                            data-open-modal="live-complete-modal"
                        >
                            {{ __('interviewer.live.complete_review') }}
                        </button>
                    </div>
                    <p class="mt-3 text-xs text-hb-muted">{{ $live['notes_footnote'] }}</p>
                </section>

                <section class="hb-surface mt-6 p-5" aria-labelledby="live-context-heading">
                    <h2 id="live-context-heading" class="text-base font-semibold text-hb-navy">{{ __('interviewer.live.context_heading') }}</h2>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div>
                            <dt class="text-xs text-hb-muted">{{ __('interviewer.prepare.label_interview_id') }}</dt>
                            <dd class="font-medium text-hb-text" dir="ltr">{{ $interview['id'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-hb-muted">{{ __('interviewer.schedule.candidate_role') }}</dt>
                            <dd class="text-hb-text">{{ $interview['applicant'] }} · {{ $interview['job_code'] }}</dd>
                        </div>
                    </dl>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('interviewer.schedule.index') }}" class="hb-btn-outline">
                            {{ __('interviewer.prepare.back_schedule') }}
                        </a>
                        <a href="{{ $live['prepare_url'] }}" class="hb-btn-outline">
                            {{ __('interviewer.live.back_prepare') }}
                        </a>
                    </div>
                </section>
            </aside>
        </div>

        <p class="mt-8 text-center text-xs text-hb-muted">{{ $live['footer'] }}</p>
    </div>

    @include('layouts.modal', [
        'modalId' => 'live-complete-modal',
        'modalTitle' => __('interviewer.live.complete_confirm_title'),
        'modalSize' => 'md',
        'modalBody' => '<p class="text-sm text-hb-text">'.e(__('interviewer.live.complete_confirm_body')).'</p><p class="mt-3 text-sm text-hb-muted">'.e(__('interviewer.live.complete_confirm_note')).'</p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button><a href="'.e($live['scorecard_url']).'" class="hb-btn-primary">'.e(__('interviewer.live.continue_scorecard')).'</a>',
    ])
@endsection
