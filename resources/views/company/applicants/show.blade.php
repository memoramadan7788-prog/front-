@extends('layouts.app')

@section('title', $pageTitle ?? ($applicant['name'].' · Hirebridge'))
@section('breadcrumb', __('company.applicants.show_breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ $applicant['name'] }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ $applicant['subtitle'] }}</p>
    <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm">
        <x-badge variant="info" :dot="true">{{ $applicant['stage'] }}</x-badge>
        <x-badge variant="neutral" :dot="true">{{ $applicant['source'] }}</x-badge>
        <span class="text-hb-muted">
            {{ __('company.applicants.meta_line', [
                'code' => $applicant['job_code'],
                'date' => $applicant['applied_on'],
                'owner' => $applicant['owner'],
            ]) }}
        </span>
    </div>
@endsection

@section('page_actions')
    <div class="flex flex-wrap gap-2">
        <button type="button" class="hb-btn-primary" disabled title="{{ __('company.applicants.message_soon') }}">
            {{ __('company.applicants.message_applicant', ['name' => explode(' ', $applicant['name'])[0]]) }}
        </button>
        <a href="{{ route('company.interviews.schedule', ['applicant' => $applicant['id']]) }}" class="hb-btn-outline">
            {{ __('company.interviews.schedule_for_applicant') }}
        </a>
        @if (($applicant['id'] ?? '') === 'ethan-cole')
            <a href="{{ route('company.evaluations.show', 'eval-ethan-cole') }}" class="hb-btn-outline">
                {{ __('company.interviews.open_evaluation') }}
            </a>
        @endif
        <button type="button" class="hb-btn-outline" data-open-move-stage>
            {{ __('company.applicants.move_stage') }}
            <span class="ms-1" aria-hidden="true">▾</span>
        </button>
        <a href="{{ route('company.applicants.index', ['job' => $applicant['job_id']]) }}" class="hb-btn-outline">
            {{ __('company.applicants.back_to_pipeline') }}
        </a>
    </div>
@endsection

@section('content')
    @php
        $showI18n = [
            'note_saved' => __('company.applicants.js.note_saved'),
            'note_empty' => __('company.applicants.js.note_empty'),
            'move_feedback' => __('company.applicants.js.move_feedback'),
        ];
        $noteKey = 'hb_applicant_note_'.$applicant['id'];
    @endphp

    <div
        data-company-applicant
        data-applicant-id="{{ $applicant['id'] }}"
        data-applicant-name="{{ $applicant['name'] }}"
        data-note-key="{{ $noteKey }}"
        data-i18n="{{ json_encode($showI18n, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
    >
        <div class="mb-6">
            <x-timeline :steps="$applicant['pipeline']" />
        </div>

        <div class="mb-6 flex flex-wrap gap-4 border-b border-hb-border text-sm font-semibold" role="tablist" aria-label="{{ __('company.applicants.detail_tabs_aria') }}">
            @foreach ([
                ['profile', __('company.applicants.tab_profile')],
                ['interview', __('company.applicants.tab_interview_plan')],
                ['notes', __('company.applicants.tab_notes')],
                ['communications', __('company.applicants.tab_communications')],
            ] as [$key, $label])
                <button
                    type="button"
                    role="tab"
                    id="applicant-tab-{{ $key }}"
                    data-applicant-tab="{{ $key }}"
                    aria-controls="applicant-panel-{{ $key }}"
                    aria-selected="{{ $key === 'profile' ? 'true' : 'false' }}"
                    class="border-b-2 px-1 pb-3 {{ $key === 'profile' ? 'border-hb-blue text-hb-blue' : 'border-transparent text-hb-muted hover:text-hb-navy' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
            <div class="min-w-0 space-y-6">
                {{-- Profile --}}
                <div id="applicant-panel-profile" role="tabpanel" aria-labelledby="applicant-tab-profile" data-applicant-panel="profile">
                    <section class="hb-surface p-5 sm:p-6" aria-labelledby="experience-heading">
                        <h2 id="experience-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.applicants.experience_heading') }}</h2>
                        <ul class="mt-5 space-y-4">
                            @foreach ($applicant['experience'] as $role)
                                <li>
                                    <p class="font-semibold text-hb-navy">{{ $role['title'] }} · {{ $role['company'] }}</p>
                                    <p class="mt-0.5 text-xs text-hb-muted">{{ $role['dates'] }}</p>
                                    <p class="mt-1 text-sm text-hb-text">{{ $role['summary'] }}</p>
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-5 flex flex-wrap gap-2">
                            <button type="button" class="hb-btn-outline" data-open-resume>
                                {{ __('company.applicants.view_resume') }}
                            </button>
                            @if (! empty($applicant['portfolio']))
                                <a
                                    href="{{ $applicant['portfolio'] }}"
                                    class="hb-btn-outline inline-flex items-center gap-1"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    {{ __('company.applicants.open_portfolio') }}
                                    <span aria-hidden="true">↗</span>
                                </a>
                            @endif
                        </div>
                        <div class="mt-5 space-y-2 border-t border-hb-border pt-4 text-sm">
                            <p>
                                <span class="font-semibold text-hb-navy">{{ __('company.applicants.skills_label') }}:</span>
                                <span class="text-hb-muted"> {{ implode(' · ', $applicant['skills_list']) }}</span>
                            </p>
                            @if (! empty($applicant['education']))
                                <p>
                                    <span class="font-semibold text-hb-navy">{{ __('company.applicants.education_label') }}:</span>
                                    <span class="text-hb-muted"> {{ $applicant['education'] }}</span>
                                </p>
                            @endif
                        </div>
                    </section>

                    <section class="hb-surface mt-6 p-5 sm:p-6" aria-labelledby="screening-heading">
                        <h2 id="screening-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.applicants.screening_heading') }}</h2>
                        <ol class="mt-5 space-y-4 border-s border-hb-border ps-4">
                            @foreach ($applicant['screening'] as $item)
                                <li>
                                    <button
                                        type="button"
                                        class="w-full text-start"
                                        data-expand-toggle
                                        aria-expanded="true"
                                    >
                                        <p class="text-sm font-semibold text-hb-navy">{{ $item['when'] }}</p>
                                    </button>
                                    <div data-expand-panel class="mt-1">
                                        <p class="text-sm text-hb-muted">{{ $item['body'] }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </section>

                    <section class="hb-surface mt-6 p-5 sm:p-6" aria-labelledby="note-heading">
                        <h2 id="note-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.applicants.note_heading') }}</h2>
                        <label class="mt-4 block">
                            <span class="sr-only">{{ __('company.applicants.note_heading') }}</span>
                            <textarea
                                rows="4"
                                class="hb-input w-full"
                                data-note-input
                                placeholder="{{ __('company.applicants.note_placeholder') }}"
                            ></textarea>
                        </label>
                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            <button type="button" class="hb-btn-outline" data-save-note>
                                {{ __('company.applicants.save_note') }}
                            </button>
                            <p class="text-xs text-hb-muted">{{ __('company.applicants.note_visibility') }}</p>
                        </div>
                        <p class="mt-2 hidden text-sm text-hb-blue" data-note-feedback role="status"></p>
                    </section>
                </div>

                {{-- Interview plan --}}
                <div id="applicant-panel-interview" role="tabpanel" aria-labelledby="applicant-tab-interview" data-applicant-panel="interview" class="hidden" hidden>
                    <section class="hb-surface p-5 sm:p-6" aria-labelledby="interview-plan-heading">
                        <h2 id="interview-plan-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.applicants.interview_plan_heading') }}</h2>
                        @if (! empty($applicant['interview']))
                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                <x-badge variant="info" :dot="true">{{ $applicant['interview']['status'] }}</x-badge>
                                <p class="font-semibold text-hb-navy">{{ $applicant['interview']['title'] }}</p>
                            </div>
                            <p class="mt-2 text-sm text-hb-muted">{{ $applicant['interview']['when'] }}</p>
                            <p class="text-sm text-hb-muted">{{ $applicant['interview']['timezone'] }}</p>
                            <p class="mt-3 text-sm text-hb-text">
                                <span class="font-semibold text-hb-navy">{{ $applicant['interview']['interviewer'] }}</span>
                                · {{ $applicant['interview']['interviewer_meta'] }}
                            </p>
                        @else
                            <p class="mt-3 text-sm text-hb-muted">{{ __('company.applicants.no_interview') }}</p>
                        @endif

                        <h3 class="mt-6 text-sm font-semibold text-hb-navy">{{ __('company.applicants.agenda_heading') }}</h3>
                        <ol class="mt-3 list-decimal space-y-2 ps-5 text-sm text-hb-text">
                            @foreach ($applicant['interview_plan']['agenda'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ol>
                        <p class="mt-4 text-sm text-hb-muted">
                            {{ ! empty($applicant['interview_plan']['prep_shared'])
                                ? __('company.applicants.prep_shared')
                                : __('company.applicants.prep_pending') }}
                        </p>
                        <p class="mt-2 text-sm text-hb-text">
                            <span class="font-semibold text-hb-navy">{{ __('company.applicants.rubric_label') }}:</span>
                            {{ $applicant['interview_plan']['rubric'] }}
                        </p>
                    </section>
                </div>

                {{-- Notes & activity --}}
                <div id="applicant-panel-notes" role="tabpanel" aria-labelledby="applicant-tab-notes" data-applicant-panel="notes" class="hidden" hidden>
                    <section class="hb-surface p-5 sm:p-6" aria-labelledby="notes-activity-heading">
                        <h2 id="notes-activity-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.applicants.notes_activity_heading') }}</h2>
                        <ul class="mt-5 space-y-4">
                            @foreach ($applicant['notes_activity'] as $item)
                                <li class="rounded-lg border border-hb-border bg-hb-page px-4 py-3">
                                    <p class="text-xs font-medium text-hb-muted">{{ $item['when'] }} · {{ $item['author'] }}</p>
                                    <p class="mt-1 text-sm text-hb-text">{{ $item['body'] }}</p>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                </div>

                {{-- Communications --}}
                <div id="applicant-panel-communications" role="tabpanel" aria-labelledby="applicant-tab-communications" data-applicant-panel="communications" class="hidden" hidden>
                    <section class="hb-surface p-5 sm:p-6" aria-labelledby="communications-heading">
                        <h2 id="communications-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.applicants.communications_heading') }}</h2>
                        <ul class="mt-5 space-y-4">
                            @foreach ($applicant['communications'] as $item)
                                <li class="border-s-2 border-hb-border ps-4">
                                    <p class="text-xs font-medium text-hb-muted">{{ $item['when'] }} · {{ $item['channel'] }}</p>
                                    <p class="mt-1 text-sm text-hb-text">{{ $item['summary'] }}</p>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                </div>
            </div>

            <aside class="space-y-4">
                @if (! empty($applicant['interview']))
                    <section class="hb-surface p-5" aria-labelledby="next-interview-heading">
                        <div class="flex items-center gap-2">
                            <x-badge variant="info" :dot="true">{{ $applicant['interview']['status'] }}</x-badge>
                        </div>
                        <h2 id="next-interview-heading" class="mt-3 text-base font-semibold text-hb-navy">{{ $applicant['interview']['title'] }}</h2>
                        <p class="mt-2 text-sm text-hb-muted">{{ $applicant['interview']['when'] }}</p>
                        <p class="text-sm text-hb-muted">{{ $applicant['interview']['timezone'] }}</p>
                        <p class="mt-3 text-sm text-hb-text">
                            {{ $applicant['interview']['interviewer'] }}.
                            {{ $applicant['interview']['interviewer_meta'] }}
                        </p>
                        <div class="mt-4 flex flex-col gap-2">
                            <button type="button" class="hb-btn-primary w-full" data-jump-interview-tab>
                                {{ __('company.applicants.view_interview_plan') }}
                            </button>
                            @if (($applicant['id'] ?? '') === 'maya-chen')
                                <a href="{{ route('company.interviews.show', 'int-ns204-portfolio') }}" class="hb-btn-outline w-full text-center">
                                    {{ __('company.interviews.view_details') }}
                                </a>
                            @endif
                        </div>
                    </section>
                @endif

                <section class="hb-surface p-5" aria-labelledby="contact-heading">
                    <h2 id="contact-heading" class="text-base font-semibold text-hb-navy">{{ __('company.applicants.contact_consent') }}</h2>
                    <p class="mt-3 text-sm text-hb-text" dir="ltr">{{ $applicant['email'] }}</p>
                    @if (! empty($applicant['portfolio_label']))
                        <p class="mt-1 text-sm text-hb-blue" dir="ltr">{{ $applicant['portfolio_label'] }}</p>
                    @elseif (! empty($applicant['portfolio']))
                        <p class="mt-1 text-sm text-hb-blue" dir="ltr">{{ $applicant['portfolio'] }}</p>
                    @endif
                    <ul class="mt-4 space-y-2 text-xs text-hb-muted">
                        <li>{{ $applicant['consent']['processing'] }}</li>
                        <li>{{ $applicant['consent']['talent_pool'] }}</li>
                        <li>{{ $applicant['consent']['access'] }}</li>
                    </ul>
                </section>
            </aside>
        </div>

        <p class="mt-8 text-center text-xs text-hb-muted">{{ $applicant['footer'] }}</p>

        {{-- Move stage modal --}}
        <div data-move-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4" hidden role="dialog" aria-modal="true" aria-labelledby="move-stage-title">
            <div class="w-full max-w-md rounded-hb border border-hb-border bg-hb-surface p-5 shadow-hb-lg">
                <h2 id="move-stage-title" class="text-lg font-semibold text-hb-navy">{{ __('company.applicants.move_stage_title') }}</h2>
                <p class="mt-2 text-sm text-hb-muted">
                    {{ __('company.applicants.move_stage_body', ['name' => $applicant['name']]) }}
                </p>
                <label class="mt-4 block">
                    <span class="hb-label">{{ __('company.applicants.col_stage') }}</span>
                    <select class="hb-input mt-1 w-full" data-move-stage-select>
                        @foreach ($applicant['pipeline'] as $step)
                            <option value="{{ $step['label'] }}" @selected($step['status'] === 'current')>{{ $step['label'] }}</option>
                        @endforeach
                    </select>
                </label>
                <p class="mt-3 hidden text-sm text-hb-blue" data-move-feedback role="status"></p>
                <div class="mt-5 flex flex-wrap justify-end gap-2">
                    <button type="button" class="hb-btn-outline" data-close-move>{{ __('company.applicants.add_cancel') }}</button>
                    <button type="button" class="hb-btn-primary" data-confirm-move>{{ __('company.applicants.move_stage_confirm') }}</button>
                </div>
            </div>
        </div>

        {{-- Resume preview modal --}}
        <div data-resume-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4" hidden role="dialog" aria-modal="true" aria-labelledby="resume-preview-title">
            <div class="w-full max-w-md rounded-hb border border-hb-border bg-hb-surface p-5 shadow-hb-lg">
                <h2 id="resume-preview-title" class="text-lg font-semibold text-hb-navy">{{ __('company.applicants.resume_preview_title') }}</h2>
                <p class="mt-3 text-sm text-hb-text" dir="ltr">
                    {{ __('company.applicants.resume_preview_body', [
                        'filename' => $applicant['resume']['filename'] ?? '',
                        'size' => $applicant['resume']['size'] ?? '',
                        'updated' => $applicant['resume']['updated'] ?? '',
                    ]) }}
                </p>
                <p class="mt-2 text-xs text-hb-muted">{{ __('company.applicants.resume_preview_note') }}</p>
                <div class="mt-5 flex justify-end">
                    <button type="button" class="hb-btn-outline" data-close-resume>{{ __('company.applicants.modal_close') }}</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (() => {
                const root = document.querySelector('[data-company-applicant]');
                if (!root) return;

                const i18n = JSON.parse(root.getAttribute('data-i18n') || '{}');
                const noteKey = root.getAttribute('data-note-key') || '';
                const applicantName = root.getAttribute('data-applicant-name') || '';
                const tabs = [...root.querySelectorAll('[data-applicant-tab]')];
                const panels = [...root.querySelectorAll('[data-applicant-panel]')];
                const noteInput = root.querySelector('[data-note-input]');
                const noteFeedback = root.querySelector('[data-note-feedback]');

                function replacePlaceholders(template, replacements) {
                    return Object.entries(replacements).reduce(
                        (text, [key, value]) => text.replaceAll(`:${key}`, String(value)),
                        template ?? '',
                    );
                }

                function setTab(key) {
                    tabs.forEach((btn) => {
                        const active = btn.getAttribute('data-applicant-tab') === key;
                        btn.setAttribute('aria-selected', active ? 'true' : 'false');
                        btn.classList.toggle('border-hb-blue', active);
                        btn.classList.toggle('text-hb-blue', active);
                        btn.classList.toggle('border-transparent', !active);
                        btn.classList.toggle('text-hb-muted', !active);
                    });
                    panels.forEach((panel) => {
                        const active = panel.getAttribute('data-applicant-panel') === key;
                        panel.classList.toggle('hidden', !active);
                        panel.hidden = !active;
                    });
                }

                tabs.forEach((btn) => {
                    btn.addEventListener('click', () => setTab(btn.getAttribute('data-applicant-tab')));
                });

                root.querySelector('[data-jump-interview-tab]')?.addEventListener('click', () => setTab('interview'));

                root.querySelectorAll('[data-expand-toggle]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const panel = btn.parentElement?.querySelector('[data-expand-panel]');
                        if (!panel) return;
                        const expanded = btn.getAttribute('aria-expanded') === 'true';
                        btn.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                        panel.classList.toggle('hidden', expanded);
                    });
                });

                try {
                    const saved = localStorage.getItem(noteKey);
                    if (saved && noteInput) noteInput.value = saved;
                } catch (e) {}

                root.querySelector('[data-save-note]')?.addEventListener('click', () => {
                    const value = (noteInput?.value || '').trim();
                    if (!value) {
                        if (noteFeedback) {
                            noteFeedback.textContent = i18n.note_empty || '';
                            noteFeedback.classList.remove('hidden');
                        }
                        return;
                    }
                    try {
                        localStorage.setItem(noteKey, value);
                    } catch (e) {}
                    if (noteFeedback) {
                        noteFeedback.textContent = i18n.note_saved || '';
                        noteFeedback.classList.remove('hidden');
                    }
                });

                function openModal(modal) {
                    if (!modal) return;
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    modal.hidden = false;
                    modal.querySelector('button, select, input')?.focus();
                }

                function closeModal(modal) {
                    if (!modal) return;
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    modal.hidden = true;
                }

                const moveModal = root.querySelector('[data-move-modal]');
                const resumeModal = root.querySelector('[data-resume-modal]');

                root.querySelector('[data-open-move-stage]')?.addEventListener('click', () => openModal(moveModal));
                root.querySelector('[data-close-move]')?.addEventListener('click', () => closeModal(moveModal));
                root.querySelector('[data-open-resume]')?.addEventListener('click', () => openModal(resumeModal));
                root.querySelector('[data-close-resume]')?.addEventListener('click', () => closeModal(resumeModal));

                root.querySelector('[data-confirm-move]')?.addEventListener('click', () => {
                    const feedback = root.querySelector('[data-move-feedback]');
                    if (feedback) {
                        feedback.textContent = replacePlaceholders(i18n.move_feedback, { name: applicantName });
                        feedback.classList.remove('hidden');
                    }
                });

                [moveModal, resumeModal].forEach((modal) => {
                    modal?.addEventListener('click', (event) => {
                        if (event.target === modal) closeModal(modal);
                    });
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key !== 'Escape') return;
                    closeModal(moveModal);
                    closeModal(resumeModal);
                });
            })();
        </script>
    @endpush
@endsection
