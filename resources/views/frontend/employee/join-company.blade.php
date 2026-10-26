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
    $companiesMeta = is_array($companiesMeta ?? null) ? $companiesMeta : null;
    $statusLegend = is_array($statusLegend ?? null) ? $statusLegend : [];
    $companyQuery = (string) ($companyQuery ?? '');
    $companyPage = max(1, (int) ($companyPage ?? 1));
    $oldCompanyId = old('company_id');
    $currentPage = (int) data_get($companiesMeta, 'current_page', $companyPage);
    $lastPage = (int) data_get($companiesMeta, 'last_page', $currentPage);
    $hasMore = $lastPage > $currentPage;
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
            data-discovery-available="{{ $canSubmit ? '1' : '0' }}"
            data-submitting-label="{{ __('candidate.membership.submitting') }}"
            @if (! $canSubmit) aria-disabled="true" @endif
        >
            @csrf

            {{-- List-first company picker (names + public details; ID submitted via radio only) --}}
            <fieldset class="w-full" data-company-picker>
                <legend class="hb-label mb-2">
                    {{ __('candidate.membership.company_list_label') }}
                    <span class="text-hb-error" aria-hidden="true">*</span>
                </legend>
                <p class="mb-3 text-xs text-hb-muted">{{ __('candidate.membership.company_list_hint') }}</p>

                @if (! $discoveryAvailable)
                    <div class="rounded-xl border border-dashed border-hb-border bg-hb-page/50 px-4 py-5" role="status" data-discovery-unavailable>
                        <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.membership.list_unavailable_title') }}</p>
                        <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.membership.list_unavailable_body') }}</p>

                        <div class="pointer-events-none mt-4 space-y-2 opacity-50" aria-hidden="true">
                            <div class="flex items-center gap-3 rounded-xl border border-hb-border bg-hb-surface px-4 py-3">
                                <span class="h-4 w-4 shrink-0 rounded-full border border-hb-border"></span>
                                <div class="min-w-0 flex-1 space-y-2">
                                    <div class="h-3 w-2/5 rounded bg-hb-border/70"></div>
                                    <div class="h-2 w-3/5 rounded bg-hb-border/40"></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 rounded-xl border border-hb-border bg-hb-surface px-4 py-3">
                                <span class="h-4 w-4 shrink-0 rounded-full border border-hb-border"></span>
                                <div class="min-w-0 flex-1 space-y-2">
                                    <div class="h-3 w-1/3 rounded bg-hb-border/70"></div>
                                    <div class="h-2 w-1/2 rounded bg-hb-border/40"></div>
                                </div>
                            </div>
                        </div>

                        <div class="pointer-events-none mt-3 opacity-50" aria-hidden="true">
                            <input
                                type="search"
                                class="hb-input"
                                disabled
                                placeholder="{{ __('candidate.membership.company_filter_placeholder') }}"
                            >
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
                @else
                    {{-- Optional filter (GET) above the visible list --}}
                    <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-end">
                        <div class="min-w-0 flex-1">
                            <label for="company-filter" class="sr-only">{{ __('candidate.membership.company_filter_label') }}</label>
                            <input
                                id="company-filter"
                                form="company-filter-form"
                                type="search"
                                name="q"
                                value="{{ $companyQuery }}"
                                class="hb-input"
                                placeholder="{{ __('candidate.membership.company_filter_placeholder') }}"
                                autocomplete="off"
                                data-company-filter
                            >
                        </div>
                        <x-button form="company-filter-form" type="submit" variant="light" size="sm">
                            {{ __('candidate.membership.company_filter_apply') }}
                        </x-button>
                    </div>

                    @error('company_id')
                        <p class="mb-2 hb-field-error" role="alert">{{ $message }}</p>
                    @enderror

                    @if (empty($companies))
                        <div class="rounded-xl border border-dashed border-hb-border bg-hb-page/40 px-5 py-8 text-center" role="status">
                            <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.membership.company_list_empty_title') }}</p>
                            <p class="mx-auto mt-2 max-w-md text-sm text-hb-muted">
                                {{ filled($companyQuery) ? __('candidate.membership.company_list_empty_filtered') : __('candidate.membership.company_list_empty_body') }}
                            </p>
                        </div>
                    @else
                        <div
                            class="space-y-2"
                            role="radiogroup"
                            aria-label="{{ __('candidate.membership.company_list_label') }}"
                            data-company-list
                        >
                            @foreach ($companies as $company)
                                @php
                                    $cid = data_get($company, 'id');
                                    $cname = data_get($company, 'name', data_get($company, 'company_name'));
                                    $industry = data_get($company, 'industry');
                                    $location = data_get($company, 'location', data_get($company, 'city'));
                                    $website = data_get($company, 'website');
                                    $isSelected = (string) $oldCompanyId !== '' && (string) $oldCompanyId === (string) $cid;
                                @endphp
                                @if (filled($cid) && filled($cname))
                                    <label
                                        class="flex cursor-pointer items-start gap-3 rounded-xl border border-hb-border bg-hb-surface px-4 py-3 transition-colors has-[:checked]:border-hb-blue has-[:checked]:bg-hb-info-soft/30 hover:border-hb-blue/40"
                                        data-company-card
                                    >
                                        <input
                                            type="radio"
                                            name="company_id"
                                            value="{{ $cid }}"
                                            class="mt-1 h-4 w-4 shrink-0 border-hb-border text-hb-blue focus:ring-hb-blue"
                                            @checked($isSelected)
                                            required
                                            data-company-radio
                                            aria-label="{{ $cname }}"
                                        >
                                        <span class="min-w-0 flex-1">
                                            <span class="block text-sm font-semibold text-hb-navy">{{ $cname }}</span>
                                            @if (filled($industry) || filled($location))
                                                <span class="mt-1 block text-xs text-hb-muted">
                                                    @if (filled($industry)){{ $industry }}@endif
                                                    @if (filled($industry) && filled($location))
                                                        <span aria-hidden="true"> · </span>
                                                    @endif
                                                    @if (filled($location)){{ $location }}@endif
                                                </span>
                                            @endif
                                            @if (filled($website))
                                                <span class="mt-1 block truncate text-xs text-hb-muted">{{ $website }}</span>
                                            @endif
                                        </span>
                                        <span class="{{ $isSelected ? '' : 'hidden' }} shrink-0 text-xs font-medium text-hb-blue" data-selected-label>
                                            {{ __('candidate.membership.company_selected') }}
                                        </span>
                                    </label>
                                @endif
                            @endforeach
                        </div>

                        @if ($hasMore || $currentPage > 1)
                            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                                <p class="text-xs text-hb-muted">
                                    {{ __('candidate.membership.company_page_status', [
                                        'current' => $currentPage,
                                        'last' => max(1, $lastPage),
                                    ]) }}
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    @if ($currentPage > 1)
                                        <x-button
                                            variant="light"
                                            size="sm"
                                            :href="route('frontend.candidate.join-company', array_filter(['q' => $companyQuery ?: null, 'page' => $currentPage - 1]))"
                                        >
                                            {{ __('candidate.membership.company_prev') }}
                                        </x-button>
                                    @endif
                                    @if ($hasMore)
                                        <x-button
                                            variant="light"
                                            size="sm"
                                            :href="route('frontend.candidate.join-company', array_filter(['q' => $companyQuery ?: null, 'page' => $currentPage + 1]))"
                                            data-company-load-more
                                        >
                                            {{ __('candidate.membership.company_load_more') }}
                                        </x-button>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endif
                @endif
            </fieldset>

            <x-form.input
                name="requested_job_title"
                :label="__('candidate.membership.job_title')"
                type="text"
                :required="$canSubmit"
                :error="$errors->first('requested_job_title')"
                value="{{ old('requested_job_title') }}"
                maxlength="255"
                autocomplete="organization-title"
                :disabled="! $canSubmit"
            />

            <x-form.input
                name="requested_department"
                :label="__('candidate.membership.department')"
                type="text"
                :required="$canSubmit"
                :error="$errors->first('requested_department')"
                value="{{ old('requested_department') }}"
                maxlength="255"
                autocomplete="organization"
                :disabled="! $canSubmit"
            />

            <div class="flex flex-wrap items-center gap-3 pt-1">
                <x-button type="submit" :disabled="! $canSubmit" data-membership-submit>
                    {{ __('candidate.membership.submit') }}
                </x-button>
                <x-button variant="light" :href="route('frontend.candidate.dashboard')">
                    {{ __('candidate.membership.cancel') }}
                </x-button>
            </div>

            @if (! $canSubmit)
                <p class="text-xs text-hb-muted" role="status">{{ __('candidate.membership.submit_disabled_hint') }}</p>
            @endif
        </form>

        {{-- Separate GET filter form so POST membership fields stay clean --}}
        @if ($discoveryAvailable && blank($discoveryError ?? null))
            <form
                id="company-filter-form"
                method="GET"
                action="{{ route('frontend.candidate.join-company') }}"
                class="hidden"
                aria-hidden="true"
            ></form>
        @endif
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

    form.addEventListener('submit', (event) => {
        if (!canSubmit) {
            event.preventDefault();
            return;
        }
        if (!submit || submit.disabled) {
            event.preventDefault();
            return;
        }
        const selected = form.querySelector('[data-company-radio]:checked');
        if (!selected) {
            event.preventDefault();
            return;
        }
        submit.disabled = true;
        if (label) submit.textContent = label;
    });

    form.querySelectorAll('[data-company-card]').forEach((card) => {
        const radio = card.querySelector('[data-company-radio]');
        const mark = card.querySelector('[data-selected-label]');
        if (!radio) return;
        const sync = () => {
            if (mark) mark.classList.toggle('hidden', !radio.checked);
        };
        radio.addEventListener('change', () => {
            form.querySelectorAll('[data-company-card] [data-selected-label]').forEach((el) => {
                el.classList.add('hidden');
            });
            sync();
        });
        sync();
    });
})();
</script>
@endpush
@endsection
