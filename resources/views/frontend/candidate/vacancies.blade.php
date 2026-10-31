@extends('frontend.layouts.app')

@section('title', __('Discover jobs'))

@section('page_header')
    <div>
        <p class="text-sm font-semibold text-hb-blue">{{ __('Find your next opportunity') }}</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Discover jobs') }}</h1>
    </div>
@endsection

@section('content')
@php
    $items = \App\Support\Rows::from($vacancies ?? []);
    $meta = $pagination ?? \App\Support\Rows::meta($vacancies ?? null);
    $skillRows = \App\Support\Rows::from($coreSkills ?? []);
    $skillNames = collect($skillRows)->map(fn ($skill) => is_string($skill) ? $skill : data_get($skill, 'name'))->filter(fn ($name) => is_string($name) && $name !== '')->values()->all();
    $preferences = data_get($profile ?? [], 'preferences', []);
    $preferredLocation = data_get($preferences, 'location') ?? data_get($profile ?? [], 'preferred_location') ?? data_get($profile ?? [], 'location') ?? '';
    $preferredMode = data_get($preferences, 'work_mode') ?? data_get($profile ?? [], 'work_mode') ?? '';
    $queryParameters = request()->query->all();
    $hasFilterQuery = count(array_intersect(['q', 'location', 'work_mode'], array_keys($queryParameters))) > 0;
    $searchQuery = (string) ($queryParameters['q'] ?? '');
    $selectedLocation = array_key_exists('location', $queryParameters) ? (string) $queryParameters['location'] : ($hasFilterQuery ? '' : $preferredLocation);
    $selectedMode = array_key_exists('work_mode', $queryParameters) ? (string) $queryParameters['work_mode'] : ($hasFilterQuery ? '' : $preferredMode);
@endphp

