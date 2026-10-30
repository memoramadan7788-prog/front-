@extends('frontend.layouts.app')

@section('title', __('Hiring analytics'))

@php
    $jobs = \App\Support\Rows::from($jobs ?? []);
    $displayValue = static fn (mixed $value, string $fallback = '—'): string => is_scalar($value) ? (string) $value : $fallback;
    $statusCounts = collect($jobs)->groupBy(fn ($job) => strtolower($displayValue(data_get($job, 'status'), 'unknown')))->map->count();
    $activeCount = collect($jobs)->filter(fn ($job) => in_array(strtolower($displayValue(data_get($job, 'status'), '')), ['active', 'open', 'published'], true))->count();
    $statusTotal = max(1, count($jobs));
    $sampleStages = [__('Applied') => 52, __('Screening') => 31, __('Interview') => 18, __('Offer') => 7];
    $sampleStageTotal = array_sum($sampleStages);
    $sampleStageDurations = [__('Applied') => '2.1', __('Screening') => '3.4', __('Interview') => '5.2', __('Offer') => '2.6'];
@endphp

@section('page_header')
    <div>
        <p class="text-sm font-semibold text-hb-blue">{{ __('Hiring team') }}</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Hiring analytics') }}</h1>
    </div>
@endsection

@section('content')
<div class="space-y-5">
    <section class="flex flex-col gap-3 rounded-2xl border border-sky-200 bg-sky-50 p-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm leading-6 text-sky-950">{{ __('Live data is limited to jobs returned by the connected company-jobs API. Applicant stage duration and source analytics require an analytics API that is not available in this portal.') }}</p>
        <span class="w-fit shrink-0 rounded-full bg-white px-3 py-1 text-xs font-bold text-sky-900">{{ __('Verified job data') }}</span>
    </section>

    <form method="GET" action="{{ route('frontend.company.analytics') }}" class="flex flex-col gap-3 rounded-2xl border border-hb-border bg-white p-4 sm:flex-row sm:items-end">
        <label class="block text-sm font-medium text-hb-text">{{ __('Created from') }}
            <input type="date" name="from" value="{{ $from }}" class="mt-1.5 w-full rounded-lg border border-hb-border px-3 py-2 sm:w-auto">
        </label>
        <label class="block text-sm font-medium text-hb-text">{{ __('Created to') }}
            <input type="date" name="to" value="{{ $to }}" min="{{ $from }}" class="mt-1.5 w-full rounded-lg border border-hb-border px-3 py-2 sm:w-auto">
        </label>
        <button type="submit" class="rounded-lg bg-hb-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-hb-navy">{{ __('Apply date range') }}</button>
        <a href="{{ route('frontend.company.analytics') }}" class="rounded-lg border border-hb-border px-4 py-2.5 text-center text-sm font-semibold text-hb-navy hover:bg-slate-50">{{ __('Clear') }}</a>
        <button type="button" data-export-jobs class="sm:ms-auto rounded-lg border border-hb-border px-4 py-2.5 text-sm font-semibold text-hb-navy hover:bg-slate-50">{{ __('Export job data') }}</button>
    </form>

    @if (! $analyticsAvailable)
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-900" role="alert">{{ __('Could not load verified job data: :message', ['message' => $apiMessage]) }}</div>
    @endif

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="{{ __('Hiring key performance indicators') }}">
        @foreach ([
            ['label' => __('Roles in range'), 'value' => $analyticsAvailable ? count($jobs) : '—', 'note' => __('Jobs returned by company API')],
            ['label' => __('Active / published roles'), 'value' => $analyticsAvailable ? $activeCount : '—', 'note' => __('Based on job status')],
            ['label' => __('Applications'), 'value' => '—', 'note' => __('No aggregate analytics endpoint available')],
            ['label' => __('Median time in stage'), 'value' => '—', 'note' => __('Stage history is not returned by connected API')],
        ] as $kpi)
            <article class="rounded-xl border border-hb-border bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ $kpi['label'] }}</p>
                <p class="mt-2 text-2xl font-bold text-hb-navy">{{ $kpi['value'] }}</p>
                <p class="mt-1 text-xs text-hb-muted">{{ $kpi['note'] }}</p>
            </article>
        @endforeach
    </section>

    <div class="grid gap-5 lg:grid-cols-2">
        <section class="rounded-2xl border border-hb-border bg-white p-5 shadow-sm" aria-labelledby="job-status-chart">
            <h2 id="job-status-chart" class="text-lg font-bold text-hb-navy">{{ __('Job status distribution') }}</h2>
            <p class="mt-1 text-sm text-hb-muted">{{ __('Distribution of jobs returned for the selected date range. This is not the applicant pipeline.') }}</p>
            @if ($analyticsAvailable && $statusCounts->isNotEmpty())
                <ul class="mt-5 space-y-4">
                    @foreach ($statusCounts as $status => $count)
                        @php $percent = (int) round(($count / $statusTotal) * 100); @endphp
                        <li>
                            <div class="mb-1 flex justify-between gap-3 text-sm"><span class="font-medium text-hb-text">{{ \Illuminate\Support\Str::headline($status) }}</span><span class="text-hb-muted">{{ $count }} · {{ $percent }}%</span></div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-label="{{ \Illuminate\Support\Str::headline($status) }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percent }}"><span class="block h-full rounded-full bg-sky-500" style="width: {{ $percent }}%"></span></div>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-5 rounded-lg bg-slate-50 p-4 text-sm text-hb-muted">{{ __('No job status data is available for this range.') }}</p>
            @endif
        </section>

        <section class="rounded-2xl border border-hb-border bg-white p-5 shadow-sm" aria-labelledby="stage-distribution">
            <h2 id="stage-distribution" class="text-lg font-bold text-hb-navy">{{ __('Applicant stage distribution') }}</h2>
            <p class="mt-1 text-sm text-hb-muted">{{ __('Sample distribution for preview only. Live applicant stage counts require a company analytics endpoint.') }}</p>
            <span class="mt-3 inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-900">{{ __('Illustrative sample data') }}</span>
            <ul class="mt-5 space-y-4" aria-label="{{ __('Illustrative sample applicant stage distribution') }}">
                @foreach ($sampleStages as $stage => $count)
                    @php $percent = (int) round(($count / $sampleStageTotal) * 100); @endphp
                    <li>
                        <div class="mb-1 flex justify-between gap-3 text-sm"><span class="font-medium text-hb-text">{{ $stage }}</span><span class="text-hb-muted">{{ $count }} · {{ $percent }}%</span></div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100" role="img" aria-label="{{ $stage }}: {{ $percent }}%"><span class="block h-full rounded-full bg-indigo-500" style="width: {{ $percent }}%"></span></div>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="rounded-2xl border border-hb-border bg-white p-5 shadow-sm" aria-labelledby="time-in-stage">
            <h2 id="time-in-stage" class="text-lg font-bold text-hb-navy">{{ __('Time in stage') }}</h2>
            <p class="mt-1 text-sm text-hb-muted">{{ __('Sample median days in each stage. Live durations require stage-change timestamps.') }}</p>
            <span class="mt-3 inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-900">{{ __('Illustrative sample data') }}</span>
            <ul class="mt-5 divide-y divide-hb-border" aria-label="{{ __('Illustrative sample time in stage') }}">
                @foreach ($sampleStageDurations as $stage => $days)
                    <li class="flex items-center justify-between gap-3 py-3 text-sm"><span class="font-medium text-hb-text">{{ $stage }}</span><span class="font-bold text-hb-navy">{{ $days }} {{ __('days median') }}</span></li>
                @endforeach
            </ul>
        </section>

        <section class="rounded-2xl border border-hb-border bg-white p-5 shadow-sm" aria-labelledby="source-performance">
            <h2 id="source-performance" class="text-lg font-bold text-hb-navy">{{ __('Source performance') }}</h2>
            <p class="mt-1 text-sm text-hb-muted">{{ __('Source comparisons require applicant source fields and outcome totals.') }}</p>
            <p class="mt-5 rounded-lg bg-slate-50 p-4 text-sm text-hb-muted">{{ __('Source performance data is not available from the company jobs endpoint.') }}</p>
        </section>
    </div>

    <section class="rounded-2xl border border-hb-border bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-hb-navy">{{ __('Jobs in selected range') }}</h2>
                <p class="mt-1 text-sm text-hb-muted">{{ __('CSV export includes only the live job data shown below.') }}</p>
            </div>
            <span class="text-sm text-hb-muted">{{ count($jobs) }} {{ __('roles') }}</span>
        </div>
        @if (count($jobs))
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[36rem] text-start text-sm" data-export-table>
                    <thead><tr class="border-b border-hb-border text-xs uppercase text-hb-muted"><th class="px-3 py-3">{{ __('Title') }}</th><th class="px-3 py-3">{{ __('Status') }}</th><th class="px-3 py-3">{{ __('Location') }}</th><th class="px-3 py-3">{{ __('Created') }}</th></tr></thead>
                    <tbody>
                        @foreach ($jobs as $job)
                            <tr class="border-b border-hb-border/70"><td class="px-3 py-3 font-medium text-hb-navy">{{ $displayValue(data_get($job, 'title')) }}</td><td class="px-3 py-3">{{ $displayValue(data_get($job, 'status')) }}</td><td class="px-3 py-3">{{ $displayValue(data_get($job, 'location')) }}</td><td class="px-3 py-3">{{ \App\Support\Rows::date($displayValue(data_get($job, 'created_at'), ''), 'Y-m-d') }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="mt-4 text-sm text-hb-muted">{{ __('No jobs match this date range.') }}</p>
        @endif
    </section>
</div>

<script>
    document.querySelector('[data-export-jobs]')?.addEventListener('click', () => {
        const table = document.querySelector('[data-export-table]');
        if (!table) return;
        const csv = [...table.rows].map((row) => [...row.cells].map((cell) => `"${cell.innerText.replaceAll('"', '""')}"`).join(',')).join('\r\n');
        const link = document.createElement('a');
        link.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
        link.download = 'hiring-jobs.csv';
        link.click();
        URL.revokeObjectURL(link.href);
    });
</script>
@endsection
