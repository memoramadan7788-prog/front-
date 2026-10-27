@extends('frontend.layouts.app')

@section('title', __('candidate.membership.page_title'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.membership.title') }}</h1>
    <p class="mt-1 max-w-2xl text-sm text-hb-muted">{{ __('candidate.membership.intro') }}</p>
@endsection

@section('content')
@php
    $discoveryAvailable = (bool) ($discoveryAvailable ?? false);
    $canSubmit = (bool) ($canSubmit ?? false);
    $membershipAvailable = (bool) ($membershipAvailable ?? false);
    $membershipRows = is_array($membershipRows ?? null) ? $membershipRows : [];
    $companies = is_array($companies ?? null) ? $companies : [];
    $statusLegend = is_array($statusLegend ?? null) ? $statusLegend : [];
    $oldCompanyId = old('company_id');
    $dropdownEnabled = $canSubmit && empty($discoveryError ?? null) && ! empty($companies);
@endphp

<div class="mx-auto flex max-w-3xl flex-col gap-6">
    {{-- Separation from recruiting --}}
    <div class="rounded-2xl border border-hb-border bg-hb-surface px-5 py-4 sm:px-6">
        <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.membership.vs_jobs_title') }}</p>
        <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.membership.vs_jobs_body') }}</p>
        <div class="mt-3 flex flex-wrap gap-2">
            <x-button variant="light" size="sm" :href="route('frontend.candidate.vacancies.index')">
                {{ __('candidate.membership.browse_jobs') }}
            </x-button>
            <x-button variant="light" size="sm" :href="route('frontend.candidate.applications.index')">
                {{ __('candidate.membership.view_applications') }}
            </x-button>
        </div>
    </div>

    {{-- My membership requests --}}
    <x-card>
        <x-slot:header>
            <div>
                <h2 class="text-lg font-bold text-hb-navy">{{ __('candidate.membership.requests_title') }}</h2>
                <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.membership.requests_intro') }}</p>
            </div>
        </x-slot:header>

        @if (! empty($statusLegend))
            <div class="mb-5 flex flex-wrap gap-2" aria-label="{{ __('candidate.membership.status_legend') }}">
                @foreach ($statusLegend as $key => $meta)
                    @if ($key === 'none')
                        @continue
                    @endif
                    <x-badge :variant="$meta['variant'] ?? 'neutral'" :dot="true">{{ $meta['label'] ?? $key }}</x-badge>
                @endforeach
            </div>
        @endif

        @if (! $membershipAvailable)
            <div class="rounded-xl border border-dashed border-hb-border bg-hb-page/40 px-5 py-8 text-center" role="status">
                <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.membership.status_unavailable_title') }}</p>
                <p class="mx-auto mt-2 max-w-md text-sm text-hb-muted">{{ __('candidate.membership.status_unavailable_body') }}</p>
            </div>
        @elseif (filled($membershipError ?? null))
            <div class="rounded-xl border border-hb-error/30 bg-hb-error/5 px-5 py-6" role="alert">
                <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.membership.status_load_error_title') }}</p>
                <p class="mt-2 text-sm text-hb-muted">{{ $membershipError }}</p>
                <div class="mt-4">
                    <x-button variant="light" size="sm" :href="request()->url()">{{ __('candidate.membership.retry') }}</x-button>
                </div>
            </div>
        @elseif (empty($membershipRows))
            <div class="rounded-xl border border-dashed border-hb-border bg-hb-page/40 px-5 py-8 text-center" role="status">
                <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.membership.requests_empty_title') }}</p>
                <p class="mx-auto mt-2 max-w-md text-sm text-hb-muted">{{ __('candidate.membership.requests_empty_body') }}</p>
            </div>
        @else
            <ul class="divide-y divide-hb-border rounded-xl border border-hb-border" role="list">
                @foreach ($membershipRows as $row)
                    @php
                        $status = strtolower((string) data_get($row, 'status', ''));
                        $label = data_get($statusLegend, $status.'.label', data_get($row, 'status', '—'));
                        $variant = data_get($statusLegend, $status.'.variant', \App\Support\Rows::badgeVariant($status));
                        $companyName = data_get($row, 'company_name', data_get($row, 'company.name'));
                    @endphp
                    <li class="flex flex-col gap-2 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-hb-navy">
                                {{ filled($companyName) ? $companyName : __('candidate.membership.company_unnamed') }}
                            </p>
                            <p class="mt-1 text-xs text-hb-muted">
                                {{ data_get($row, 'requested_job_title', '—') }}
                                @if (filled(data_get($row, 'requested_department')))
                                    <span aria-hidden="true"> · </span>{{ data_get($row, 'requested_department') }}
                                @endif
                            </p>
                            @if (filled(data_get($row, 'requested_at')))
                                <p class="mt-1 text-xs text-hb-muted">
                                    {{ __('candidate.membership.submitted_at') }}:
                                    {{ \App\Support\Rows::date(data_get($row, 'requested_at')) }}
                                </p>
                            @endif
                            @if ($status === 'rejected' && filled(data_get($row, 'rejection_reason')))
                                <p class="mt-2 text-xs text-hb-error">{{ data_get($row, 'rejection_reason') }}</p>
                            @endif
                        </div>
                        <x-badge :variant="$variant" :dot="true">{{ $label }}</x-badge>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-card>

    {{-- Join request form --}}
    <x-card>
        <x-slot:header>
            <div>
                <h2 class="text-lg font-bold text-hb-navy">{{ __('candidate.membership.form_title') }}</h2>
                <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.membership.form_intro') }}</p>
            </div>
        </x-slot:header>

        <div class="mb-5 rounded-xl border border-hb-info/20 bg-hb-info-soft/40 px-4 py-3" role="note">
            <p class="text-sm text-hb-navy">{{ __('candidate.membership.approval_note') }}</p>
            <p class="mt-1 text-xs text-hb-muted">{{ __('candidate.membership.one_request_rule') }}</p>
        </div>

        <form
            method="POST"
            action="{{ route('frontend.candidate.join-company.submit') }}"
            class="space-y-5"
            data-membership-form
            data-discovery-available="{{ $dropdownEnabled ? '1' : '0' }}"
            data-submitting-label="{{ __('candidate.membership.submitting') }}"
            @if (! $dropdownEnabled) aria-disabled="true" @endif
        >
            @csrf

            {{-- Simple single-select Company dropdown (names visible; company_id submitted) --}}
            <div class="w-full" data-company-picker>
                @if (! $discoveryAvailable)
                    <div class="rounded-xl border border-dashed border-hb-border bg-hb-page/50 px-4 py-5" role="status" data-discovery-unavailable>
                        <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.membership.list_unavailable_title') }}</p>
                        <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.membership.list_unavailable_body') }}</p>
                        <div class="pointer-events-none mt-4 opacity-60" aria-hidden="true">
                            <label class="hb-label">{{ __('candidate.membership.company') }}</label>
                            <select class="hb-input" disabled>
                                <option>{{ __('candidate.membership.company_placeholder') }}</option>
                            </select>
                        </div>
                    </div>
                @elseif (filled($discoveryError ?? null))
                    <div class="rounded-xl border border-hb-error/30 bg-hb-error/5 px-4 py-4" role="alert">
                        <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.membership.discovery_load_error_title') }}</p>
                        <p class="mt-1 text-sm text-hb-muted">{{ $discoveryError }}</p>
                        <div class="mt-3">
                            <x-button variant="light" size="sm" :href="request()->url()">{{ __('candidate.membership.retry') }}</x-button>
                        </div>
                    </div>
                @elseif (! empty($discoveryLoading))
                    <div class="rounded-xl border border-hb-border bg-hb-page/40 px-4 py-6 text-center" role="status" aria-live="polite">
                        <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.membership.list_loading') }}</p>
                    </div>
                @elseif (empty($companies))
                    <div class="rounded-xl border border-dashed border-hb-border bg-hb-page/40 px-5 py-8 text-center" role="status">
                        <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.membership.company_list_empty_title') }}</p>
                        <p class="mx-auto mt-2 max-w-md text-sm text-hb-muted">{{ __('candidate.membership.company_list_empty_body') }}</p>
                    </div>
                @else
                    <x-form.select
                        name="company_id"
                        id="company_id"
                        :label="__('candidate.membership.company')"
                        :required="true"
                        :error="$errors->first('company_id')"
                        data-company-select
                    >
                        <option value="" @selected($oldCompanyId === null || $oldCompanyId === '')>
                            {{ __('candidate.membership.company_placeholder') }}
                        </option>
                        @foreach ($companies as $company)
                            @php
                                $cid = data_get($company, 'id');
                                $cname = data_get($company, 'name', data_get($company, 'company_name'));
                            @endphp
                            @if (filled($cid) && filled($cname))
                                <option value="{{ $cid }}" @selected((string) $oldCompanyId === (string) $cid)>
                                    {{ $cname }}
                                </option>
                            @endif
                        @endforeach
                    </x-form.select>
                    <p class="mt-1.5 text-xs text-hb-muted">{{ __('candidate.membership.company_dropdown_hint') }}</p>
                @endif
            </div>

            <x-form.input
                name="requested_job_title"
                :label="__('candidate.membership.job_title')"
                type="text"
                :required="$dropdownEnabled"
                :error="$errors->first('requested_job_title')"
                value="{{ old('requested_job_title') }}"
                maxlength="255"
                autocomplete="organization-title"
                :disabled="! $dropdownEnabled"
            />

            <x-form.input
                name="requested_department"
                :label="__('candidate.membership.department')"
                type="text"
                :required="$dropdownEnabled"
                :error="$errors->first('requested_department')"
                value="{{ old('requested_department') }}"
                maxlength="255"
                autocomplete="organization"
                :disabled="! $dropdownEnabled"
            />

            <div class="flex flex-wrap items-center gap-3 pt-1">
                <x-button type="submit" :disabled="! $dropdownEnabled" data-membership-submit>
                    {{ __('candidate.membership.submit') }}
                </x-button>
                <x-button variant="light" :href="route('frontend.candidate.dashboard')">
                    {{ __('candidate.membership.cancel') }}
                </x-button>
            </div>

            @if (! $dropdownEnabled)
                <p class="text-xs text-hb-muted" role="status">{{ __('candidate.membership.submit_disabled_hint') }}</p>
            @endif
        </form>
    </x-card>
</div>

@push('scripts')
<script>
(() => {
    const form = document.querySelector('[data-membership-form]');
    if (!form) return;

    const canSubmit = form.getAttribute('data-discovery-available') === '1';
    const submit = form.querySelector('[data-membership-submit]');
    const label = form.getAttribute('data-submitting-label') || '';
    const select = form.querySelector('[data-company-select], #company_id');

    form.addEventListener('submit', (event) => {
        if (!canSubmit) {
            event.preventDefault();
            return;
        }
        if (!submit || submit.disabled) {
            event.preventDefault();
            return;
        }
        if (select && !String(select.value || '').trim()) {
            event.preventDefault();
            return;
        }
        submit.disabled = true;
        if (label) submit.textContent = label;
    });
})();
</script>
@endpush
@endsection
