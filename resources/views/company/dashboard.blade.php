@extends('layouts.app')

@section('title', $pageTitle ?? __('company.dashboard.page_title'))
@section('breadcrumb', __('company.dashboard.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ $dashboard['greeting'] }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ $dashboard['intro'] }}</p>
@endsection

@section('page_actions')
    <div class="flex flex-wrap items-center gap-2" data-company-dashboard>
        <label class="sr-only" for="company-date-range">{{ __('company.dashboard.date_range') }}</label>
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
        <a href="{{ route('company.jobs.create') }}" class="hb-btn-primary">
            {{ __('company.dashboard.create_job_short') }}
        </a>
    </div>
@endsection

@section('content')
    <p id="company-date-range-note" class="sr-only">{{ __('company.dashboard.date_range_note') }}</p>

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
                    <a
                        href="{{ route('company.applicants.index', ['job' => 'ns-204']) }}"
                        class="inline-flex items-center rounded-lg border border-hb-border px-3 py-1.5 text-xs font-semibold text-hb-blue hover:bg-hb-page"
                    >
                        {{ __('company.dashboard.open_board') }}
                    </a>
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
                                aria-label="{{ __('company.dashboard.stage_candidates_aria', ['label' => $stage['label'], 'count' => $stage['count']]) }}"
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
                        <h2 id="requisitions-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.dashboard.active_requisitions') }}</h2>
                        <p class="mt-1 text-sm text-hb-muted">{{ __('company.dashboard.open_roles_subtitle') }}</p>
                    </div>
                    <a
                        href="{{ route('company.jobs.index') }}"
                        class="inline-flex items-center rounded-lg border border-hb-border px-3 py-1.5 text-xs font-semibold text-hb-blue hover:bg-hb-page"
                    >
                        {{ __('common.actions.view_all') }}
                    </a>
                </div>

                <div class="mt-5 overflow-x-auto">
                    <table class="w-full min-w-[36rem] text-start text-sm">
                        <caption class="sr-only">{{ __('company.dashboard.table_caption_requisitions') }}</caption>
                        <thead>
                            <tr class="border-b border-hb-border text-xs font-semibold tracking-wide text-hb-muted uppercase">
                                <th scope="col" class="pb-3 pe-4 font-semibold">{{ __('company.dashboard.col_requisition') }}</th>
                                <th scope="col" class="pb-3 pe-4 font-semibold">{{ __('company.dashboard.col_owner_team') }}</th>
                                <th scope="col" class="pb-3 pe-4 font-semibold">{{ __('company.dashboard.col_pipeline') }}</th>
                                <th scope="col" class="pb-3 font-semibold">{{ __('company.dashboard.col_status') }}</th>
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
                <h2 id="activity-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.dashboard.hiring_activity') }}</h2>
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
                    <h2 id="interviews-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.dashboard.upcoming_interviews') }}</h2>
                    <a
                        href="{{ route('company.interviews.index') }}"
                        class="text-xs font-semibold text-hb-blue hover:underline"
                    >
                        {{ __('company.dashboard.calendar_label') }}
                    </a>
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
                <h2 id="pending-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.dashboard.pending_actions') }}</h2>
                <ul class="mt-4 space-y-3">
                    @foreach ($dashboard['pending'] as $item)
                        <li class="rounded-lg border border-hb-border p-3">
                            <p class="text-sm font-semibold text-hb-navy">{{ $item['title'] }}</p>
                            <p class="mt-1 text-sm text-hb-muted">{{ $item['body'] }}</p>
                            @if (($item['pending_batch'] ?? '') === '5B')
                                <a href="{{ route('company.jobs.create') }}" class="hb-btn-outline mt-3 w-full text-sm">
                                    {{ $item['action'] }}
                                </a>
                            @elseif (($item['pending_batch'] ?? '') === '5E')
                                <a href="{{ route('company.offers.create', ['applicant' => 'ethan-cole']) }}" class="hb-btn-outline mt-3 w-full text-sm">
                                    {{ $item['action'] }}
                                </a>
                            @else
                                <button
                                    type="button"
                                    class="hb-btn-outline mt-3 w-full text-sm"
                                    disabled
                                    title="{{ __('company.dashboard.available_in_batch', ['batch' => $item['pending_batch']]) }}"
                                >
                                    {{ $item['action'] }} · {{ __('common.soon') }}
                                </button>
                            @endif
                        </li>
                    @endforeach
                </ul>
                <div class="mt-4">
                    <a href="{{ route('company.profile') }}" class="text-sm font-semibold text-hb-blue hover:underline">
                        {{ __('company.dashboard.review_company_profile') }}
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
            const rangeTemplate = @json(__('company.dashboard.date_range_selected'));
            if (!select || !note) return;
            select.addEventListener('change', () => {
                note.textContent = rangeTemplate.replace(':range', select.value);
                note.classList.remove('hidden');
            });
        })();
    </script>
@endpush
