@extends('layouts.app')

@section('title', $pageTitle ?? __('company.talent_pool.page_title'))
@section('breadcrumb', __('company.talent_pool.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ __('company.talent_pool.title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ __('company.talent_pool.subtitle') }}</p>
@endsection

@section('page_actions')
    <button type="button" class="hb-btn-primary" data-open-add-prospect>{{ __('company.talent_pool.add_prospect') }}</button>
@endsection

@section('content')
    @php
        $poolI18n = [
            'showing' => __('company.talent_pool.js.showing'),
            'showing_zero' => __('company.talent_pool.js.showing_zero'),
            'empty' => __('company.talent_pool.js.empty'),
            'add_feedback' => __('company.talent_pool.js.add_feedback'),
            'action_feedback' => __('company.talent_pool.js.action_feedback'),
            'shortlist_saved' => __('company.talent_pool.js.shortlist_saved'),
            'shortlist_removed' => __('company.talent_pool.js.shortlist_removed'),
            'shortlist_add' => __('company.talent_pool.js.shortlist_add'),
            'shortlist_added' => __('company.talent_pool.js.shortlist_added'),
            'tab_all' => __('company.talent_pool.js.tab_all'),
            'tab_product' => __('company.talent_pool.js.tab_product'),
            'tab_engineering' => __('company.talent_pool.js.tab_engineering'),
            'tab_other' => __('company.talent_pool.js.tab_other'),
            'previous' => __('company.talent_pool.previous'),
            'next' => __('company.talent_pool.next'),
        ];
        $tabCounts = [
            'all' => $pool['total'],
            'product_design' => 7,
            'engineering' => 8,
            'other' => 3,
        ];
    @endphp

    <div
        data-company-talent-pool
        data-i18n="{{ json_encode($poolI18n, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-prospects="{{ json_encode($pool['prospects'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-total="{{ $pool['total'] }}"
        data-page-size="{{ $pool['page_size'] }}"
        data-shortlist-key="hb_talent_shortlist"
    >
        <div class="mb-6 flex flex-wrap gap-4 border-b border-hb-border text-sm font-semibold" role="tablist" aria-label="{{ __('company.talent_pool.tabs_aria') }}">
            @foreach ([
                ['all', __('company.talent_pool.tab_all', ['count' => $tabCounts['all']])],
                ['product_design', __('company.talent_pool.tab_product', ['count' => $tabCounts['product_design']])],
                ['engineering', __('company.talent_pool.tab_engineering', ['count' => $tabCounts['engineering']])],
                ['other', __('company.talent_pool.tab_other', ['count' => $tabCounts['other']])],
            ] as [$key, $label])
                <button
                    type="button"
                    role="tab"
                    data-pool-tab="{{ $key }}"
                    aria-selected="{{ $key === 'all' ? 'true' : 'false' }}"
                    class="border-b-2 px-1 pb-3 {{ $key === 'all' ? 'border-hb-blue text-hb-blue' : 'border-transparent text-hb-muted hover:text-hb-navy' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center">
            <label class="relative min-w-0 flex-1">
                <span class="sr-only">{{ __('company.talent_pool.search_placeholder') }}</span>
                <span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-hb-muted" aria-hidden="true">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.473 9.8l3.614 3.613a.75.75 0 1 0 1.06-1.06l-3.613-3.614A5.5 5.5 0 0 0 9 3.5ZM5.5 9a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0Z" clip-rule="evenodd"/></svg>
                </span>
                <input type="search" data-pool-q class="hb-input w-full ps-9" placeholder="{{ __('company.talent_pool.search_placeholder') }}" autocomplete="off">
            </label>
            <div class="flex flex-wrap gap-2">
                <label class="sr-only" for="pool-rel">{{ __('company.talent_pool.all_relationships') }}</label>
                <select id="pool-rel" data-pool-rel class="hb-input w-auto min-w-[10rem]">
                    <option value="">{{ __('company.talent_pool.all_relationships') }}</option>
                    <option value="open">{{ __('company.talent_pool.rel_open') }}</option>
                    <option value="nurturing">{{ __('company.talent_pool.rel_nurturing') }}</option>
                    <option value="available">{{ __('company.talent_pool.rel_available') }}</option>
                    <option value="requested">{{ __('company.talent_pool.rel_requested') }}</option>
                </select>
                <label class="sr-only" for="pool-source">{{ __('company.talent_pool.all_sources') }}</label>
                <select id="pool-source" data-pool-source class="hb-input w-auto min-w-[9rem]">
                    <option value="">{{ __('company.talent_pool.all_sources') }}</option>
                    <option value="direct">{{ __('company.talent_pool.source_direct') }}</option>
                    <option value="agency">{{ __('company.talent_pool.source_agency') }}</option>
                </select>
                <label class="sr-only" for="pool-sort">{{ __('company.talent_pool.sort_default') }}</label>
                <select id="pool-sort" data-pool-sort class="hb-input w-auto min-w-[9rem]">
                    <option value="default">{{ __('company.talent_pool.sort_default') }}</option>
                    <option value="name">{{ __('company.talent_pool.sort_name') }}</option>
                    <option value="owner">{{ __('company.talent_pool.sort_owner') }}</option>
                    <option value="relationship">{{ __('company.talent_pool.sort_relationship') }}</option>
                </select>
                <button type="button" class="hb-btn-outline" data-pool-clear>{{ __('company.talent_pool.clear_filters') }}</button>
            </div>
        </div>

        <section class="hb-surface overflow-hidden">
            <ul class="divide-y divide-hb-border" data-pool-list></ul>
            <p class="hidden px-5 py-10 text-center text-sm text-hb-muted" data-pool-empty>{{ __('company.talent_pool.empty') }}</p>
            <div class="flex flex-col gap-3 border-t border-hb-border px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-hb-muted" data-pool-showing role="status"></p>
                <div class="flex flex-wrap gap-2" data-pool-pages></div>
            </div>
        </section>

        <div class="mt-5 rounded-lg border border-hb-blue/20 bg-hb-info-soft/50 px-4 py-3 text-sm text-hb-text" role="note">
            <p class="font-semibold text-hb-navy">{{ __('company.talent_pool.consent_heading') }}</p>
            <p class="mt-1 text-hb-muted">{{ $pool['consent_note'] }}</p>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <button type="button" class="hb-btn-outline" data-open-tags>{{ __('company.talent_pool.manage_tags') }}</button>
            <button type="button" class="hb-btn-outline" data-open-consent>{{ __('company.talent_pool.review_consent') }}</button>
        </div>
        <p class="mt-3 hidden text-sm text-hb-blue" data-pool-feedback role="status"></p>

        <p class="mt-8 text-center text-xs text-hb-muted">{{ $pool['footer'] }}</p>

        {{-- Add prospect --}}
        <div data-add-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4" hidden role="dialog" aria-modal="true" aria-labelledby="add-prospect-title">
            <div class="w-full max-w-md rounded-hb border border-hb-border bg-hb-surface p-5 shadow-hb-lg">
                <h2 id="add-prospect-title" class="text-lg font-semibold text-hb-navy">{{ __('company.talent_pool.add_modal_title') }}</h2>
                <p class="mt-2 text-sm text-hb-muted">{{ __('company.talent_pool.add_modal_body') }}</p>
                <label class="mt-4 block">
                    <span class="hb-label">{{ __('company.talent_pool.add_name') }}</span>
                    <input type="text" class="hb-input mt-1 w-full" data-add-name autocomplete="off">
                </label>
                <div class="mt-5 flex flex-wrap justify-end gap-2">
                    <button type="button" class="hb-btn-outline" data-close-add>{{ __('company.talent_pool.add_cancel') }}</button>
                    <button type="button" class="hb-btn-primary" data-confirm-add>{{ __('company.talent_pool.add_confirm') }}</button>
                </div>
            </div>
        </div>

        <div data-tags-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4" hidden role="dialog" aria-modal="true" aria-labelledby="tags-modal-title">
            <div class="w-full max-w-md rounded-hb border border-hb-border bg-hb-surface p-5 shadow-hb-lg">
                <h2 id="tags-modal-title" class="text-lg font-semibold text-hb-navy">{{ __('company.talent_pool.tags_modal_title') }}</h2>
                <p class="mt-2 text-sm text-hb-muted">{{ __('company.talent_pool.tags_modal_body') }}</p>
                <div class="mt-5 flex justify-end">
                    <button type="button" class="hb-btn-outline" data-close-tags>{{ __('company.talent_pool.modal_close') }}</button>
                </div>
            </div>
        </div>

        <div data-consent-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4" hidden role="dialog" aria-modal="true" aria-labelledby="consent-modal-title">
            <div class="w-full max-w-md rounded-hb border border-hb-border bg-hb-surface p-5 shadow-hb-lg">
                <h2 id="consent-modal-title" class="text-lg font-semibold text-hb-navy">{{ __('company.talent_pool.consent_modal_title') }}</h2>
                <p class="mt-2 text-sm text-hb-muted">{{ __('company.talent_pool.consent_modal_body') }}</p>
                <div class="mt-5 flex justify-end">
                    <button type="button" class="hb-btn-outline" data-close-consent>{{ __('company.talent_pool.modal_close') }}</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (() => {
                const root = document.querySelector('[data-company-talent-pool]');
                if (!root) return;
                const i18n = JSON.parse(root.getAttribute('data-i18n') || '{}');
                const prospects = JSON.parse(root.getAttribute('data-prospects') || '[]');
                const catalogTotal = Number(root.getAttribute('data-total') || prospects.length);
                const pageSize = Number(root.getAttribute('data-page-size') || 5);
                const shortlistKey = root.getAttribute('data-shortlist-key') || '';
                const listEl = root.querySelector('[data-pool-list]');
                const emptyEl = root.querySelector('[data-pool-empty]');
                const showingEl = root.querySelector('[data-pool-showing]');
                const pagesEl = root.querySelector('[data-pool-pages]');
                const feedback = root.querySelector('[data-pool-feedback]');
                const qInput = root.querySelector('[data-pool-q]');
                const relSelect = root.querySelector('[data-pool-rel]');
                const sourceSelect = root.querySelector('[data-pool-source]');
                const sortSelect = root.querySelector('[data-pool-sort]');
                const tabs = [...root.querySelectorAll('[data-pool-tab]')];

                let tab = 'all';
                let page = 1;
                let shortlist = [];
                try {
                    shortlist = JSON.parse(localStorage.getItem(shortlistKey) || '[]');
                    if (!Array.isArray(shortlist)) shortlist = [];
                } catch (e) {
                    shortlist = [];
                }

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
                    const rel = relSelect?.value || '';
                    const source = sourceSelect?.value || '';
                    let rows = prospects.filter((row) => {
                        if (tab !== 'all' && row.category !== tab) return false;
                        if (rel && row.relationship_key !== rel) return false;
                        if (source && row.source_key !== source) return false;
                        if (!q) return true;
                        const hay = `${row.name || ''} ${row.skills || ''} ${row.headline || ''} ${row.location || ''}`.toLowerCase();
                        return hay.includes(q);
                    });
                    const sort = sortSelect?.value || 'default';
                    if (sort === 'default') {
                        return rows;
                    }
                    rows = [...rows].sort((a, b) => {
                        if (sort === 'owner') return String(a.owner || '').localeCompare(String(b.owner || ''));
                        if (sort === 'relationship') return String(a.relationship || '').localeCompare(String(b.relationship || ''));
                        return String(a.name || '').localeCompare(String(b.name || ''));
                    });
                    return rows;
                }

                function render() {
                    const rows = filtered();
                    const totalPages = Math.max(1, Math.ceil(rows.length / pageSize) || 1);
                    if (page > totalPages) page = totalPages;
                    const start = (page - 1) * pageSize;
                    const slice = rows.slice(start, start + pageSize);

                    if (listEl) {
                        listEl.innerHTML = slice.map((row) => {
                            const saved = shortlist.includes(row.id);
                            return `
                                <li class="px-5 py-4" data-prospect-id="${escapeHtml(row.id)}">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="min-w-0">
                                            <p class="font-semibold text-hb-navy">${escapeHtml(row.name)} · ${escapeHtml(row.location)} · ${escapeHtml(row.headline)}</p>
                                            <p class="mt-1 text-sm text-hb-muted">
                                                ${escapeHtml(row.skills)} · ${escapeHtml(row.source)} · ${escapeHtml(row.relationship)} · ${escapeHtml(row.consent)} · ${escapeHtml(row.owner)} ·
                                                <button type="button" class="font-semibold text-hb-blue hover:underline" data-pool-action="${escapeHtml(row.action)}" data-pool-name="${escapeHtml(row.name)}">${escapeHtml(row.action)}</button>
                                            </p>
                                        </div>
                                        <button type="button" class="shrink-0 self-start text-xs font-semibold text-hb-blue hover:underline" data-shortlist-toggle="${escapeHtml(row.id)}" data-pool-name="${escapeHtml(row.name)}" aria-pressed="${saved ? 'true' : 'false'}">
                                            ${saved ? '★' : '☆'} ${escapeHtml(saved ? (i18n.shortlist_added || '') : (i18n.shortlist_add || ''))}
                                        </button>
                                    </div>
                                </li>
                            `;
                        }).join('');
                    }

                    const hasRows = slice.length > 0;
                    if (emptyEl) {
                        emptyEl.classList.toggle('hidden', hasRows);
                        emptyEl.hidden = hasRows;
                    }
                    if (listEl) listEl.classList.toggle('hidden', !hasRows);

                    if (showingEl) {
                        showingEl.textContent = rows.length
                            ? replacePlaceholders(i18n.showing, {
                                start: start + 1,
                                end: start + slice.length,
                                total: tab === 'all' ? catalogTotal : rows.length,
                            })
                            : (i18n.showing_zero || '');
                    }

                    if (pagesEl) {
                        const prevDisabled = page <= 1;
                        const nextDisabled = page >= totalPages || rows.length === 0;
                        let html = `<button type="button" class="rounded-lg border border-hb-border px-3 py-1.5 text-sm ${prevDisabled ? 'cursor-not-allowed text-hb-muted' : 'text-hb-navy hover:bg-hb-page'}" data-pool-page="prev" ${prevDisabled ? 'disabled' : ''}>${escapeHtml(i18n.previous)}</button>`;
                        for (let i = 1; i <= totalPages; i++) {
                            const active = i === page;
                            html += `<button type="button" class="min-w-9 rounded-lg border px-3 py-1.5 text-sm font-semibold ${active ? 'border-hb-blue bg-hb-blue text-white' : 'border-hb-border text-hb-navy hover:bg-hb-page'}" data-pool-page="${i}" aria-current="${active ? 'page' : 'false'}">${i}</button>`;
                        }
                        html += `<button type="button" class="rounded-lg border border-hb-border px-3 py-1.5 text-sm ${nextDisabled ? 'cursor-not-allowed text-hb-muted' : 'text-hb-navy hover:bg-hb-page'}" data-pool-page="next" ${nextDisabled ? 'disabled' : ''}>${escapeHtml(i18n.next)}</button>`;
                        pagesEl.innerHTML = html;
                    }
                }

                tabs.forEach((btn) => {
                    btn.addEventListener('click', () => {
                        tab = btn.getAttribute('data-pool-tab') || 'all';
                        page = 1;
                        tabs.forEach((t) => {
                            const active = t === btn;
                            t.setAttribute('aria-selected', active ? 'true' : 'false');
                            t.classList.toggle('border-hb-blue', active);
                            t.classList.toggle('text-hb-blue', active);
                            t.classList.toggle('border-transparent', !active);
                            t.classList.toggle('text-hb-muted', !active);
                        });
                        render();
                    });
                });

                [qInput, relSelect, sourceSelect, sortSelect].forEach((el) => {
                    el?.addEventListener('input', () => { page = 1; render(); });
                    el?.addEventListener('change', () => { page = 1; render(); });
                });

                root.querySelector('[data-pool-clear]')?.addEventListener('click', () => {
                    if (qInput) qInput.value = '';
                    if (relSelect) relSelect.value = '';
                    if (sourceSelect) sourceSelect.value = '';
                    if (sortSelect) sortSelect.value = 'default';
                    page = 1;
                    render();
                });

                pagesEl?.addEventListener('click', (event) => {
                    const btn = event.target.closest('[data-pool-page]');
                    if (!btn || btn.disabled) return;
                    const value = btn.getAttribute('data-pool-page');
                    const rows = filtered();
                    const totalPages = Math.max(1, Math.ceil(rows.length / pageSize) || 1);
                    if (value === 'prev') page = Math.max(1, page - 1);
                    else if (value === 'next') page = Math.min(totalPages, page + 1);
                    else page = Number(value) || 1;
                    render();
                });

                listEl?.addEventListener('click', (event) => {
                    const actionBtn = event.target.closest('[data-pool-action]');
                    if (actionBtn) {
                        if (feedback) {
                            feedback.textContent = replacePlaceholders(i18n.action_feedback, {
                                action: actionBtn.getAttribute('data-pool-action') || '',
                                name: actionBtn.getAttribute('data-pool-name') || '',
                            });
                            feedback.classList.remove('hidden');
                        }
                        return;
                    }
                    const star = event.target.closest('[data-shortlist-toggle]');
                    if (!star) return;
                    const id = star.getAttribute('data-shortlist-toggle');
                    const name = star.getAttribute('data-pool-name') || '';
                    if (shortlist.includes(id)) {
                        shortlist = shortlist.filter((item) => item !== id);
                        if (feedback) feedback.textContent = replacePlaceholders(i18n.shortlist_removed, { name });
                    } else {
                        shortlist.push(id);
                        if (feedback) feedback.textContent = replacePlaceholders(i18n.shortlist_saved, { name });
                    }
                    try {
                        localStorage.setItem(shortlistKey, JSON.stringify(shortlist));
                    } catch (e) {}
                    feedback?.classList.remove('hidden');
                    render();
                });

                function openModal(modal) {
                    if (!modal) return;
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    modal.hidden = false;
                    modal.querySelector('input, button')?.focus();
                }
                function closeModal(modal) {
                    if (!modal) return;
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    modal.hidden = true;
                }

                const addModal = root.querySelector('[data-add-modal]');
                const tagsModal = root.querySelector('[data-tags-modal]');
                const consentModal = root.querySelector('[data-consent-modal]');

                document.querySelector('[data-open-add-prospect]')?.addEventListener('click', () => openModal(addModal));
                root.querySelector('[data-close-add]')?.addEventListener('click', () => closeModal(addModal));
                root.querySelector('[data-open-tags]')?.addEventListener('click', () => openModal(tagsModal));
                root.querySelector('[data-close-tags]')?.addEventListener('click', () => closeModal(tagsModal));
                root.querySelector('[data-open-consent]')?.addEventListener('click', () => openModal(consentModal));
                root.querySelector('[data-close-consent]')?.addEventListener('click', () => closeModal(consentModal));

                root.querySelector('[data-confirm-add]')?.addEventListener('click', () => {
                    const name = (root.querySelector('[data-add-name]')?.value || '').trim() || 'Prospect';
                    if (feedback) {
                        feedback.textContent = replacePlaceholders(i18n.add_feedback, { name });
                        feedback.classList.remove('hidden');
                    }
                    closeModal(addModal);
                });

                [addModal, tagsModal, consentModal].forEach((modal) => {
                    modal?.addEventListener('click', (event) => {
                        if (event.target === modal) closeModal(modal);
                    });
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key !== 'Escape') return;
                    closeModal(addModal);
                    closeModal(tagsModal);
                    closeModal(consentModal);
                });

                render();
            })();
        </script>
    @endpush
@endsection
