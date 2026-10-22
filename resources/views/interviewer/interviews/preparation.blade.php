@extends('layouts.app')

@section('title', $pageTitle ?? __('interviewer.prepare.page_title', ['name' => $preparation['interview']['applicant']]))
@section('breadcrumb', __('interviewer.prepare.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ $preparation['title'] }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ $preparation['meta'] }}</p>
@endsection

@section('page_actions')
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('interviewer.schedule.index') }}" class="hb-btn-outline">
            {{ __('interviewer.prepare.back_schedule') }}
        </a>
        <a href="{{ route('interviewer.interviews.live', $preparation['interview']['id']) }}" class="hb-btn-primary">
            {{ __('interviewer.prepare.open_workspace') }}
        </a>
    </div>
@endsection

@section('content')
    @php
        $interview = $preparation['interview'];
        $applicant = $preparation['applicant'] ?? [];
        $storagePrefix = $preparation['storage_prefix'];
    @endphp

    <div
        data-interviewer-prepare
        data-interview-id="{{ $interview['id'] }}"
        data-storage-prefix="{{ $storagePrefix }}"
        data-i18n="{{ json_encode([
            'checklist_saved' => __('interviewer.prepare.checklist_saved'),
            'notes_saved' => __('interviewer.prepare.notes_saved'),
            'notes_empty' => __('interviewer.prepare.notes_empty'),
            'progress' => __('interviewer.prepare.progress_label'),
            'show_background' => __('interviewer.prepare.toggle_background'),
            'hide_background' => __('interviewer.prepare.toggle_background_hide'),
            'show_question' => __('interviewer.prepare.show_question'),
            'hide_question' => __('interviewer.prepare.hide_question'),
        ], JSON_UNESCAPED_UNICODE) }}"
    >
        <nav class="mb-6 flex flex-wrap gap-2 border-b border-hb-border pb-3" aria-label="{{ __('interviewer.prepare.section_nav') }}">
            @foreach ([
                ['brief', __('interviewer.prepare.nav_brief')],
                ['competencies', __('interviewer.prepare.nav_competencies')],
                ['agenda', __('interviewer.prepare.nav_agenda')],
                ['checklist', __('interviewer.prepare.nav_checklist')],
                ['notes', __('interviewer.prepare.nav_notes')],
            ] as [$anchor, $label])
                <a
                    href="#prep-{{ $anchor }}"
                    class="rounded-lg px-3 py-1.5 text-sm font-medium text-hb-muted hover:bg-hb-page hover:text-hb-navy focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40"
                    data-prep-nav="{{ $anchor }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
            <div class="min-w-0 space-y-6">
                <section id="prep-brief" class="hb-surface p-5 sm:p-6" aria-labelledby="prep-brief-heading">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 id="prep-brief-heading" class="text-lg font-semibold text-hb-navy">{{ __('interviewer.prepare.brief_heading') }}</h2>
                            <p class="mt-1 text-sm text-hb-muted">
                                {{ $applicant['name'] ?? $interview['applicant'] }} · {{ $applicant['headline'] ?? $interview['job_title'] }}
                                @if (! empty($applicant['location']))
                                    · {{ $applicant['location'] }}
                                @endif
                            </p>
                        </div>
                        <button
                            type="button"
                            class="text-sm font-semibold text-hb-blue hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40"
                            data-prep-expand="background"
                            aria-expanded="false"
                            aria-controls="prep-background-panel"
                        >
                            {{ __('interviewer.prepare.toggle_background') }}
                        </button>
                    </div>
                    <p class="mt-4 text-sm leading-relaxed text-hb-text">{{ $preparation['brief'] }}</p>
                    <div id="prep-background-panel" class="mt-4 hidden border-t border-hb-border pt-4" hidden>
                        <h3 class="text-sm font-semibold text-hb-navy">{{ __('interviewer.prepare.experience_heading') }}</h3>
                        <ul class="mt-3 space-y-3">
                            @forelse (($applicant['experience'] ?? []) as $role)
                                <li>
                                    <p class="font-semibold text-hb-navy">{{ $role['title'] }} · {{ $role['company'] }}</p>
                                    <p class="mt-0.5 text-xs text-hb-muted">{{ $role['dates'] }}</p>
                                    <p class="mt-1 text-sm text-hb-text">{{ $role['summary'] }}</p>
                                </li>
                            @empty
                                <li class="text-sm text-hb-muted">{{ __('interviewer.prepare.experience_empty') }}</li>
                            @endforelse
                        </ul>
                        <p class="mt-4 text-sm text-hb-muted">{{ $preparation['permitted_context'] }}</p>
                    </div>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <button type="button" class="hb-btn-outline" data-open-modal="prep-resume-modal">
                            {{ __('interviewer.prepare.view_resume') }}
                        </button>
                        @if (! empty($preparation['portfolio_url']))
                            <a
                                href="{{ $preparation['portfolio_url'] }}"
                                class="hb-btn-outline inline-flex items-center gap-1"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                {{ __('interviewer.prepare.open_portfolio') }}
                                <span aria-hidden="true">↗</span>
                            </a>
                        @else
                            <button
                                type="button"
                                class="hb-btn-outline"
                                disabled
                                title="{{ __('interviewer.prepare.portfolio_unavailable') }}"
                                aria-disabled="true"
                            >
                                {{ __('interviewer.prepare.open_portfolio') }}
                            </button>
                        @endif
                    </div>
                </section>

                <section id="prep-competencies" class="hb-surface p-5 sm:p-6" aria-labelledby="prep-comp-heading">
                    <h2 id="prep-comp-heading" class="text-lg font-semibold text-hb-navy">{{ __('interviewer.prepare.competencies_heading') }}</h2>
                    <p class="mt-1 text-sm text-hb-muted">{{ __('interviewer.prepare.competencies_intro') }}</p>
                    <ul class="mt-5 space-y-3">
                        @foreach ($preparation['competencies'] as $comp)
                            <li class="rounded-xl border border-hb-border bg-hb-page/40 p-4">
                                <button
                                    type="button"
                                    class="flex w-full items-start justify-between gap-3 text-start focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40"
                                    data-prep-expand="comp-{{ $comp['id'] }}"
                                    aria-expanded="false"
                                    aria-controls="prep-comp-{{ $comp['id'] }}"
                                >
                                    <span class="font-semibold text-hb-navy">{{ $comp['label'] }}</span>
                                    <span class="text-xs font-medium text-hb-blue" data-expand-label>{{ __('interviewer.prepare.show_question') }}</span>
                                </button>
                                <div id="prep-comp-{{ $comp['id'] }}" class="mt-3 hidden border-t border-hb-border pt-3 text-sm text-hb-text" hidden>
                                    {{ $comp['question'] }}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section id="prep-agenda" class="hb-surface p-5 sm:p-6" aria-labelledby="prep-agenda-heading">
                    <h2 id="prep-agenda-heading" class="text-lg font-semibold text-hb-navy">{{ __('interviewer.prepare.agenda_heading') }}</h2>
                    <ol class="mt-4 space-y-3">
                        @foreach ($preparation['agenda'] as $item)
                            <li class="flex gap-3 text-sm">
                                <span class="w-14 shrink-0 font-medium tabular-nums text-hb-muted">{{ $item['range'] }}</span>
                                <span class="text-hb-text">{{ $item['label'] }}</span>
                            </li>
                        @endforeach
                    </ol>
                </section>

                <section id="prep-notes" class="hb-surface p-5 sm:p-6" aria-labelledby="prep-notes-heading">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 id="prep-notes-heading" class="text-lg font-semibold text-hb-navy">{{ __('interviewer.prepare.notes_heading') }}</h2>
                        <p class="text-xs text-hb-muted" data-prep-notes-status role="status" aria-live="polite"></p>
                    </div>
                    <label class="mt-4 block">
                        <span class="sr-only">{{ __('interviewer.prepare.notes_heading') }}</span>
                        <textarea
                            class="hb-input min-h-[8rem] w-full resize-y"
                            data-prep-notes
                            rows="5"
                            dir="auto"
                            placeholder="{{ __('interviewer.prepare.notes_placeholder') }}"
                        ></textarea>
                    </label>
                    <p class="mt-2 text-xs text-hb-muted">{{ __('interviewer.prepare.notes_footnote') }}</p>
                </section>
            </div>

            <aside class="space-y-6">
                <section id="prep-checklist" class="hb-surface p-5" aria-labelledby="prep-role-heading">
                    <h2 id="prep-role-heading" class="text-base font-semibold text-hb-navy">{{ $preparation['role']['label'] }}</h2>
                    <p class="mt-2 text-sm text-hb-muted">{{ $preparation['role']['body'] }}</p>
                    <p class="mt-3 text-xs font-medium text-hb-blue" data-prep-progress role="status" aria-live="polite"></p>
                    <ul class="mt-4 space-y-3">
                        @foreach ($preparation['role_checklist'] as $item)
                            <li>
                                <label class="flex cursor-pointer items-start gap-3 text-sm">
                                    <input
                                        type="checkbox"
                                        class="mt-0.5 h-4 w-4 rounded border-hb-border text-hb-blue focus-visible:ring-2 focus-visible:ring-hb-blue/40"
                                        data-prep-checklist-item="{{ $item['id'] }}"
                                        @checked($item['done'])
                                    >
                                    <span class="text-hb-text">{{ $item['label'] }}</span>
                                </label>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-3 text-xs text-hb-muted" data-prep-checklist-status>{{ __('interviewer.prepare.checklist_hint') }}</p>
                </section>

                @if (! empty($preparation['session_checklist']))
                    <section
                        class="hb-surface p-5"
                        aria-labelledby="prep-session-heading"
                        data-session-checklist
                        data-checklist-key="{{ $preparation['session_checklist_key'] }}"
                        data-checklist-legacy-keys="{{ json_encode($preparation['session_checklist_legacy_keys'] ?? [], JSON_UNESCAPED_UNICODE) }}"
                    >
                        <h2 id="prep-session-heading" class="text-base font-semibold text-hb-navy">{{ __('interviewer.prepare.session_heading') }}</h2>
                        <p class="mt-2 text-sm text-hb-muted">{{ __('interviewer.prepare.session_body') }}</p>
                        <ul class="mt-4 space-y-3">
                            @foreach ($preparation['session_checklist'] as $item)
                                <li>
                                    <label class="flex cursor-pointer items-start gap-3 text-sm">
                                        <input
                                            type="checkbox"
                                            class="mt-0.5 h-4 w-4 rounded border-hb-border text-hb-blue focus-visible:ring-2 focus-visible:ring-hb-blue/40"
                                            data-checklist-item="{{ $item['id'] }}"
                                            @checked($item['done'])
                                        >
                                        <span class="text-hb-text">{{ $item['label'] }}</span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                        <p class="mt-3 text-xs text-hb-muted" data-checklist-status role="status">{{ __('interviewer.dashboard.checklist_saved') }}</p>
                    </section>
                @endif

                <section class="hb-surface p-5" aria-labelledby="prep-scoped-heading">
                    <h2 id="prep-scoped-heading" class="text-base font-semibold text-hb-navy">{{ __('interviewer.prepare.scoped_heading') }}</h2>
                    <p class="mt-2 text-sm text-hb-muted">{{ $preparation['scoped_note'] }}</p>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div>
                            <dt class="text-xs text-hb-muted">{{ __('interviewer.prepare.label_interview_id') }}</dt>
                            <dd class="font-medium text-hb-text" dir="ltr">{{ $interview['id'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-hb-muted">{{ __('interviewer.prepare.label_job') }}</dt>
                            <dd class="text-hb-text">{{ $interview['job_title'] }} · {{ $interview['job_code'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-hb-muted">{{ __('interviewer.prepare.label_when') }}</dt>
                            <dd class="text-hb-text">{{ $interview['when'] }} · {{ $interview['time'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-hb-muted">{{ __('interviewer.prepare.label_type') }}</dt>
                            <dd class="text-hb-text">{{ $interview['title'] }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="hb-surface p-5" aria-labelledby="prep-coord-heading">
                    <h2 id="prep-coord-heading" class="text-base font-semibold text-hb-navy">{{ __('interviewer.prepare.coordination_heading') }}</h2>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div>
                            <dt class="text-xs text-hb-muted">{{ __('interviewer.prepare.label_organizer') }}</dt>
                            <dd class="text-hb-text">{{ $preparation['coordination']['organizer'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-hb-muted">{{ __('interviewer.prepare.label_hiring_manager') }}</dt>
                            <dd class="text-hb-text">{{ $preparation['coordination']['hiring_manager'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-hb-muted">{{ __('interviewer.prepare.label_video') }}</dt>
                            <dd class="text-hb-text">{{ $preparation['coordination']['video_access'] }}</dd>
                        </div>
                    </dl>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="hb-btn-outline"
                            disabled
                            title="{{ __('interviewer.prepare.meeting_soon') }}"
                            aria-disabled="true"
                        >
                            {{ __('interviewer.prepare.meeting_details') }}
                        </button>
                        <a href="{{ route('interviewer.interviews.live', $interview['id']) }}" class="hb-btn-primary">
                            {{ __('interviewer.prepare.start_interview') }}
                        </a>
                    </div>
                </section>
            </aside>
        </div>

        <p class="mt-8 text-center text-xs text-hb-muted">{{ $preparation['footer'] }}</p>
    </div>

    @include('layouts.modal', [
        'modalId' => 'prep-resume-modal',
        'modalTitle' => __('interviewer.prepare.resume_modal_title', ['name' => $interview['applicant']]),
        'modalSize' => 'lg',
        'modalBody' => view('interviewer.interviews._resume-preview', [
            'applicant' => $applicant,
            'interview' => $interview,
        ])->render(),
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button>',
    ])
@endsection
