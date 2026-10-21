@extends('layouts.app')

@section('title', $pageTitle ?? 'Overview · Hirebridge')
@section('breadcrumb', '25 / Company workspace / Overview')

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ $dashboard['greeting'] }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ $dashboard['intro'] }}</p>
@endsection

@section('page_actions')
    <div class="flex flex-wrap items-center gap-2" data-company-dashboard>
        <label class="sr-only" for="company-date-range">Date range</label>
        <select
            id="company-date-range"
            class="hb-input w-auto min-w-[10rem] py-2 text-sm"
            data-dashboard-date-range
            aria-describedby="company-date-range-note"
        >
            @foreach ($dashboard['date_ranges'] as $range)
                <option value="{{ $range }}" @selected($range === $dashboard['date_range'])>{{ $range }}</option>
            @endforeach
        </select>
        <button
            type="button"
            class="hb-btn-primary"
            disabled
            title="Create job arrives in Batch 5B"
            aria-describedby="create-job-pending"
        >
            + Create job
        </button>
    </div>
@endsection

@section('content')
    <div class="hb-mock-banner mb-6 rounded-lg text-start">
        Frontend UI only — dashboard metrics and actions are mock presentation data (Figma Frame 25). Analytics queries are not live.
    </div>
    <p id="company-date-range-note" class="sr-only">Date range filters mock presentation only until Backend analytics exist.</p>
    <p id="create-job-pending" class="sr-only">Create job is pending Batch 5B.</p>

    <p
        class="mb-4 hidden rounded-lg border border-hb-info-soft bg-hb-info-soft px-3 py-2.5 text-sm text-hb-navy"
        data-dashboard-range-note
        role="status"
    ></p>

    {{-- KPI cards --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($dashboard['stats'] as $stat)
            <div class="hb-surface p-5">
                <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ $stat['label'] }}</p>
                <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ $stat['value'] }}</p>
                <p class="mt-2 text-sm text-hb-muted">{{ $stat['meta'] }}</p>
                @if (! empty($stat['trend']))
                    <p class="mt-2 text-xs font-medium {{ ($stat['trend_tone'] ?? '') === 'up' ? 'text-hb-success' : 'text-hb-muted' }}">
                        {{ $stat['trend'] }}
                    </p>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div class="space-y-6">
            {{-- Pipeline overview --}}
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="pipeline-heading" data-pipeline-overview>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 id="pipeline-heading" class="text-lg font-semibold text-hb-navy">
                            {{ $dashboard['pipeline']['title'] }}
                        </h2>
                        <p class="mt-1 text-sm text-hb-muted">{{ $dashboard['pipeline']['subtitle'] }}</p>
                    </div>
                    <span
                        class="inline-flex cursor-not-allowed items-center rounded-lg border border-hb-border px-3 py-1.5 text-xs font-semibold text-hb-muted"
                        title="Applicant pipeline board arrives in Batch 5C"
                        aria-disabled="true"
                    >
                        Open board · Soon
                    </span>
                </div>

                <ul class="mt-5 space-y-3" role="list">
                    @foreach ($dashboard['pipeline']['stages'] as $stage)
                        <li>
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="font-medium text-hb-navy">{{ $stage['label'] }}</span>
                                <span class="tabular-nums text-hb-muted">{{ $stage['count'] }}</span>
                            </div>
                            <div
                                class="mt-1.5 h-2 overflow-hidden rounded-full bg-hb-page"
                                role="img"
                                aria-label="{{ $stage['label'] }}: {{ $stage['count'] }} candidates"
                            >
                                <div
                                    class="h-full rounded-full {{ ($stage['tone'] ?? '') === 'success' ? 'bg-hb-success' : 'bg-hb-blue' }}"
                                    style="width: {{ min(100, (int) $stage['share']) }}%"
                                ></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-4 text-xs text-hb-muted">{{ $dashboard['pipeline']['footer'] }}</p>
            </section>

            {{-- Active requisitions --}}
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="requisitions-heading">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 id="requisitions-heading" class="text-lg font-semibold text-hb-navy">Active requisitions</h2>
                        <p class="mt-1 text-sm text-hb-muted">Open roles across Northstar Labs</p>
                    </div>
                    <span
                        class="inline-flex cursor-not-allowed items-center rounded-lg border border-hb-border px-3 py-1.5 text-xs font-semibold text-hb-muted"
                        title="Jobs & requisitions arrives in Batch 5B"
                        aria-disabled="true"
                    >
                        View all · Soon
                    </span>
                </div>

                <div class="mt-5 overflow-x-auto">
                    <table class="w-full min-w-[36rem] text-start text-sm">
                        <caption class="sr-only">Active open requisitions</caption>
                        <thead>
                            <tr class="border-b border-hb-border text-xs font-semibold tracking-wide text-hb-muted uppercase">
                                <th scope="col" class="pb-3 pe-4 font-semibold">Requisition</th>
                                <th scope="col" class="pb-3 pe-4 font-semibold">Owner / team</th>
                                <th scope="col" class="pb-3 pe-4 font-semibold">Pipeline</th>
                                <th scope="col" class="pb-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hb-border">
                            @foreach ($dashboard['requisitions'] as $req)
                                <tr>
                                    <td class="py-3 pe-4 align-top">
                                        <p class="font-semibold text-hb-navy">{{ $req['code'] }}</p>
                                        <p class="mt-0.5 text-hb-muted">{{ $req['title'] }}</p>
                                    </td>
                                    <td class="py-3 pe-4 align-top">
                                        <p class="text-hb-text">{{ $req['owner'] }}</p>
                                        <p class="mt-0.5 text-hb-muted">{{ $req['team'] }}</p>
                                    </td>
                                    <td class="py-3 pe-4 align-top text-hb-muted">{{ $req['pipeline'] }}</td>
                                    <td class="py-3 align-top">
                                        <x-badge variant="success" :dot="true">{{ $req['status'] }}</x-badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Recent activity --}}
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="activity-heading">
                <h2 id="activity-heading" class="text-lg font-semibold text-hb-navy">Hiring activity</h2>
                <ol class="mt-5 space-y-4">
                    @foreach ($dashboard['activity'] as $item)
                        <li class="flex gap-3 border-s-2 border-hb-border ps-4">
                            <div>
                                <p class="text-xs font-medium text-hb-muted">{{ $item['time'] }}</p>
                                <p class="mt-1 text-sm text-hb-text">{{ $item['body'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>
        </div>

        <aside class="space-y-6">
            {{-- Upcoming interviews --}}
            <section class="hb-surface p-5" aria-labelledby="interviews-heading">
                <div class="flex items-start justify-between gap-2">
                    <h2 id="interviews-heading" class="text-lg font-semibold text-hb-navy">Upcoming interviews</h2>
                    <span
                        class="cursor-not-allowed text-xs font-semibold text-hb-muted"
                        title="Interview calendar arrives in Batch 5D"
                        aria-disabled="true"
                    >
                        Calendar · Soon
                    </span>
                </div>
                <ul class="mt-4 space-y-3">
                    @foreach ($dashboard['interviews'] as $interview)
                        <li class="rounded-lg border {{ ! empty($interview['highlight']) ? 'border-hb-blue bg-hb-info-soft/40' : 'border-hb-border bg-hb-page' }} p-3">
                            <p class="text-xs font-semibold text-hb-muted">
                                {{ $interview['when'] }} · {{ $interview['time'] }}
                            </p>
                            <p class="mt-1 font-semibold text-hb-navy">{{ $interview['candidate'] }}</p>
                            <p class="mt-0.5 text-sm text-hb-muted">{{ $interview['role'] }}</p>
                            <p class="mt-2 text-xs text-hb-text">{{ $interview['interviewer'] }}</p>
                        </li>
                    @endforeach
                </ul>
            </section>

            {{-- Pending actions --}}
            <section class="hb-surface p-5" aria-labelledby="pending-heading">
                <h2 id="pending-heading" class="text-lg font-semibold text-hb-navy">Pending actions</h2>
                <ul class="mt-4 space-y-3">
                    @foreach ($dashboard['pending'] as $item)
                        <li class="rounded-lg border border-hb-border p-3">
                            <p class="text-sm font-semibold text-hb-navy">{{ $item['title'] }}</p>
                            <p class="mt-1 text-sm text-hb-muted">{{ $item['body'] }}</p>
                            <button
                                type="button"
                                class="hb-btn-outline mt-3 w-full text-sm"
                                disabled
                                title="Available in Batch {{ $item['pending_batch'] }}"
                            >
                                {{ $item['action'] }} · Soon
                            </button>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-4">
                    <a href="{{ route('company.profile') }}" class="text-sm font-semibold text-hb-blue hover:underline">
                        Review company profile
                    </a>
                </div>
            </section>
        </aside>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const select = document.querySelector('[data-dashboard-date-range]');
            const note = document.querySelector('[data-dashboard-range-note]');
            if (!select || !note) return;
            select.addEventListener('change', () => {
                note.textContent = `Showing mock metrics for “${select.value}”. Date filtering is presentation-only until Backend analytics exist.`;
                note.classList.remove('hidden');
            });
        })();
    </script>
@endpush
