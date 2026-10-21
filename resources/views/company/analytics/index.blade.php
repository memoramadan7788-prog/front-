@extends('layouts.app')

@section('title', $pageTitle ?? __('company.analytics.page_title'))
@section('breadcrumb', __('company.analytics.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ __('company.analytics.title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ $analytics['subtitle'] }}</p>
@endsection

@section('page_actions')
    <button
        type="button"
        class="inline-flex cursor-not-allowed items-center justify-center rounded-lg bg-hb-border px-4 py-2 text-sm font-semibold text-hb-muted"
        disabled
        aria-disabled="true"
        title="{{ __('company.analytics.export_unavailable') }}"
    >
        {{ __('company.analytics.export') }}
    </button>
@endsection

@section('content')
    @php
        $analyticsI18n = [
            'empty' => __('company.analytics.js.empty'),
            'source_line' => __('company.analytics.source_line'),
            'median_days' => __('company.analytics.median_days'),
            'total' => __('company.analytics.total'),
        ];
    @endphp

    <div
        data-company-analytics
        data-i18n="{{ json_encode($analyticsI18n, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-analytics="{{ json_encode($analytics, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
    >
        <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-center">
            <label class="min-w-0 flex-1">
                <span class="sr-only">{{ __('company.analytics.date_range') }}</span>
                <select data-analytics-date class="hb-input w-full">
                    @foreach ($analytics['date_options'] as $value => $label)
                        <option value="{{ $value }}" @selected($value === 'oct-1-20')>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <div class="flex flex-wrap gap-2">
                <label class="sr-only" for="analytics-req">{{ __('company.analytics.all_requisitions') }}</label>
                <select id="analytics-req" data-analytics-req class="hb-input w-auto min-w-[10rem]">
                    <option value="">{{ __('company.analytics.all_requisitions') }}</option>
                    @foreach ($analytics['requisitions'] as $row)
                        <option value="{{ $row['id'] }}">{{ $row['label'] }}</option>
                    @endforeach
                </select>
                <label class="sr-only" for="analytics-source">{{ __('company.analytics.all_sources') }}</label>
                <select id="analytics-source" data-analytics-source class="hb-input w-auto min-w-[9rem]">
                    <option value="">{{ __('company.analytics.all_sources') }}</option>
                    <option value="direct">{{ __('company.analytics.source_direct') }}</option>
                    <option value="agency">{{ __('company.analytics.source_agency') }}</option>
                </select>
                <button type="button" class="hb-btn-outline" data-analytics-clear>{{ __('company.analytics.clear_filters') }}</button>
            </div>
        </div>

        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" data-kpi-grid>
            @foreach ($analytics['kpis'] as $kpi)
                <article class="hb-surface p-5" data-kpi="{{ $kpi['key'] }}">
                    <p class="text-sm font-medium text-hb-muted">{{ $kpi['label'] }}</p>
                    <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy" dir="ltr">{{ $kpi['value'] }}</p>
                    <p class="mt-2 text-sm text-hb-muted">{{ $kpi['detail'] }}</p>
                </article>
            @endforeach
        </div>

        <div class="mb-6 grid gap-6 lg:grid-cols-2">
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="stage-dist-heading">
                <h2 id="stage-dist-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.analytics.stage_distribution') }}</h2>
                <p class="mt-1 text-xs text-hb-muted">{{ $analytics['stage_note'] }}</p>
                <ul class="mt-5 space-y-3" data-stage-bars role="list" aria-label="{{ __('company.analytics.stage_distribution') }}">
                    @foreach ($analytics['stages'] as $stage)
                        @php
                            $pct = $analytics['stage_max'] > 0 ? round(($stage['count'] / $analytics['stage_max']) * 100) : 0;
                            $barClass = ($stage['tone'] ?? '') === 'success' ? 'bg-hb-success' : 'bg-hb-blue';
                        @endphp
                        <li>
                            <div class="mb-1 flex items-center justify-between gap-3 text-sm">
                                <span class="font-medium text-hb-navy">{{ $stage['label'] }}</span>
                                <span class="font-semibold text-hb-text" dir="ltr">{{ $stage['count'] }}</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-hb-page" role="presentation">
                                <div class="h-full rounded-full {{ $barClass }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="hb-surface p-5 sm:p-6" aria-labelledby="time-stage-heading">
                <h2 id="time-stage-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.analytics.time_in_stage') }}</h2>
                <ul class="mt-5 space-y-4">
                    @foreach ($analytics['time_in_stage'] as $row)
                        <li class="text-sm">
                            <p class="font-semibold text-hb-navy">{{ $row['label'] }}</p>
                            <p class="mt-1 text-hb-muted">
                                {{ __('company.analytics.median_days', ['days' => $row['median'], 'count' => $row['records']]) }}
                            </p>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-5 text-xs text-hb-muted">{{ $analytics['time_note'] }}</p>
            </section>
        </div>

        <section class="hb-surface mb-6 p-5 sm:p-6" aria-labelledby="source-heading">
            <h2 id="source-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.analytics.source_performance') }}</h2>
            <ul class="mt-5 space-y-4" data-source-list>
                @foreach ($analytics['sources'] as $source)
                    <li class="rounded-lg border border-hb-border px-4 py-3" data-source-key="{{ $source['key'] }}">
                        <p class="font-semibold text-hb-navy">{{ $source['label'] }}</p>
                        <p class="mt-1 text-sm text-hb-muted">
                            {{ __('company.analytics.source_line', [
                                'records' => $source['records'],
                                'interview' => $source['interview'],
                                'offer' => $source['offer'],
                                'hired' => $source['hired'],
                            ]) }}
                        </p>
                    </li>
                @endforeach
                <li class="rounded-lg border border-hb-border bg-hb-page px-4 py-3">
                    <p class="font-semibold text-hb-navy">{{ __('company.analytics.total') }}</p>
                    <p class="mt-1 text-sm text-hb-muted">
                        {{ __('company.analytics.source_line', [
                            'records' => $analytics['sources_total']['records'],
                            'interview' => $analytics['sources_total']['interview'],
                            'offer' => $analytics['sources_total']['offer'],
                            'hired' => $analytics['sources_total']['hired'],
                        ]) }}
                    </p>
                </li>
            </ul>
            <p class="mt-4 text-xs text-hb-muted">{{ $analytics['sources_note'] }}</p>
        </section>

        <section class="hb-surface overflow-hidden" aria-labelledby="req-breakdown-heading">
            <div class="border-b border-hb-border px-5 py-4">
                <h2 id="req-breakdown-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.analytics.requisition_breakdown') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <caption class="sr-only">{{ __('company.analytics.requisition_breakdown') }}</caption>
                    <thead class="bg-hb-page text-start text-hb-muted">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold">{{ __('company.analytics.col_requisition') }}</th>
                            <th scope="col" class="px-5 py-3 font-semibold" dir="ltr">{{ __('company.analytics.col_applied') }}</th>
                            <th scope="col" class="px-5 py-3 font-semibold" dir="ltr">{{ __('company.analytics.col_screening') }}</th>
                            <th scope="col" class="px-5 py-3 font-semibold" dir="ltr">{{ __('company.analytics.col_interview') }}</th>
                            <th scope="col" class="px-5 py-3 font-semibold" dir="ltr">{{ __('company.analytics.col_offer') }}</th>
                            <th scope="col" class="px-5 py-3 font-semibold" dir="ltr">{{ __('company.analytics.col_hired') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-hb-border" data-req-body>
                        @foreach ($analytics['requisitions'] as $row)
                            <tr data-req-id="{{ $row['id'] }}" data-source-key="{{ $row['source_key'] }}">
                                <th scope="row" class="px-5 py-3 text-start font-semibold text-hb-navy">{{ $row['label'] }}</th>
                                <td class="px-5 py-3 text-hb-text" dir="ltr">{{ $row['applied'] }}</td>
                                <td class="px-5 py-3 text-hb-text" dir="ltr">{{ $row['screening'] }}</td>
                                <td class="px-5 py-3 text-hb-text" dir="ltr">{{ $row['interview'] }}</td>
                                <td class="px-5 py-3 text-hb-text" dir="ltr">{{ $row['offer'] }}</td>
                                <td class="px-5 py-3 text-hb-text" dir="ltr">{{ $row['hired'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="hidden px-5 py-8 text-center text-sm text-hb-muted" data-analytics-empty>{{ __('company.analytics.empty') }}</p>
        </section>

        <p class="mt-8 text-center text-xs text-hb-muted">{{ $analytics['footer'] }}</p>
    </div>

    @push('scripts')
        <script>
            (() => {
                const root = document.querySelector('[data-company-analytics]');
                if (!root) return;
                const reqSelect = root.querySelector('[data-analytics-req]');
                const sourceSelect = root.querySelector('[data-analytics-source]');
                const rows = [...root.querySelectorAll('[data-req-body] tr')];
                const sourceCards = [...root.querySelectorAll('[data-source-key]')];
                const emptyEl = root.querySelector('[data-analytics-empty]');
                const table = root.querySelector('table');

                function applyFilters() {
                    const req = reqSelect?.value || '';
                    const source = sourceSelect?.value || '';
                    let visible = 0;
                    rows.forEach((row) => {
                        const id = row.getAttribute('data-req-id') || '';
                        const src = row.getAttribute('data-source-key') || '';
                        const matchReq = !req || id === req;
                        const matchSource = !source || src === source || src === 'mixed';
                        const show = matchReq && matchSource;
                        row.classList.toggle('hidden', !show);
                        row.hidden = !show;
                        if (show) visible += 1;
                    });
                    sourceCards.forEach((card) => {
                        const key = card.getAttribute('data-source-key') || '';
                        const show = !source || key === source;
                        card.classList.toggle('hidden', !show);
                        card.hidden = !show;
                    });
                    const hasRows = visible > 0;
                    emptyEl?.classList.toggle('hidden', hasRows);
                    if (emptyEl) emptyEl.hidden = hasRows;
                    table?.classList.toggle('hidden', !hasRows);
                }

                [reqSelect, sourceSelect].forEach((el) => el?.addEventListener('change', applyFilters));
                root.querySelector('[data-analytics-clear]')?.addEventListener('click', () => {
                    if (reqSelect) reqSelect.value = '';
                    if (sourceSelect) sourceSelect.value = '';
                    const date = root.querySelector('[data-analytics-date]');
                    if (date) date.value = 'oct-1-20';
                    applyFilters();
                });
            })();
        </script>
    @endpush
@endsection
