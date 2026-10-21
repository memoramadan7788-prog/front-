@extends('layouts.public')

@section('title', $pageTitle ?? __('public.jobs.index_page_title'))

@section('content')
    {{-- Figma 02 · All Jobs (8:31300) --}}
    <section class="bg-hb-page" data-jobs-directory>
        <div class="hb-container py-10 lg:py-14">
            <p class="text-xs font-semibold tracking-[0.12em] text-hb-muted uppercase">{{ __('public.jobs.index_eyebrow') }}</p>
            <div class="mt-3 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="text-3xl font-semibold text-hb-navy sm:text-4xl">{{ __('public.jobs.index_title') }}</h1>
                    <p class="mt-2 max-w-2xl text-sm text-hb-muted">
                        {{ __('public.jobs.index_intro') }}
                    </p>
                </div>
                <span class="rounded-full bg-hb-pending-soft px-3 py-1 text-[10px] font-semibold tracking-wide text-hb-pending uppercase">
                    {{ __('public.jobs.open_roles_badge') }}
                </span>
            </div>

            <div class="mt-8 grid gap-8 lg:grid-cols-[280px_minmax(0,1fr)]">
                <aside class="hb-surface h-fit p-5" aria-label="{{ __('public.jobs.refine_aria') }}">
                    <h2 class="text-sm font-semibold text-hb-navy">{{ __('public.jobs.refine_title') }}</h2>

                    <form id="jobs-filter-form" class="mt-5 space-y-6" onsubmit="return false;">
                        <div>
                            <label for="filter-q" class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('public.jobs.keyword') }}</label>
                            <input id="filter-q" name="q" type="search" value="{{ request('q') }}" placeholder="Product designer"
                                   class="mt-2 w-full rounded-lg border border-hb-border bg-hb-surface px-3 py-2.5 text-sm">
                        </div>

                        <div>
                            <label for="filter-location" class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('public.jobs.location') }}</label>
                            <input id="filter-location" name="location" type="text" value="{{ request('location') }}" placeholder="Austin, TX"
                                   class="mt-2 w-full rounded-lg border border-hb-border bg-hb-surface px-3 py-2.5 text-sm">
                        </div>

                        <fieldset>
                            <legend class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('public.jobs.work_model') }}</legend>
                            <div class="mt-2 space-y-2 text-sm">
                                @foreach (['Hybrid', 'Remote', 'On-site'] as $model)
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="work_model[]" value="{{ $model }}" class="rounded border-hb-border text-hb-blue"
                                            @checked(in_array($model, ['Hybrid', 'Remote'], true) && ! request()->has('work_model'))
                                            @checked(is_array(request('work_model')) && in_array($model, request('work_model'), true))>
                                        {{ $model }}
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('public.jobs.employment') }}</legend>
                            <div class="mt-2 space-y-2 text-sm">
                                @foreach (['Full-time', 'Contract'] as $type)
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="employment[]" value="{{ $type }}" class="rounded border-hb-border text-hb-blue"
                                            @checked($type === 'Full-time' && ! request()->has('employment'))
                                            @checked(is_array(request('employment')) && in_array($type, request('employment'), true))>
                                        {{ $type }}
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <div>
                            <label class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('public.jobs.salary_usd') }}</label>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <input id="filter-salary-min" type="number" name="salary_min" value="130000" min="0" step="5000"
                                       class="w-full rounded-lg border border-hb-border px-3 py-2 text-sm" aria-label="{{ __('public.jobs.salary_min_aria') }}">
                                <input id="filter-salary-max" type="number" name="salary_max" value="180000" min="0" step="5000"
                                       class="w-full rounded-lg border border-hb-border px-3 py-2 text-sm" aria-label="{{ __('public.jobs.salary_max_aria') }}">
                            </div>
                        </div>

                        <fieldset>
                            <legend class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('public.jobs.discipline') }}</legend>
                            <div class="mt-2 space-y-2 text-sm">
                                @foreach (['Product & Design', 'Engineering & Data', 'Marketing & Growth', 'People & Operations'] as $discipline)
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="discipline[]" value="{{ $discipline }}" class="rounded border-hb-border text-hb-blue"
                                            @checked($discipline === 'Product & Design' && ! request()->filled('discipline'))
                                            @checked(request('discipline') === $discipline || (is_array(request('discipline')) && in_array($discipline, request('discipline'), true)))>
                                        {{ $discipline }}
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <x-button type="button" variant="secondary" class="w-full" data-clear-filters>{{ __('public.jobs.clear_filters') }}</x-button>
                    </form>
                </aside>

                <div>
                    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-hb-muted">
                            <span data-jobs-count>{{ count($jobs) }}</span> {{ __('public.jobs.match_count_suffix') }}
                        </p>
                        <label class="text-sm text-hb-muted">
                            <span class="sr-only">{{ __('common.sort') }}</span>
                            <select class="rounded-lg border border-hb-border bg-hb-surface px-3 py-2 text-sm text-hb-text" disabled>
                                <option>{{ __('public.jobs.sort_newest') }}</option>
                            </select>
                        </label>
                    </div>

                    <div class="space-y-4" data-jobs-results>
                        @foreach ($jobs as $job)
                            @include('public.jobs._card', ['job' => $job])
                        @endforeach
                    </div>

                    <div class="hb-surface mt-4 hidden px-5 py-10 text-center" data-jobs-empty>
                        <p class="font-semibold text-hb-navy">{{ __('public.jobs.empty_title') }}</p>
                        <p class="mt-2 text-sm text-hb-muted">{{ __('public.jobs.empty_body') }}</p>
                        <x-button class="mt-4" variant="secondary" data-clear-filters>{{ __('public.jobs.clear_filters') }}</x-button>
                    </div>

                    <div class="mt-8 hb-surface flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-semibold text-hb-navy">{{ __('public.jobs.alert_title') }}</p>
                            <p class="mt-1 text-sm text-hb-muted">{{ __('public.jobs.alert_body') }}</p>
                        </div>
                        <a href="{{ route('register') }}" class="hb-btn-primary">{{ __('public.jobs.create_account') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const root = document.querySelector('[data-jobs-directory]');
        if (!root) return;

        const form = root.querySelector('#jobs-filter-form');
        const cards = () => Array.from(root.querySelectorAll('[data-job-card]'));
        const countEl = root.querySelector('[data-jobs-count]');
        const emptyEl = root.querySelector('[data-jobs-empty]');
        const resultsEl = root.querySelector('[data-jobs-results]');

        const checked = (name) => Array.from(form.querySelectorAll(`input[name="${name}[]"]:checked`)).map((el) => el.value);

        const applyFilters = () => {
            const q = (form.q.value || '').trim().toLowerCase();
            const location = (form.location.value || '').trim().toLowerCase();
            const models = checked('work_model');
            const employment = checked('employment');
            const disciplines = checked('discipline');
            const salaryMin = Number(form.salary_min.value || 0);
            const salaryMax = Number(form.salary_max.value || Number.MAX_SAFE_INTEGER);

            let visible = 0;
            cards().forEach((card) => {
                const hay = card.dataset.title || '';
                const loc = card.dataset.location || '';
                const model = card.dataset.workModel || '';
                const emp = card.dataset.employment || '';
                const discipline = card.dataset.discipline || '';
                const min = Number(card.dataset.salaryMin || 0);
                const max = Number(card.dataset.salaryMax || 0);

                const matchQ = !q || hay.includes(q);
                const matchLoc = !location || loc.includes(location) || hay.includes(location);
                const matchModel = models.length === 0 || models.includes(model);
                const matchEmp = employment.length === 0 || employment.includes(emp);
                const matchDisc = disciplines.length === 0 || disciplines.includes(discipline);
                const matchSalary = max >= salaryMin && min <= salaryMax;

                const show = matchQ && matchLoc && matchModel && matchEmp && matchDisc && matchSalary;
                card.classList.toggle('hidden', !show);
                if (show) visible += 1;
            });

            if (countEl) countEl.textContent = String(visible);
            if (emptyEl) emptyEl.classList.toggle('hidden', visible !== 0);
            if (resultsEl) resultsEl.classList.toggle('hidden', visible === 0);
        };

        form.addEventListener('input', applyFilters);
        form.addEventListener('change', applyFilters);
        root.querySelectorAll('[data-clear-filters]').forEach((btn) => {
            btn.addEventListener('click', () => {
                form.reset();
                // Restore Figma-like defaults after clear.
                form.querySelectorAll('input[name="work_model[]"]').forEach((el) => {
                    el.checked = ['Hybrid', 'Remote'].includes(el.value);
                });
                form.querySelectorAll('input[name="employment[]"]').forEach((el) => {
                    el.checked = el.value === 'Full-time';
                });
                form.querySelectorAll('input[name="discipline[]"]').forEach((el) => {
                    el.checked = el.value === 'Product & Design';
                });
                form.salary_min.value = 130000;
                form.salary_max.value = 180000;
                applyFilters();
            });
        });

        // Honor query params from landing hero / discipline links.
        if (form.q.value || form.location.value || {{ request()->filled('discipline') ? 'true' : 'false' }}) {
            applyFilters();
        } else {
            applyFilters();
        }
    });
</script>
@endpush