<div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(16rem,0.42fr)]">
    <section class="space-y-5" aria-label="{{ __('Job discovery') }}">
        <div class="grid gap-4 md:grid-cols-2">
            <section class="rounded-2xl border border-hb-border bg-white p-5 shadow-sm" aria-labelledby="preferences-heading">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 id="preferences-heading" class="text-base font-bold text-hb-navy">{{ __('Your preferences') }}</h2>
                        <p class="mt-1 text-sm text-hb-muted">{{ __('Narrow roles by location and work mode.') }}</p>
                    </div>
                    <span class="rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-800">{{ __('Filters') }}</span>
                </div>
                <form method="GET" action="{{ route('frontend.candidate.vacancies.index') }}" class="mt-4" data-vacancy-search-form>
                    <input type="hidden" name="page" value="{{ request()->query('page', 1) }}" data-vacancy-page>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <label class="text-sm font-medium text-hb-text">
                            {{ __('candidate.vacancies_search.search_label') }}
                            <span class="mt-1.5 flex gap-2">
                                <input type="search" name="q" value="{{ $searchQuery }}" maxlength="100" data-vacancy-search class="w-full min-w-0 rounded-lg border border-hb-border px-3 py-2 text-sm focus:border-hb-blue focus:outline-none focus:ring-2 focus:ring-hb-blue/20" placeholder="{{ __('candidate.vacancies_search.search_placeholder') }}">
                                <button type="button" data-clear-vacancy-search class="rounded-lg border border-hb-border px-3 py-2 text-sm font-semibold text-hb-navy transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50" @disabled($searchQuery === '')>{{ __('candidate.vacancies_search.clear_search') }}</button>
                            </span>
                        </label>
                    <label class="text-sm font-medium text-hb-text">
                        {{ __('Location') }}
                        <input type="search" name="location" value="{{ $selectedLocation }}" maxlength="100" data-preference-location class="mt-1.5 w-full rounded-lg border border-hb-border px-3 py-2 text-sm focus:border-hb-blue focus:outline-none focus:ring-2 focus:ring-hb-blue/20" placeholder="{{ __('Any location') }}">
                    </label>
                    <label class="text-sm font-medium text-hb-text">
                        {{ __('Work mode') }}
                        <select name="work_mode" data-preference-mode class="mt-1.5 w-full rounded-lg border border-hb-border bg-white px-3 py-2 text-sm focus:border-hb-blue focus:outline-none focus:ring-2 focus:ring-hb-blue/20">
                            <option value="">{{ __('Any mode') }}</option>
                            <option value="remote" @selected(strtolower((string) $selectedMode) === 'remote')>{{ __('Remote') }}</option>
                            <option value="hybrid" @selected(strtolower((string) $selectedMode) === 'hybrid')>{{ __('Hybrid') }}</option>
                            <option value="onsite" @selected(strtolower((string) $selectedMode) === 'onsite')>{{ __('On-site') }}</option>
                        </select>
                    </label>
                    </div>
                    @if ($errors->hasAny(['q', 'location', 'work_mode', 'page']))
                        <p class="mt-3 text-sm text-hb-error" role="alert">{{ __('candidate.vacancies_search.validation_error') }}</p>
                    @endif
                </form>
                <p class="mt-3 text-xs text-hb-muted">{{ __('candidate.vacancies_search.filter_hint') }}</p>
            </section>

            <section class="rounded-2xl border border-hb-border bg-white p-5 shadow-sm" aria-labelledby="skills-heading">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 id="skills-heading" class="text-base font-bold text-hb-navy">{{ __('Core skills') }}</h2>
                        <p class="mt-1 text-sm text-hb-muted">{{ __('Skills from your candidate profile.') }}</p>
                    </div>
                    <a href="{{ route('frontend.candidate.skills.add') }}" class="text-sm font-semibold text-hb-blue hover:underline">{{ __('Edit') }}</a>
                </div>
                @if (count($skillNames))
                    <ul class="mt-4 flex flex-wrap gap-2">
                        @foreach ($skillNames as $skill)
                            <li class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">{{ $skill }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-4 rounded-lg bg-slate-50 p-3 text-sm text-hb-muted">{{ __('Add skills to your profile to see more relevant match explanations.') }}</p>
                @endif
                @if (($skillsSource ?? null) === 'profile')
                    <p class="mt-3 text-xs text-hb-muted" role="status">{{ __('Skills are read from your profile because the dedicated skills endpoint is unavailable.') }}</p>
                @endif
            </section>
        </div>

        <div class="flex flex-col gap-3 rounded-xl border border-hb-border bg-white p-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-hb-navy">{{ __('Matching opportunities') }}</h2>
                <p class="text-sm text-hb-muted">{{ __('Why it fits is based on skill overlap visible in the job and your profile.') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" data-save-search class="rounded-lg border border-hb-border px-3 py-2 text-sm font-semibold text-hb-navy transition hover:bg-slate-50">{{ __('Save this search') }}</button>
                <button type="button" data-toggle-hidden class="rounded-lg border border-hb-border px-3 py-2 text-sm font-semibold text-hb-navy transition hover:bg-slate-50" aria-pressed="false">{{ __('Show hidden roles') }}</button>
            </div>
        </div>
        <p class="text-sm text-hb-muted" data-discovery-status role="status" aria-live="polite"></p>

        @if ($loadError ?? false)
            <div class="rounded-2xl border border-hb-error/30 bg-white p-6" role="alert" data-vacancies-load-error>
                <p class="font-semibold text-hb-error">{{ $loadError }}</p>
                <a href="{{ request()->fullUrl() }}" class="mt-3 inline-flex font-semibold text-hb-blue hover:underline">{{ __('candidate.vacancies_search.retry') }}</a>
            </div>
        @else
        <div class="grid gap-4 lg:grid-cols-2" data-job-list>
            @forelse ($items as $row)
                @php
                    $jobId = (string) data_get($row, 'id', '');
                    $companyValue = data_get($row, 'company.name') ?? data_get($row, 'company_name') ?? data_get($row, 'company');
                    $company = is_scalar($companyValue) ? $companyValue : '—';
                    $jobSkillRows = \App\Support\Rows::from(data_get($row, 'skills', []));
                    $jobSkillNames = collect($jobSkillRows)->map(fn ($skill) => is_string($skill) ? $skill : data_get($skill, 'name'))->filter(fn ($name) => is_string($name) && $name !== '')->values()->all();
                    $overlap = collect($jobSkillNames)->filter(fn ($jobSkill) => collect($skillNames)->contains(fn ($candidateSkill) => mb_strtolower($candidateSkill) === mb_strtolower($jobSkill)))->values()->all();
                    $applied = in_array($jobId, $appliedJobIds ?? [], true);
                @endphp
                <article
                    class="flex h-full flex-col rounded-2xl border border-hb-border bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                    data-job-card
                    data-job-id="{{ $jobId }}"
                    data-location="{{ data_get($row, 'location', '') }}"
                    data-mode="{{ data_get($row, 'work_mode', '') }}"
                    data-search-text="{{ data_get($row, 'title', '') }} {{ $company }} {{ data_get($row, 'location', '') }}"
                    @if (in_array($jobId, $appliedJobIds ?? [], true)) data-applied="true" @endif
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="font-bold text-hb-navy">{{ data_get($row, 'title', '—') }}</h3>
                            <p class="mt-1 text-sm text-hb-muted">{{ $company }} <span aria-hidden="true">·</span> {{ data_get($row, 'location', '—') }}</p>
                        </div>
                        @if ($applied)
                            <span class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-800">{{ __('Already applied') }}</span>
                        @endif
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs">
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-700">{{ data_get($row, 'work_mode', __('Work mode not listed')) }}</span>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-700">{{ __('Deadline') }}: {{ \App\Support\Rows::date(data_get($row, 'deadline'), 'Y-m-d') }}</span>
                    </div>
                    <div class="mt-4 rounded-xl bg-sky-50/70 p-3">
                        <p class="text-xs font-bold uppercase tracking-wide text-sky-900">{{ __('Why it fits') }}</p>
                        @if (count($overlap))
                            <p class="mt-1 text-sm text-slate-700">{{ __('Your profile matches :count listed skill(s): :skills', ['count' => count($overlap), 'skills' => implode(', ', $overlap)]) }}</p>
                        @else
                            <p class="mt-1 text-sm text-slate-700">{{ __('Compare the role requirements with your profile skills to see where you align.') }}</p>
                        @endif
                    </div>
                    <div class="mt-auto flex flex-wrap justify-end gap-2 pt-4">
                        <button type="button" data-hide-job class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">{{ __('Hide role') }}</button>
                        <x-button variant="light" size="sm" :href="route('frontend.candidate.vacancies.show', ['vacancyId' => data_get($row, 'id')])">{{ __('View') }}</x-button>
                        @if ($applied)
                            <x-button variant="primary" size="sm" :href="route('frontend.candidate.applications.index')">{{ __('View applications') }}</x-button>
                        @else
                            <x-button variant="primary" size="sm" :href="route('frontend.candidate.vacancies.apply', ['vacancyId' => data_get($row, 'id')])">{{ __('Apply') }}</x-button>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-hb-border bg-white px-6 py-12 text-center text-hb-muted lg:col-span-2" data-empty-results>{{ __('candidate.vacancies_search.empty') }}</div>
            @endforelse
        </div>
        <p class="hidden rounded-xl border border-hb-border bg-white p-6 text-center text-sm text-hb-muted" data-empty-filter>{{ __('candidate.vacancies_search.hidden_empty') }}</p>
        @include('frontend.partials.pagination', ['meta' => $meta, 'ariaLabel' => __('candidate.vacancies_search.pagination')])
        @endif
    </section>

    <aside class="h-fit rounded-2xl border border-hb-border bg-slate-900 p-5 text-white xl:sticky xl:top-6" aria-labelledby="discover-guide">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-sky-300">{{ __('Your search') }}</p>
        <h2 id="discover-guide" class="mt-2 text-lg font-bold">{{ __('Make discovery work for you') }}</h2>
        <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-300">
            <li>{{ __('candidate.vacancies_search.guide') }}</li>
            <li>{{ __('Hide roles to remove them from this device’s results.') }}</li>
            <li>{{ __('Saved search and hidden-role preferences are browser-local, not synced to your account.') }}</li>
        </ul>
        @if (count($contextErrors ?? []))
            <p class="mt-5 rounded-lg border border-amber-300/30 bg-amber-300/10 p-3 text-xs leading-5 text-amber-100" role="status">{{ __('Some profile, skill, or application context could not be loaded. Discovery remains available, but match and applied indicators may be incomplete.') }}</p>
        @endif
    </aside>
</div>

<script>
    (() => {
        const form = document.querySelector('[data-vacancy-search-form]');
        const searchInput = document.querySelector('[data-vacancy-search]');
        const clearSearchButton = document.querySelector('[data-clear-vacancy-search]');
        const pageInput = document.querySelector('[data-vacancy-page]');
        const list = document.querySelector('[data-job-list]');
        const cards = list ? [...list.querySelectorAll('[data-job-card]')] : [];
        const locationInput = document.querySelector('[data-preference-location]');
        const modeSelect = document.querySelector('[data-preference-mode]');
        const saveButton = document.querySelector('[data-save-search]');
        const hiddenToggle = document.querySelector('[data-toggle-hidden]');
        const status = document.querySelector('[data-discovery-status]');
        const empty = document.querySelector('[data-empty-filter]');
        const storageKey = 'candidate-discovery-v1';
        let hiddenIds = [];
        let showHidden = false;
        let filterTimer = null;
        const queryParameters = new URLSearchParams(window.location.search);
        const hasFilterQuery = ['q', 'location', 'work_mode'].some((key) => queryParameters.has(key));

        function readPreferences() {
            try {
                const stored = JSON.parse(localStorage.getItem(storageKey) || '{}');
                hiddenIds = Array.isArray(stored.hiddenIds) ? stored.hiddenIds.map(String) : [];
                if (!hasFilterQuery && stored.search && typeof stored.search === 'object') {
                    searchInput.value = String(stored.search.q ?? searchInput.value ?? '');
                    locationInput.value = String(stored.search.location ?? locationInput.value ?? '');
                    modeSelect.value = String(stored.search.mode ?? modeSelect.value ?? '');
                }
            } catch {
                status.textContent = @json(__('Your browser could not load saved discovery preferences.'));
            }
        }

        function persist(extra = {}) {
            try {
                const current = JSON.parse(localStorage.getItem(storageKey) || '{}');
                localStorage.setItem(storageKey, JSON.stringify({
                    ...current,
                    hiddenIds,
                    search: { q: searchInput.value.trim(), location: locationInput.value.trim(), mode: modeSelect.value },
                    ...extra,
                }));
                return true;
            } catch {
                status.textContent = @json(__('Changes could not be saved in this browser.'));
                return false;
            }
        }

        function updateCards() {
            let visible = 0;
            cards.forEach((card) => {
                const hidden = hiddenIds.includes(card.dataset.jobId);
                const isVisible = showHidden || !hidden;
                card.hidden = !isVisible;
                visible += Number(isVisible);
                const hideButton = card.querySelector('[data-hide-job]');
                hideButton.textContent = hidden ? @json(__('Unhide role')) : @json(__('Hide role'));
                hideButton.setAttribute('aria-pressed', String(hidden));
            });
            empty?.classList.toggle('hidden', visible !== 0 || cards.length === 0);
            hiddenToggle.textContent = showHidden ? @json(__('Hide hidden roles')) : @json(__('Show hidden roles'));
            hiddenToggle.setAttribute('aria-pressed', String(showHidden));
        }

        function scheduleFilterSubmit(delay = 250) {
            window.clearTimeout(filterTimer);
            filterTimer = window.setTimeout(() => {
                pageInput.value = '1';
                form.requestSubmit();
            }, delay);
        }

        readPreferences();
        updateCards();
        searchInput.addEventListener('input', () => {
            clearSearchButton.disabled = searchInput.value.trim() === '';
            scheduleFilterSubmit(300);
        });
        clearSearchButton.addEventListener('click', () => {
            searchInput.value = '';
            clearSearchButton.disabled = true;
            scheduleFilterSubmit(0);
            searchInput.focus();
        });
        locationInput.addEventListener('input', () => scheduleFilterSubmit(300));
        modeSelect.addEventListener('change', () => scheduleFilterSubmit(0));
        form.addEventListener('submit', () => {
            pageInput.value = '1';
            status.textContent = @json(__('candidate.vacancies_search.loading'));
        });
        if (!hasFilterQuery && !document.querySelector('[data-vacancies-load-error]')
            && (searchInput.value.trim() || locationInput.value.trim() || modeSelect.value)) {
            scheduleFilterSubmit(0);
        }
        saveButton.addEventListener('click', () => {
            status.textContent = persist({ savedAt: new Date().toISOString() })
                ? @json(__('Search saved on this device.'))
                : @json(__('Changes could not be saved in this browser.'));
        });
        hiddenToggle.addEventListener('click', () => {
            showHidden = !showHidden;
            updateCards();
        });
        cards.forEach((card) => {
            card.querySelector('[data-hide-job]').addEventListener('click', () => {
                const id = card.dataset.jobId;
                hiddenIds = hiddenIds.includes(id) ? hiddenIds.filter((hiddenId) => hiddenId !== id) : [...hiddenIds, id];
                const saved = persist();
                updateCards();
                status.textContent = saved
                    ? @json(__('Hidden roles updated on this device.'))
                    : @json(__('Changes could not be saved in this browser.'));
            });
        });
    })();
</script>
@endsection
