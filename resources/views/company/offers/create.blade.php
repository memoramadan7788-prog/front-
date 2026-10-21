@extends('layouts.app')

@section('title', $pageTitle ?? __('company.offers.create_page_title', ['name' => $offer['applicant']]))
@section('breadcrumb', __('company.offers.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">
        {{ __('company.offers.title', ['name' => $offer['applicant']]) }}
    </h1>
    <p class="mt-1 text-sm text-hb-muted">
        {{ __('company.offers.subtitle', [
            'company' => $offer['company'],
            'code' => $offer['job_code'],
            'title' => $offer['job_title'],
            'offer' => $offer['code'],
        ]) }}
    </p>
@endsection

@section('page_actions')
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('company.evaluations.show', 'eval-ethan-cole') }}" class="hb-btn-outline">
            {{ __('company.offers.back_to_evaluation') }}
        </a>
        <a href="{{ route('company.applicants.show', $offer['applicant_id']) }}" class="hb-btn-outline">
            {{ __('company.offers.back_to_applicant') }}
        </a>
    </div>
@endsection

@section('content')
    @php
        $offerI18n = [
            'draft_saved' => __('company.offers.js.draft_saved'),
            'approval_requested' => __('company.offers.js.approval_requested'),
            'validation_salary' => __('company.offers.js.validation_salary'),
            'validation_start' => __('company.offers.js.validation_start'),
            'validation_deadline' => __('company.offers.js.validation_deadline'),
            'validation_work' => __('company.offers.js.validation_work'),
            'preview_salary' => __('company.offers.js.preview_salary'),
            'preview_bonus' => __('company.offers.js.preview_bonus'),
            'preview_meta' => __('company.offers.js.preview_meta'),
            'preview_start' => __('company.offers.js.preview_start'),
            'preview_deadline' => __('company.offers.js.preview_deadline'),
            'approver' => 'Daniel Brooks',
            'job_title' => $offer['job_title'],
        ];
    @endphp

    <div
        data-company-offer
        data-i18n="{{ json_encode($offerI18n, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-draft-key="hb_offer_draft_{{ $offer['id'] }}"
    >
        <div class="mb-6 rounded-lg border border-hb-pending/40 bg-hb-pending-soft/50 px-4 py-3 text-sm text-hb-text" role="status">
            <p class="font-semibold text-hb-navy">{{ __('company.offers.banner_status') }}</p>
            <p class="mt-1 text-hb-muted">{{ $offer['banner'] }}</p>
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
            <div class="min-w-0 space-y-6">
                <section class="hb-surface p-5 sm:p-6" aria-labelledby="compensation-heading">
                    <h2 id="compensation-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.offers.compensation_heading') }}</h2>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="hb-label">{{ __('company.offers.salary') }} <span class="text-hb-error">*</span></span>
                            <input type="text" class="hb-input mt-1 w-full" data-offer-salary value="{{ $offer['salary'] }}" dir="ltr" required>
                        </label>
                        <label class="block">
                            <span class="hb-label">{{ __('company.offers.bonus') }}</span>
                            <input type="text" class="hb-input mt-1 w-full" data-offer-bonus value="{{ $offer['bonus'] }}">
                        </label>
                        <label class="block">
                            <span class="hb-label">{{ __('company.offers.employment') }} <span class="text-hb-error">*</span></span>
                            <select class="hb-input mt-1 w-full" data-offer-employment>
                                @foreach ($offer['employment_options'] as $option)
                                    <option value="{{ $option }}" @selected($option === $offer['employment'])>{{ $option }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="hb-label">{{ __('company.offers.start_date') }} <span class="text-hb-error">*</span></span>
                            <input type="text" class="hb-input mt-1 w-full" data-offer-start value="{{ $offer['start_date'] }}" required>
                        </label>
                        <label class="block">
                            <span class="hb-label">{{ __('company.offers.deadline') }} <span class="text-hb-error">*</span></span>
                            <input type="text" class="hb-input mt-1 w-full" data-offer-deadline value="{{ $offer['deadline'] }}" required>
                        </label>
                        <label class="block">
                            <span class="hb-label">{{ __('company.offers.work_model') }} <span class="text-hb-error">*</span></span>
                            <input type="text" class="hb-input mt-1 w-full" data-offer-work value="{{ $offer['work_model'] }}" required>
                        </label>
                        <label class="block sm:col-span-2">
                            <span class="hb-label">{{ __('company.offers.equity') }}</span>
                            <textarea class="hb-input mt-1 w-full" rows="3" data-offer-equity>{{ $offer['equity'] }}</textarea>
                        </label>
                        <label class="block sm:col-span-2">
                            <span class="hb-label">{{ __('company.offers.template') }}</span>
                            <select class="hb-input mt-1 w-full" data-offer-template dir="ltr">
                                @foreach ($offer['templates'] as $template)
                                    <option value="{{ $template }}" @selected($template === $offer['template'])>{{ $template }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block sm:col-span-2">
                            <span class="hb-label">{{ __('company.offers.message') }}</span>
                            <textarea class="hb-input mt-1 w-full" rows="4" data-offer-message>{{ $offer['message'] }}</textarea>
                        </label>
                    </div>
                </section>

                <section class="hb-surface p-5 sm:p-6" aria-labelledby="approvals-heading">
                    <h2 id="approvals-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.offers.approvals_heading') }}</h2>
                    <ul class="mt-5 space-y-3">
                        @foreach ($offer['approvals'] as $item)
                            <li class="rounded-lg border border-hb-border bg-hb-page px-4 py-3 text-sm">
                                <p class="font-semibold text-hb-navy">{{ $item['label'] }}</p>
                                <p class="mt-1 text-hb-muted">
                                    {{ __('company.offers.owner_status', ['owner' => $item['owner'], 'status' => $item['status']]) }}
                                </p>
                            </li>
                        @endforeach
                    </ul>
                    <label class="mt-4 flex items-start gap-2 text-sm text-hb-text">
                        <input type="checkbox" class="mt-1" data-offer-esign @checked($offer['require_esignature'])>
                        <span>{{ __('company.offers.require_esignature') }}</span>
                    </label>
                </section>

                <div class="flex flex-wrap gap-2">
                    <button type="button" class="hb-btn-outline" data-offer-save>{{ __('company.offers.save_draft') }}</button>
                    <button type="button" class="hb-btn-primary" data-offer-request>{{ __('company.offers.request_approval') }}</button>
                    <button type="button" class="inline-flex cursor-not-allowed items-center justify-center rounded-lg bg-hb-border px-4 py-2 text-sm font-semibold text-hb-muted" disabled title="{{ __('company.offers.send_disabled_title') }}" aria-disabled="true">
                        {{ __('company.offers.send_offer') }}
                    </button>
                </div>
                <p class="hidden text-sm text-hb-blue" data-offer-feedback role="status"></p>
            </div>

            <aside class="space-y-4">
                <section class="hb-surface p-5" aria-labelledby="preview-heading">
                    <div class="flex items-center justify-between gap-2">
                        <h2 id="preview-heading" class="text-base font-semibold text-hb-navy">{{ __('company.offers.preview_heading') }}</h2>
                        <x-badge variant="neutral" :dot="true">{{ __('company.offers.preview_status') }}</x-badge>
                    </div>
                    <p class="mt-4 font-semibold text-hb-navy" data-preview-heading>{{ $offer['preview']['heading'] }}</p>
                    <ul class="mt-3 space-y-1.5 text-sm text-hb-text" data-preview-lines>
                        <li data-preview-title>{{ $offer['job_title'] }}</li>
                        <li data-preview-salary dir="ltr">{{ $offer['preview']['lines'][1] }}</li>
                        <li data-preview-bonus>{{ $offer['preview']['lines'][2] }}</li>
                        <li data-preview-meta>{{ $offer['preview']['lines'][3] }}</li>
                        <li data-preview-start>{{ $offer['preview']['lines'][4] }}</li>
                        <li data-preview-deadline>{{ $offer['preview']['lines'][5] }}</li>
                    </ul>
                    <button type="button" class="hb-btn-outline mt-5 w-full" data-offer-preview-letter>
                        {{ __('company.offers.preview_letter') }}
                    </button>
                    <p class="mt-3 text-xs text-hb-muted">{{ $offer['send_note'] }}</p>
                </section>
            </aside>
        </div>

        <p class="mt-8 text-center text-xs text-hb-muted">{{ $offer['footer'] }}</p>

        {{-- Letter preview modal --}}
        <div data-letter-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4" hidden role="dialog" aria-modal="true" aria-labelledby="letter-modal-title">
            <div class="w-full max-w-lg rounded-hb border border-hb-border bg-hb-surface p-5 shadow-hb-lg">
                <h2 id="letter-modal-title" class="text-lg font-semibold text-hb-navy">{{ __('company.offers.letter_modal_title') }}</h2>
                <div class="mt-4 space-y-2 text-sm text-hb-text" data-letter-body>
                    <p class="font-semibold" data-letter-heading>{{ $offer['preview']['heading'] }}</p>
                    <p data-letter-title>{{ $offer['job_title'] }}</p>
                    <p data-letter-salary dir="ltr">{{ $offer['salary'] }}</p>
                    <p data-letter-bonus>{{ $offer['bonus'] }}</p>
                    <p data-letter-meta>{{ $offer['work_model'] }} · {{ $offer['employment'] }}</p>
                    <p data-letter-start>{{ $offer['start_date'] }}</p>
                    <p data-letter-deadline>{{ $offer['deadline'] }}</p>
                    <p class="pt-2 text-hb-muted" data-letter-message>{{ $offer['message'] }}</p>
                </div>
                <div class="mt-5 flex justify-end">
                    <button type="button" class="hb-btn-outline" data-close-letter>{{ __('company.offers.letter_modal_close') }}</button>
                </div>
            </div>
        </div>

        {{-- Approval confirmation modal --}}
        <div data-approval-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4" hidden role="dialog" aria-modal="true" aria-labelledby="approval-modal-title">
            <div class="w-full max-w-md rounded-hb border border-hb-border bg-hb-surface p-5 shadow-hb-lg">
                <h2 id="approval-modal-title" class="text-lg font-semibold text-hb-navy">{{ __('company.offers.confirm_approval_title') }}</h2>
                <p class="mt-2 text-sm text-hb-muted">
                    {{ __('company.offers.confirm_approval_body', ['owner' => 'Daniel Brooks']) }}
                </p>
                <div class="mt-5 flex flex-wrap justify-end gap-2">
                    <button type="button" class="hb-btn-outline" data-close-approval>{{ __('company.offers.confirm_approval_cancel') }}</button>
                    <button type="button" class="hb-btn-primary" data-confirm-approval>{{ __('company.offers.confirm_approval_confirm') }}</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (() => {
                const root = document.querySelector('[data-company-offer]');
                if (!root) return;
                const i18n = JSON.parse(root.getAttribute('data-i18n') || '{}');
                const draftKey = root.getAttribute('data-draft-key') || '';
                const feedback = root.querySelector('[data-offer-feedback]');
                const salary = root.querySelector('[data-offer-salary]');
                const bonus = root.querySelector('[data-offer-bonus]');
                const employment = root.querySelector('[data-offer-employment]');
                const work = root.querySelector('[data-offer-work]');
                const start = root.querySelector('[data-offer-start]');
                const deadline = root.querySelector('[data-offer-deadline]');
                const equity = root.querySelector('[data-offer-equity]');
                const template = root.querySelector('[data-offer-template]');
                const message = root.querySelector('[data-offer-message]');
                const esign = root.querySelector('[data-offer-esign]');

                function replacePlaceholders(templateText, replacements) {
                    return Object.entries(replacements).reduce(
                        (text, [key, value]) => text.replaceAll(`:${key}`, String(value)),
                        templateText ?? '',
                    );
                }

                function readDraft() {
                    return {
                        salary: salary?.value?.trim() || '',
                        bonus: bonus?.value?.trim() || '',
                        employment: employment?.value || '',
                        work: work?.value?.trim() || '',
                        start: start?.value?.trim() || '',
                        deadline: deadline?.value?.trim() || '',
                        equity: equity?.value?.trim() || '',
                        template: template?.value || '',
                        message: message?.value?.trim() || '',
                        esign: !!esign?.checked,
                    };
                }

                function updatePreview() {
                    const data = readDraft();
                    const set = (sel, text) => {
                        const el = root.querySelector(sel);
                        if (el) el.textContent = text;
                    };
                    set('[data-preview-title]', i18n.job_title || '');
                    set('[data-preview-salary]', replacePlaceholders(i18n.preview_salary, { value: data.salary || '—' }));
                    set('[data-preview-bonus]', replacePlaceholders(i18n.preview_bonus, { value: data.bonus || '—' }));
                    set('[data-preview-meta]', replacePlaceholders(i18n.preview_meta, {
                        work: data.work || '—',
                        employment: data.employment || '—',
                    }));
                    set('[data-preview-start]', replacePlaceholders(i18n.preview_start, { value: data.start || '—' }));
                    set('[data-preview-deadline]', replacePlaceholders(i18n.preview_deadline, { value: data.deadline || '—' }));
                    set('[data-letter-title]', i18n.job_title || '');
                    set('[data-letter-salary]', data.salary || '—');
                    set('[data-letter-bonus]', data.bonus || '—');
                    set('[data-letter-meta]', `${data.work || '—'} · ${data.employment || '—'}`);
                    set('[data-letter-start]', data.start || '—');
                    set('[data-letter-deadline]', data.deadline || '—');
                    set('[data-letter-message]', data.message || '');
                }

                function validate() {
                    const data = readDraft();
                    if (!data.salary) return i18n.validation_salary;
                    if (!data.start) return i18n.validation_start;
                    if (!data.deadline) return i18n.validation_deadline;
                    if (!data.work) return i18n.validation_work;
                    return '';
                }

                function openModal(modal) {
                    if (!modal) return;
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    modal.hidden = false;
                    modal.querySelector('button, input')?.focus();
                }

                function closeModal(modal) {
                    if (!modal) return;
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    modal.hidden = true;
                }

                try {
                    const saved = JSON.parse(localStorage.getItem(draftKey) || 'null');
                    if (saved) {
                        if (salary && saved.salary) salary.value = saved.salary;
                        if (bonus && saved.bonus) bonus.value = saved.bonus;
                        if (employment && saved.employment) employment.value = saved.employment;
                        if (work && saved.work) work.value = saved.work;
                        if (start && saved.start) start.value = saved.start;
                        if (deadline && saved.deadline) deadline.value = saved.deadline;
                        if (equity && saved.equity) equity.value = saved.equity;
                        if (template && saved.template) template.value = saved.template;
                        if (message && saved.message) message.value = saved.message;
                        if (esign && typeof saved.esign === 'boolean') esign.checked = saved.esign;
                    }
                } catch (e) {}

                [salary, bonus, employment, work, start, deadline, equity, message].forEach((el) => {
                    el?.addEventListener('input', updatePreview);
                    el?.addEventListener('change', updatePreview);
                });

                root.querySelector('[data-offer-save]')?.addEventListener('click', () => {
                    const error = validate();
                    if (error) {
                        if (feedback) {
                            feedback.textContent = error;
                            feedback.classList.remove('hidden');
                        }
                        return;
                    }
                    try {
                        localStorage.setItem(draftKey, JSON.stringify(readDraft()));
                    } catch (e) {}
                    if (feedback) {
                        feedback.textContent = i18n.draft_saved || '';
                        feedback.classList.remove('hidden');
                    }
                });

                const letterModal = root.querySelector('[data-letter-modal]');
                const approvalModal = root.querySelector('[data-approval-modal]');

                root.querySelector('[data-offer-preview-letter]')?.addEventListener('click', () => {
                    updatePreview();
                    openModal(letterModal);
                });
                root.querySelector('[data-close-letter]')?.addEventListener('click', () => closeModal(letterModal));

                root.querySelector('[data-offer-request]')?.addEventListener('click', () => {
                    const error = validate();
                    if (error) {
                        if (feedback) {
                            feedback.textContent = error;
                            feedback.classList.remove('hidden');
                        }
                        return;
                    }
                    openModal(approvalModal);
                });
                root.querySelector('[data-close-approval]')?.addEventListener('click', () => closeModal(approvalModal));
                root.querySelector('[data-confirm-approval]')?.addEventListener('click', () => {
                    closeModal(approvalModal);
                    if (feedback) {
                        feedback.textContent = replacePlaceholders(i18n.approval_requested, {
                            owner: i18n.approver || 'Daniel Brooks',
                        });
                        feedback.classList.remove('hidden');
                    }
                });

                [letterModal, approvalModal].forEach((modal) => {
                    modal?.addEventListener('click', (event) => {
                        if (event.target === modal) closeModal(modal);
                    });
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key !== 'Escape') return;
                    closeModal(letterModal);
                    closeModal(approvalModal);
                });

                updatePreview();
            })();
        </script>
    @endpush
@endsection
