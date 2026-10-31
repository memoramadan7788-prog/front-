@extends('frontend.layouts.app')

@section('title', data_get($company, 'name', __('common.role_labels.company')))

@section('page_header')
    <div class="flex flex-wrap items-center gap-3">
        <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ data_get($company, 'name', '—') }}</h1>
        @php
            $status = data_get($company, 'status');
            $companyId = data_get($company, 'id');
            $permissions = data_get(auth()->user(), 'permissions', []);
            $canSuspend = is_array($permissions) && in_array('companies.suspend', $permissions, true);
            $hasJobsCount = is_numeric(data_get($company, 'jobs_count'));
            $hasEmployeesCount = is_numeric(data_get($company, 'employees_count'));
        @endphp
        <x-badge :variant="\App\Support\Rows::badgeVariant($status)" :dot="true">{{ $status ?? '—' }}</x-badge>
    </div>
@endsection

@section('page_actions')
    <x-button variant="light" size="md" :href="route('frontend.admin.companies.index')">{{ __('admin.organizations.back_to_directory') }}</x-button>
    @if (in_array($status, ['pending', 'suspended'], true))
        <form method="POST" action="{{ route('frontend.admin.companies.approve', ['companyId' => $companyId]) }}" class="inline">@csrf <x-button type="submit" variant="primary" size="md">{{ $status === 'suspended' ? __('Reactivate') : __('Approve') }}</x-button></form>
    @endif
    @if ($status === 'pending')
        <x-button variant="destructive" size="md" :href="route('frontend.admin.companies.reject', ['companyId' => $companyId])">{{ __('Reject') }}</x-button>
    @endif
@endsection

