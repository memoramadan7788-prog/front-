@extends('layouts.app')

@section('title', $pageTitle ?? __('company.employees.page_title'))
@section('breadcrumb', __('company.employees.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ __('company.employees.title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ __('company.employees.subtitle') }}</p>
@endsection

@section('page_actions')
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('company.employees.requests') }}" class="hb-btn-outline">
            {{ __('company.employees.join_requests', ['count' => $team['join_request_count']]) }}
        </a>
        <button type="button" class="hb-btn-primary" data-open-invite>{{ __('company.employees.invite_teammate') }}</button>
    </div>
@endsection

@section('content')
    @php
        $teamI18n = [
            'invite_sent' => __('company.employees.js.invite_sent'),
            'invite_validation_email' => __('company.employees.js.invite_validation_email'),
            'scope_saved' => __('company.employees.js.scope_saved'),
            'access_removed' => __('company.employees.js.access_removed'),
            'review_done' => __('company.employees.js.review_done'),
            'empty_members' => __('company.employees.js.empty_members'),
            'empty_invites' => __('company.employees.js.empty_invites'),
            'manage' => __('company.employees.manage'),
            'edit_scope' => __('company.employees.edit_scope'),
            'review_access' => __('company.employees.review_access'),
            'view_profile' => __('company.employees.view_profile'),
            'member_line' => __('company.employees.member_line'),
        ];
        $memberCount = count($team['members']);
        $inviteCount = count($team['invites']);
    @endphp

    <div
        data-company-team
        data-i18n="{{ json_encode($teamI18n, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-members="{{ json_encode($team['members'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-invites="{{ json_encode($team['invites'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-show-base="{{ url('/company/employees') }}"
    >
        <div class="mb-6 flex flex-wrap gap-4 border-b border-hb-border text-sm font-semibold" role="tablist" aria-label="{{ __('company.employees.tabs_aria') }}">
            @foreach ([
                ['members', __('company.employees.tab_members', ['count' => $memberCount])],
                ['invites', __('company.employees.tab_invites', ['count' => $inviteCount])],
                ['roles', __('company.employees.tab_roles')],
            ] as [$key, $label])
                <button
                    type="button"
                    role="tab"
                    data-team-tab="{{ $key }}"
                    aria-selected="{{ $key === 'members' ? 'true' : 'false' }}"
                    class="border-b-2 px-1 pb-3 {{ $key === 'members' ? 'border-hb-blue text-hb-blue' : 'border-transparent text-hb-muted hover:text-hb-navy' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div data-panel-members class="space-y-5">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <label class="relative min-w-0 flex-1">
                    <span class="sr-only">{{ __('company.employees.search_placeholder') }}</span>
                    <input type="search" data-team-q class="hb-input w-full" placeholder="{{ __('company.employees.search_placeholder') }}" autocomplete="off">
                </label>
                <div class="flex flex-wrap gap-2">
                    <label class="sr-only" for="team-role">{{ __('company.employees.all_roles') }}</label>
                    <select id="team-role" data-team-role class="hb-input w-auto min-w-[10rem]">
                        <option value="">{{ __('company.employees.all_roles') }}</option>
                        <option value="hr">{{ __('company.employees.role_hr') }}</option>
                        <option value="hiring_manager">{{ __('company.employees.role_hiring_manager') }}</option>
                        <option value="interviewer">{{ __('company.employees.role_interviewer') }}</option>
                        <option value="agency">{{ __('company.employees.role_agency') }}</option>
                    </select>
                    <label class="sr-only" for="team-status">{{ __('company.employees.all_status') }}</label>
                    <select id="team-status" data-team-status class="hb-input w-auto min-w-[9rem]">
                        <option value="">{{ __('company.employees.all_status') }}</option>
                        <option value="active">{{ __('company.employees.status_active') }}</option>
                        <option value="external">{{ __('company.employees.status_external') }}</option>
                    </select>
                    <button type="button" class="hb-btn-outline" data-team-clear>{{ __('company.employees.clear_filters') }}</button>
                </div>
            </div>

            <section class="hb-surface overflow-hidden" aria-label="{{ __('company.employees.tab_members', ['count' => $memberCount]) }}">
                <ul class="divide-y divide-hb-border" data-members-list></ul>
                <p class="hidden px-5 py-10 text-center text-sm text-hb-muted" data-members-empty>{{ __('company.employees.empty_members') }}</p>
            </section>
        </div>

        <div data-panel-invites class="hidden space-y-5" hidden>
            <section class="hb-surface overflow-hidden">
                <ul class="divide-y divide-hb-border" data-invites-list>
                    @forelse ($team['invites'] as $invite)
                        <li class="px-5 py-4">
                            <p class="font-semibold text-hb-navy">{{ $invite['name'] }}</p>
                            <p class="mt-1 text-sm text-hb-muted" dir="ltr">{{ $invite['email'] }}</p>
                            <p class="mt-1 text-sm text-hb-muted">
                                {{ $invite['role'] }} · {{ $invite['scope'] }} · {{ __('company.employees.status_pending') }} · {{ $invite['invited'] }} · {{ $invite['expires'] }}
                            </p>
                        </li>
                    @empty
                        <li class="px-5 py-10 text-center text-sm text-hb-muted">{{ __('company.employees.empty_invites') }}</li>
                    @endforelse
                </ul>
            </section>
        </div>

        <div data-panel-roles class="hidden space-y-5" hidden>
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="roles-heading">
                <h2 id="roles-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.employees.permissions_heading') }}</h2>
                <ul class="mt-5 space-y-4">
                    @foreach ($team['roles'] as $role)
                        <li>
                            <p class="font-semibold text-hb-navy">{{ $role['title'] }}</p>
                            <p class="mt-1 text-sm text-hb-muted">{{ $role['body'] }}</p>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="invite-heading">
                <h2 id="invite-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.employees.invite_heading') }}</h2>
                <form class="mt-5 space-y-4" data-invite-form novalidate>
                    <label class="block">
                        <span class="hb-label">{{ __('company.employees.work_email') }}</span>
                        <input type="email" class="hb-input mt-1 w-full" data-invite-email value="{{ $team['invite_defaults']['email'] }}" dir="ltr" autocomplete="off">
                    </label>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="hb-label">{{ __('company.employees.role') }}</span>
                            <select class="hb-input mt-1 w-full" data-invite-role>
                                @foreach ([
                                    'hr' => __('company.employees.role_hr'),
                                    'hiring_manager' => __('company.employees.role_hiring_manager'),
                                    'interviewer' => __('company.employees.role_interviewer'),
                                    'agency' => __('company.employees.role_agency'),
                                ] as $value => $label)
                                    <option value="{{ $value }}" @selected($value === $team['invite_defaults']['role_key'])>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="hb-label">{{ __('company.employees.scope') }}</span>
                            <select class="hb-input mt-1 w-full" data-invite-scope>
                                @foreach ($team['scope_options'] as $scope)
                                    <option value="{{ $scope }}" @selected($scope === $team['invite_defaults']['scope'])>{{ $scope }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <label class="flex items-start gap-2 text-sm text-hb-text">
                        <input type="checkbox" class="mt-1" data-invite-send-email @checked($team['invite_defaults']['send_email'])>
                        <span>{{ __('company.employees.send_email') }}</span>
                    </label>
                    <button type="submit" class="hb-btn-primary">{{ __('company.employees.send_invite') }}</button>
                    <p class="text-sm text-hb-muted">
                        <span class="font-semibold text-hb-navy">{{ __('company.employees.pending_label') }}</span>
                        {{ $team['pending_invite_note'] }}
                    </p>
                </form>
            </section>

            <section class="hb-surface p-5 sm:p-6" aria-labelledby="boundaries-heading">
                <h2 id="boundaries-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.employees.permissions_heading') }}</h2>
                <ul class="mt-5 space-y-4">
                    @foreach ($team['roles'] as $role)
                        <li>
                            <p class="font-semibold text-hb-navy">{{ $role['title'] }}</p>
                            <p class="mt-1 text-sm text-hb-muted">{{ $role['body'] }}</p>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>

        <div class="mt-5 rounded-lg border border-hb-blue/20 bg-hb-info-soft/50 px-4 py-3 text-sm text-hb-text" role="note">
            <p class="font-semibold text-hb-navy">{{ __('company.employees.audit_heading') }}</p>
            <p class="mt-1 text-hb-muted">{{ $team['audit_body'] }}</p>
        </div>

        <p class="mt-3 hidden text-sm text-hb-blue" data-team-feedback role="status"></p>
        <p class="mt-8 text-center text-xs text-hb-muted">{{ $team['footer'] }}</p>

        {{-- Scope modal --}}
        <div data-scope-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4" hidden role="dialog" aria-modal="true" aria-labelledby="scope-modal-title">
            <div class="w-full max-w-md rounded-hb border border-hb-border bg-hb-surface p-5 shadow-hb-lg">
                <h2 id="scope-modal-title" class="text-lg font-semibold text-hb-navy">{{ __('company.employees.scope_modal_title') }}</h2>
                <p class="mt-2 text-sm text-hb-muted">{{ __('company.employees.scope_modal_body') }}</p>
                <p class="mt-3 text-sm font-semibold text-hb-navy" data-scope-name></p>
                <label class="mt-4 block">
                    <span class="hb-label">{{ __('company.employees.scope') }}</span>
                    <select class="hb-input mt-1 w-full" data-scope-select>
                        @foreach ($team['scope_options'] as $scope)
                            <option value="{{ $scope }}">{{ $scope }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="mt-5 flex flex-wrap justify-end gap-2">
                    <button type="button" class="hb-btn-outline" data-close-scope>{{ __('company.employees.scope_cancel') }}</button>
                    <button type="button" class="hb-btn-primary" data-confirm-scope>{{ __('company.employees.scope_save') }}</button>
                </div>
            </div>
        </div>

        <div data-manage-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4" hidden role="dialog" aria-modal="true" aria-labelledby="manage-modal-title">
            <div class="w-full max-w-md rounded-hb border border-hb-border bg-hb-surface p-5 shadow-hb-lg">
                <h2 id="manage-modal-title" class="text-lg font-semibold text-hb-navy">{{ __('company.employees.manage_modal_title') }}</h2>
                <p class="mt-2 text-sm text-hb-muted">{{ __('company.employees.manage_modal_body') }}</p>
                <p class="mt-3 text-sm font-semibold text-hb-navy" data-manage-name></p>
                <div class="mt-5 flex flex-wrap justify-end gap-2">
                    <button type="button" class="hb-btn-outline" data-close-manage>{{ __('company.employees.modal_close') }}</button>
                    <a href="{{ route('company.employees.index') }}" class="hb-btn-outline hidden" data-manage-details hidden>{{ __('company.employees.view_profile') }}</a>
                    <button type="button" class="hb-btn-primary" data-confirm-remove>{{ __('company.employees.remove_access') }}</button>
                </div>
            </div>
        </div>

        <div data-review-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4" hidden role="dialog" aria-modal="true" aria-labelledby="review-modal-title">
            <div class="w-full max-w-md rounded-hb border border-hb-border bg-hb-surface p-5 shadow-hb-lg">
                <h2 id="review-modal-title" class="text-lg font-semibold text-hb-navy">{{ __('company.employees.review_modal_title') }}</h2>
                <p class="mt-2 text-sm text-hb-muted">{{ __('company.employees.review_modal_body') }}</p>
                <p class="mt-3 text-sm font-semibold text-hb-navy" data-review-name></p>
                <div class="mt-5 flex flex-wrap justify-end gap-2">
                    <button type="button" class="hb-btn-outline" data-close-review>{{ __('company.employees.modal_close') }}</button>
                    <button type="button" class="hb-btn-primary" data-confirm-review>{{ __('company.employees.review_access') }}</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (() => {
                const root = document.querySelector('[data-company-team]');
                if (!root) return;
                const i18n = JSON.parse(root.getAttribute('data-i18n') || '{}');
                const members = JSON.parse(root.getAttribute('data-members') || '[]');
                const showBase = root.getAttribute('data-show-base') || '/company/employees';
                const feedback = root.querySelector('[data-team-feedback]');
                const listEl = root.querySelector('[data-members-list]');
                const emptyEl = root.querySelector('[data-members-empty]');
                const qInput = root.querySelector('[data-team-q]');
                const roleSelect = root.querySelector('[data-team-role]');
                const statusSelect = root.querySelector('[data-team-status]');
                const tabs = [...root.querySelectorAll('[data-team-tab]')];
                const panels = {
                    members: root.querySelector('[data-panel-members]'),
                    invites: root.querySelector('[data-panel-invites]'),
                    roles: root.querySelector('[data-panel-roles]'),
                };
                let activeMember = null;

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

                function filteredMembers() {
                    const q = (qInput?.value || '').trim().toLowerCase();
                    const role = roleSelect?.value || '';
                    const status = statusSelect?.value || '';
                    return members.filter((row) => {
                        if (role && row.role_key !== role) return false;
                        if (status && row.status_key !== status) return false;
                        if (!q) return true;
                        const hay = `${row.name || ''} ${row.email || ''} ${row.role || ''} ${row.scope || ''}`.toLowerCase();
                        return hay.includes(q);
                    });
                }

                function actionLabel(key) {
                    if (key === 'manage') return i18n.manage || '';
                    if (key === 'review') return i18n.review_access || '';
                    return i18n.edit_scope || '';
                }

                function renderMembers() {
                    const rows = filteredMembers();
                    if (listEl) {
                        listEl.innerHTML = rows.map((row) => `
                            <li class="px-5 py-4" data-member-id="${escapeHtml(row.id)}">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-hb-navy">${escapeHtml(row.name)}</p>
                                        <p class="mt-1 text-sm text-hb-muted">
                                            ${escapeHtml(replacePlaceholders(i18n.member_line, {
                                                role: row.role || '',
                                                scope: row.scope || '',
                                                status: row.status || '',
                                            }))}
                                        </p>
                                    </div>
                                    <div class="flex flex-wrap gap-3">
                                        <a href="${escapeHtml(showBase)}/${escapeHtml(row.id)}" class="text-sm font-semibold text-hb-blue hover:underline">${escapeHtml(i18n.view_profile || '')}</a>
                                        <button type="button" class="text-sm font-semibold text-hb-blue hover:underline" data-member-action="${escapeHtml(row.action_key)}" data-member-id="${escapeHtml(row.id)}" data-member-name="${escapeHtml(row.name)}" data-member-scope="${escapeHtml(row.scope)}">
                                            ${escapeHtml(actionLabel(row.action_key))}
                                        </button>
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

                function showPanel(key) {
                    Object.entries(panels).forEach(([name, panel]) => {
                        const active = name === key;
                        if (!panel) return;
                        panel.classList.toggle('hidden', !active);
                        panel.hidden = !active;
                    });
                    tabs.forEach((btn) => {
                        const active = btn.getAttribute('data-team-tab') === key;
                        btn.setAttribute('aria-selected', active ? 'true' : 'false');
                        btn.classList.toggle('border-hb-blue', active);
                        btn.classList.toggle('text-hb-blue', active);
                        btn.classList.toggle('border-transparent', !active);
                        btn.classList.toggle('text-hb-muted', !active);
                    });
                }

                function openModal(modal) {
                    if (!modal) return;
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    modal.hidden = false;
                    modal.querySelector('button, input, select, a')?.focus();
                }

                function closeModal(modal) {
                    if (!modal) return;
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    modal.hidden = true;
                }

                const scopeModal = root.querySelector('[data-scope-modal]');
                const manageModal = root.querySelector('[data-manage-modal]');
                const reviewModal = root.querySelector('[data-review-modal]');

                tabs.forEach((btn) => {
                    btn.addEventListener('click', () => showPanel(btn.getAttribute('data-team-tab') || 'members'));
                });

                [qInput, roleSelect, statusSelect].forEach((el) => {
                    el?.addEventListener('input', renderMembers);
                    el?.addEventListener('change', renderMembers);
                });

                root.querySelector('[data-team-clear]')?.addEventListener('click', () => {
                    if (qInput) qInput.value = '';
                    if (roleSelect) roleSelect.value = '';
                    if (statusSelect) statusSelect.value = '';
                    renderMembers();
                });

                listEl?.addEventListener('click', (event) => {
                    const btn = event.target.closest('[data-member-action]');
                    if (!btn) return;
                    const id = btn.getAttribute('data-member-id');
                    const name = btn.getAttribute('data-member-name') || '';
                    const scope = btn.getAttribute('data-member-scope') || '';
                    const action = btn.getAttribute('data-member-action');
                    activeMember = { id, name, scope };
                    if (action === 'manage') {
                        const nameEl = root.querySelector('[data-manage-name]');
                        if (nameEl) nameEl.textContent = name;
                        const details = root.querySelector('[data-manage-details]');
                        if (details && id) {
                            details.setAttribute('href', `${showBase}/${encodeURIComponent(id)}`);
                            details.classList.remove('hidden');
                            details.hidden = false;
                        } else if (details) {
                            details.removeAttribute('href');
                            details.classList.add('hidden');
                            details.hidden = true;
                        }
                        openModal(manageModal);
                        return;
                    }
                    if (action === 'review') {
                        const nameEl = root.querySelector('[data-review-name]');
                        if (nameEl) nameEl.textContent = name;
                        openModal(reviewModal);
                        return;
                    }
                    const nameEl = root.querySelector('[data-scope-name]');
                    if (nameEl) nameEl.textContent = name;
                    const select = root.querySelector('[data-scope-select]');
                    if (select && scope) select.value = scope;
                    openModal(scopeModal);
                });

                root.querySelector('[data-close-scope]')?.addEventListener('click', () => closeModal(scopeModal));
                root.querySelector('[data-confirm-scope]')?.addEventListener('click', () => {
                    closeModal(scopeModal);
                    if (feedback) {
                        feedback.textContent = replacePlaceholders(i18n.scope_saved, { name: activeMember?.name || '' });
                        feedback.classList.remove('hidden');
                    }
                });

                root.querySelector('[data-close-manage]')?.addEventListener('click', () => closeModal(manageModal));
                root.querySelector('[data-confirm-remove]')?.addEventListener('click', () => {
                    closeModal(manageModal);
                    if (feedback) {
                        feedback.textContent = replacePlaceholders(i18n.access_removed, { name: activeMember?.name || '' });
                        feedback.classList.remove('hidden');
                    }
                });

                root.querySelector('[data-close-review]')?.addEventListener('click', () => closeModal(reviewModal));
                root.querySelector('[data-confirm-review]')?.addEventListener('click', () => {
                    closeModal(reviewModal);
                    if (feedback) {
                        feedback.textContent = replacePlaceholders(i18n.review_done, { name: activeMember?.name || '' });
                        feedback.classList.remove('hidden');
                    }
                });

                document.querySelector('[data-open-invite]')?.addEventListener('click', () => {
                    showPanel('members');
                    root.querySelector('[data-invite-email]')?.focus();
                    root.querySelector('#invite-heading')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });

                root.querySelector('[data-invite-form]')?.addEventListener('submit', (event) => {
                    event.preventDefault();
                    const email = (root.querySelector('[data-invite-email]')?.value || '').trim();
                    if (!email) {
                        if (feedback) {
                            feedback.textContent = i18n.invite_validation_email || '';
                            feedback.classList.remove('hidden');
                        }
                        return;
                    }
                    if (feedback) {
                        feedback.textContent = replacePlaceholders(i18n.invite_sent, { email });
                        feedback.classList.remove('hidden');
                    }
                });

                [scopeModal, manageModal, reviewModal].forEach((modal) => {
                    modal?.addEventListener('click', (event) => {
                        if (event.target === modal) closeModal(modal);
                    });
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key !== 'Escape') return;
                    closeModal(scopeModal);
                    closeModal(manageModal);
                    closeModal(reviewModal);
                });

                renderMembers();
            })();
        </script>
    @endpush
@endsection
