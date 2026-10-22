@extends('layouts.app')

@section('title', $pageTitle ?? __('company.interviews.schedule_page_title'))
@section('breadcrumb', __('company.interviews.schedule_breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ __('company.interviews.schedule_title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">
        {{ __('company.interviews.schedule_subtitle', [
            'name' => $schedule['applicant'],
            'code' => $schedule['job_code'],
            'title' => $schedule['job_title'],
            'stage' => $schedule['stage'],
        ]) }}
    </p>
@endsection

@section('page_actions')
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('company.interviews.index') }}" class="hb-btn-outline">{{ __('company.interviews.back_to_calendar') }}</a>
        <a href="{{ route('company.applicants.show', $schedule['applicant_id']) }}" class="hb-btn-outline">{{ __('company.interviews.cancel') }}</a>
    </div>
@endsection

@section('content')
    @php
        $scheduleI18n = [
            'confirm_feedback' => __('company.interviews.js.confirm_feedback'),
            'conflict_feedback' => __('company.interviews.js.conflict_feedback'),
            'validation_required' => __('company.interviews.js.validation_required'),
            'summary_type' => __('company.interviews.js.summary_type'),
            'summary_interviewer' => __('company.interviews.js.summary_interviewer'),
            'summary_slot' => __('company.interviews.js.summary_slot'),
            'summary_timezone' => __('company.interviews.js.summary_timezone'),
            'summary_empty' => __('company.interviews.summary_empty'),
            'applicant_name' => $schedule['applicant'],
            'timezone' => $schedule['timezone'],
        ];
    @endphp

    <div
        data-company-schedule
        data-i18n="{{ json_encode($scheduleI18n, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-slots="{{ json_encode($schedule['slots'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-applicant-base="{{ route('company.interviews.schedule') }}"
    >
        <x-alert variant="info" class="mb-6" data-schedule-authority>
            <p class="text-sm text-hb-text">{{ $schedule['availability_authority'] ?? __('company.interviews.availability_authority') }}</p>
        </x-alert>
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
            <div class="min-w-0 space-y-6">
                <section class="hb-surface p-5 sm:p-6" aria-labelledby="schedule-form-heading">
                    <h2 id="schedule-form-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.interviews.form_heading') }}</h2>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <label class="block sm:col-span-2">
                            <span class="hb-label">{{ __('company.interviews.applicant_field') }}</span>
                            <select class="hb-input mt-1 w-full" data-schedule-applicant>
                                @foreach ($schedule['applicants'] as $option)
                                    <option value="{{ $option['id'] }}" @selected($option['id'] === $schedule['applicant_id'])>
                                        {{ $option['name'] }} · {{ $option['job_code'] }} · {{ $option['stage'] }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block">
                            <span class="hb-label">{{ __('company.interviews.type_field') }} <span class="text-hb-error">*</span></span>
                            <select class="hb-input mt-1 w-full" data-schedule-type required>
                                @foreach ($schedule['types'] as $type)
                                    <option value="{{ $type['value'] }}" @selected($type['value'] === $schedule['default_type'])>
                                        {{ $type['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block">
                            <span class="hb-label">{{ __('company.interviews.interviewer_field') }} <span class="text-hb-error">*</span></span>
                            <select class="hb-input mt-1 w-full" data-schedule-interviewer required>
                                @foreach ($schedule['interviewers'] as $person)
                                    <option value="{{ $person['value'] }}" @selected($person['value'] === $schedule['default_interviewer'])>
                                        {{ $person['label'] }} · {{ $person['meta'] }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block">
                            <span class="hb-label">{{ __('company.interviews.date_field') }}</span>
                            <select class="hb-input mt-1 w-full" data-schedule-date>
                                @foreach ($schedule['dates'] as $date)
                                    <option value="{{ $date['value'] }}" @selected($date['value'] === $schedule['default_date'])>
                                        {{ $date['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block">
                            <span class="hb-label">{{ __('company.interviews.stage_field') }}</span>
                            <input type="text" class="hb-input mt-1 w-full" value="{{ $schedule['stage'] }}" readonly>
                        </label>

                        <div class="sm:col-span-2">
                            <p class="hb-label">{{ __('company.interviews.slot_field') }} <span class="text-hb-error">*</span></p>
                            <div class="mt-2" data-schedule-slots>
                                <x-slot-picker
                                    name="interview_slot"
                                    :slots="$schedule['slots']"
                                    :value="$schedule['default_slot']"
                                    :label="__('company.interviews.slot_field')"
                                />
                            </div>
                        </div>

                        <div class="sm:col-span-2 rounded-lg border border-hb-border bg-hb-page px-4 py-3 text-sm text-hb-muted">
                            <p><span class="font-semibold text-hb-navy">{{ __('company.interviews.timezone_field') }}:</span> {{ $schedule['timezone'] }}</p>
                            <p class="mt-1"><span class="font-semibold text-hb-navy">{{ __('company.interviews.duration_field') }}:</span> {{ $schedule['duration_default'] }}</p>
                            <p class="mt-1"><span class="font-semibold text-hb-navy">{{ __('company.interviews.format_field') }}:</span> {{ __('company.interviews.format_video') }}</p>
                        </div>

                        <div class="sm:col-span-2">
                            <h3 class="text-sm font-semibold text-hb-navy">{{ __('company.interviews.instructions') }}</h3>
                            <p class="mt-2 text-sm text-hb-muted">{{ $schedule['instructions'] }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-2">
                        <button type="button" class="hb-btn-primary" data-schedule-confirm>{{ __('company.interviews.confirm') }}</button>
                        <a href="{{ route('company.interviews.index') }}" class="hb-btn-outline">{{ __('company.interviews.cancel') }}</a>
                    </div>
                    <p class="mt-3 hidden text-sm text-hb-blue" data-schedule-feedback role="status"></p>
                </section>
            </div>

            <aside>
                <section class="hb-surface p-5" aria-labelledby="summary-heading">
                    <h2 id="summary-heading" class="text-base font-semibold text-hb-navy">{{ __('company.interviews.summary_heading') }}</h2>
                    <ul class="mt-4 space-y-2 text-sm text-hb-text" data-schedule-summary>
                        <li class="text-hb-muted">{{ __('company.interviews.summary_empty') }}</li>
                    </ul>
                </section>
            </aside>
        </div>

        <p class="mt-8 text-center text-xs text-hb-muted">{{ $schedule['footer'] }}</p>
    </div>

    @push('scripts')
        <script>
            (() => {
                const root = document.querySelector('[data-company-schedule]');
                if (!root) return;
                const i18n = JSON.parse(root.getAttribute('data-i18n') || '{}');
                const slots = JSON.parse(root.getAttribute('data-slots') || '[]');
                const applicantSelect = root.querySelector('[data-schedule-applicant]');
                const typeSelect = root.querySelector('[data-schedule-type]');
                const interviewerSelect = root.querySelector('[data-schedule-interviewer]');
                const dateSelect = root.querySelector('[data-schedule-date]');
                const summaryEl = root.querySelector('[data-schedule-summary]');
                const feedback = root.querySelector('[data-schedule-feedback]');
                const base = root.getAttribute('data-applicant-base') || '';

                function replacePlaceholders(template, replacements) {
                    return Object.entries(replacements).reduce(
                        (text, [key, value]) => text.replaceAll(`:${key}`, String(value)),
                        template ?? '',
                    );
                }

                function selectedSlot() {
                    const hidden = root.querySelector('[data-slot-value]');
                    return hidden?.value || '';
                }

                function filterSlotsByDate() {
                    const date = dateSelect?.value || '';
                    root.querySelectorAll('[data-slot-option]').forEach((btn) => {
                        const value = btn.getAttribute('data-slot-value') || '';
                        const slot = slots.find((s) => s.value === value);
                        const show = !date || !slot || slot.date === date;
                        btn.classList.toggle('hidden', !show);
                        btn.hidden = !show;
                    });
                }

                function updateSummary() {
                    const typeLabel = typeSelect?.selectedOptions?.[0]?.textContent?.trim() || '';
                    const interviewerLabel = interviewerSelect?.selectedOptions?.[0]?.textContent?.trim() || '';
                    const slotValue = selectedSlot();
                    const slot = slots.find((s) => s.value === slotValue);
                    if (!typeLabel || !interviewerLabel || !slot) {
                        if (summaryEl) {
                            summaryEl.innerHTML = `<li class="text-hb-muted">${i18n.summary_empty || ''}</li>`;
                        }
                        return;
                    }
                    if (summaryEl) {
                        summaryEl.innerHTML = [
                            replacePlaceholders(i18n.summary_type, { value: typeLabel }),
                            replacePlaceholders(i18n.summary_interviewer, { value: interviewerLabel }),
                            replacePlaceholders(i18n.summary_slot, { value: slot.label || slotValue }),
                            replacePlaceholders(i18n.summary_timezone, { value: i18n.timezone || '' }),
                        ].map((line) => `<li>${line}</li>`).join('');
                    }
                }

                applicantSelect?.addEventListener('change', () => {
                    if (!base) return;
                    const url = new URL(base, window.location.origin);
                    url.searchParams.set('applicant', applicantSelect.value);
                    window.location.assign(url.toString());
                });

                [typeSelect, interviewerSelect, dateSelect].forEach((el) => {
                    el?.addEventListener('change', () => {
                        filterSlotsByDate();
                        updateSummary();
                    });
                });

                root.addEventListener('click', (event) => {
                    if (event.target.closest('[data-slot-option]')) {
                        setTimeout(updateSummary, 0);
                    }
                });

                root.querySelector('[data-schedule-confirm]')?.addEventListener('click', () => {
                    const slotValue = selectedSlot();
                    const ok = typeSelect?.value && interviewerSelect?.value && slotValue;
                    if (!ok) {
                        if (feedback) {
                            feedback.textContent = i18n.validation_required || '';
                            feedback.classList.remove('hidden', 'text-hb-error');
                            feedback.classList.add('text-hb-blue');
                        }
                        return;
                    }
                    const slot = slots.find((s) => s.value === slotValue);
                    if (slot?.conflict_on_confirm || slot?.state === 'conflict') {
                        if (feedback) {
                            feedback.textContent = i18n.conflict_feedback || '';
                            feedback.classList.remove('hidden', 'text-hb-blue');
                            feedback.classList.add('text-hb-error');
                        }
                        return;
                    }
                    if (feedback) {
                        feedback.textContent = replacePlaceholders(i18n.confirm_feedback, {
                            name: i18n.applicant_name || 'candidate',
                        });
                        feedback.classList.remove('hidden', 'text-hb-error');
                        feedback.classList.add('text-hb-blue');
                    }
                });

                filterSlotsByDate();
                updateSummary();
            })();
        </script>
    @endpush
@endsection
