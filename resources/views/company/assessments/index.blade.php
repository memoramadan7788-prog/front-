@extends('layouts.app')

@section('title', $pageTitle ?? __('company.assessments.page_title'))
@section('breadcrumb', __('company.assessments.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ __('company.assessments.title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ __('company.assessments.subtitle') }}</p>
@endsection

@section('page_actions')
    <a href="{{ route('company.assessments.create') }}" class="hb-btn-primary">{{ __('company.assessments.create') }}</a>
@endsection

@section('content')
    @php
        $listI18n = [
            'empty' => __('company.assessments.js.empty'),
            'meta_line' => __('company.assessments.meta_line'),
            'assigned' => __('company.assessments.assigned'),
            'completed' => __('company.assessments.completed'),
            'view' => __('company.assessments.view'),
            'view_results' => __('company.assessments.view_results'),
        ];
    @endphp

    <div
        data-company-assessments
        data-i18n="{{ json_encode($listI18n, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-items="{{ json_encode($catalog['items'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-show-base="{{ url('/company/assessments') }}"
    >
        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center">
            <label class="relative min-w-0 flex-1">
                <span class="sr-only">{{ __('company.assessments.search_placeholder') }}</span>
                <input type="search" data-assess-q class="hb-input w-full" placeholder="{{ __('company.assessments.search_placeholder') }}" autocomplete="off">
            </label>
            <div class="flex flex-wrap gap-2">
                <label class="sr-only" for="assess-status">{{ __('company.assessments.all_status') }}</label>
                <select id="assess-status" data-assess-status class="hb-input w-auto min-w-[9rem]">
                    <option value="">{{ __('company.assessments.all_status') }}</option>
                    <option value="active">{{ __('company.assessments.status_active') }}</option>
                    <option value="draft">{{ __('company.assessments.status_draft') }}</option>
                    <option value="pending_approval">{{ __('company.assessments.status_pending_approval') }}</option>
                </select>
                <button type="button" class="hb-btn-outline" data-assess-clear>{{ __('company.assessments.clear_filters') }}</button>
            </div>
        </div>

        <section class="hb-surface overflow-hidden">
            <ul class="divide-y divide-hb-border" data-assess-list></ul>
            <p class="hidden px-5 py-10 text-center text-sm text-hb-muted" data-assess-empty>{{ __('company.assessments.empty') }}</p>
        </section>

        <p class="mt-8 text-center text-xs text-hb-muted">{{ $catalog['footer'] }}</p>
    </div>

    @push('scripts')
        <script>
            (() => {
                const root = document.querySelector('[data-company-assessments]');
                if (!root) return;
                const i18n = JSON.parse(root.getAttribute('data-i18n') || '{}');
                const items = JSON.parse(root.getAttribute('data-items') || '[]');
                const showBase = root.getAttribute('data-show-base') || '/company/assessments';
                const listEl = root.querySelector('[data-assess-list]');
                const emptyEl = root.querySelector('[data-assess-empty]');
                const qInput = root.querySelector('[data-assess-q]');
                const statusSelect = root.querySelector('[data-assess-status]');

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

                function filtered() {
                    const q = (qInput?.value || '').trim().toLowerCase();
                    const status = statusSelect?.value || '';
                    return items.filter((row) => {
                        if (status && row.status_key !== status) return false;
                        if (!q) return true;
                        const hay = `${row.title || ''} ${row.job_code || ''} ${row.job_title || ''}`.toLowerCase();
                        return hay.includes(q);
                    });
                }

                function render() {
                    const rows = filtered();
                    if (listEl) {
                        listEl.innerHTML = rows.map((row) => `
                            <li class="px-5 py-4">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-hb-navy">${escapeHtml(row.title)} · ${escapeHtml(row.status)}</p>
                                        <p class="mt-1 text-sm text-hb-muted">
                                            ${escapeHtml(replacePlaceholders(i18n.meta_line, {
                                                code: row.job_code || '',
                                                job: row.job_title || '',
                                                questions: row.questions_count || 0,
                                                minutes: row.time_limit_min || 0,
                                                pass: row.passing_score || 0,
                                            }))}
                                        </p>
                                        <p class="mt-1 text-sm text-hb-muted">
                                            ${escapeHtml(replacePlaceholders(i18n.assigned, { count: row.assigned_count || 0 }))} ·
                                            ${escapeHtml(replacePlaceholders(i18n.completed, { count: row.completed_count || 0 }))}
                                        </p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <a href="${escapeHtml(showBase)}/${escapeHtml(row.id)}" class="hb-btn-outline text-sm">${escapeHtml(i18n.view || '')}</a>
                                        <a href="${escapeHtml(showBase)}/${escapeHtml(row.id)}/results" class="hb-btn-outline text-sm">${escapeHtml(i18n.view_results || '')}</a>
                                    </div>
                                </div>
                            </li>
                        `).join('');
                    }
                    const hasRows = rows.length > 0;
                    emptyEl?.classList.toggle('hidden', hasRows);
                    if (emptyEl) emptyEl.hidden = hasRows;
                    listEl?.classList.toggle('hidden', !hasRows);
                }

                [qInput, statusSelect].forEach((el) => {
                    el?.addEventListener('input', render);
                    el?.addEventListener('change', render);
                });
                root.querySelector('[data-assess-clear]')?.addEventListener('click', () => {
                    if (qInput) qInput.value = '';
                    if (statusSelect) statusSelect.value = '';
                    render();
                });
                render();
            })();
        </script>
    @endpush
@endsection
