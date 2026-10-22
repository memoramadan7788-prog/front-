@extends('layouts.app')

@section('title', $pageTitle ?? __('candidate.membership.page_title'))
@section('breadcrumb', __('candidate.workspace') . ' / ' . __('candidate.membership.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.membership.title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.membership.intro') }}</p>
@endsection

@section('content')
    @php
        $membership = $membership ?? [];
        $request = $membership['request'] ?? null;
        $companies = $membership['companies'] ?? [];
        $demoState = $membership['demo_state'] ?? 'none';
        $hasActiveRequest = is_array($request) && in_array($request['status'] ?? '', ['pending', 'approved'], true);
    @endphp

    <div
        class="max-w-3xl"
        data-candidate-membership
        data-storage-key="{{ $membership['storage_key'] ?? 'hb_candidate_membership_request_v1' }}"
        data-demo-state="{{ $demoState }}"
        data-has-active="{{ $hasActiveRequest ? 'true' : 'false' }}"
        data-i18n="{{ json_encode([
            'preview_saved' => __('candidate.membership.preview_saved'),
            'preview_cleared' => __('candidate.membership.preview_cleared'),
            'validation_company' => __('candidate.membership.validation_company'),
            'validation_title' => __('candidate.membership.validation_title'),
            'validation_department' => __('candidate.membership.validation_department'),
            'one_request' => __('candidate.membership.one_request_rule'),
            'works_yes' => __('candidate.membership.works_yes'),
        ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
    >
        <x-alert variant="info" class="mb-6">
            {{ __('candidate.membership.preview_disclaimer') }}
        </x-alert>

        @if (is_array($request))
            <section class="hb-surface mb-6 p-5 sm:p-6" aria-labelledby="membership-status-heading" data-membership-status-card>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 id="membership-status-heading" class="text-lg font-semibold text-hb-navy">{{ __('candidate.membership.current_request') }}</h2>
                        <p class="mt-1 text-sm text-hb-muted">{{ $request['company_name'] }}</p>
                    </div>
                    <x-badge
                        :variant="match ($request['status'] ?? '') {
                            'approved' => 'success',
                            'rejected' => 'error',
                            default => 'pending',
                        }"
                        :dot="true"
                    >
                        {{ $request['status_label'] ?? $request['status'] }}
                    </x-badge>
                </div>
                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-hb-muted">{{ __('candidate.membership.job_title') }}</dt>
                        <dd class="font-medium text-hb-navy">{{ $request['job_title'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-hb-muted">{{ __('candidate.membership.department') }}</dt>
                        <dd class="font-medium text-hb-navy">{{ $request['department'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-hb-muted">{{ __('candidate.membership.submitted_at') }}</dt>
                        <dd class="font-medium text-hb-navy">{{ $request['submitted_at'] ?? '—' }}</dd>
                    </div>
                    @if (! empty($request['assigned_role']))
                        <div>
                            <dt class="text-hb-muted">{{ __('candidate.membership.assigned_role') }}</dt>
                            <dd class="font-medium text-hb-navy">{{ $request['assigned_role'] }}</dd>
                        </div>
                    @endif
                </dl>
                @if (! empty($request['rejection_reason']))
                    <x-alert variant="error" class="mt-4">
                        <span class="font-medium">{{ __('candidate.membership.rejection_reason') }}:</span>
                        {{ $request['rejection_reason'] }}
                    </x-alert>
                @endif
                @if (! empty($request['can_submit_another']))
                    <p class="mt-4 text-sm text-hb-muted">{{ __('candidate.membership.can_submit_another') }}</p>
                @elseif (($request['status'] ?? '') === 'pending')
                    <p class="mt-4 text-sm text-hb-muted">{{ __('candidate.membership.one_request_rule') }}</p>
                @endif
            </section>
        @endif

        <section class="hb-surface p-5 sm:p-6" aria-labelledby="membership-form-heading" data-membership-form-section>
            <h2 id="membership-form-heading" class="text-lg font-semibold text-hb-navy">{{ __('candidate.membership.form_title') }}</h2>
            <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.membership.form_intro') }}</p>

            @if ($hasActiveRequest && ($request['status'] ?? '') === 'pending')
                <x-alert variant="warning" class="mt-4" data-one-request-banner>
                    {{ __('candidate.membership.one_request_rule') }}
                </x-alert>
            @endif

            <form class="mt-5 space-y-5" data-membership-form novalidate @if ($hasActiveRequest && ($request['status'] ?? '') === 'pending') aria-disabled="true" @endif>
                @php $lockForm = $hasActiveRequest && ($request['status'] ?? '') === 'pending'; @endphp
                <fieldset class="space-y-3" @if ($lockForm) disabled @endif>
                    <legend class="hb-label">{{ __('candidate.membership.works_at_company') }}</legend>
                    <label class="flex items-center gap-2 text-sm text-hb-text">
                        <input type="radio" name="works_at_company" value="no" class="rounded border-hb-border text-hb-blue" data-works-at checked>
                        <span>{{ __('candidate.membership.works_no') }}</span>
                    </label>
                    <label class="flex items-center gap-2 text-sm text-hb-text">
                        <input type="radio" name="works_at_company" value="yes" class="rounded border-hb-border text-hb-blue" data-works-at>
                        <span>{{ __('candidate.membership.works_yes') }}</span>
                    </label>
                </fieldset>

                <div class="hidden space-y-4" data-membership-fields hidden>
                    <div data-field="company_id">
                        <label for="membership-company" class="hb-label">{{ __('candidate.membership.company') }} <span class="text-hb-error">*</span></label>
                        <select id="membership-company" name="company_id" class="hb-input" data-membership-company @if ($lockForm) disabled @endif>
                            <option value="">{{ __('candidate.membership.company_placeholder') }}</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company['id'] }}" data-company-name="{{ $company['name'] }}">{{ $company['name'] }} · {{ $company['domain'] }}</option>
                            @endforeach
                        </select>
                        <p class="hb-field-error hidden" data-error-for="company_id" role="alert"></p>
                    </div>
                    <div data-field="job_title">
                        <label for="membership-title" class="hb-label">{{ __('candidate.membership.job_title') }} <span class="text-hb-error">*</span></label>
                        <input id="membership-title" name="job_title" type="text" class="hb-input" autocomplete="organization-title" data-membership-title @if ($lockForm) disabled @endif>
                        <p class="hb-field-error hidden" data-error-for="job_title" role="alert"></p>
                    </div>
                    <div data-field="department">
                        <label for="membership-department" class="hb-label">{{ __('candidate.membership.department') }} <span class="text-hb-error">*</span></label>
                        <input id="membership-department" name="department" type="text" class="hb-input" data-membership-department @if ($lockForm) disabled @endif>
                        <p class="hb-field-error hidden" data-error-for="department" role="alert"></p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="hb-btn-primary" data-membership-submit @if ($lockForm) disabled @endif>
                        {{ __('candidate.membership.submit_preview') }}
                    </button>
                    <button type="button" class="hb-btn-outline" data-membership-clear>
                        {{ __('candidate.membership.clear_preview') }}
                    </button>
                    <a href="{{ route('candidate.profile') }}" class="hb-btn-outline">{{ __('candidate.membership.back_profile') }}</a>
                </div>
            </form>

            <p class="mt-4 hidden text-sm text-hb-blue" data-membership-feedback role="status"></p>
        </section>

        <p class="mt-6 text-xs text-hb-muted">
            {{ __('candidate.membership.demo_states') }}
            <a class="text-hb-blue hover:underline" href="{{ route('candidate.membership', ['membership' => 'pending']) }}">pending</a>
            ·
            <a class="text-hb-blue hover:underline" href="{{ route('candidate.membership', ['membership' => 'approved']) }}">approved</a>
            ·
            <a class="text-hb-blue hover:underline" href="{{ route('candidate.membership', ['membership' => 'rejected']) }}">rejected</a>
            ·
            <a class="text-hb-blue hover:underline" href="{{ route('candidate.membership', ['membership' => 'none']) }}">none</a>
        </p>
    </div>

    @push('scripts')
        <script>
            (() => {
                const root = document.querySelector('[data-candidate-membership]');
                if (!root) return;
                const i18n = JSON.parse(root.getAttribute('data-i18n') || '{}');
                const storageKey = root.getAttribute('data-storage-key') || '';
                const form = root.querySelector('[data-membership-form]');
                const fields = root.querySelector('[data-membership-fields]');
                const feedback = root.querySelector('[data-membership-feedback]');
                const hasActive = root.getAttribute('data-has-active') === 'true';

                function showFields(show) {
                    if (!fields) return;
                    fields.classList.toggle('hidden', !show);
                    fields.hidden = !show;
                }

                root.querySelectorAll('[data-works-at]').forEach((input) => {
                    input.addEventListener('change', () => showFields(input.value === 'yes' && input.checked));
                });

                function setError(name, message) {
                    const el = root.querySelector(`[data-error-for="${name}"]`);
                    if (!el) return;
                    el.textContent = message || '';
                    el.classList.toggle('hidden', !message);
                }

                form?.addEventListener('submit', (event) => {
                    event.preventDefault();
                    if (hasActive) {
                        if (feedback) {
                            feedback.textContent = i18n.one_request || '';
                            feedback.classList.remove('hidden');
                        }
                        return;
                    }
                    const worksYes = root.querySelector('[data-works-at][value="yes"]')?.checked;
                    if (!worksYes) {
                        showFields(false);
                        if (feedback) {
                            feedback.textContent = i18n.preview_cleared || '';
                            feedback.classList.remove('hidden');
                        }
                        if (storageKey) window.localStorage.removeItem(storageKey);
                        return;
                    }
                    const company = root.querySelector('[data-membership-company]');
                    const title = root.querySelector('[data-membership-title]');
                    const department = root.querySelector('[data-membership-department]');
                    let valid = true;
                    setError('company_id', '');
                    setError('job_title', '');
                    setError('department', '');
                    if (!company?.value) {
                        setError('company_id', i18n.validation_company || '');
                        valid = false;
                    }
                    if (!title?.value?.trim()) {
                        setError('job_title', i18n.validation_title || '');
                        valid = false;
                    }
                    if (!department?.value?.trim()) {
                        setError('department', i18n.validation_department || '');
                        valid = false;
                    }
                    if (!valid) return;
                    const companyName = company.selectedOptions[0]?.getAttribute('data-company-name') || company.value;
                    const draft = {
                        id: 'local-draft',
                        company_id: company.value,
                        company_name: companyName,
                        job_title: title.value.trim(),
                        department: department.value.trim(),
                        status: 'pending',
                        saved_at: new Date().toISOString(),
                        preview_only: true,
                    };
                    if (storageKey) {
                        window.localStorage.setItem(storageKey, JSON.stringify(draft));
                    }
                    if (feedback) {
                        feedback.textContent = i18n.preview_saved || '';
                        feedback.classList.remove('hidden');
                    }
                });

                root.querySelector('[data-membership-clear]')?.addEventListener('click', () => {
                    if (storageKey) window.localStorage.removeItem(storageKey);
                    form?.reset();
                    showFields(false);
                    setError('company_id', '');
                    setError('job_title', '');
                    setError('department', '');
                    if (feedback) {
                        feedback.textContent = i18n.preview_cleared || '';
                        feedback.classList.remove('hidden');
                    }
                });

                try {
                    const saved = storageKey ? JSON.parse(window.localStorage.getItem(storageKey) || 'null') : null;
                    if (saved && !hasActive) {
                        const yes = root.querySelector('[data-works-at][value="yes"]');
                        if (yes) yes.checked = true;
                        showFields(true);
                        const company = root.querySelector('[data-membership-company]');
                        const title = root.querySelector('[data-membership-title]');
                        const department = root.querySelector('[data-membership-department]');
                        if (company) company.value = saved.company_id || '';
                        if (title) title.value = saved.job_title || '';
                        if (department) department.value = saved.department || '';
                    }
                } catch (e) {}
            })();
        </script>
    @endpush
@endsection
