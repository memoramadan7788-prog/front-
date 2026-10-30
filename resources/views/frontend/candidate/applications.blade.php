@extends('frontend.layouts.app')

@section('title', __('candidate.applications.index_title'))

@section('page_header')
    <div>
        <p class="text-xs font-medium uppercase tracking-wide text-hb-muted">{{ __('candidate.applications.index_breadcrumb') }}</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.applications.index_title') }}</h1>
        <p class="mt-1 max-w-2xl text-sm text-hb-muted">{{ __('candidate.applications.index_intro') }}</p>
    </div>
@endsection

@section('content')
@php
    $items = is_array($applications ?? null) ? $applications : [];
    $tabRows = is_array($tabs ?? null) ? $tabs : [];
    $filterState = is_array($filters ?? null) ? $filters : ['tab' => 'all', 'q' => '', 'status' => '', 'sort' => 'applied_at_desc'];
    $statuses = is_array($statusOptions ?? null) ? $statusOptions : [];
    $hasError = filled($loadError ?? null);
    $statusLabel = function (?string $status) {
        $key = strtolower(trim((string) $status));
        if ($key === '') {
            return __('candidate.analytics.status.unknown');
        }
        $full = 'candidate.analytics.status.'.$key;
        $translated = __($full);

        return $translated !== $full ? $translated : __('candidate.analytics.status.unknown');
    };
    $tabLabel = function (string $key) {
        return match ($key) {
            'in_progress' => __('candidate.applications.tab_in_progress'),
            'interviewing' => __('candidate.applications.tab_interviewing'),
            'offered' => __('candidate.applications.tab_offered'),
            'completed' => __('candidate.applications.tab_completed'),
            default => __('candidate.applications.tab_all'),
        };
    };
    $resetQuery = array_filter([
        'tab' => ($filterState['tab'] ?? 'all') !== 'all' ? $filterState['tab'] : null,
    ]);
@endphp

@if ($hasError)
    @include('frontend.partials.pipeline-state', [
        'loadError' => $loadError,
        'loadStatus' => $loadStatus ?? null,
        'emptyTitle' => __('candidate.pipeline.applications_empty_title'),
        'emptyBody' => __('candidate.pipeline.applications_empty_body'),
        'ctaLabel' => __('candidate.pipeline.applications_empty_cta'),
        'ctaHref' => route('frontend.candidate.vacancies.index'),
    ])
