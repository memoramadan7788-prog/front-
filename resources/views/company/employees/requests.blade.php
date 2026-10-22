@extends('layouts.app')

@section('title', $pageTitle ?? __('company.employees.requests_page_title'))
@section('breadcrumb', __('company.employees.requests_breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ __('company.employees.requests_title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ __('company.employees.requests_subtitle') }}</p>
@endsection

@section('page_actions')
    <a href="{{ route('company.employees.index') }}" class="hb-btn-outline">{{ __('company.employees.back_to_team') }}</a>
@endsection

@section('content')
    @php
        $organizationContext = $organizationContext ?? \App\Support\Frontend\MockCompanyData::organizationContext();
        $canAssignRoles = (bool) ($organizationContext['capabilities']['can_assign_roles'] ?? false);
        $requests = $requestsPage['requests'] ?? [];
        $assignableRoles = $requestsPage['assignable_roles'] ?? [];
        $pendingCount = collect($requests)->where('status_key', 'pending')->count();
        $declinedCount = collect($requests)->where('status_key', 'declined')->count();
        $reqI18n = [
            'approved_feedback' => __('company.employees.js.approved_feedback'),
            'declined_feedback' => __('company.employees.js.declined_feedback'),
            'approved_with_role' => __('company.employees.js.approved_with_role'),
            'requests_empty' => __('company.employees.js.requests_empty'),
            'approve' => __('company.employees.approve'),
            'decline' => __('company.employees.decline'),
            'view_request' => __('company.employees.view_request'),
            'requested_role' => __('company.employees.requested_role'),
            'requested_at' => __('company.employees.requested_at'),
            'department' => __('company.employees.department'),
            'approve_title' => __('company.employees.approve_title'),
            'approve_body' => __('company.employees.approve_body'),
            'decline_title' => __('company.employees.decline_title'),
            'decline_body' => __('company.employees.decline_body'),
            'assign_role_label' => __('company.employees.assign_role_label'),
            'assign_role_required' => __('company.employees.assign_role_required'),
            'rejection_reason_label' => __('company.employees.rejection_reason_label'),
            'rejection_reason_required' => __('company.employees.rejection_reason_required'),
            'rejection_reason_placeholder' => __('company.employees.rejection_reason_placeholder'),
            'preview_disclaimer' => __('company.employees.preview_disclaimer'),
            'role_assign_unavailable' => __('company.organization.assign_roles_unavailable'),
        ];
    @endphp

    @include('partials.company-organization-banner', ['organizationContext' => $organizationContext])

    <div
        data-company-join-requests
        data-can-assign-roles="{{ $canAssignRoles ? '1' : '0' }}"
        data-i18n="{{ json_encode($reqI18n, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-requests="{{ json_encode($requests, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-assignable-roles="{{ json_encode($assignableRoles, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
    >
        <p class="mb-4 rounded-lg border border-hb-border bg-hb-page px-4 py-3 text-xs text-hb-muted" role="note">
            {{ $requestsPage['preview_disclaimer'] ?? __('company.employees.preview_disclaimer') }}
        </p>

        <div class="mb-6 flex flex-wrap gap-4 border-b border-hb-border text-sm font-semibold" role="tablist" aria-label="{{ __('company.employees.requests_tabs_aria') }}">
            @foreach ([
                ['all', __('company.employees.requests_tab_all', ['count' => count($requests)])],
                ['pending', __('company.employees.requests_tab_pending', ['count' => $pendingCount])],
                ['declined', __('company.employees.requests_tab_declined', ['count' => $declinedCount])],
            ] as [$key, $label])
                <button
                    type="button"
                    role="tab"
                    data-req-tab="{{ $key }}"
                    aria-selected="{{ $key === 'pending' ? 'true' : 'false' }}"
                    class="border-b-2 px-1 pb-3 {{ $key === 'pending' ? 'border-hb-blue text-hb-blue' : 'border-transparent text-hb-muted hover:text-hb-navy' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="mb-5">
            <label class="block max-w-xl">
                <span class="sr-only">{{ __('company.employees.requests_search') }}</span>
                <input type="search" data-req-q class="hb-input w-full" placeholder="{{ __('company.employees.requests_search') }}" autocomplete="off">
            </label>
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
            <section class="hb-surface overflow-hidden">
                <ul class="divide-y divide-hb-border" data-req-list></ul>
                <p class="hidden px-5 py-10 text-center text-sm text-hb-muted" data-req-empty>{{ __('company.employees.requests_empty') }}</p>
            </section>

            <aside class="hb-surface p-5" aria-labelledby="req-detail-heading">
                <h2 id="req-detail-heading" class="text-base font-semibold text-hb-navy">{{ __('company.employees.view_request') }}</h2>
                <div class="mt-4 space-y-3 text-sm text-hb-muted" data-req-detail>
                    <p class="font-semibold text-hb-navy" data-detail-name>—</p>
                    <p dir="ltr" data-detail-email></p>
                    <p><span class="font-medium text-hb-text">{{ __('company.employees.requested_role') }}:</span> <span data-detail-role></span></p>
                    <p><span class="font-medium text-hb-text">{{ __('company.employees.department') }}:</span> <span data-detail-dept></span></p>
                    <p><span class="font-medium text-hb-text">{{ __('company.employees.requested_at') }}:</span> <span data-detail-date></span></p>
                    <p data-detail-note></p>
                    <p class="hidden" data-detail-rejection-wrap>
                        <span class="font-medium text-hb-text">{{ __('company.employees.rejection_reason_label') }}:</span>
                        <span data-detail-rejection></span>
                    </p>
                    <p><x-badge variant="pending" :dot="true"><span data-detail-status></span></x-badge></p>
                </div>
                <div class="mt-5 flex flex-col gap-2" data-req-actions>
                    <button type="button" class="hb-btn-primary" data-open-approve>{{ __('company.employees.approve') }}</button>
                    <button type="button" class="hb-btn-outline" data-open-decline>{{ __('company.employees.decline') }}</button>
                </div>
            </aside>
        </div>

        <p class="mt-4 hidden text-sm text-hb-blue" data-req-feedback role="status"></p>
        <p class="mt-8 text-center text-xs text-hb-muted">{{ $requestsPage['footer'] }}</p>

        <div data-approve-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4" hidden role="dialog" aria-modal="true" aria-labelledby="approve-modal-title">
            <div class="w-full max-w-md rounded-hb border border-hb-border bg-hb-surface p-5 shadow-hb-lg">
                <h2 id="approve-modal-title" class="text-lg font-semibold text-hb-navy">{{ __('company.employees.approve_title') }}</h2>
                <p class="mt-2 text-sm text-hb-muted" data-approve-body></p>
                <div class="mt-4" data-approve-role-field>
                    <label for="approve-assign-role" class="hb-label">{{ __('company.employees.assign_role_label') }} <span class="text-hb-error">*</span></label>
                    <select id="approve-assign-role" class="hb-input mt-1" data-approve-role>
                        <option value="">{{ __('company.employees.assign_role_placeholder') }}</option>
                        @foreach ($assignableRoles as $role)
                            <option value="{{ $role['value'] }}">{{ $role['label'] }}</option>
                        @endforeach
                    </select>
                    <p class="hb-field-error hidden" data-approve-role-error role="alert"></p>
                    @unless ($canAssignRoles)
                        <p class="mt-2 text-xs text-hb-muted">{{ __('company.organization.assign_roles_unavailable') }}</p>
                    @endunless
                </div>
                <p class="mt-3 text-xs text-hb-muted">{{ __('company.employees.preview_disclaimer') }}</p>
                <div class="mt-5 flex flex-wrap justify-end gap-2">
                    <button type="button" class="hb-btn-outline" data-close-approve>{{ __('company.employees.cancel') }}</button>
                    <button type="button" class="hb-btn-primary" data-confirm-approve>{{ __('company.employees.confirm_approve') }}</button>
                </div>
            </div>
        </div>

        <div data-decline-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4" hidden role="dialog" aria-modal="true" aria-labelledby="decline-modal-title">
            <div class="w-full max-w-md rounded-hb border border-hb-border bg-hb-surface p-5 shadow-hb-lg">
                <h2 id="decline-modal-title" class="text-lg font-semibold text-hb-navy">{{ __('company.employees.decline_title') }}</h2>
                <p class="mt-2 text-sm text-hb-muted" data-decline-body></p>
                <div class="mt-4">
                    <label for="decline-reason" class="hb-label">{{ __('company.employees.rejection_reason_label') }} <span class="text-hb-error">*</span></label>
                    <textarea id="decline-reason" class="hb-input mt-1 min-h-24" data-decline-reason rows="3" placeholder="{{ __('company.employees.rejection_reason_placeholder') }}"></textarea>
                    <p class="hb-field-error hidden" data-decline-reason-error role="alert"></p>
                </div>
                <p class="mt-3 text-xs text-hb-muted">{{ __('company.employees.preview_disclaimer') }}</p>
                <div class="mt-5 flex flex-wrap justify-end gap-2">
                    <button type="button" class="hb-btn-outline" data-close-decline>{{ __('company.employees.cancel') }}</button>
                    <button type="button" class="hb-btn-primary" data-confirm-decline>{{ __('company.employees.confirm_decline') }}</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (() => {
                const root = document.querySelector('[data-company-join-requests]');
                if (!root) return;
                const i18n = JSON.parse(root.getAttribute('data-i18n') || '{}');
                const requests = JSON.parse(root.getAttribute('data-requests') || '[]');
                const canAssignRoles = root.getAttribute('data-can-assign-roles') === '1';
                const listEl = root.querySelector('[data-req-list]');
                const emptyEl = root.querySelector('[data-req-empty]');
                const feedback = root.querySelector('[data-req-feedback]');
                const qInput = root.querySelector('[data-req-q]');
                const tabs = [...root.querySelectorAll('[data-req-tab]')];
                const actionsEl = root.querySelector('[data-req-actions]');
                const roleSelect = root.querySelector('[data-approve-role]');
                const roleError = root.querySelector('[data-approve-role-error]');
                const reasonInput = root.querySelector('[data-decline-reason]');
                const reasonError = root.querySelector('[data-decline-reason-error]');
                let tab = 'pending';
                let selected = requests.find((row) => row.status_key === 'pending') || requests[0] || null;
                let lastFocus = null;

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
                    return requests.filter((row) => {
                        if (tab !== 'all' && row.status_key !== tab) return false;
                        if (!q) return true;
                        const hay = `${row.name || ''} ${row.email || ''} ${row.requested_role || ''}`.toLowerCase();
                        return hay.includes(q);
                    });
                }

                function paintDetail() {
                    const set = (sel, text) => {
                        const el = root.querySelector(sel);
                        if (el) el.textContent = text || '';
                    };
                    const rejectionWrap = root.querySelector('[data-detail-rejection-wrap]');
                    if (!selected) {
                        set('[data-detail-name]', '—');
                        set('[data-detail-email]', '');
                        set('[data-detail-role]', '');
                        set('[data-detail-dept]', '');
                        set('[data-detail-date]', '');
                        set('[data-detail-note]', '');
                        set('[data-detail-status]', '');
                        set('[data-detail-rejection]', '');
                        rejectionWrap?.classList.add('hidden');
                        actionsEl?.classList.add('hidden');
                        return;
                    }
                    set('[data-detail-name]', selected.name);
                    set('[data-detail-email]', selected.email);
                    set('[data-detail-role]', selected.requested_role);
                    set('[data-detail-dept]', selected.department);
                    set('[data-detail-date]', selected.requested_at);
                    set('[data-detail-note]', selected.note);
                    set('[data-detail-status]', selected.status);
                    const reason = selected.rejection_reason || '';
                    set('[data-detail-rejection]', reason);
                    rejectionWrap?.classList.toggle('hidden', !reason);
                    const canAct = selected.status_key === 'pending';
                    actionsEl?.classList.toggle('hidden', !canAct);
                }

                function render() {
                    const rows = filtered();
                    if (listEl) {
                        listEl.innerHTML = rows.map((row) => {
                            const active = selected && selected.id === row.id;
                            return `
                                <li>
                                    <button type="button" class="w-full px-5 py-4 text-start hover:bg-hb-page ${active ? 'bg-hb-info-soft/40' : ''}" data-select-req="${escapeHtml(row.id)}" aria-current="${active ? 'true' : 'false'}">
                                        <p class="font-semibold text-hb-navy">${escapeHtml(row.name)}</p>
                                        <p class="mt-1 text-sm text-hb-muted" dir="ltr">${escapeHtml(row.email)}</p>
                                        <p class="mt-1 text-sm text-hb-muted">${escapeHtml(row.requested_role)} · ${escapeHtml(row.status)} · ${escapeHtml(row.requested_at)}</p>
                                    </button>
                                </li>
                            `;
                        }).join('');
                    }
                    const hasRows = rows.length > 0;
                    emptyEl?.classList.toggle('hidden', hasRows);
                    if (emptyEl) emptyEl.hidden = hasRows;
                    listEl?.classList.toggle('hidden', !hasRows);
                    if (selected && !rows.some((row) => row.id === selected.id)) {
                        selected = rows[0] || null;
                    }
                    paintDetail();
                }

                function openModal(modal) {
                    if (!modal) return;
                    lastFocus = document.activeElement;
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    modal.hidden = false;
                    const focusable = modal.querySelector('select, textarea, button');
                    focusable?.focus();
                }
                function closeModal(modal) {
                    if (!modal) return;
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    modal.hidden = true;
                    if (lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
                }

                const approveModal = root.querySelector('[data-approve-modal]');
                const declineModal = root.querySelector('[data-decline-modal]');

                tabs.forEach((btn) => {
                    btn.addEventListener('click', () => {
                        tab = btn.getAttribute('data-req-tab') || 'all';
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

                qInput?.addEventListener('input', render);

                listEl?.addEventListener('click', (event) => {
                    const btn = event.target.closest('[data-select-req]');
                    if (!btn) return;
                    selected = requests.find((row) => row.id === btn.getAttribute('data-select-req')) || null;
                    render();
                });

                root.querySelector('[data-open-approve]')?.addEventListener('click', () => {
                    if (!selected || selected.status_key !== 'pending') return;
                    const body = root.querySelector('[data-approve-body]');
                    if (body) body.textContent = replacePlaceholders(i18n.approve_body, { name: selected.name });
                    if (roleSelect) {
                        roleSelect.value = selected.requested_role_key || '';
                        roleSelect.disabled = !canAssignRoles;
                    }
                    roleError?.classList.add('hidden');
                    openModal(approveModal);
                });
                root.querySelector('[data-open-decline]')?.addEventListener('click', () => {
                    if (!selected || selected.status_key !== 'pending') return;
                    const body = root.querySelector('[data-decline-body]');
                    if (body) body.textContent = replacePlaceholders(i18n.decline_body, { name: selected.name });
                    if (reasonInput) reasonInput.value = '';
                    reasonError?.classList.add('hidden');
                    openModal(declineModal);
                });
                root.querySelector('[data-close-approve]')?.addEventListener('click', () => closeModal(approveModal));
                root.querySelector('[data-close-decline]')?.addEventListener('click', () => closeModal(declineModal));
                root.querySelector('[data-confirm-approve]')?.addEventListener('click', () => {
                    const roleValue = roleSelect?.value || '';
                    if (canAssignRoles && !roleValue) {
                        if (roleError) {
                            roleError.textContent = i18n.assign_role_required || '';
                            roleError.classList.remove('hidden');
                        }
                        roleSelect?.focus();
                        return;
                    }
                    const roleLabel = roleSelect?.selectedOptions?.[0]?.textContent?.trim() || roleValue;
                    closeModal(approveModal);
                    if (feedback) {
                        feedback.textContent = roleLabel
                            ? replacePlaceholders(i18n.approved_with_role || i18n.approved_feedback, {
                                name: selected?.name || '',
                                role: roleLabel,
                            })
                            : replacePlaceholders(i18n.approved_feedback, { name: selected?.name || '' });
                        feedback.classList.remove('hidden');
                    }
                });
                root.querySelector('[data-confirm-decline]')?.addEventListener('click', () => {
                    const reason = (reasonInput?.value || '').trim();
                    if (!reason) {
                        if (reasonError) {
                            reasonError.textContent = i18n.rejection_reason_required || '';
                            reasonError.classList.remove('hidden');
                        }
                        reasonInput?.focus();
                        return;
                    }
                    closeModal(declineModal);
                    if (feedback) {
                        feedback.textContent = replacePlaceholders(i18n.declined_feedback, { name: selected?.name || '' });
                        feedback.classList.remove('hidden');
                    }
                });

                [approveModal, declineModal].forEach((modal) => {
                    modal?.addEventListener('click', (event) => {
                        if (event.target === modal) closeModal(modal);
                    });
                });
                document.addEventListener('keydown', (event) => {
                    if (event.key !== 'Escape') return;
                    closeModal(approveModal);
                    closeModal(declineModal);
                });

                render();
            })();
        </script>
    @endpush
@endsection
