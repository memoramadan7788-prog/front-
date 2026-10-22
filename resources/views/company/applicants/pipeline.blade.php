@extends('layouts.app')

@section('title', $pageTitle ?? __('company.applicants.page_title'))
@section('breadcrumb', __('company.applicants.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ __('company.applicants.title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted" data-pipeline-subtitle>
        {{ __('company.applicants.subtitle', [
            'code' => $pipeline['job_code'],
            'title' => $pipeline['job_title'],
            'count' => $pipeline['candidate_count'],
            'owner' => $pipeline['owner'],
        ]) }}
    </p>
@endsection

@section('page_actions')
    <button type="button" class="hb-btn-primary" data-open-add-candidate>
        {{ __('company.applicants.add_candidate') }}
    </button>
@endsection

@section('content')
    @php
        $pipelineI18n = [
            'empty_board' => __('company.applicants.js.empty_board'),
            'empty_column' => __('company.applicants.js.empty_column'),
            'filter_note' => __('company.applicants.js.filter_note'),
            'add_feedback' => __('company.applicants.js.add_feedback'),
            'applied_by' => __('company.applicants.js.applied_by'),
            'view_profile' => __('company.applicants.view_profile'),
        ];
        $summary = $pipeline['summary'];
    @endphp

    <div
        data-company-pipeline
        data-i18n="{{ json_encode($pipelineI18n, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-applicants="{{ json_encode($pipeline['applicants'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-stages="{{ json_encode($pipeline['stages'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-show-base="{{ url('/company/applicants') }}"
        data-job-base="{{ route('company.applicants.index') }}"
    >
        <div class="mb-6 flex flex-wrap gap-4 border-b border-hb-border text-sm font-semibold" role="tablist" aria-label="{{ __('company.applicants.view_tabs_aria') }}">
            @foreach ([
                ['board', __('company.applicants.tab_board')],
                ['list', __('company.applicants.tab_list')],
                ['activity', __('company.applicants.tab_activity')],
            ] as [$key, $label])
                <button
                    type="button"
                    role="tab"
                    id="pipeline-tab-{{ $key }}"
                    data-pipeline-view="{{ $key }}"
                    aria-controls="pipeline-panel-{{ $key }}"
                    aria-selected="{{ $key === 'board' ? 'true' : 'false' }}"
                    class="border-b-2 px-1 pb-3 {{ $key === 'board' ? 'border-hb-blue text-hb-blue' : 'border-transparent text-hb-muted hover:text-hb-navy' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center">
            <label class="relative min-w-0 flex-1">
                <span class="sr-only">{{ __('company.applicants.search_placeholder') }}</span>
                <span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-hb-muted" aria-hidden="true">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.473 9.8l3.614 3.613a.75.75 0 1 0 1.06-1.06l-3.613-3.614A5.5 5.5 0 0 0 9 3.5ZM5.5 9a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0Z" clip-rule="evenodd"/></svg>
                </span>
                <input
                    type="search"
                    data-pipeline-q
                    class="hb-input w-full ps-9"
                    placeholder="{{ __('company.applicants.search_placeholder') }}"
                    autocomplete="off"
                >
            </label>
            <div class="flex flex-wrap gap-2">
                <label class="sr-only" for="pipeline-job">{{ __('company.applicants.job_filter') }}</label>
                <select id="pipeline-job" data-pipeline-job class="hb-input w-auto min-w-[10rem]">
                    @foreach ($pipeline['jobs'] as $jobOption)
                        <option value="{{ $jobOption['id'] }}" @selected($jobOption['id'] === $pipeline['job_id'])>
                            {{ $jobOption['code'] }} · {{ $jobOption['title'] }}
                        </option>
                    @endforeach
                </select>
                <label class="sr-only" for="pipeline-source">{{ __('company.applicants.all_sources') }}</label>
                <select id="pipeline-source" data-pipeline-source class="hb-input w-auto min-w-[9rem]">
                    <option value="">{{ __('company.applicants.all_sources') }}</option>
                    <option value="direct">{{ __('company.applicants.source_direct') }}</option>
                    <option value="agency">{{ __('company.applicants.source_agency') }}</option>
                </select>
                <label class="sr-only" for="pipeline-owner">{{ __('company.applicants.all_owners') }}</label>
                <select id="pipeline-owner" data-pipeline-owner class="hb-input w-auto min-w-[9rem]">
                    <option value="">{{ __('company.applicants.all_owners') }}</option>
                    @foreach ($pipeline['owners'] as $owner)
                        <option value="{{ $owner }}" @selected($owner === $pipeline['owner'])>{{ $owner }}</option>
                    @endforeach
                </select>
                <label class="sr-only" for="pipeline-stage">{{ __('company.applicants.all_stages') }}</label>
                <select id="pipeline-stage" data-pipeline-stage class="hb-input w-auto min-w-[8rem]">
                    <option value="">{{ __('company.applicants.all_stages') }}</option>
                    @foreach ($pipeline['stages'] as $stage)
                        <option value="{{ $stage }}">{{ $stage }}</option>
                    @endforeach
                </select>
                <label class="sr-only" for="pipeline-sort">{{ __('company.applicants.sort_latest') }}</label>
                <select id="pipeline-sort" data-pipeline-sort class="hb-input w-auto min-w-[9rem]">
                    <option value="latest">{{ __('company.applicants.sort_latest') }}</option>
                    <option value="name">{{ __('company.applicants.sort_name') }}</option>
                    <option value="stage">{{ __('company.applicants.sort_stage') }}</option>
                </select>
                <button type="button" class="hb-btn-outline" data-pipeline-clear>
                    {{ __('company.applicants.clear_filters') }}
                </button>
            </div>
        </div>

        <p class="mb-4 text-sm text-hb-muted" data-pipeline-filter-note role="status" aria-live="polite"></p>

        {{-- Board --}}
        <div id="pipeline-panel-board" role="tabpanel" aria-labelledby="pipeline-tab-board" data-pipeline-panel="board">
            <div class="flex gap-4 overflow-x-auto pb-2" data-pipeline-board>
                @foreach ($pipeline['columns'] as $column)
                    <section
                        class="flex w-72 shrink-0 flex-col rounded-hb bg-hb-page/80 p-3"
                        aria-label="{{ $column['title'] }}"
                        data-pipeline-column="{{ $column['title'] }}"
                    >
                        <header class="mb-3 flex items-center justify-between px-1">
                            <h2 class="text-sm font-semibold text-hb-navy">{{ $column['title'] }}</h2>
                            <span class="text-xs font-medium text-hb-muted" data-column-count>{{ $column['count'] }}</span>
                        </header>
                        <ul class="flex flex-1 flex-col gap-3" data-column-cards>
                            @foreach ($column['cards'] as $card)
                                <li data-applicant-card data-id="{{ $card['id'] }}">
                                    <a
                                        href="{{ route('company.applicants.show', $card['id']) }}"
                                        @class([
                                            'block rounded-hb border bg-hb-surface p-3 shadow-hb-sm transition hover:border-hb-blue focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-hb-blue',
                                            'border-hb-blue ring-1 ring-hb-blue/20' => ! empty($card['highlighted']),
                                            'border-hb-border' => empty($card['highlighted']),
                                        ])
                                    >
                                        <h3 class="text-sm font-semibold text-hb-text">{{ $card['name'] }}</h3>
                                        <p class="mt-1 text-xs text-hb-muted">{{ $card['skills'] }}</p>
                                        <p class="mt-2 text-xs text-hb-muted">
                                            <span class="me-1 inline-block h-1.5 w-1.5 rounded-full {{ ($card['source_key'] ?? '') === 'direct' ? 'bg-hb-blue' : 'bg-hb-muted' }} align-middle" aria-hidden="true"></span>
                                            {{ $card['source'] }}
                                        </p>
                                        <p class="mt-2 text-xs text-hb-muted">
                                            {{ __('company.applicants.applied_by', ['owner' => $card['owner'], 'date' => $card['applied_on']]) }}
                                        </p>
                                        @if (! empty($card['flag']))
                                            <p class="mt-2 text-xs font-medium text-hb-blue">{{ $card['flag'] }}</p>
                                        @endif
                                        @if (! empty($card['badge']))
                                            <div class="mt-2">
                                                <x-badge :variant="$card['badge']['variant'] ?? 'neutral'" :dot="true">
                                                    {{ $card['badge']['label'] ?? '' }}
                                                </x-badge>
                                            </div>
                                        @endif
                                        @if (! empty($card['ai_pipeline_label']))
                                            <p class="mt-2 text-xs font-medium text-hb-muted" data-ai-pipeline-label>
                                                {{ $card['ai_pipeline_label'] }}
                                            </p>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <p class="mt-3 hidden rounded-hb border border-dashed border-hb-border px-3 py-6 text-center text-xs text-hb-muted" data-column-empty>
                            {{ __('company.applicants.empty_column') }}
                        </p>
                    </section>
                @endforeach
            </div>
            <p class="mt-4 hidden text-sm text-hb-muted" data-board-empty>{{ __('company.applicants.empty_board') }}</p>
        </div>

        {{-- List --}}
        <div id="pipeline-panel-list" role="tabpanel" aria-labelledby="pipeline-tab-list" data-pipeline-panel="list" class="hidden" hidden>
            <div class="hb-surface overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[48rem] text-start text-sm">
                        <caption class="sr-only">{{ __('company.applicants.title') }}</caption>
                        <thead>
                            <tr class="border-b border-hb-border bg-hb-page/60 text-xs font-semibold tracking-wide text-hb-muted uppercase">
                                <th scope="col" class="px-4 py-3 font-semibold">{{ __('company.applicants.col_name') }}</th>
                                <th scope="col" class="px-4 py-3 font-semibold">{{ __('company.applicants.col_stage') }}</th>
                                <th scope="col" class="px-4 py-3 font-semibold">{{ __('company.applicants.col_skills') }}</th>
                                <th scope="col" class="px-4 py-3 font-semibold">{{ __('company.applicants.col_source') }}</th>
                                <th scope="col" class="px-4 py-3 font-semibold">{{ __('company.applicants.col_owner') }}</th>
                                <th scope="col" class="px-4 py-3 font-semibold">{{ __('company.applicants.col_applied') }}</th>
                                <th scope="col" class="px-4 py-3 font-semibold"><span class="sr-only">{{ __('company.applicants.view_profile') }}</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hb-border" data-pipeline-list-body>
                            @foreach ($pipeline['applicants'] as $row)
                                <tr data-list-row data-id="{{ $row['id'] }}">
                                    <td class="px-4 py-3 font-semibold text-hb-navy">
                                        {{ $row['name'] }}
                                        @if (! empty($row['ai_pipeline_label']))
                                            <span class="mt-1 block text-xs font-medium text-hb-muted">{{ $row['ai_pipeline_label'] }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-hb-muted">{{ $row['stage'] }}</td>
                                    <td class="px-4 py-3 text-hb-muted">{{ $row['skills'] }}</td>
                                    <td class="px-4 py-3 text-hb-muted">{{ $row['source'] }}</td>
                                    <td class="px-4 py-3 text-hb-muted">{{ $row['owner'] }}</td>
                                    <td class="px-4 py-3 text-hb-muted">{{ $row['applied_on'] }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('company.applicants.show', $row['id']) }}" class="font-semibold text-hb-blue hover:underline">
                                            {{ __('company.applicants.view_profile') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="hidden px-4 py-8 text-center text-sm text-hb-muted" data-list-empty>{{ __('company.applicants.empty_list') }}</p>
            </div>
        </div>

        {{-- Activity --}}
        <div id="pipeline-panel-activity" role="tabpanel" aria-labelledby="pipeline-tab-activity" data-pipeline-panel="activity" class="hidden" hidden>
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="pipeline-activity-heading">
                <h2 id="pipeline-activity-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.applicants.activity_heading') }}</h2>
                @if (! empty($pipeline['activity']))
                    <ul class="mt-5 space-y-4">
                        @foreach ($pipeline['activity'] as $item)
                            <li class="border-s-2 border-hb-border ps-4">
                                <p class="text-xs font-medium text-hb-muted">{{ $item['time'] }}</p>
                                <p class="mt-1 text-sm text-hb-text">{{ $item['body'] }}</p>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-4 text-sm text-hb-muted">{{ __('company.applicants.empty_activity') }}</p>
                @endif
            </section>
        </div>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-hb-muted">
                {{ __('company.applicants.summary', [
                    'direct' => $summary['direct'],
                    'agency' => $summary['agency'],
                    'rejected' => $summary['rejected'],
                    'withdrawn' => $summary['withdrawn'],
                ]) }}
            </p>
            <button type="button" class="hb-btn-outline" data-open-closed>
                {{ __('company.applicants.view_closed') }}
            </button>
        </div>

        <p class="mt-8 text-center text-xs text-hb-muted">{{ $pipeline['footer'] }}</p>

        {{-- Add candidate modal --}}
        <div data-add-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4" hidden role="dialog" aria-modal="true" aria-labelledby="add-candidate-title">
            <div class="w-full max-w-md rounded-hb border border-hb-border bg-hb-surface p-5 shadow-hb-lg">
                <h2 id="add-candidate-title" class="text-lg font-semibold text-hb-navy">{{ __('company.applicants.add_modal_title') }}</h2>
                <p class="mt-2 text-sm text-hb-muted">{{ __('company.applicants.add_modal_body') }}</p>
                <label class="mt-4 block">
                    <span class="hb-label">{{ __('company.applicants.add_name_label') }}</span>
                    <input type="text" class="hb-input mt-1 w-full" data-add-name autocomplete="off">
                </label>
                <p class="mt-3 hidden text-sm text-hb-blue" data-add-feedback role="status"></p>
                <div class="mt-5 flex flex-wrap justify-end gap-2">
                    <button type="button" class="hb-btn-outline" data-close-add>{{ __('company.applicants.add_cancel') }}</button>
                    <button type="button" class="hb-btn-primary" data-confirm-add>{{ __('company.applicants.add_confirm') }}</button>
                </div>
            </div>
        </div>

        {{-- Closed outcomes modal --}}
        <div data-closed-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4" hidden role="dialog" aria-modal="true" aria-labelledby="closed-outcomes-title">
            <div class="w-full max-w-md rounded-hb border border-hb-border bg-hb-surface p-5 shadow-hb-lg">
                <h2 id="closed-outcomes-title" class="text-lg font-semibold text-hb-navy">{{ __('company.applicants.closed_modal_title') }}</h2>
                <p class="mt-3 text-sm font-semibold text-hb-navy">{{ __('company.applicants.closed_empty_title') }}</p>
                <p class="mt-1 text-sm text-hb-muted">{{ __('company.applicants.closed_empty_body') }}</p>
                <div class="mt-5 flex justify-end">
                    <button type="button" class="hb-btn-outline" data-close-closed>{{ __('company.applicants.modal_close') }}</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (() => {
                const root = document.querySelector('[data-company-pipeline]');
                if (!root) return;

                const i18n = JSON.parse(root.getAttribute('data-i18n') || '{}');
                const applicants = JSON.parse(root.getAttribute('data-applicants') || '[]');
                const stages = JSON.parse(root.getAttribute('data-stages') || '[]');
                const showBase = (root.getAttribute('data-show-base') || '').replace(/\/$/, '');
                const jobBase = root.getAttribute('data-job-base') || '';

                const qInput = root.querySelector('[data-pipeline-q]');
                const sourceSelect = root.querySelector('[data-pipeline-source]');
                const ownerSelect = root.querySelector('[data-pipeline-owner]');
                const stageSelect = root.querySelector('[data-pipeline-stage]');
                const sortSelect = root.querySelector('[data-pipeline-sort]');
                const jobSelect = root.querySelector('[data-pipeline-job]');
                const note = root.querySelector('[data-pipeline-filter-note]');
                const viewButtons = [...root.querySelectorAll('[data-pipeline-view]')];
                const panels = [...root.querySelectorAll('[data-pipeline-panel]')];

                const stageParam = new URLSearchParams(window.location.search).get('stage');
                if (stageParam && stageSelect) {
                    const match = [...stageSelect.options].find((opt) => opt.value === stageParam || opt.value.toLowerCase() === stageParam.toLowerCase());
                    if (match) stageSelect.value = match.value;
                }

                function replacePlaceholders(template, replacements) {
                    return Object.entries(replacements).reduce(
                        (text, [key, value]) => text.replaceAll(`:${key}`, String(value)),
                        template ?? '',
                    );
                }

                function setView(view) {
                    viewButtons.forEach((btn) => {
                        const active = btn.getAttribute('data-pipeline-view') === view;
                        btn.setAttribute('aria-selected', active ? 'true' : 'false');
                        btn.classList.toggle('border-hb-blue', active);
                        btn.classList.toggle('text-hb-blue', active);
                        btn.classList.toggle('border-transparent', !active);
                        btn.classList.toggle('text-hb-muted', !active);
                    });
                    panels.forEach((panel) => {
                        const active = panel.getAttribute('data-pipeline-panel') === view;
                        panel.classList.toggle('hidden', !active);
                        panel.hidden = !active;
                    });
                }

                function filteredIds() {
                    const q = (qInput?.value || '').trim().toLowerCase();
                    const source = sourceSelect?.value || '';
                    const owner = ownerSelect?.value || '';
                    const stage = stageSelect?.value || '';
                    let rows = applicants.filter((row) => {
                        if (source && row.source_key !== source) return false;
                        if (owner && row.owner !== owner) return false;
                        if (stage && row.stage !== stage) return false;
                        if (!q) return true;
                        const hay = `${row.name || ''} ${row.skills || ''} ${row.source || ''}`.toLowerCase();
                        return hay.includes(q);
                    });

                    const sort = sortSelect?.value || 'latest';
                    const stageOrder = Object.fromEntries(stages.map((s, i) => [s, i]));
                    rows = [...rows].sort((a, b) => {
                        if (sort === 'name') return String(a.name || '').localeCompare(String(b.name || ''));
                        if (sort === 'stage') return (stageOrder[a.stage] ?? 99) - (stageOrder[b.stage] ?? 99);
                        return String(b.applied_on_long || '').localeCompare(String(a.applied_on_long || ''));
                    });
                    return rows.map((r) => r.id);
                }

                function applyFilters() {
                    const ids = filteredIds();
                    const idSet = new Set(ids);

                    root.querySelectorAll('[data-applicant-card]').forEach((card) => {
                        const show = idSet.has(card.getAttribute('data-id'));
                        card.classList.toggle('hidden', !show);
                        card.hidden = !show;
                    });

                    root.querySelectorAll('[data-pipeline-column]').forEach((col) => {
                        const visible = [...col.querySelectorAll('[data-applicant-card]')].filter((c) => !c.hidden);
                        const countEl = col.querySelector('[data-column-count]');
                        const emptyEl = col.querySelector('[data-column-empty]');
                        if (countEl) countEl.textContent = String(visible.length);
                        if (emptyEl) {
                            emptyEl.classList.toggle('hidden', visible.length > 0);
                        }
                    });

                    root.querySelectorAll('[data-list-row]').forEach((row) => {
                        const show = idSet.has(row.getAttribute('data-id'));
                        row.classList.toggle('hidden', !show);
                        row.hidden = !show;
                    });

                    const listBody = root.querySelector('[data-pipeline-list-body]');
                    const listEmpty = root.querySelector('[data-list-empty]');
                    const visibleRows = ids.length;
                    if (listEmpty) {
                        listEmpty.classList.toggle('hidden', visibleRows > 0);
                        listEmpty.hidden = visibleRows > 0;
                    }
                    if (listBody) {
                        listBody.classList.toggle('hidden', visibleRows === 0);
                    }

                    const boardEmpty = root.querySelector('[data-board-empty]');
                    if (boardEmpty) {
                        boardEmpty.classList.toggle('hidden', visibleRows > 0);
                    }

                    if (note) {
                        note.textContent = replacePlaceholders(i18n.filter_note, {
                            count: visibleRows,
                            total: applicants.length,
                        });
                    }
                }

                viewButtons.forEach((btn) => {
                    btn.addEventListener('click', () => setView(btn.getAttribute('data-pipeline-view')));
                });

                [qInput, sourceSelect, ownerSelect, stageSelect, sortSelect].forEach((el) => {
                    el?.addEventListener('input', applyFilters);
                    el?.addEventListener('change', applyFilters);
                });

                root.querySelector('[data-pipeline-clear]')?.addEventListener('click', () => {
                    if (qInput) qInput.value = '';
                    if (sourceSelect) sourceSelect.value = '';
                    if (ownerSelect) ownerSelect.value = '';
                    if (stageSelect) stageSelect.value = '';
                    if (sortSelect) sortSelect.value = 'latest';
                    applyFilters();
                });

                jobSelect?.addEventListener('change', () => {
                    const job = jobSelect.value;
                    if (!jobBase) return;
                    const url = new URL(jobBase, window.location.origin);
                    url.searchParams.set('job', job);
                    window.location.assign(url.toString());
                });

                function openModal(modal) {
                    if (!modal) return;
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    modal.hidden = false;
                    const focusable = modal.querySelector('input, button');
                    focusable?.focus();
                }

                function closeModal(modal) {
                    if (!modal) return;
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    modal.hidden = true;
                }

                const addModal = root.querySelector('[data-add-modal]');
                const closedModal = root.querySelector('[data-closed-modal]');

                root.querySelector('[data-open-add-candidate]')?.addEventListener('click', () => openModal(addModal));
                root.querySelector('[data-close-add]')?.addEventListener('click', () => closeModal(addModal));
                root.querySelector('[data-open-closed]')?.addEventListener('click', () => openModal(closedModal));
                root.querySelector('[data-close-closed]')?.addEventListener('click', () => closeModal(closedModal));

                root.querySelector('[data-confirm-add]')?.addEventListener('click', () => {
                    const nameInput = root.querySelector('[data-add-name]');
                    const feedback = root.querySelector('[data-add-feedback]');
                    const name = (nameInput?.value || '').trim() || 'Candidate';
                    if (feedback) {
                        feedback.textContent = replacePlaceholders(i18n.add_feedback, { name });
                        feedback.classList.remove('hidden');
                    }
                });

                [addModal, closedModal].forEach((modal) => {
                    modal?.addEventListener('click', (event) => {
                        if (event.target === modal) closeModal(modal);
                    });
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key !== 'Escape') return;
                    closeModal(addModal);
                    closeModal(closedModal);
                });

                applyFilters();
            })();
        </script>
    @endpush
@endsection
