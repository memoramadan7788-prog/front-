@extends('frontend.layouts.public')

@section('title', __('public.jobs.index_page_title'))

@section('content')
@php
    $items = \App\Support\Rows::from($jobs ?? []);
    $meta = $meta ?? null;
    $categories = \App\Support\Rows::from($categories ?? []);
    $totalCount = $totalCount ?? (is_numeric(data_get($meta, 'total')) ? (int) data_get($meta, 'total') : null);
    $sort = $sort ?? request('sort', 'newest');
    $isCandidate = (bool) ($isCandidate ?? false);
    $isAuthenticated = (bool) ($isAuthenticated ?? false);
    $selectedCategory = request('category_id', request('discipline'));
    $hasActiveFilters = request()->hasAny(['q', 'location', 'work_mode', 'employment_type', 'salary_min', 'salary_max', 'discipline', 'category_id', 'sort']);
    $employmentOptions = [
        'full_time' => __('common.employment.full_time'),
        'part_time' => __('common.employment.part_time'),
        'contract' => __('common.employment.contract'),
        'internship' => __('common.employment.internship'),
    ];
    $workModeOptions = [
        'onsite' => __('common.work_models.on_site'),
        'remote' => __('common.work_models.remote'),
        'hybrid' => __('common.work_models.hybrid'),
    ];
    $sortOptions = [
        'newest' => __('public.jobs.sort_newest'),
        'salary' => __('public.jobs.sort_salary'),
        'title' => __('public.jobs.sort_title'),
        'deadline' => __('public.jobs.sort_deadline'),
    ];
@endphp

<section class="border-b border-hb-border bg-hb-page">
    <div class="hb-container py-12 lg:py-16">
        <p class="text-xs font-semibold tracking-[0.14em] text-hb-blue uppercase">{{ __('public.jobs.index_eyebrow') }}</p>
        <h1 class="mt-3 text-4xl font-semibold tracking-tight text-hb-navy sm:text-5xl">{{ __('public.jobs.index_title') }}</h1>
        <p class="mt-3 max-w-2xl text-base text-hb-muted">{{ __('public.jobs.index_intro') }}</p>

        <form
            method="GET"
            action="{{ route('frontend.jobs.index') }}"
            class="hb-surface mt-8 space-y-4 p-5 shadow-hb-md"
            aria-label="{{ __('public.jobs.refine_aria') }}"
            data-jobs-filters
        >
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-sm font-semibold text-hb-navy">{{ __('public.jobs.refine_title') }}</h2>
                <a href="{{ route('frontend.jobs.index') }}" class="text-sm font-semibold text-hb-blue hover:underline {{ $hasActiveFilters ? '' : 'opacity-60' }}">{{ __('public.jobs.clear_filters') }}</a>
            </div>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <x-form.input name="q" :label="__('public.jobs.keyword')" value="{{ request('q') }}" autocomplete="off" />
                <x-form.input name="location" :label="__('public.jobs.location')" value="{{ request('location') }}" autocomplete="address-level2" />
                <x-form.select name="work_mode" :label="__('public.jobs.work_model')">
                    <option value="">{{ __('public.jobs.any_work_model') }}</option>
                    @foreach ($workModeOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('work_mode') === $value)>{{ $label }}</option>
                    @endforeach
                </x-form.select>
                <x-form.select name="employment_type" :label="__('public.jobs.employment')">
                    <option value="">{{ __('public.jobs.any_employment') }}</option>
                    @foreach ($employmentOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('employment_type') === $value)>{{ $label }}</option>
                    @endforeach
                </x-form.select>
            </div>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <x-form.input
                    name="salary_min"
                    type="number"
                    min="0"
                    step="1000"
                    :label="__('public.jobs.salary_min')"
                    :aria-label="__('public.jobs.salary_min_aria')"
                    value="{{ request('salary_min') }}"
                />
                <x-form.input
                    name="salary_max"
                    type="number"
                    min="0"
                    step="1000"
                    :label="__('public.jobs.salary_max')"
                    :aria-label="__('public.jobs.salary_max_aria')"
                    value="{{ request('salary_max') }}"
                />
                <x-form.select name="discipline" :label="__('public.jobs.discipline')">
                    <option value="">{{ __('public.jobs.any_discipline') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ data_get($category, 'id') }}" @selected((string) $selectedCategory === (string) data_get($category, 'id'))>
                            {{ data_get($category, 'name') }}
                        </option>
                    @endforeach
                </x-form.select>
                <x-form.select name="sort" :label="__('public.jobs.sort_label')">
                    @foreach ($sortOptions as $value => $label)
                        <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                    @endforeach
                </x-form.select>
            </div>

            <div class="flex flex-wrap items-center gap-3 pt-1">
                <x-button type="submit">{{ __('public.jobs.filters_apply') }}</x-button>
                @if ($hasActiveFilters)
                    <x-button variant="light" :href="route('frontend.jobs.index')">{{ __('public.jobs.clear_filters') }}</x-button>
                @endif
            </div>
        </form>
    </div>
