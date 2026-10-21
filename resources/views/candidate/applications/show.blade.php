@extends('layouts.app')

@section('title', $pageTitle ?? 'Application · Hirebridge')
@section('breadcrumb', '19 / My workspace / My applications')

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ $application['title'] }}</h1>
    <p class="mt-1 text-sm text-hb-muted">
        {{ $application['company'] }} · {{ $application['code'] }} · Application {{ $application['reference'] }}
    </p>
    <div class="mt-3 flex flex-wrap items-center gap-2">
        <x-badge
            :variant="$application['archived'] ? 'neutral' : 'info'"
            :dot="true"
        >{{ $application['stage'] }}</x-badge>
        <x-badge variant="neutral" :dot="true">{{ $application['source'] }}</x-badge>
        <span class="text-sm text-hb-muted">Applied {{ $application['applied_on_long'] }}</span>
    </div>
@endsection

@section('page_actions')
    <button type="button" class="hb-btn-primary" disabled title="Messaging — Batch 4D">
        Message hiring team
    </button>
@endsection

@section('content')
    <div class="hb-mock-banner mb-6 rounded-lg text-start">
        Frontend UI only — application detail, timeline, and actions are mock presentation data.
    </div>

    <div class="mb-6">
        <x-timeline :steps="$application['pipeline']" />
    </div>

    <div data-tabs>
        <div class="border-b border-hb-border" role="tablist" aria-label="Application detail sections">
            <div class="flex flex-wrap gap-1">
                <button type="button" id="app-detail-tab-overview" role="tab" aria-selected="true" aria-controls="app-detail-panel-overview" tabindex="0" class="border-b-2 border-hb-blue px-3 py-2.5 text-sm font-semibold text-hb-blue">
                    Overview
                </button>
                <button type="button" id="app-detail-tab-documents" role="tab" aria-selected="false" aria-controls="app-detail-panel-documents" tabindex="-1" class="border-b-2 border-transparent px-3 py-2.5 text-sm font-semibold text-hb-muted">
                    Documents
                </button>
                <button type="button" id="app-detail-tab-activity" role="tab" aria-selected="false" aria-controls="app-detail-panel-activity" tabindex="-1" class="border-b-2 border-transparent px-3 py-2.5 text-sm font-semibold text-hb-muted">
                    Activity
                </button>
            </div>
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
            <div class="min-w-0 space-y-6">
                <div id="app-detail-panel-overview" role="tabpanel" aria-labelledby="app-detail-tab-overview">
                    @if (! empty($application['interview']))
                        <section class="hb-surface p-5 sm:p-6" aria-labelledby="interview-heading">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h2 id="interview-heading" class="text-lg font-semibold text-hb-navy">
                                        {{ $application['interview']['title'] }}
                                    </h2>
                                    <p class="mt-1 text-sm text-hb-muted">
                                        {{ $application['company'] }} · {{ $application['title'] }} · {{ $application['code'] }}
                                    </p>
                                </div>
                                <x-badge variant="info" :dot="true">{{ $application['interview']['status'] }}</x-badge>
                            </div>

                            <div class="mt-5 space-y-2 text-sm text-hb-text">
                                <p class="text-base font-semibold text-hb-navy">{{ $application['interview']['when'] }}</p>
                                <p class="text-hb-muted">{{ $application['interview']['time'] }}</p>
                                <p class="pt-2">{{ $application['interview']['interviewer'] }}</p>
                                <p class="text-hb-muted">{{ $application['interview']['format'] }}</p>
                            </div>

                            <div class="mt-5 flex flex-wrap gap-2">
                                <a href="{{ route('candidate.interviews.show', 'int-ns204-portfolio') }}" class="hb-btn-primary">View preparation</a>
                                <a href="{{ route('candidate.interviews.index') }}" class="hb-btn-outline">Request reschedule</a>
                            </div>
                        </section>
                    @elseif (! empty($application['archived']))
                        <section class="hb-surface p-5 sm:p-6">
                            <h2 class="text-lg font-semibold text-hb-navy">Archived application</h2>
                            <p class="mt-2 text-sm leading-relaxed text-hb-muted">{{ $application['archived_note'] }}</p>
                        </section>
                    @else
                        <section class="hb-surface p-5 sm:p-6">
                            <h2 class="text-lg font-semibold text-hb-navy">Current status</h2>
                            <p class="mt-2 text-sm text-hb-muted">
                                Stage: <span class="font-medium text-hb-navy">{{ $application['stage'] }}</span>
                            </p>
                            <p class="mt-2 text-sm text-hb-text">
                                <span class="font-medium text-hb-navy">Next:</span> {{ $application['next_step'] }}
                            </p>
                            <p class="mt-2 text-sm text-hb-muted">Last activity: {{ $application['last_activity'] }}</p>
                            @if (($application['stage_key'] ?? '') === 'offer')
                                <a href="{{ route('candidate.offers.show', $application['id']) }}" class="hb-btn-primary mt-4 inline-flex">
                                    Review offer
                                </a>
                            @endif
                        </section>
                    @endif

                    <section class="hb-surface mt-6 p-5 sm:p-6" aria-labelledby="activity-heading-overview">
                        <h2 id="activity-heading-overview" class="text-lg font-semibold text-hb-navy">Application activity</h2>
                        <ol class="mt-4 space-y-3 rounded-lg border border-hb-border px-4 py-4">
                            @foreach ($application['activity'] as $item)
                                <li class="text-sm">
                                    <p class="font-medium text-hb-navy">{{ $item['when'] }}</p>
                                    <p class="mt-0.5 text-hb-muted">{{ $item['text'] }}</p>
                                </li>
                            @endforeach
                        </ol>
                    </section>
                </div>

                <div id="app-detail-panel-documents" role="tabpanel" aria-labelledby="app-detail-tab-documents" class="hidden" hidden>
                    <section class="hb-surface p-5 sm:p-6">
                        <h2 class="text-lg font-semibold text-hb-navy">Submitted materials</h2>
                        <ul class="mt-4 divide-y divide-hb-border rounded-lg border border-hb-border">
                            @foreach ($application['materials'] as $material)
                                <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                                    <span class="font-medium text-hb-navy">{{ $material['label'] }}</span>
                                    <span class="text-hb-muted">{{ $material['meta'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <p class="mt-4 text-sm text-hb-muted">{{ $application['screening_summary'] }}</p>
                        <button type="button" class="hb-btn-outline mt-4" disabled title="Document viewer — Backend Team">
                            View documents
                        </button>
                    </section>
                </div>

                <div id="app-detail-panel-activity" role="tabpanel" aria-labelledby="app-detail-tab-activity" class="hidden" hidden>
                    <section class="hb-surface p-5 sm:p-6">
                        <h2 class="text-lg font-semibold text-hb-navy">Full activity</h2>
                        <ol class="mt-4 space-y-4">
                            @foreach ($application['activity'] as $item)
                                <li class="border-s-2 border-hb-border ps-4 text-sm">
                                    <p class="font-medium text-hb-navy">{{ $item['when'] }}</p>
                                    <p class="mt-1 text-hb-muted">{{ $item['text'] }}</p>
                                </li>
                            @endforeach
                        </ol>
                    </section>
                </div>
            </div>

            <aside class="space-y-4">
                <section class="hb-surface p-5" aria-labelledby="contacts-heading">
                    <h2 id="contacts-heading" class="text-base font-semibold text-hb-navy">Your hiring contacts</h2>
                    <ul class="mt-3 space-y-3 text-sm">
                        @foreach ($application['contacts'] as $contact)
                            <li>
                                <p class="font-medium text-hb-navy">{{ $contact['name'] }}</p>
                                <p class="text-hb-muted">{{ $contact['role'] }}</p>
                            </li>
                        @endforeach
                    </ul>
                    <button type="button" class="hb-btn-outline mt-4 w-full" disabled title="Messaging — Batch 4D">
                        Send a message
                    </button>
                </section>

                <section class="hb-surface p-5" aria-labelledby="materials-heading">
                    <h2 id="materials-heading" class="text-base font-semibold text-hb-navy">Submitted materials</h2>
                    <ul class="mt-3 space-y-2 text-sm text-hb-text">
                        @foreach ($application['materials'] as $material)
                            <li>{{ $material['label'] }}@if (! empty($material['meta'])) · {{ $material['meta'] }}@endif</li>
                        @endforeach
                    </ul>
                    <button type="button" class="hb-btn-outline mt-4 w-full" disabled title="Document viewer — Backend Team">
                        View documents
                    </button>
                </section>

                <section class="hb-surface p-5" aria-labelledby="controls-heading">
                    <h2 id="controls-heading" class="text-base font-semibold text-hb-navy">Application controls</h2>
                    <p class="mt-2 text-sm leading-relaxed text-hb-muted">
                        Need to step back? Withdrawing notifies the hiring team and moves this application to Archived. Frontend preview only — no status change is persisted.
                    </p>
                    <button
                        type="button"
                        class="mt-4 text-sm font-semibold text-hb-blue hover:underline disabled:cursor-not-allowed disabled:opacity-50"
                        data-withdraw-preview
                        @disabled(! empty($application['archived']))
                    >
                        {{ ! empty($application['archived']) ? 'Already withdrawn' : 'Withdraw application' }}
                    </button>
                    <p class="mt-2 hidden text-sm text-hb-muted" data-withdraw-status role="status"></p>
                </section>

                <a href="{{ route('candidate.applications.index') }}" class="inline-flex text-sm font-semibold text-hb-blue hover:underline">
                    ← Back to My applications
                </a>
            </aside>
        </div>
    </div>

    <p class="mt-8 text-xs text-hb-muted">Hirebridge · Private workspace · All times Central unless noted · October 20, 2026</p>
@endsection
