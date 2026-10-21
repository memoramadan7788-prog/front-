@extends('layouts.app')

@section('title', $pageTitle ?? __('company.interviews.page_title'))
@section('breadcrumb', __('company.interviews.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ __('company.interviews.title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ __('company.interviews.subtitle') }}</p>
@endsection

@section('page_actions')
    <a href="{{ route('company.interviews.schedule') }}" class="hb-btn-primary">
        {{ __('company.interviews.schedule_interview') }}
    </a>
@endsection

@section('content')
    @php
        $calendarI18n = [
            'week_nav_status' => __('company.interviews.js.week_nav_status'),
            'empty_search' => __('company.interviews.js.empty_search'),
            'reschedule_feedback' => __('company.interviews.js.reschedule_feedback'),
        ];
        $selected = $calendar['selected'] ?? null;
    @endphp

    <div
        data-company-calendar
        data-i18n="{{ json_encode($calendarI18n, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-interviews="{{ json_encode($calendar['interviews'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-week-label="{{ $calendar['week_label'] }}"
        data-selected-id="{{ $selected['id'] ?? '' }}"
    >
        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center">
            <label class="relative min-w-0 flex-1">
                <span class="sr-only">{{ __('company.interviews.search_placeholder') }}</span>
                <span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-hb-muted" aria-hidden="true">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.473 9.8l3.614 3.613a.75.75 0 1 0 1.06-1.06l-3.613-3.614A5.5 5.5 0 0 0 9 3.5ZM5.5 9a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0Z" clip-rule="evenodd"/></svg>
                </span>
                <input type="search" data-cal-q class="hb-input w-full ps-9" placeholder="{{ __('company.interviews.search_placeholder') }}" autocomplete="off">
            </label>
            <div class="flex flex-wrap gap-2">
                <label class="sr-only" for="cal-job">{{ __('company.interviews.all_jobs') }}</label>
                <select id="cal-job" data-cal-job class="hb-input w-auto min-w-[9rem]">
                    <option value="">{{ __('company.interviews.all_jobs') }}</option>
                    @foreach ($calendar['jobs'] as $job)
                        <option value="{{ $job['id'] }}">{{ $job['code'] }}</option>
                    @endforeach
                </select>
                <label class="sr-only" for="cal-dept">{{ __('company.interviews.department') }}</label>
                <select id="cal-dept" data-cal-dept class="hb-input w-auto min-w-[10rem]">
                    @foreach ($calendar['departments'] as $dept)
                        <option value="{{ $dept }}" @selected($dept === $calendar['department_filter'])>{{ $dept }}</option>
                    @endforeach
                </select>
                <button type="button" class="hb-btn-outline" data-cal-clear>{{ __('company.interviews.clear_filters') }}</button>
            </div>
        </div>

        <p class="mb-4 hidden text-sm text-hb-muted" data-cal-empty role="status">{{ __('company.interviews.empty_search') }}</p>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
            <div class="min-w-0 space-y-6">
                <section class="hb-surface p-5 sm:p-6" aria-labelledby="week-heading">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 id="week-heading" class="text-base font-semibold text-hb-navy" data-week-heading>{{ $calendar['week_label'] }}</h2>
                            <p class="mt-1 text-xs text-hb-muted">{{ $calendar['timezone'] }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2" role="group" aria-label="{{ __('company.interviews.week_nav_aria') }}">
                            <button type="button" class="hb-btn-outline px-3 py-2" data-week-nav="prev" aria-label="{{ __('company.interviews.previous_week') }}">‹</button>
                            <button type="button" class="hb-btn-outline px-3 py-2" data-week-today>{{ __('company.interviews.today') }}</button>
                            <button type="button" class="hb-btn-outline px-3 py-2" data-week-nav="next" aria-label="{{ __('company.interviews.next_week') }}">›</button>
                        </div>
                    </div>
                    <p class="mt-2 hidden text-xs text-hb-muted" data-week-nav-status role="status"></p>

                    <div class="mt-5 grid gap-2 sm:grid-cols-5" data-cal-grid>
                        @foreach ($calendar['days'] as $day)
                            <div
                                @class([
                                    'rounded-lg border px-2 py-3 text-sm',
                                    'border-hb-blue bg-hb-info-soft/40' => ! empty($day['highlight']),
                                    'border-hb-border bg-hb-surface' => empty($day['highlight']),
                                ])
                                data-cal-day="{{ $day['key'] }}"
                            >
                                <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ $day['label'] }}</p>
                                <ul class="mt-2 space-y-2">
                                    @forelse ($day['slots'] ?? [] as $slot)
                                        <li>
                                            @if (! empty($slot['interview_id']))
                                                <button
                                                    type="button"
                                                    class="w-full rounded-md border border-hb-border bg-hb-page px-2 py-1.5 text-start hover:border-hb-blue"
                                                    data-select-interview="{{ $slot['interview_id'] }}"
                                                >
                                                    <span class="block text-[11px] font-medium text-hb-blue">{{ $slot['time'] }}</span>
                                                    <span class="mt-0.5 block text-xs text-hb-navy">{{ $slot['label'] }}</span>
                                                </button>
                                            @else
                                                <p class="rounded-md px-2 py-1.5 text-xs text-hb-muted">
                                                    <span class="font-medium text-hb-text">{{ $slot['time'] }}</span>
                                                    · {{ $slot['label'] }}
                                                </p>
                                            @endif
                                        </li>
                                    @empty
                                        <li class="text-xs text-hb-muted">{{ __('company.interviews.empty_day') }}</li>
                                    @endforelse
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="hb-surface p-5 sm:p-6" aria-labelledby="availability-heading">
                    <h2 id="availability-heading" class="text-base font-semibold text-hb-navy">{{ __('company.interviews.availability_heading') }}</h2>
                    <ul class="mt-4 space-y-3 text-sm">
                        @foreach ($calendar['availability'] as $row)
                            <li>
                                <span class="font-semibold text-hb-navy">{{ $row['name'] }}</span>
                                <span class="text-hb-muted"> · {{ $row['windows'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-4 text-xs text-hb-muted">{{ $calendar['availability_note'] }}</p>
                </section>
            </div>

            <aside>
                <section class="hb-surface p-5" aria-labelledby="selected-interview-heading" data-selected-panel>
                    <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('company.interviews.selected_heading') }}</p>
                    @if ($selected)
                        <div class="mt-3">
                            <x-badge variant="info" :dot="true" data-sel-status>{{ $selected['status'] }}</x-badge>
                        </div>
                        <h2 id="selected-interview-heading" class="mt-3 text-lg font-semibold text-hb-navy" data-sel-name>{{ $selected['applicant'] }}</h2>
                        <dl class="mt-4 space-y-2 text-sm">
                            <div>
                                <dt class="text-xs text-hb-muted">{{ __('company.interviews.job_label') }}</dt>
                                <dd class="font-medium text-hb-text" data-sel-job>{{ $selected['title'] }} · {{ $selected['job_code'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-hb-muted">{{ __('company.interviews.when_label') }}</dt>
                                <dd class="text-hb-text" data-sel-when>{{ $selected['when'] }} · {{ $selected['time'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-hb-muted">{{ __('company.interviews.interviewer_label') }}</dt>
                                <dd class="text-hb-text" data-sel-interviewer>{{ $selected['interviewer'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-hb-muted">{{ __('company.interviews.organizer_label') }}</dt>
                                <dd class="text-hb-text" data-sel-organizer>{{ $selected['organizer'] }}</dd>
                            </div>
                        </dl>
                        <ul class="mt-4 space-y-2 text-sm" data-sel-checklist>
                            @foreach ($selected['checklist'] ?? [] as $item)
                                <li class="flex items-start gap-2 text-hb-muted">
                                    <span aria-hidden="true">{{ ! empty($item['done']) ? '✓' : '○' }}</span>
                                    <span>{{ $item['label'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-5 flex flex-col gap-2">
                            <a href="{{ route('company.interviews.show', $selected['id']) }}" class="hb-btn-primary text-center" data-sel-details>
                                {{ __('company.interviews.view_details') }}
                            </a>
                            <button type="button" class="hb-btn-outline" data-reschedule>
                                {{ __('company.interviews.reschedule') }}
                            </button>
                            <p class="hidden text-xs text-hb-blue" data-reschedule-feedback role="status"></p>
                        </div>
                    @endif
                </section>
            </aside>
        </div>

        <p class="mt-8 text-center text-xs text-hb-muted">{{ $calendar['footer'] }}</p>
    </div>

    @push('scripts')
        <script>
            (() => {
                const root = document.querySelector('[data-company-calendar]');
                if (!root) return;
                const i18n = JSON.parse(root.getAttribute('data-i18n') || '{}');
                const interviews = JSON.parse(root.getAttribute('data-interviews') || '[]');
                const weekLabel = root.getAttribute('data-week-label') || '';
                let selectedId = root.getAttribute('data-selected-id') || '';
                const qInput = root.querySelector('[data-cal-q]');
                const jobSelect = root.querySelector('[data-cal-job]');
                const deptSelect = root.querySelector('[data-cal-dept]');
                const emptyEl = root.querySelector('[data-cal-empty]');
                const statusEl = root.querySelector('[data-week-nav-status]');

                function replacePlaceholders(template, replacements) {
                    return Object.entries(replacements).reduce(
                        (text, [key, value]) => text.replaceAll(`:${key}`, String(value)),
                        template ?? '',
                    );
                }

                function applyFilters() {
                    const q = (qInput?.value || '').trim().toLowerCase();
                    const job = jobSelect?.value || '';
                    const dept = deptSelect?.value || '';
                    let visible = 0;
                    interviews.forEach((row) => {
                        const matchQ = !q || `${row.applicant || ''} ${row.interviewer || ''}`.toLowerCase().includes(q);
                        const matchJob = !job || row.job_id === job;
                        const matchDept = !dept || row.department === dept;
                        const show = matchQ && matchJob && matchDept;
                        root.querySelectorAll(`[data-select-interview="${row.id}"]`).forEach((btn) => {
                            const li = btn.closest('li');
                            if (li) {
                                li.classList.toggle('hidden', !show);
                                li.hidden = !show;
                            }
                            if (show) visible += 1;
                        });
                    });
                    if (emptyEl) {
                        emptyEl.classList.toggle('hidden', visible > 0);
                    }
                }

                function renderSelected(id) {
                    const row = interviews.find((i) => i.id === id);
                    if (!row) return;
                    selectedId = id;
                    const set = (sel, text) => {
                        const el = root.querySelector(sel);
                        if (el) el.textContent = text;
                    };
                    set('[data-sel-status]', row.status);
                    set('[data-sel-name]', row.applicant);
                    set('[data-sel-job]', `${row.title} · ${row.job_code}`);
                    set('[data-sel-when]', `${row.when} · ${row.time}`);
                    set('[data-sel-interviewer]', row.interviewer);
                    set('[data-sel-organizer]', row.organizer);
                    const details = root.querySelector('[data-sel-details]');
                    if (details) details.setAttribute('href', `/company/interviews/${encodeURIComponent(row.id)}`);
                    const checklist = root.querySelector('[data-sel-checklist]');
                    if (checklist && Array.isArray(row.checklist)) {
                        checklist.innerHTML = row.checklist.map((item) => `
                            <li class="flex items-start gap-2 text-hb-muted">
                                <span aria-hidden="true">${item.done ? '✓' : '○'}</span>
                                <span>${item.label || ''}</span>
                            </li>
                        `).join('');
                    }
                }

                root.querySelectorAll('[data-select-interview]').forEach((btn) => {
                    btn.addEventListener('click', () => renderSelected(btn.getAttribute('data-select-interview')));
                });

                [qInput, jobSelect, deptSelect].forEach((el) => {
                    el?.addEventListener('input', applyFilters);
                    el?.addEventListener('change', applyFilters);
                });

                root.querySelector('[data-cal-clear]')?.addEventListener('click', () => {
                    if (qInput) qInput.value = '';
                    if (jobSelect) jobSelect.value = '';
                    if (deptSelect) deptSelect.value = 'Product & Design';
                    applyFilters();
                });

                root.querySelectorAll('[data-week-nav], [data-week-today]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        if (statusEl) {
                            statusEl.textContent = replacePlaceholders(i18n.week_nav_status, { label: weekLabel });
                            statusEl.classList.remove('hidden');
                        }
                    });
                });

                root.querySelector('[data-reschedule]')?.addEventListener('click', () => {
                    const feedback = root.querySelector('[data-reschedule-feedback]');
                    if (feedback) {
                        feedback.textContent = i18n.reschedule_feedback || '';
                        feedback.classList.remove('hidden');
                    }
                });

                applyFilters();
            })();
        </script>
    @endpush
@endsection