</section>

<div class="hb-container py-10">
    @if (session('success_key'))
        <div class="mb-6 rounded-2xl border border-hb-success/30 bg-hb-success-soft px-5 py-4 text-sm text-hb-success" role="status">
            {{ __(session('success_key')) }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-hb-error/30 bg-hb-error-soft px-5 py-4 text-sm text-hb-error" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    @if (! empty($loadError))
        <div class="hb-surface mb-8 p-6" role="alert" data-jobs-load-error>
            <h2 class="text-lg font-semibold text-hb-navy">{{ __('public.jobs.load_error_title') }}</h2>
            <p class="mt-2 text-sm text-hb-muted">{{ __('public.jobs.load_error_body') }}</p>
            {{-- Only show an extra detail line when it is a safe, distinct user message (never SQL/debug). --}}
            @if ($loadError !== __('public.jobs.load_error_body'))
                <p class="mt-2 text-sm text-hb-error">{{ $loadError }}</p>
            @endif
            <div class="mt-4">
                <x-button variant="light" :href="request()->fullUrl()">{{ __('public.jobs.retry') }}</x-button>
            </div>
        </div>
    @else
        <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-hb-navy">{{ __('public.jobs.results_heading') }}</h2>
                @if ($totalCount !== null)
                    <p class="mt-1 text-sm text-hb-muted" data-jobs-match-count>
                        {{ __('public.jobs.match_count', ['count' => $totalCount]) }}
                    </p>
                @endif
            </div>
            @if ($hasActiveFilters)
                <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('public.jobs.active_filters') }}</p>
            @endif
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            @forelse ($items as $row)
                @php
                    $jobId = (int) data_get($row, 'id');
                    $hiringModel = data_get($row, 'hiring_model');
                    $salaryMin = data_get($row, 'salary.min', data_get($row, 'salary_min'));
                    $salaryMax = data_get($row, 'salary.max', data_get($row, 'salary_max'));
                    $currency = data_get($row, 'salary.currency', data_get($row, 'currency'));
                    $saved = (bool) data_get($row, 'saved_by_me', false);
                    $employment = data_get($row, 'employment_type');
                    $workMode = data_get($row, 'work_mode');
                @endphp
                <article class="hb-surface flex flex-col p-6 transition duration-200 hover:-translate-y-0.5 hover:border-hb-blue/40 hover:shadow-hb-md" data-job-card="{{ $jobId }}">
                    <a href="{{ route('frontend.jobs.show', ['jobId' => $jobId]) }}" class="group block grow">
                        <h3 class="text-lg font-semibold text-hb-navy transition group-hover:text-hb-blue">{{ data_get($row, 'title') }}</h3>
                        <p class="mt-1 text-sm text-hb-muted">
                            {{ data_get($row, 'company.name', data_get($row, 'company_name', '')) }}
                            · {{ data_get($row, 'location', '—') }}
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @if ($workMode)
                                <x-badge variant="info">{{ $workModeOptions[$workMode] ?? $workMode }}</x-badge>
                            @endif
                            @if ($employment)
                                <x-badge>{{ $employmentOptions[$employment] ?? $employment }}</x-badge>
                            @endif
                            @if ($hiringModel === 'direct')
                                <x-badge variant="success">{{ __('common.sources.direct_hire') }}</x-badge>
                            @elseif ($hiringModel === 'agency')
                                <x-badge variant="pending">{{ __('common.sources.agency_managed') }}</x-badge>
                            @endif
                        </div>
                        @if ($salaryMin !== null || $salaryMax !== null)
                            <p class="mt-3 text-sm text-hb-navy">
                                {{ \App\Support\Rows::money($salaryMin ?? $salaryMax, $currency) }}
                                @if ($salaryMin !== null && $salaryMax !== null && (string) $salaryMin !== (string) $salaryMax)
                                    – {{ \App\Support\Rows::money($salaryMax, $currency) }}
                                @endif
                                <span class="text-hb-muted"> · {{ __('public.jobs.card_salary_note') }}</span>
                            </p>
                        @endif
                    </a>

                    <div class="mt-5 flex flex-wrap items-center gap-2 border-t border-hb-border pt-4">
                        <x-button variant="light" size="sm" :href="route('frontend.jobs.show', ['jobId' => $jobId])">{{ __('public.jobs.card_view_role') }}</x-button>

                        @if ($isCandidate)
                            @if ($saved)
                                <form method="POST" action="{{ route('frontend.jobs.unsave', ['jobId' => $jobId]) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                                    <x-button type="submit" size="sm" variant="ghost">{{ __('public.jobs.unsave_job') }}</x-button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('frontend.jobs.save', ['jobId' => $jobId]) }}" class="inline" data-save-job-form onsubmit="if(this.dataset.submitted==='1'){return false;} this.dataset.submitted='1';">
                                    @csrf
                                    <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                                    <x-button type="submit" size="sm" variant="primary">{{ __('public.jobs.save_job') }}</x-button>
                                </form>
                            @endif
                        @elseif (! $isAuthenticated)
                            <x-button
                                variant="ghost"
                                size="sm"
                                :href="route('frontend.account.login', ['redirect' => request()->fullUrl()])"
                                :title="__('public.jobs.save_login_hint')"
                            >{{ __('public.jobs.save_login') }}</x-button>
                        @endif
                    </div>
                </article>
            @empty
                <div class="hb-surface md:col-span-2">
                    <x-empty-state
                        :title="__('public.jobs.empty_title')"
                        :action-label="__('public.jobs.clear_filters')"
                        :action-href="route('frontend.jobs.index')"
                    >
                        {{ __('public.jobs.empty_body') }}
                    </x-empty-state>
                </div>
            @endforelse
        </div>

        @include('frontend.partials.pagination', [
            'meta' => $meta,
            'ariaLabel' => __('public.jobs.pagination_aria'),
            'showStatus' => true,
            'prevLabel' => __('public.jobs.pagination_prev'),
            'nextLabel' => __('public.jobs.pagination_next'),
        ])
    @endif

    <aside class="mt-10 overflow-hidden rounded-3xl border border-hb-border bg-gradient-to-br from-hb-blue/10 via-hb-surface to-hb-page p-6 shadow-hb-sm sm:p-8" data-jobs-cta-banner>
        <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <h2 class="text-xl font-semibold text-hb-navy">{{ __('public.jobs.alert_title') }}</h2>
                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-hb-muted">{{ __('public.jobs.alert_body') }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <x-button :href="route('frontend.account.register')">{{ __('public.jobs.create_account') }}</x-button>
                @if (! $isAuthenticated)
                    <x-button variant="light" :href="route('frontend.account.login')">{{ __('public.jobs.apply_sign_in') }}</x-button>
                @endif
            </div>
        </div>
    </aside>
</div>
@endsection
