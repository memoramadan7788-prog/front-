@extends('frontend.layouts.app')

@php
    $mode = $mode ?? 'error';
    $context = is_array($context ?? null) ? $context : null;
    $candidateLabel = $context['candidate_name'] ?? __('interviewer.live.candidate_unknown');
    $pageTitle = __('interviewer.live.page_title', ['name' => $candidateLabel]);
@endphp

@section('title', $pageTitle)

@section('page_header')
    <div>
        <p class="text-xs font-medium uppercase tracking-wide text-hb-muted">{{ __('interviewer.live.breadcrumb') }}</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-hb-navy">{{ __('interviewer.live.title') }}</h1>
        <p class="mt-1 text-sm text-hb-muted">{{ __('interviewer.live.subtitle') }}</p>
    </div>
@endsection

@section('content')
<div class="mx-auto max-w-6xl space-y-5" data-live-workspace data-mode="{{ $mode }}" data-interview-id="{{ $interviewId }}">
    @if ($errors->any())
        <div class="rounded-xl border border-hb-error/30 bg-hb-error/5 px-4 py-3 text-sm text-hb-navy" role="alert">
            <ul class="list-disc ps-5">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($mode !== 'workspace')
        <div class="hb-surface rounded-2xl p-5 sm:p-6" @if ($mode === 'forbidden') data-unauthorized="1" @endif data-live-empty>
            <p class="text-sm text-hb-navy">{{ $emptyMessage }}</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <x-button variant="light" :href="route('frontend.interviewer.interviews.index')">{{ __('interviewer.live.back_list') }}</x-button>
            </div>
        </div>
    @else
        {{-- Header / context --}}
        <section class="hb-surface rounded-2xl p-5 sm:p-6" aria-labelledby="live-context-heading" data-live-header>
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 id="live-context-heading" class="text-lg font-semibold text-hb-navy">{{ $candidateLabel }}</h2>
                    @if (! empty($context['candidate_headline']))
                        <p class="mt-1 text-sm text-hb-muted">{{ $context['candidate_headline'] }}</p>
                    @endif
                    <p class="mt-2 text-xs text-hb-muted">{{ __('interviewer.live.pii_note') }}</p>
                </div>
                <x-badge :variant="\App\Support\Rows::badgeVariant($context['status'] ?? null)" :dot="true">{{ $context['status'] ?? '—' }}</x-badge>
            </div>

            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <dt class="text-hb-muted">{{ __('interviewer.live.label_job') }}</dt>
                    <dd class="font-medium text-hb-navy">
                        @if (! empty($context['job_title_available']))
                            {{ $context['job_title'] }}
                        @else
                            <span data-job-unavailable>{{ __('interviewer.live.job_unavailable') }}</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-hb-muted">{{ __('interviewer.live.label_type') }}</dt>
                    <dd class="font-medium text-hb-navy">{{ $context['interview_type'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-hb-muted">{{ __('interviewer.live.label_when') }}</dt>
                    <dd class="font-medium text-hb-navy">{{ $context['when_label'] ?? '—' }}</dd>
                    <p class="mt-0.5 text-xs text-hb-muted">{{ $context['timezone_note'] ?? '' }}</p>
                </div>
                <div>
                    <dt class="text-hb-muted">{{ __('interviewer.live.label_interviewer') }}</dt>
                    <dd class="font-medium text-hb-navy">{{ $context['interviewer_name'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-hb-muted">{{ __('interviewer.live.label_round') }}</dt>
                    <dd class="font-medium text-hb-navy">{{ $context['round_no'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-hb-muted">{{ __('interviewer.live.label_interview_id') }}</dt>
                    <dd class="font-medium text-hb-navy">#{{ $context['id'] ?? $interviewId }}</dd>
                </div>
            </dl>

            <div class="mt-4 flex flex-wrap gap-2">
                @if (! empty($context['meeting_link']))
                    <x-button variant="light" size="sm" :href="$context['meeting_link']" target="_blank" rel="noopener noreferrer">{{ __('interviewer.live.open_meeting') }}</x-button>
                @else
                    <p class="text-xs text-hb-muted" data-meeting-unavailable>{{ __('interviewer.live.meeting_unavailable') }}</p>
                @endif
                <x-button variant="light" size="sm" :href="route('frontend.interviewer.interviews.show', ['interviewId' => $interviewId])">{{ __('interviewer.live.back_details') }}</x-button>
                <x-button variant="primary" size="sm" :href="route('frontend.interviewer.interviews.evaluation', ['interviewId' => $interviewId])">{{ __('interviewer.live.continue_scorecard') }}</x-button>
            </div>
        </section>

        <div class="grid gap-5 lg:grid-cols-2">
            {{-- Timer (local-only) --}}
            <section class="hb-surface rounded-2xl p-5 sm:p-6" aria-labelledby="timer-heading" data-live-timer data-timer-mode="local">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 id="timer-heading" class="text-base font-semibold text-hb-navy">{{ __('interviewer.live.timer_heading') }}</h2>
                    <x-badge variant="pending">{{ __('interviewer.live.local_only_badge') }}</x-badge>
                </div>
                <p class="mt-1 text-xs text-hb-muted">{{ __('interviewer.live.timer_footnote') }}</p>
                <p class="mt-4 font-mono text-4xl font-semibold tracking-tight text-hb-navy" aria-live="polite" aria-atomic="true" data-timer-display>00:00:00</p>
                <p class="mt-2 text-xs text-hb-muted" data-timer-status>{{ __('interviewer.live.timer_idle') }}</p>
                <div class="mt-4 flex flex-wrap gap-2" role="group" aria-label="{{ __('interviewer.live.timer_aria') }}">
                    <x-button type="button" size="sm" variant="primary" data-timer-start>{{ __('interviewer.live.timer_start') }}</x-button>
                    <x-button type="button" size="sm" variant="light" data-timer-pause hidden>{{ __('interviewer.live.timer_pause') }}</x-button>
                    <x-button type="button" size="sm" variant="light" data-timer-resume hidden>{{ __('interviewer.live.timer_resume') }}</x-button>
                    <x-button type="button" size="sm" variant="light" data-timer-reset>{{ __('interviewer.live.timer_reset') }}</x-button>
                </div>
            </section>

            {{-- Agenda (local guide) --}}
            <section class="hb-surface rounded-2xl p-5 sm:p-6" aria-labelledby="agenda-heading" data-live-agenda data-agenda-mode="local">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 id="agenda-heading" class="text-base font-semibold text-hb-navy">{{ __('interviewer.live.agenda_heading') }}</h2>
                    <x-badge variant="pending">{{ __('interviewer.live.local_only_badge') }}</x-badge>
                </div>
                <p class="mt-1 text-xs text-hb-muted">{{ __('interviewer.live.agenda_local_note') }}</p>
                <p class="mt-2 text-xs text-hb-muted" data-agenda-progress>{{ __('interviewer.live.agenda_progress', ['done' => 0, 'total' => count($agendaItems)]) }}</p>
                <ul class="mt-3 space-y-2">
                    @foreach ($agendaItems as $item)
                        <li>
                            <label class="flex cursor-pointer items-start gap-2 text-sm text-hb-navy">
                                <input type="checkbox" class="mt-1 rounded border-hb-border" data-agenda-check value="{{ $item['key'] }}">
                                <span>{{ $item['label'] }}</span>
                            </label>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>

        {{-- Question prompts (local — not Backend bank) --}}
        <section class="hb-surface rounded-2xl p-5 sm:p-6" aria-labelledby="questions-heading" data-live-questions data-questions-mode="local">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 id="questions-heading" class="text-base font-semibold text-hb-navy">{{ __('interviewer.live.questions_heading') }}</h2>
                <x-badge variant="pending">{{ __('interviewer.live.local_only_badge') }}</x-badge>
            </div>
            <p class="mt-1 text-sm text-hb-muted">{{ __('interviewer.live.questions_unavailable_api') }}</p>
            <p class="mt-1 text-xs text-hb-muted">{{ __('interviewer.live.questions_intro') }}</p>
            <ul class="mt-4 space-y-3" data-question-list>
                @foreach ($questionPrompts as $q)
                    <li class="rounded-xl border border-hb-border px-3 py-2" data-question-item="{{ $q['key'] }}">
                        <label class="flex cursor-pointer items-start gap-2 text-sm text-hb-navy">
                            <input type="checkbox" class="mt-1 rounded border-hb-border" data-question-check value="{{ $q['key'] }}">
                            <span>{{ $q['label'] }}</span>
                        </label>
                    </li>
                @endforeach
            </ul>
            <p class="mt-3 text-xs text-hb-muted">{{ __('interviewer.live.questions_not_persisted') }}</p>
        </section>

        {{-- Notes (page-memory only — no Backend draft, no silent localStorage) --}}
        <section class="hb-surface rounded-2xl p-5 sm:p-6" aria-labelledby="notes-heading" data-live-notes data-notes-mode="local">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 id="notes-heading" class="text-base font-semibold text-hb-navy">{{ __('interviewer.live.notes_heading') }}</h2>
                <x-badge variant="pending">{{ __('interviewer.live.local_only_badge') }}</x-badge>
            </div>
            <p class="mt-1 text-sm text-hb-muted">{{ __('interviewer.live.notes_no_draft_api') }}</p>

            <div class="mt-3 rounded-xl border border-hb-pending/40 bg-hb-pending-soft/40 px-3 py-2 text-xs text-hb-navy" data-draft-blocked role="status">
                {{ __('interviewer.live.save_draft_blocked') }}
            </div>

            <label class="hb-label mt-4" for="live-notes">{{ __('interviewer.live.notes_field') }}</label>
            <textarea
                id="live-notes"
                class="hb-input min-h-[10rem]"
                rows="8"
                maxlength="10000"
                data-notes-input
                placeholder="{{ __('interviewer.live.notes_placeholder') }}"
                autocomplete="off"
            ></textarea>
            <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-xs">
                <p class="text-hb-muted" data-notes-status>{{ __('interviewer.live.notes_pristine') }}</p>
                <p class="text-hb-muted" data-notes-count>0 / 10000</p>
            </div>
            <p class="mt-2 text-xs text-hb-muted">{{ __('interviewer.live.notes_footnote') }}</p>

            <div class="mt-4 flex flex-wrap gap-2">
                <button type="button" class="inline-flex cursor-not-allowed items-center rounded-lg border border-hb-border bg-hb-page px-3 py-2 text-sm font-medium text-hb-muted opacity-70" disabled aria-disabled="true" data-save-draft-blocked title="{{ __('interviewer.live.save_draft_blocked') }}">
                    {{ __('interviewer.live.save_draft') }}
                </button>
                <x-button variant="primary" size="sm" :href="route('frontend.interviewer.interviews.evaluation', ['interviewId' => $interviewId])" data-open-scorecard>
                    {{ __('interviewer.live.open_final_scorecard') }}
                </x-button>
            </div>
            <p class="mt-2 text-xs text-hb-muted">{{ __('interviewer.live.scorecard_separate') }}</p>
        </section>
    @endif
</div>

@if ($mode === 'workspace')
@push('scripts')
<script>
(() => {
    const root = document.querySelector('[data-live-workspace][data-mode="workspace"]');
    if (!root) return;

    // —— Local timer (not server session) ——
    const display = root.querySelector('[data-timer-display]');
    const status = root.querySelector('[data-timer-status]');
    const btnStart = root.querySelector('[data-timer-start]');
    const btnPause = root.querySelector('[data-timer-pause]');
    const btnResume = root.querySelector('[data-timer-resume]');
    const btnReset = root.querySelector('[data-timer-reset]');
    let elapsedMs = 0;
    let tickStarted = 0;
    let running = false;
    let raf = null;

    const labels = {
        idle: @json(__('interviewer.live.timer_idle')),
        running: @json(__('interviewer.live.timer_running')),
        paused: @json(__('interviewer.live.timer_paused')),
    };

    function format(ms) {
        const total = Math.floor(ms / 1000);
        const h = String(Math.floor(total / 3600)).padStart(2, '0');
        const m = String(Math.floor((total % 3600) / 60)).padStart(2, '0');
        const s = String(total % 60).padStart(2, '0');
        return `${h}:${m}:${s}`;
    }

    function render() {
        const now = running ? elapsedMs + (Date.now() - tickStarted) : elapsedMs;
        if (display) display.textContent = format(now);
        if (status) status.textContent = running ? labels.running : (elapsedMs > 0 ? labels.paused : labels.idle);
        if (btnStart) btnStart.hidden = running || elapsedMs > 0;
        if (btnPause) btnPause.hidden = !running;
        if (btnResume) btnResume.hidden = running || elapsedMs === 0;
    }

    function loop() {
        render();
        if (running) raf = requestAnimationFrame(loop);
    }

    btnStart?.addEventListener('click', () => {
        if (running) return;
        running = true;
        tickStarted = Date.now();
        cancelAnimationFrame(raf);
        loop();
    });
    btnPause?.addEventListener('click', () => {
        if (!running) return;
        elapsedMs += Date.now() - tickStarted;
        running = false;
        cancelAnimationFrame(raf);
        render();
    });
    btnResume?.addEventListener('click', () => {
        if (running || elapsedMs === 0) return;
        running = true;
        tickStarted = Date.now();
        cancelAnimationFrame(raf);
        loop();
    });
    btnReset?.addEventListener('click', () => {
        running = false;
        elapsedMs = 0;
        cancelAnimationFrame(raf);
        render();
    });
    render();

    // —— Agenda progress (local) ——
    const agendaChecks = root.querySelectorAll('[data-agenda-check]');
    const agendaProgress = root.querySelector('[data-agenda-progress]');
    const agendaTemplate = @json(__('interviewer.live.agenda_progress'));
    function updateAgenda() {
        const total = agendaChecks.length;
        let done = 0;
        agendaChecks.forEach((el) => { if (el.checked) done++; });
        if (agendaProgress) {
            agendaProgress.textContent = agendaTemplate.replace(':done', String(done)).replace(':total', String(total));
        }
    }
    agendaChecks.forEach((el) => el.addEventListener('change', updateAgenda));
    updateAgenda();

    // —— Notes: page memory only (no localStorage; warn on leave) ——
    const notes = root.querySelector('[data-notes-input]');
    const notesStatus = root.querySelector('[data-notes-status]');
    const notesCount = root.querySelector('[data-notes-count]');
    let dirty = false;
    const pristine = @json(__('interviewer.live.notes_pristine'));
    const unsaved = @json(__('interviewer.live.notes_unsaved'));
    const leaveWarn = @json(__('interviewer.live.notes_leave_warn'));

    function updateNotesMeta() {
        const len = notes ? notes.value.length : 0;
        if (notesCount) notesCount.textContent = `${len} / 10000`;
        if (notesStatus) notesStatus.textContent = dirty ? unsaved : pristine;
    }
    notes?.addEventListener('input', () => {
        dirty = notes.value.length > 0;
        updateNotesMeta();
    });
    updateNotesMeta();

    window.addEventListener('beforeunload', (e) => {
        if (!dirty) return;
        e.preventDefault();
        e.returnValue = leaveWarn;
        return leaveWarn;
    });

    // Scorecard links intentionally clear the leave guard only after user confirms intent via navigation
    root.querySelectorAll('[data-open-scorecard]').forEach((el) => {
        el.addEventListener('click', (e) => {
            if (!dirty) return;
            if (!window.confirm(leaveWarn)) {
                e.preventDefault();
            } else {
                dirty = false;
            }
        });
    });
})();
</script>
@endpush
@endif
@endsection