@else
    @if (filled($catalogWarning ?? null))
        <div class="mb-4 rounded-xl border border-hb-pending/30 bg-hb-pending-soft/40 px-4 py-3 text-sm text-hb-navy" role="status" data-catalog-warning>
            {{ $catalogWarning }}
        </div>
    @endif

    @if ($tabRows !== [])
        <div class="hb-apps-tabs mb-5 flex flex-wrap gap-2 border-b border-hb-border pb-3" role="tablist" aria-label="{{ __('candidate.applications.tablist_aria') }}">
            @foreach ($tabRows as $tab)
                @php
                    $active = (bool) ($tab['active'] ?? false);
                    $count = $tab['count'] ?? null;
                    $label = $tabLabel((string) ($tab['key'] ?? 'all'));
                @endphp
                <a
                    href="{{ $tab['href'] ?? '#' }}"
                    role="tab"
                    aria-selected="{{ $active ? 'true' : 'false' }}"
                    data-apps-tab="{{ $tab['key'] ?? 'all' }}"
                    class="hb-apps-tab inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-sm font-medium transition {{ $active ? 'bg-hb-navy text-white shadow-sm' : 'bg-hb-page text-hb-muted hover:text-hb-navy' }}"
                >
                    <span>{{ $label }}</span>
                    @if ($count !== null)
                        <span class="rounded-full px-1.5 text-xs {{ $active ? 'bg-white/20 text-white' : 'bg-hb-surface text-hb-muted' }}" data-tab-count>{{ $count }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    @endif

    <form method="GET" action="{{ route('frontend.candidate.applications.index') }}" class="hb-apps-filters mb-5 grid gap-3 rounded-2xl border border-hb-border bg-hb-surface p-4 sm:grid-cols-2 lg:grid-cols-4 lg:items-end" data-apps-filters>
        @if (($filterState['tab'] ?? 'all') !== 'all')
            <input type="hidden" name="tab" value="{{ $filterState['tab'] }}">
        @endif

        <div>
            <label class="hb-label" for="apps-filter-q">{{ __('candidate.applications.search') }}</label>
            <input id="apps-filter-q" class="hb-input" type="search" name="q" value="{{ $filterState['q'] ?? '' }}" placeholder="{{ __('candidate.applications.search_placeholder') }}" autocomplete="off">
        </div>

        <div>
            <label class="hb-label" for="apps-filter-status">{{ __('candidate.applications.filter_status') }}</label>
            <select id="apps-filter-status" class="hb-input" name="status">
                <option value="">{{ __('candidate.applications.all_statuses') }}</option>
                @foreach ($statuses as $statusKey)
                    <option value="{{ $statusKey }}" @selected(($filterState['status'] ?? '') === $statusKey)>{{ $statusLabel($statusKey) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="hb-label" for="apps-filter-sort">{{ __('candidate.applications.sort_apps') }}</label>
            <select id="apps-filter-sort" class="hb-input" name="sort">
                <option value="applied_at_desc" @selected(($filterState['sort'] ?? '') === 'applied_at_desc')>{{ __('candidate.applications.sort_newest') }}</option>
                <option value="applied_at_asc" @selected(($filterState['sort'] ?? '') === 'applied_at_asc')>{{ __('candidate.applications.sort_oldest') }}</option>
                <option value="title_asc" @selected(($filterState['sort'] ?? '') === 'title_asc')>{{ __('candidate.applications.sort_title') }}</option>
            </select>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <x-button type="submit" size="sm">{{ __('candidate.applications.apply_filters') }}</x-button>
            <x-button type="button" variant="light" size="sm" :href="route('frontend.candidate.applications.index', $resetQuery)">{{ __('candidate.applications.reset_filters') }}</x-button>
        </div>
    </form>

    @if ($emptyCatalog ?? false)
        @include('frontend.partials.pipeline-state', [
            'loadError' => null,
            'loadStatus' => null,
            'emptyTitle' => __('candidate.pipeline.applications_empty_title'),
            'emptyBody' => __('candidate.pipeline.applications_empty_body'),
            'ctaLabel' => __('candidate.pipeline.applications_empty_cta'),
            'ctaHref' => route('frontend.candidate.vacancies.index'),
        ])
    @elseif ($emptyFiltered ?? false)
        @include('frontend.partials.pipeline-state', [
            'loadError' => null,
            'loadStatus' => null,
            'emptyTitle' => __('candidate.applications.empty_filtered_title'),
            'emptyBody' => __('candidate.applications.empty_filtered_body'),
            'ctaLabel' => __('candidate.applications.reset_filters'),
            'ctaHref' => route('frontend.candidate.applications.index', $resetQuery),
        ])
    @else
        {{-- Desktop table --}}
        <div class="hidden md:block" data-apps-table>
            <x-table>
                <x-slot:head>
                    <th class="px-4 py-3 text-start">{{ __('candidate.applications.col_job') }}</th>
                    <th class="px-4 py-3 text-start">{{ __('candidate.applications.col_company') }}</th>
                    <th class="px-4 py-3 text-start">{{ __('candidate.applications.col_status') }}</th>
                    <th class="px-4 py-3 text-start">{{ __('candidate.applications.col_pipeline') }}</th>
                    <th class="px-4 py-3 text-start">{{ __('candidate.applications.col_applied') }}</th>
                    <th class="px-4 py-3 text-end">{{ __('candidate.applications.col_actions') }}</th>
                </x-slot:head>

                @foreach ($items as $row)
                    @php
                        $status = (string) data_get($row, 'status', '');
                        $documented = (bool) data_get($row, 'status_documented', false);
                        $badgeLabel = $documented ? $statusLabel($status) : __('candidate.analytics.status.unknown');
                        $pipeline = is_array(data_get($row, 'pipeline')) ? data_get($row, 'pipeline') : [];
                    @endphp
                    <tr data-application-row data-status="{{ $documented ? $status : 'unknown' }}">
                        <td class="px-4 py-3 font-medium text-hb-navy">{{ data_get($row, 'job_title', '—') }}</td>
                        <td class="px-4 py-3 text-hb-muted">{{ data_get($row, 'company_name', '—') }}</td>
                        <td class="px-4 py-3">
                            <x-badge :variant="\App\Support\Rows::badgeVariant($documented ? $status : null)" :dot="true">{{ $badgeLabel }}</x-badge>
                        </td>
                        <td class="px-4 py-3">
                            @include('frontend.candidate.partials.application-pipeline', ['pipeline' => $pipeline, 'compact' => true])
                        </td>
                        <td class="px-4 py-3 text-sm text-hb-muted">{{ \App\Support\Rows::date(data_get($row, 'applied_at'), 'Y-m-d') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap justify-end gap-2">
                                <x-button variant="light" size="sm" :href="route('frontend.candidate.applications.show', ['applicationId' => data_get($row, 'id')])">{{ __('candidate.applications.view') }}</x-button>
                                @if (data_get($row, 'can_withdraw'))
                                    <form method="POST" action="{{ route('frontend.candidate.applications.withdraw', ['applicationId' => data_get($row, 'id')]) }}" class="inline" onsubmit="return confirm(@js(__('Withdraw this application?')))">
                                        @csrf
                                        <x-button type="submit" size="sm" variant="destructive">{{ __('Withdraw') }}</x-button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </div>

        {{-- Mobile cards --}}
        <div class="space-y-3 md:hidden" data-apps-cards>
            @foreach ($items as $row)
                @php
                    $status = (string) data_get($row, 'status', '');
                    $documented = (bool) data_get($row, 'status_documented', false);
                    $badgeLabel = $documented ? $statusLabel($status) : __('candidate.analytics.status.unknown');
                    $pipeline = is_array(data_get($row, 'pipeline')) ? data_get($row, 'pipeline') : [];
                @endphp
                <article class="hb-surface rounded-2xl p-4" data-application-card data-status="{{ $documented ? $status : 'unknown' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-hb-navy">{{ data_get($row, 'job_title', '—') }}</h2>
                            <p class="mt-0.5 text-sm text-hb-muted">{{ data_get($row, 'company_name', '—') }}</p>
                        </div>
                        <x-badge :variant="\App\Support\Rows::badgeVariant($documented ? $status : null)" :dot="true">{{ $badgeLabel }}</x-badge>
                    </div>
                    <p class="mt-3 text-xs text-hb-muted">{{ __('candidate.applications.applied_on', ['date' => \App\Support\Rows::date(data_get($row, 'applied_at'), 'Y-m-d')]) }}</p>
                    <div class="mt-3">
                        @include('frontend.candidate.partials.application-pipeline', ['pipeline' => $pipeline, 'compact' => false])
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <x-button variant="light" size="sm" :href="route('frontend.candidate.applications.show', ['applicationId' => data_get($row, 'id')])">{{ __('candidate.applications.view_application') }}</x-button>
                        @if (data_get($row, 'can_withdraw'))
                            <form method="POST" action="{{ route('frontend.candidate.applications.withdraw', ['applicationId' => data_get($row, 'id')]) }}" class="inline" onsubmit="return confirm(@js(__('Withdraw this application?')))">
                                @csrf
                                <x-button type="submit" size="sm" variant="destructive">{{ __('Withdraw') }}</x-button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        @include('frontend.partials.pagination', [
            'meta' => $meta ?? null,
            'showStatus' => true,
            'ariaLabel' => __('candidate.applications.pagination_aria'),
        ])

        <p class="mt-4 text-xs text-hb-muted" data-pipeline-footnote>{{ __('candidate.applications.pipeline_snapshot_note') }}</p>
        <p class="mt-1 text-xs text-hb-muted">{{ __('candidate.applications.footer_note') }}</p>
    @endif
@endif
@endsection