@section('content')
<div class="mx-auto max-w-6xl space-y-5" data-admin-organization-detail>
    @if (session('success_key'))
        <x-alert variant="success" role="status">{{ __(session('success_key')) }}</x-alert>
    @endif
    @if (session('success'))
        <x-alert variant="success" role="status">{{ session('success') }}</x-alert>
    @endif
    @if ($errors->has('error'))
        <x-alert variant="error" role="alert">{{ $errors->first('error') }}</x-alert>
    @endif

    <p class="text-sm text-hb-muted">{{ __('admin.organizations.limited_overview_note') }}</p>

    <div class="flex flex-wrap gap-2 border-b border-hb-border pb-3" role="tablist" aria-label="{{ __('admin.organizations.detail_tablist_aria') }}" data-organization-tabs>
        @foreach (['subscription' => __('admin.organizations.tab_subscription'), 'members' => __('admin.organizations.tab_members'), 'usage' => __('admin.organizations.tab_usage'), 'admin' => __('admin.organizations.tab_admin_actions')] as $tab => $label)
            <button type="button" id="organization-tab-{{ $tab }}" role="tab" aria-controls="organization-panel-{{ $tab }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" tabindex="{{ $loop->first ? '0' : '-1' }}" data-organization-tab="{{ $tab }}" class="shrink-0 border-b-2 px-4 py-3 text-sm font-semibold {{ $loop->first ? 'border-hb-blue text-hb-blue' : 'border-transparent text-hb-muted hover:text-hb-navy' }}">{{ $label }}</button>
        @endforeach
    </div>

    <section id="organization-panel-subscription" role="tabpanel" aria-labelledby="organization-tab-subscription" data-organization-panel="subscription">
        <x-card>
            <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('admin.organizations.subscription_heading') }}</h2></x-slot:header>
            <p class="text-sm text-hb-muted">{{ __('admin.organizations.subscription_unavailable') }}</p>
            <p class="mt-3 text-sm text-hb-muted">{{ __('admin.organizations.boundary_no_write') }}</p>
        </x-card>
    </section>

    <section id="organization-panel-members" role="tabpanel" aria-labelledby="organization-tab-members" data-organization-panel="members" class="hidden">
        <x-card>
            <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('admin.organizations.members_heading') }}</h2></x-slot:header>
            <p class="mb-4 text-sm text-hb-muted">{{ __('admin.organizations.members_intro') }}</p>
            @if ($hasEmployeesCount)
                <dl class="rounded-xl bg-slate-50 p-4 text-sm">
                    <dt class="text-hb-muted">{{ __('admin.organizations.usage_members') }}</dt>
                    <dd class="font-semibold text-hb-navy">{{ data_get($company, 'employees_count') }}</dd>
                </dl>
            @else
                <p class="rounded-xl bg-slate-50 p-4 text-sm text-hb-muted" data-members-unavailable>{{ __('admin.organizations.member_list_unavailable') }}</p>
            @endif
            <p class="mt-4 text-sm text-hb-muted">{{ __('admin.organizations.boundary_no_write') }}</p>
        </x-card>
    </section>

    <section id="organization-panel-usage" role="tabpanel" aria-labelledby="organization-tab-usage" data-organization-panel="usage" class="hidden">
        <x-card>
            <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('admin.organizations.usage_heading') }}</h2></x-slot:header>
            <dl class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-slate-50 p-4 text-sm">
                    <dt class="text-hb-muted">{{ __('common.labels.jobs') }}</dt>
                    <dd class="font-semibold text-hb-navy">{{ $hasJobsCount ? data_get($company, 'jobs_count') : '—' }}</dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-4 text-sm">
                    <dt class="text-hb-muted">{{ __('Employees') }}</dt>
                    <dd class="font-semibold text-hb-navy">{{ $hasEmployeesCount ? data_get($company, 'employees_count') : '—' }}</dd>
                </div>
            </dl>
            <p class="mt-4 text-sm text-hb-muted">{{ __('admin.organizations.usage_scope_hint') }}</p>
        </x-card>
    </section>

    <section id="organization-panel-admin" role="tabpanel" aria-labelledby="organization-tab-admin" data-organization-panel="admin" class="hidden space-y-6">
        <div class="grid gap-6 lg:grid-cols-3">
            <x-card class="lg:col-span-1">
                <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('admin.organizations.members_access') }}</h2></x-slot:header>
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-hb-muted">{{ __('Owner') }}</dt><dd class="font-semibold text-hb-navy">{{ data_get($company, 'owner.name', '—') }}</dd><dd class="text-xs text-hb-muted">{{ data_get($company, 'owner.email') }}</dd></div>
                    <div><dt class="text-hb-muted">{{ __('Registered') }}</dt><dd class="font-semibold text-hb-navy">{{ \App\Support\Rows::date(data_get($company, 'created_at'), 'Y-m-d') }}</dd></div>
                    @if (data_get($company, 'reviewed_by'))
                        <div><dt class="text-hb-muted">{{ __('Reviewed by') }}</dt><dd class="font-semibold text-hb-navy">{{ data_get($company, 'reviewed_by') }}</dd><dd class="text-xs text-hb-muted">{{ \App\Support\Rows::date(data_get($company, 'reviewed_at'), 'Y-m-d H:i') }}</dd></div>
                    @endif
                    @if ($status === 'rejected' && data_get($company, 'rejection_reason'))
                        <div><dt class="text-hb-muted">{{ __('Rejection reason') }}</dt><dd class="text-hb-text">{{ data_get($company, 'rejection_reason') }}</dd></div>
                    @endif
                    @if ($status === 'suspended')
                        <div><dt class="text-hb-muted">{{ __('admin.organizations.suspend_reason_label') }}</dt><dd class="text-hb-text">{{ __('admin.organizations.suspension_reason_unavailable') }}</dd></div>
                    @endif
                </dl>
            </x-card>

            <x-card class="lg:col-span-2">
                <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('Company details') }}</h2></x-slot:header>
                <form method="POST" action="{{ route('frontend.admin.companies.update', ['companyId' => $companyId]) }}" class="space-y-5">
                    @csrf
                    <x-form.input name="name" :label="__('Company name')" type="text" :required="true" :error="$errors->first('name')" value="{{ old('name', data_get($company, 'name', '')) }}" />
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-form.input name="website" :label="__('Website')" type="url" :error="$errors->first('website')" value="{{ old('website', data_get($company, 'website', '')) }}" />
                        <x-form.input name="industry" :label="__('Industry')" type="text" :error="$errors->first('industry')" value="{{ old('industry', data_get($company, 'industry', '')) }}" />
                        <x-form.input name="contact_email" :label="__('Contact email')" type="email" :error="$errors->first('contact_email')" value="{{ old('contact_email', data_get($company, 'contact_email', '')) }}" />
                        <x-form.input name="contact_phone" :label="__('Contact phone')" type="tel" :error="$errors->first('contact_phone')" value="{{ old('contact_phone', data_get($company, 'contact_phone', '')) }}" />
                    </div>
                    <x-form.textarea name="address" :label="__('Address')" rows="3" :error="$errors->first('address')">{{ old('address', data_get($company, 'address', '')) }}</x-form.textarea>
                    <x-button type="submit">{{ __('Save changes') }}</x-button>
                </form>
            </x-card>
        </div>

        @if ($status === 'approved' && $canSuspend)
            <x-card>
                <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('admin.organizations.suspend_heading') }}</h2></x-slot:header>
                <p class="mb-4 text-sm text-hb-muted">{{ __('admin.organizations.suspend_warning') }}</p>
                <form method="POST" action="{{ route('frontend.admin.companies.suspend', ['companyId' => $companyId]) }}" class="space-y-4" data-suspend-form>
                    @csrf
                    <x-form.textarea name="reason" :label="__('admin.organizations.suspend_reason_label')" rows="3" required maxlength="500" :error="$errors->first('reason')">{{ old('reason') }}</x-form.textarea>
                    <label class="flex items-start gap-3 text-sm text-hb-text">
                        <input type="checkbox" name="acknowledged" value="1" required class="mt-1 rounded border-hb-border text-hb-blue focus:ring-hb-blue">
                        <span>{{ __('admin.organizations.suspend_ack') }}</span>
                    </label>
                    @error('acknowledged')
                        <p class="text-sm text-hb-error" role="alert">{{ $message }}</p>
                    @enderror
                    <p class="text-sm text-hb-muted">{{ __('admin.organizations.suspend_footer') }}</p>
                    <x-button type="submit" variant="destructive" data-suspend-submit>{{ __('admin.organizations.review_suspension') }}</x-button>
                </form>
            </x-card>
        @elseif ($status === 'suspended')
            <x-alert variant="warning" role="status">{{ __('admin.organizations.already_suspended') }}</x-alert>
        @endif

        <x-card>
            <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('Company documents') }}</h2></x-slot:header>
            @include('frontend.partials.company-documents', [
                'documents' => data_get($company, 'documents', []),
                'uploadUrl' => route('frontend.admin.companies.documents.upload', ['companyId' => $companyId]),
                'downloadUrl' => fn ($document) => route('frontend.admin.companies.documents.download', ['companyId' => $companyId, 'documentId' => data_get($document, 'id'), 'name' => data_get($document, 'original_filename')]),
                'deleteUrl' => fn ($document) => route('frontend.admin.companies.documents.delete', ['companyId' => $companyId, 'documentId' => data_get($document, 'id')]),
            ])
        </x-card>
    </section>
</div>

<script>
    (() => {
        const tabs = [...document.querySelectorAll('[data-organization-tab]')];
        const panels = [...document.querySelectorAll('[data-organization-panel]')];
        tabs.forEach((tab) => tab.addEventListener('click', () => {
            tabs.forEach((item) => {
                const active = item === tab;
                item.setAttribute('aria-selected', String(active));
                item.tabIndex = active ? 0 : -1;
                item.classList.toggle('border-hb-blue', active);
                item.classList.toggle('text-hb-blue', active);
                item.classList.toggle('border-transparent', !active);
                item.classList.toggle('text-hb-muted', !active);
            });
            panels.forEach((panel) => panel.classList.toggle('hidden', panel.dataset.organizationPanel !== tab.dataset.organizationTab));
        }));

        const form = document.querySelector('[data-suspend-form]');
        if (form) {
            form.addEventListener('submit', (event) => {
                if (!window.confirm(@json(__('admin.organizations.suspend_warning')))
                    || !window.confirm(@json(__('admin.organizations.suspend_footer')))) {
                    event.preventDefault();
                    return;
                }

                const submit = form.querySelector('[data-suspend-submit]');
                if (submit) {
                    submit.disabled = true;
                }
            });
        }
    })();
</script>
@endsection
