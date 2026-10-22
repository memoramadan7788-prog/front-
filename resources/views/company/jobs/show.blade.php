@extends('layouts.app')

@section('title', $pageTitle ?? __('company.jobs.show_page_title'))
@section('breadcrumb', __('company.jobs.show_breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ $job['title'] }}</h1>
    <p class="mt-1 text-sm text-hb-muted">
        {{ $job['code'] }} · {{ $job['department'] }} · {{ $job['location_short'] ?? $job['location'] }} · {{ $job['salary_label'] }}
    </p>
    <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm">
        @if (($job['status_key'] ?? '') === 'open')
            <x-badge variant="success" :dot="true">{{ $job['status'] }}</x-badge>
        @elseif (($job['status_key'] ?? '') === 'draft')
            <x-badge variant="pending" :dot="true">{{ $job['status'] }}</x-badge>
        @else
            <x-badge variant="neutral" :dot="true">{{ $job['status'] }}</x-badge>
        @endif
        <x-badge variant="neutral" :dot="true">{{ $job['model'] }}</x-badge>
        <span class="text-hb-muted">
            {{ $job['published_label'] ?? ($job['posted_at'] ? __('company.jobs.published_on', ['date' => $job['posted_at']]) : __('company.jobs.not_published')) }}
            · {{ __('company.jobs.owner_label', ['owner' => $job['owner']]) }}
        </span>
    </div>
@endsection

@section('page_actions')
    <div class="flex flex-wrap gap-2">
        @if (($job['status_key'] ?? '') === 'open')
            <a href="{{ route('jobs.show', $job['id']) }}" class="hb-btn-primary" target="_blank" rel="noopener noreferrer">
                {{ __('company.jobs.view_public_job') }}
            </a>
            <a href="{{ route('company.jobs.create', ['job' => $job['id']]) }}" class="hb-btn-outline">{{ __('company.jobs.edit_job') }}</a>
        @elseif (($job['status_key'] ?? '') === 'draft')
            <a href="{{ route('company.jobs.create', ['job' => $job['id']]) }}" class="hb-btn-primary">{{ __('company.jobs.continue_editing') }}</a>
            <a href="{{ route('company.jobs.index') }}" class="hb-btn-outline">{{ __('company.jobs.all_jobs') }}</a>
        @else
            <a href="{{ route('company.jobs.index') }}" class="hb-btn-outline">{{ __('company.jobs.all_jobs') }}</a>
            <a href="{{ route('company.jobs.create', ['job' => $job['id']]) }}" class="hb-btn-outline">{{ __('company.jobs.edit_job') }}</a>
        @endif
    </div>
@endsection

@section('content')
    <div data-company-job-detail>
        <div class="mb-6 flex flex-wrap gap-4 border-b border-hb-border text-sm font-semibold" role="tablist" aria-label="{{ __('company.jobs.tablist_detail_aria') }}">
            @foreach ([
                ['overview', __('company.jobs.tab_overview')],
                ['pipeline', __('company.jobs.tab_pipeline', ['count' => $job['pipeline_total'] ?? 0])],
                ['team', __('company.jobs.hiring_team')],
                ['publication', __('company.jobs.publication')],
            ] as [$key, $label])
                <button
                    type="button"
                    role="tab"
                    id="job-detail-tab-{{ $key }}"
                    data-job-tab="{{ $key }}"
                    aria-controls="job-detail-panel-{{ $key }}"
                    aria-selected="{{ $key === 'overview' ? 'true' : 'false' }}"
                    class="border-b-2 px-1 pb-3 {{ $key === 'overview' ? 'border-hb-blue text-hb-blue' : 'border-transparent text-hb-muted hover:text-hb-navy' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Overview --}}
        <div id="job-detail-panel-overview" role="tabpanel" aria-labelledby="job-detail-tab-overview" data-job-panel="overview">
            @if (! empty($job['stat_cards']))
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($job['stat_cards'] as $card)
                        @php
                            $stageHref = ! empty($card['stage'])
                                ? route('company.applicants.index', ['job' => $job['id'], 'stage' => $card['stage']])
                                : route('company.applicants.index', ['job' => $job['id']]);
                        @endphp
                        <a href="{{ $stageHref }}" class="hb-surface block p-5 transition hover:border-hb-blue/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40">
                            <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ $card['label'] }}</p>
                            <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ $card['value'] }}</p>
                            <p class="mt-2 text-sm text-hb-muted">{{ $card['meta'] }}</p>
                        </a>
                    @endforeach
                    @if (! empty($job['offer_hired']))
                        <div class="hb-surface p-5">
                            <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ $job['offer_hired']['label'] }}</p>
                            <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ $job['offer_hired']['value'] }}</p>
                            <p class="mt-2 text-sm text-hb-muted">{{ $job['offer_hired']['meta'] }}</p>
                        </div>
                    @endif
                </div>
            @endif

            <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
                <div class="space-y-6 min-w-0">
                    <section class="hb-surface p-5 sm:p-6" aria-labelledby="forward-heading">
                        <h2 id="forward-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.jobs.move_forward') }}</h2>
                        <div class="mt-5 overflow-x-auto">
                            <table class="w-full min-w-[32rem] text-start text-sm">
                                <caption class="sr-only">{{ __('company.jobs.forward_table_caption') }}</caption>
                                <thead>
                                    <tr class="border-b border-hb-border text-xs font-semibold tracking-wide text-hb-muted uppercase">
                                        <th scope="col" class="pb-3 pe-4 font-semibold">{{ __('company.jobs.col_candidate') }}</th>
                                        <th scope="col" class="pb-3 pe-4 font-semibold">{{ __('company.jobs.col_current_stage') }}</th>
                                        <th scope="col" class="pb-3 font-semibold">{{ __('company.jobs.col_next_action') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-hb-border">
                                    @foreach ($job['forward_rows'] ?? [] as $row)
                                        <tr>
                                            <td class="py-3 pe-4 font-semibold text-hb-navy">
                                                @if (! empty($row['applicant_id']))
                                                    <a href="{{ route('company.applicants.show', $row['applicant_id']) }}" class="text-hb-blue hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40">
                                                        {{ $row['candidate'] }}
                                                    </a>
                                                @else
                                                    {{ $row['candidate'] }}
                                                @endif
                                            </td>
                                            <td class="py-3 pe-4 text-hb-muted">
                                                @if (! empty($row['stage']))
                                                    <a href="{{ route('company.applicants.index', ['job' => $job['id'], 'stage' => $row['stage']]) }}" class="hover:text-hb-blue hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40">
                                                        {{ $row['stage'] }}
                                                    </a>
                                                @else
                                                    {{ $row['stage'] }}
                                                @endif
                                            </td>
                                            <td class="py-3 text-hb-text">{{ $row['next'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <a
                            href="{{ route('company.applicants.index', ['job' => $job['id']]) }}"
                            class="hb-btn-primary mt-5 inline-flex"
                        >
                            {{ __('company.jobs.open_pipeline') }}
                        </a>
                    </section>

                    <section class="hb-surface p-5 sm:p-6" aria-labelledby="sourcing-heading">
                        <h2 id="sourcing-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.jobs.sourcing') }}</h2>
                        <ul class="mt-5 space-y-4">
                            @foreach ($job['sourcing'] ?? [] as $source)
                                <li class="rounded-lg border border-hb-border bg-hb-page px-4 py-3 text-sm">
                                    <p class="font-semibold text-hb-navy">{{ $source['label'] }}</p>
                                    <p class="mt-1 text-hb-muted">
                                        {{ __('company.jobs.recruiter_ownership', ['recruiter' => $source['recruiter'], 'ownership' => $source['ownership']]) }}
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                        @if (! empty($job['publication_notes']))
                            <ul class="mt-4 flex flex-wrap gap-x-4 gap-y-1 text-xs text-hb-muted">
                                @foreach ($job['publication_notes'] as $note)
                                    <li>{{ $note }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </section>
                </div>

                <aside class="space-y-4">
                    <section class="hb-surface p-5" aria-labelledby="hiring-team-heading">
                        <h2 id="hiring-team-heading" class="text-base font-semibold text-hb-navy">{{ __('company.jobs.hiring_team') }}</h2>
                        <ul class="mt-4 space-y-3">
                            @foreach ($job['hiring_team'] ?? [] as $member)
                                <li>
                                    <p class="text-sm font-semibold text-hb-navy">{{ $member['name'] }}</p>
                                    <p class="mt-0.5 text-xs text-hb-muted">{{ $member['role'] }}</p>
                                </li>
                            @endforeach
                        </ul>
                    </section>

                    <section class="hb-surface p-5" aria-labelledby="stage-expectations-heading">
                        <h2 id="stage-expectations-heading" class="text-base font-semibold text-hb-navy">{{ __('company.jobs.stage_expectations') }}</h2>
                        <p class="mt-3 text-sm font-medium text-hb-navy">
                            {{ __('company.jobs.stage_flow') }}
                        </p>
                        <p class="mt-3 text-xs leading-relaxed text-hb-muted">
                            {{ $job['stage_note'] ?? __('company.jobs.stage_note_default') }}
                        </p>
                    </section>
                </aside>
            </div>
        </div>

        {{-- Pipeline tab --}}
        <div id="job-detail-panel-pipeline" role="tabpanel" aria-labelledby="job-detail-tab-pipeline" data-job-panel="pipeline" class="hidden" hidden>
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="pipeline-panel-heading">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 id="pipeline-panel-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.jobs.pipeline_tab') }}</h2>
                        <p class="mt-1 text-sm text-hb-muted">{{ $job['pipeline'] }}</p>
                    </div>
                    <a
                        href="{{ route('company.applicants.index', ['job' => $job['id']]) }}"
                        class="hb-btn-primary inline-flex"
                    >
                        {{ __('company.jobs.open_pipeline') }}
                    </a>
                </div>
                @if (! empty($job['stages']))
                    <ul class="mt-6 grid gap-3 sm:grid-cols-5">
                        @foreach ($job['stages'] as $stage)
                            <li>
                                <a
                                    href="{{ route('company.applicants.index', ['job' => $job['id'], 'stage' => $stage['key'] ?? $stage['label']]) }}"
                                    class="block rounded-lg border border-hb-border bg-hb-page px-3 py-4 text-center transition hover:border-hb-blue/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40"
                                >
                                    <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ $stage['label'] }}</p>
                                    <p class="mt-1 text-2xl font-semibold text-hb-navy">{{ $stage['count'] }}</p>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        {{-- Hiring team tab --}}
        <div id="job-detail-panel-team" role="tabpanel" aria-labelledby="job-detail-tab-team" data-job-panel="team" class="hidden" hidden>
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="team-panel-heading">
                <h2 id="team-panel-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.jobs.hiring_team') }}</h2>
                <ul class="mt-5 divide-y divide-hb-border">
                    @foreach ($job['hiring_team'] ?? [] as $member)
                        <li class="flex flex-wrap items-baseline justify-between gap-2 py-4 first:pt-0 last:pb-0">
                            <p class="font-semibold text-hb-navy">{{ $member['name'] }}</p>
                            <p class="text-sm text-hb-muted">{{ $member['role'] }}</p>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>

        {{-- Publication tab --}}
        <div id="job-detail-panel-publication" role="tabpanel" aria-labelledby="job-detail-tab-publication" data-job-panel="publication" class="hidden" hidden>
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="publication-panel-heading">
                <h2 id="publication-panel-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.jobs.sourcing') }}</h2>
                <ul class="mt-5 space-y-4">
                    @foreach ($job['sourcing'] ?? [] as $source)
                        <li class="rounded-lg border border-hb-border bg-hb-page px-4 py-3 text-sm">
                            <p class="font-semibold text-hb-navy">{{ $source['label'] }}</p>
                            <p class="mt-1 text-hb-muted">
                                {{ __('company.jobs.recruiter_ownership', ['recruiter' => $source['recruiter'], 'ownership' => $source['ownership']]) }}
                            </p>
                        </li>
                    @endforeach
                </ul>
                @if (! empty($job['publication_notes']))
                    <ul class="mt-5 space-y-2 text-sm text-hb-muted">
                        @foreach ($job['publication_notes'] as $note)
                            <li class="flex gap-2">
                                <span class="text-hb-success" aria-hidden="true">✓</span>
                                {{ $note }}
                            </li>
                        @endforeach
                    </ul>
                @endif
                <div class="mt-6 flex flex-wrap gap-2">
                    <a href="{{ route('jobs.show', $job['id']) }}" class="hb-btn-primary" target="_blank" rel="noopener noreferrer">
                        {{ __('company.jobs.view_public_job') }}
                    </a>
                    <button type="button" class="hb-btn-outline" disabled title="{{ __('company.jobs.publication_controls_soon') }}">
                        {{ __('company.jobs.manage_publication') }}
                    </button>
                </div>
            </section>
        </div>

        <p class="mt-8 text-xs text-hb-muted">{{ $job['footer'] ?? 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026' }}</p>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const root = document.querySelector('[data-company-job-detail]');
            if (!root) return;
            const tabs = [...root.querySelectorAll('[data-job-tab]')];
            const panels = [...root.querySelectorAll('[data-job-panel]')];

            function show(key) {
                tabs.forEach((tab) => {
                    const active = tab.getAttribute('data-job-tab') === key;
                    tab.setAttribute('aria-selected', active ? 'true' : 'false');
                    tab.classList.toggle('border-hb-blue', active);
                    tab.classList.toggle('text-hb-blue', active);
                    tab.classList.toggle('border-transparent', !active);
                    tab.classList.toggle('text-hb-muted', !active);
                });
                panels.forEach((panel) => {
                    const active = panel.getAttribute('data-job-panel') === key;
                    panel.classList.toggle('hidden', !active);
                    panel.hidden = !active;
                });
            }

            tabs.forEach((tab) => tab.addEventListener('click', () => show(tab.getAttribute('data-job-tab'))));
        })();
    </script>
@endpush
