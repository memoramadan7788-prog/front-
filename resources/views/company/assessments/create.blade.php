@extends('layouts.app')

@section('title', $pageTitle ?? __('company.assessments.create_page_title'))
@section('breadcrumb', __('company.assessments.create_breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ __('company.assessments.create_title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ __('company.assessments.create_subtitle') }}</p>
@endsection

@section('page_actions')
    <a href="{{ route('company.assessments.index') }}" class="hb-btn-outline">{{ __('company.assessments.back') }}</a>
@endsection

@section('content')
    @php
        $createI18n = [
            'draft_saved' => __('company.assessments.js.draft_saved'),
            'validation_title' => __('company.assessments.js.validation_title'),
            'validation_questions' => __('company.assessments.js.validation_questions'),
            'question_label' => __('company.assessments.js.question_label'),
            'remove_question' => __('company.assessments.remove_question'),
            'question_prompt' => __('company.assessments.question_prompt'),
            'question_type' => __('company.assessments.question_type'),
            'type_mc' => __('company.assessments.type_mc'),
            'type_short' => __('company.assessments.type_short'),
        ];
    @endphp

    <div
        data-assessment-create
        data-i18n="{{ json_encode($createI18n, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-draft-key="hb_assessment_draft"
    >
        <form class="hb-surface max-w-3xl space-y-5 p-5 sm:p-6" data-create-form novalidate>
            <label class="block">
                <span class="hb-label">{{ __('company.assessments.assessment_title') }} <span class="text-hb-error">*</span></span>
                <input type="text" class="hb-input mt-1 w-full" data-create-title required autocomplete="off">
            </label>
            <label class="block">
                <span class="hb-label">{{ __('company.assessments.linked_job') }}</span>
                <select class="hb-input mt-1 w-full" data-create-job>
                    @foreach ($catalog['job_options'] as $job)
                        <option value="{{ $job['id'] }}">{{ $job['label'] }}</option>
                    @endforeach
                </select>
            </label>
            <div class="grid gap-4 sm:grid-cols-3">
                <label class="block">
                    <span class="hb-label">{{ __('company.assessments.time_limit') }}</span>
                    <input type="number" min="5" max="180" value="45" class="hb-input mt-1 w-full" data-create-minutes dir="ltr">
                </label>
                <label class="block">
                    <span class="hb-label">{{ __('company.assessments.attempt_limit') }}</span>
                    <input type="number" min="1" max="5" value="2" class="hb-input mt-1 w-full" data-create-attempts dir="ltr">
                </label>
                <label class="block">
                    <span class="hb-label">{{ __('company.assessments.passing_score') }}</span>
                    <input type="number" min="1" max="100" value="70" class="hb-input mt-1 w-full" data-create-pass dir="ltr">
                </label>
            </div>

            <div>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-base font-semibold text-hb-navy">{{ __('company.assessments.questions_heading') }}</h2>
                    <button type="button" class="hb-btn-outline text-sm" data-add-question>{{ __('company.assessments.add_question') }}</button>
                </div>
                <div class="mt-4 space-y-4" data-questions-list></div>
            </div>

            <div class="flex flex-wrap gap-2 border-t border-hb-border pt-5">
                <button type="submit" class="hb-btn-primary">{{ __('company.assessments.save_draft') }}</button>
                <a href="{{ route('company.assessments.index') }}" class="hb-btn-outline">{{ __('company.assessments.cancel') }}</a>
            </div>
            <p class="hidden text-sm text-hb-blue" data-create-feedback role="status"></p>
        </form>

        <p class="mt-8 text-center text-xs text-hb-muted">{{ $catalog['footer'] }}</p>
    </div>

    @push('scripts')
        <script>
            (() => {
                const root = document.querySelector('[data-assessment-create]');
                if (!root) return;
                const i18n = JSON.parse(root.getAttribute('data-i18n') || '{}');
                const draftKey = root.getAttribute('data-draft-key') || '';
                const listEl = root.querySelector('[data-questions-list]');
                const feedback = root.querySelector('[data-create-feedback]');
                let questions = [{ id: 1, type: 'mc', prompt: '' }];

                function replacePlaceholders(template, replacements) {
                    return Object.entries(replacements).reduce(
                        (text, [key, value]) => text.replaceAll(`:${key}`, String(value)),
                        template ?? '',
                    );
                }

                function escapeHtml(value) {
                    return String(value ?? '')
                        .replaceAll('&', '&amp;')
                        .replaceAll('<', '&lt;')
                        .replaceAll('>', '&gt;')
                        .replaceAll('"', '&quot;');
                }

                function renderQuestions() {
                    if (!listEl) return;
                    listEl.innerHTML = questions.map((q, index) => `
                        <div class="rounded-lg border border-hb-border p-4" data-question-id="${q.id}">
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-hb-navy">${escapeHtml(replacePlaceholders(i18n.question_label, { number: index + 1 }))}</p>
                                <button type="button" class="text-sm font-semibold text-hb-blue hover:underline" data-remove-question="${q.id}" ${questions.length <= 1 ? 'disabled' : ''}>
                                    ${escapeHtml(i18n.remove_question || '')}
                                </button>
                            </div>
                            <label class="block">
                                <span class="hb-label">${escapeHtml(i18n.question_prompt || '')}</span>
                                <textarea class="hb-input mt-1 w-full" rows="2" data-q-prompt="${q.id}">${escapeHtml(q.prompt || '')}</textarea>
                            </label>
                            <label class="mt-3 block">
                                <span class="hb-label">${escapeHtml(i18n.question_type || '')}</span>
                                <select class="hb-input mt-1 w-full" data-q-type="${q.id}">
                                    <option value="mc" ${q.type === 'mc' ? 'selected' : ''}>${escapeHtml(i18n.type_mc || '')}</option>
                                    <option value="short" ${q.type === 'short' ? 'selected' : ''}>${escapeHtml(i18n.type_short || '')}</option>
                                </select>
                            </label>
                        </div>
                    `).join('');
                }

                function syncFromDom() {
                    questions = questions.map((q) => {
                        const prompt = root.querySelector(`[data-q-prompt="${q.id}"]`)?.value || '';
                        const type = root.querySelector(`[data-q-type="${q.id}"]`)?.value || 'mc';
                        return { ...q, prompt, type };
                    });
                }

                try {
                    const saved = JSON.parse(localStorage.getItem(draftKey) || 'null');
                    if (saved) {
                        if (saved.title) root.querySelector('[data-create-title]').value = saved.title;
                        if (saved.job) root.querySelector('[data-create-job]').value = saved.job;
                        if (saved.minutes) root.querySelector('[data-create-minutes]').value = saved.minutes;
                        if (saved.attempts) root.querySelector('[data-create-attempts]').value = saved.attempts;
                        if (saved.pass) root.querySelector('[data-create-pass]').value = saved.pass;
                        if (Array.isArray(saved.questions) && saved.questions.length) questions = saved.questions;
                    }
                } catch (e) {}

                root.querySelector('[data-add-question]')?.addEventListener('click', () => {
                    syncFromDom();
                    questions.push({ id: Date.now(), type: 'mc', prompt: '' });
                    renderQuestions();
                });

                listEl?.addEventListener('click', (event) => {
                    const btn = event.target.closest('[data-remove-question]');
                    if (!btn || btn.disabled) return;
                    syncFromDom();
                    const id = Number(btn.getAttribute('data-remove-question'));
                    questions = questions.filter((q) => q.id !== id);
                    if (!questions.length) questions = [{ id: Date.now(), type: 'mc', prompt: '' }];
                    renderQuestions();
                });

                root.querySelector('[data-create-form]')?.addEventListener('submit', (event) => {
                    event.preventDefault();
                    syncFromDom();
                    const title = (root.querySelector('[data-create-title]')?.value || '').trim();
                    if (!title) {
                        if (feedback) {
                            feedback.textContent = i18n.validation_title || '';
                            feedback.classList.remove('hidden');
                        }
                        return;
                    }
                    if (!questions.some((q) => (q.prompt || '').trim())) {
                        if (feedback) {
                            feedback.textContent = i18n.validation_questions || '';
                            feedback.classList.remove('hidden');
                        }
                        return;
                    }
                    const draft = {
                        title,
                        job: root.querySelector('[data-create-job]')?.value || '',
                        minutes: root.querySelector('[data-create-minutes]')?.value || '',
                        attempts: root.querySelector('[data-create-attempts]')?.value || '',
                        pass: root.querySelector('[data-create-pass]')?.value || '',
                        questions,
                    };
                    try {
                        localStorage.setItem(draftKey, JSON.stringify(draft));
                    } catch (e) {}
                    if (feedback) {
                        feedback.textContent = i18n.draft_saved || '';
                        feedback.classList.remove('hidden');
                    }
                });

                renderQuestions();
            })();
        </script>
    @endpush
@endsection
