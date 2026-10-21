@extends('layouts.app')

@section('title', $pageTitle ?? __('company.jobs.index_page_title'))
@section('breadcrumb', __('company.jobs.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ $jobsPage['title'] }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ $jobsPage['intro'] }}</p>
@endsection

@section('page_actions')
    <a href="{{ route('company.jobs.create') }}" class="hb-btn-primary">{{ __('company.jobs.create') }}</a>
@endsection

@section('content')
    @php
        $jobsI18n = [
            'view_workspace' => __('company.jobs.js.view_workspace'),
            'showing_range' => __('company.jobs.js.showing_range'),
            'showing_zero_label' => __('company.jobs.js.showing_zero_label'),
            'requisitions' => __('company.jobs.js.requisitions'),
            'tab_requisitions' => __('company.jobs.js.tab_requisitions'),
            'filter_count_one' => __('company.jobs.js.filter_count_one'),
            'filter_count_many' => __('company.jobs.js.filter_count_many'),
            'previous' => __('common.actions.previous'),
            'next' => __('common.actions.next'),
        ];
    @endphp
    <div
        data-company-jobs
        data-page-size="3"
        data-jobs="{{ json_encode($jobsPage['jobs'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-i18n="{{ json_encode($jobsI18n, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
    >

        {{-- Status tabs --}}
        <div class="flex flex-wrap gap-4 border-b border-hb-border text-sm font-semibold" role="tablist" aria-label="{{ __('company.jobs.tablist_aria') }}">
            @foreach ($jobsPage['tabs'] as $tab)
                <button
                    type="button"
                    role="tab"
                    id="jobs-tab-{{ $tab['key'] }}"
                    aria-selected="{{ $tab['key'] === $jobsPage['default_tab'] ? 'true' : 'false' }}"
                    data-jobs-tab="{{ $tab['key'] }}"
                    class="border-b-2 px-1 pb-3 {{ $tab['key'] === $jobsPage['default_tab'] ? 'border-hb-blue text-hb-blue' : 'border-transparent text-hb-muted hover:text-hb-navy' }}"
                >
                    {{ $tab['label'] }} ({{ $tab['count'] }})
                </button>
            @endforeach
        </div>

        {{-- Search & filters --}}
        <div class="mt-5 flex flex-col gap-3 lg:flex-row lg:items-center">
            <label class="relative min-w-0 flex-1">
                <span class="sr-only">{{ __('company.jobs.search_placeholder') }}</span>
                <span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-hb-muted" aria-hidden="true">⌕</span>
                <input
                    type="search"
                    class="hb-input ps-9"
                    placeholder="{{ __('company.jobs.search_placeholder') }}"
                    data-jobs-q
                    autocomplete="off"
                >
            </label>
            <label class="shrink-0">
                <span class="sr-only">{{ __('company.jobs.filter_owner') }}</span>
                <select class="hb-input min-w-40" data-jobs-owner aria-label="{{ __('company.jobs.filter_owner') }}">
                    <option value="">{{ __('company.jobs.all_owners') }}</option>
                    @foreach ($jobsPage['owners'] as $owner)
                        <option value="{{ $owner }}">{{ $owner }}</option>
                    @endforeach
                </select>
            </label>
            <label class="shrink-0">
                <span class="sr-only">{{ __('company.jobs.filter_model') }}</span>
                <select class="hb-input min-w-44" data-jobs-model aria-label="{{ __('company.jobs.filter_model') }}">
                    <option value="">{{ __('company.jobs.all_models') }}</option>
                    @foreach ($jobsPage['models'] as $model)
                        <option value="{{ $model['key'] }}">{{ $model['label'] }}</option>
                    @endforeach
                </select>
            </label>
            <label class="shrink-0">
                <span class="sr-only">{{ __('company.jobs.sort') }}</span>
                <select class="hb-input min-w-40" data-jobs-sort aria-label="{{ __('company.jobs.sort') }}">
                    <option value="code">{{ __('company.jobs.sort_code') }}</option>
                    <option value="title">{{ __('company.jobs.sort_title') }}</option>
                    <option value="pipeline">{{ __('company.jobs.sort_pipeline') }}</option>
                    <option value="owner">{{ __('company.jobs.sort_owner') }}</option>
                </select>
            </label>
            <button type="button" class="text-sm font-semibold text-hb-blue hover:underline" data-jobs-clear>
                {{ __('company.jobs.clear_filters') }}
            </button>
        </div>

        <p class="mt-3 hidden text-sm text-hb-muted" data-jobs-filter-note role="status"></p>

        {{-- Table --}}
        <div class="mt-5 overflow-hidden rounded-xl border border-hb-border bg-hb-surface">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[44rem] text-start text-sm">
                    <caption class="sr-only">{{ __('company.jobs.table_caption') }}</caption>
                    <thead>
                        <tr class="border-b border-hb-border text-xs font-semibold tracking-wide text-hb-muted uppercase">
                            <th scope="col" class="px-5 py-3 font-semibold">{{ __('company.jobs.col_requisition') }}</th>
                            <th scope="col" class="px-4 py-3 font-semibold">{{ __('company.jobs.col_owner_team') }}</th>
                            <th scope="col" class="px-4 py-3 font-semibold">{{ __('company.jobs.col_model') }}</th>
                            <th scope="col" class="px-4 py-3 font-semibold">{{ __('company.jobs.col_pipeline') }}</th>
                            <th scope="col" class="px-5 py-3 font-semibold">{{ __('company.jobs.col_status_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody data-jobs-tbody class="divide-y divide-hb-border"></tbody>
                </table>
            </div>

            <div
                class="hidden border-t border-hb-border px-5 py-10 text-center"
                data-jobs-empty
                hidden
            >
                <p class="font-semibold text-hb-navy">{{ __('company.jobs.empty_title') }}</p>
                <p class="mt-1 text-sm text-hb-muted">{{ __('company.jobs.empty_body') }}</p>
                <button type="button" class="hb-btn-outline mt-4" data-jobs-clear>{{ __('company.jobs.clear_filters') }}</button>
            </div>

            <div class="flex flex-col gap-3 border-t border-hb-border px-5 py-4 sm:flex-row sm:items-center sm:justify-between" data-jobs-pager>
                <p class="text-sm text-hb-muted" data-jobs-showing>{{ __('company.jobs.showing_zero') }}</p>
                <div class="flex flex-wrap items-center gap-2" data-jobs-pages></div>
            </div>
        </div>

        {{-- Bottom cards --}}
        <div class="mt-6 grid gap-4 lg:grid-cols-2">
            <section class="hb-surface p-5" aria-labelledby="draft-review-heading">
                <h2 id="draft-review-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.jobs.draft_awaiting_review') }}</h2>
                <p class="mt-3">
                    <x-badge variant="pending" :dot="true">{{ __('company.jobs.draft_not_published') }}</x-badge>
                </p>
                <p class="mt-3 font-semibold text-hb-navy">
                    {{ $jobsPage['draft_card']['code'] }} · {{ $jobsPage['draft_card']['title'] }}
                </p>
                <p class="mt-1 text-sm text-hb-muted">
                    {{ __('company.jobs.owner_last_edited', ['owner' => $jobsPage['draft_card']['owner'], 'date' => $jobsPage['draft_card']['last_edited']]) }}
                </p>
                <p class="mt-2 text-sm text-hb-pending">{{ $jobsPage['draft_card']['alert'] }}</p>
                <a href="{{ route('company.jobs.create') }}" class="hb-btn-outline mt-4 inline-flex">
                    {{ __('company.jobs.continue_editing') }}
                </a>
            </section>

            <section class="hb-surface p-5" aria-labelledby="ownership-heading">
                <h2 id="ownership-heading" class="text-lg font-semibold text-hb-navy">
                    {{ $jobsPage['permissions_card']['title'] }}
                </h2>
                <p class="mt-3 text-sm leading-relaxed text-hb-muted">
                    {{ $jobsPage['permissions_card']['body'] }}
                </p>
                <button
                    type="button"
                    class="mt-4 text-sm font-semibold text-hb-blue hover:underline"
                    disabled
                    title="{{ __('company.jobs.team_soon') }}"
                >
                    {{ __('company.jobs.review_permissions') }}
                </button>
            </section>
        </div>

        <p class="mt-8 text-xs text-hb-muted">{{ $jobsPage['footer'] }}</p>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const root = document.querySelector('[data-company-jobs]');
            if (!root) return;

            const i18n = JSON.parse(root.getAttribute('data-i18n') || '{}');
            const jobs = JSON.parse(root.getAttribute('data-jobs') || '[]');
            const pageSize = Number(root.getAttribute('data-page-size') || 3);
            const tbody = root.querySelector('[data-jobs-tbody]');
            const empty = root.querySelector('[data-jobs-empty]');
            const showing = root.querySelector('[data-jobs-showing]');
            const pagesEl = root.querySelector('[data-jobs-pages]');
            const note = root.querySelector('[data-jobs-filter-note]');
            const qInput = root.querySelector('[data-jobs-q]');
            const ownerSelect = root.querySelector('[data-jobs-owner]');
            const modelSelect = root.querySelector('[data-jobs-model]');
            const sortSelect = root.querySelector('[data-jobs-sort]');
            const tabs = [...root.querySelectorAll('[data-jobs-tab]')];

            let tab = 'open';
            let page = 1;

            const showUrl = (id) => @json(url('/company/jobs')).replace(/\/$/, '') + '/' + encodeURIComponent(id);

            function replacePlaceholders(template, replacements) {
                return Object.entries(replacements).reduce(
                    (text, [key, value]) => text.replaceAll(`:${key}`, String(value)),
                    template ?? '',
                );
            }

            function tabLabel() {
                return tab === 'all'
                    ? i18n.requisitions
                    : replacePlaceholders(i18n.tab_requisitions, { tab });
            }

            function filtered() {
                const q = (qInput?.value || '').trim().toLowerCase();
                const owner = ownerSelect?.value || '';
                const model = modelSelect?.value || '';
                let rows = jobs.filter((job) => {
                    if (tab !== 'all' && (job.status_key || '') !== tab) return false;
                    if (owner && job.owner !== owner) return false;
                    if (model && job.model_key !== model) return false;
                    if (!q) return true;
                    const hay = `${job.code || ''} ${job.title || ''}`.toLowerCase();
                    return hay.includes(q);
                });

                const sort = sortSelect?.value || 'code';
                rows = [...rows].sort((a, b) => {
                    if (sort === 'pipeline') return (b.pipeline_total || 0) - (a.pipeline_total || 0);
                    if (sort === 'title') return String(a.title || '').localeCompare(String(b.title || ''));
                    if (sort === 'owner') return String(a.owner || '').localeCompare(String(b.owner || ''));
                    return String(a.code || '').localeCompare(String(b.code || ''));
                });
                return rows;
            }

            function render() {
                const rows = filtered();
                const totalPages = Math.max(1, Math.ceil(rows.length / pageSize) || 1);
                if (page > totalPages) page = totalPages;
                const start = (page - 1) * pageSize;
                const slice = rows.slice(start, start + pageSize);
                const label = tabLabel();

                if (tbody) {
                    tbody.innerHTML = slice.map((job) => `
                        <tr data-job-row>
                            <td class="px-5 py-4 align-top">
                                <p class="font-semibold text-hb-navy">${escapeHtml(job.code)}</p>
                                <p class="mt-0.5 text-hb-muted">${escapeHtml(job.title)}</p>
                            </td>
                            <td class="px-4 py-4 align-top">
                                <p class="text-hb-text">${escapeHtml(job.owner)}</p>
                                <p class="mt-0.5 text-hb-muted">${escapeHtml(job.team)}</p>
                            </td>
                            <td class="px-4 py-4 align-top text-hb-muted">${escapeHtml(job.model)}</td>
                            <td class="px-4 py-4 align-top text-hb-muted">${escapeHtml(job.pipeline)}</td>
                            <td class="px-5 py-4 align-top">
                                <p class="text-hb-navy">${escapeHtml(job.status)}</p>
                                <a href="${showUrl(job.id)}" class="mt-1 inline-block text-sm font-semibold text-hb-blue hover:underline">${escapeHtml(i18n.view_workspace)}</a>
                            </td>
                        </tr>
                    `).join('');
                }

                const hasRows = slice.length > 0;
                if (empty) {
                    empty.classList.toggle('hidden', hasRows);
                    empty.hidden = hasRows;
                    empty.setAttribute('aria-hidden', hasRows ? 'true' : 'false');
                }
                if (tbody) {
                    tbody.classList.toggle('hidden', !hasRows);
                    tbody.setAttribute('aria-hidden', hasRows ? 'false' : 'true');
                }

                if (showing) {
                    showing.textContent = rows.length
                        ? replacePlaceholders(i18n.showing_range, {
                            start: start + 1,
                            end: start + slice.length,
                            total: rows.length,
                            label,
                        })
                        : replacePlaceholders(i18n.showing_zero_label, { label });
                }

                if (pagesEl) {
                    const prevDisabled = page <= 1;
                    const nextDisabled = page >= totalPages || rows.length === 0;
                    let html = `<button type="button" class="rounded-lg border border-hb-border px-3 py-1.5 text-sm ${prevDisabled ? 'cursor-not-allowed text-hb-muted' : 'text-hb-navy hover:bg-hb-page'}" data-jobs-page="prev" ${prevDisabled ? 'disabled' : ''}>${escapeHtml(i18n.previous)}</button>`;
                    for (let i = 1; i <= totalPages; i++) {
                        const active = i === page;
                        html += `<button type="button" class="min-w-9 rounded-lg border px-3 py-1.5 text-sm font-semibold ${active ? 'border-hb-blue bg-hb-blue text-white' : 'border-hb-border text-hb-navy hover:bg-hb-page'}" data-jobs-page="${i}" aria-current="${active ? 'page' : 'false'}">${i}</button>`;
                    }
                    html += `<button type="button" class="rounded-lg border border-hb-border px-3 py-1.5 text-sm ${nextDisabled ? 'cursor-not-allowed text-hb-muted' : 'text-hb-navy hover:bg-hb-page'}" data-jobs-page="next" ${nextDisabled ? 'disabled' : ''}>${escapeHtml(i18n.next)}</button>`;
                    pagesEl.innerHTML = html;
                }

                if (note) {
                    const activeFilters = [qInput?.value, ownerSelect?.value, modelSelect?.value].some(Boolean);
                    if (activeFilters) {
                        const template = rows.length === 1 ? i18n.filter_count_one : i18n.filter_count_many;
                        note.textContent = replacePlaceholders(template, { count: rows.length });
                        note.classList.remove('hidden');
                    } else {
                        note.classList.add('hidden');
                    }
                }
            }

            function escapeHtml(value) {
                return String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;');
            }

            function setTab(next) {
                tab = next;
                page = 1;
                tabs.forEach((btn) => {
                    const active = btn.getAttribute('data-jobs-tab') === tab;
                    btn.setAttribute('aria-selected', active ? 'true' : 'false');
                    btn.classList.toggle('border-hb-blue', active);
                    btn.classList.toggle('text-hb-blue', active);
                    btn.classList.toggle('border-transparent', !active);
                    btn.classList.toggle('text-hb-muted', !active);
                });
                render();
            }

            tabs.forEach((btn) => btn.addEventListener('click', () => setTab(btn.getAttribute('data-jobs-tab'))));
            [qInput, ownerSelect, modelSelect, sortSelect].forEach((el) => {
                el?.addEventListener('input', () => { page = 1; render(); });
                el?.addEventListener('change', () => { page = 1; render(); });
            });

            root.querySelectorAll('[data-jobs-clear]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    if (qInput) qInput.value = '';
                    if (ownerSelect) ownerSelect.value = '';
                    if (modelSelect) modelSelect.value = '';
                    if (sortSelect) sortSelect.value = 'code';
                    page = 1;
                    render();
                });
            });

            pagesEl?.addEventListener('click', (event) => {
                const btn = event.target.closest('[data-jobs-page]');
                if (!btn || btn.disabled) return;
                const value = btn.getAttribute('data-jobs-page');
                const totalPages = Math.max(1, Math.ceil(filtered().length / pageSize) || 1);
                if (value === 'prev') page = Math.max(1, page - 1);
                else if (value === 'next') page = Math.min(totalPages, page + 1);
                else page = Number(value) || 1;
                render();
            });

            setTab('open');
        })();
    </script>
@endpush
