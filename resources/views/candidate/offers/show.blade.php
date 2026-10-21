@extends('layouts.app')

@section('title', $pageTitle ?? 'Offer · Hirebridge')
@section('breadcrumb', '24 / My workspace / My applications')

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">Your offer from {{ $offer['company'] }}</h1>
    <p class="mt-1 text-sm text-hb-muted">
        {{ $offer['title'] }} · {{ $offer['code'] }} · Offer received {{ $offer['received_on'] }}
    </p>
    <div class="mt-3 flex flex-wrap items-center gap-2">
        <x-badge variant="pending" :dot="true">{{ $offer['status'] }}</x-badge>
        <x-badge variant="neutral" :dot="true">{{ $offer['source'] }}</x-badge>
    </div>
@endsection

@section('content')
    <div class="hb-mock-banner mb-4 rounded-lg text-start">
        Frontend offer preview — accept and decline actions do not update Hirebridge or notify the employer.
    </div>

    <div class="mb-6 rounded-lg border border-hb-blue/20 bg-hb-info-soft px-4 py-3 text-sm text-hb-blue" role="status">
        {{ $offer['banner'] }}
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]" data-candidate-offer data-offer-id="{{ $offer['id'] }}">
        <div class="space-y-6">
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="offer-details-heading">
                <p class="text-sm text-hb-muted">{{ $offer['headline'] }}</p>
                <h2 id="offer-details-heading" class="mt-1 text-xl font-semibold text-hb-navy">{{ $offer['title'] }}</h2>
                <p class="mt-2 text-sm text-hb-muted">{{ $offer['meta'] }}</p>
                <p class="mt-2 text-sm text-hb-text">Proposed start date: {{ $offer['start_date'] }}</p>

                <dl class="mt-5 space-y-3 rounded-lg border border-hb-border bg-hb-page px-4 py-4 text-sm">
                    @foreach ($offer['compensation'] as $row)
                        <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:gap-4">
                            <dt class="font-medium text-hb-muted">{{ $row['label'] }}</dt>
                            <dd class="text-hb-navy sm:text-end">Annual value / details: {{ $row['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>
                <p class="mt-3 text-xs text-hb-muted">{{ $offer['compensation_note'] }}</p>
            </section>

            <section class="hb-surface p-5 sm:p-6" aria-labelledby="offer-docs-heading">
                <h2 id="offer-docs-heading" class="text-lg font-semibold text-hb-navy">Offer documents</h2>
                <ul class="mt-4 divide-y divide-hb-border rounded-lg border border-hb-border">
                    @foreach ($offer['documents'] as $doc)
                        <li class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 text-sm">
                            <div>
                                <p class="font-medium text-hb-navy">{{ $doc['name'] }}</p>
                                <p class="text-hb-muted">{{ $doc['status'] }}</p>
                            </div>
                            <button type="button" class="font-semibold text-hb-blue hover:underline" data-offer-doc disabled title="Document viewer — Backend Team">
                                {{ $doc['action'] }} ↗
                            </button>
                        </li>
                    @endforeach
                </ul>

                <label class="mt-5 flex items-start gap-3 text-sm text-hb-text">
                    <input type="checkbox" class="hb-checkbox mt-0.5" data-offer-reviewed>
                    <span>I have reviewed the offer documents</span>
                </label>
                <p class="hb-field-error hidden" data-offer-reviewed-error role="alert"></p>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <button type="button" class="hb-btn-primary" data-open-modal="offer-accept-modal">Accept offer</button>
                    <a href="{{ route('candidate.messages.index') }}" class="hb-btn-outline text-hb-blue">Ask a question</a>
                    <button type="button" class="text-sm font-semibold text-hb-blue hover:underline" data-open-modal="offer-decline-modal">
                        Decline offer
                    </button>
                </div>
                <p class="mt-3 text-xs text-hb-muted">
                    Accepting opens a confirmation step before recording your response. No decision has been submitted.
                </p>
                <p class="mt-3 hidden text-sm text-hb-success" data-offer-decision-status role="status"></p>
            </section>
        </div>

        <aside class="space-y-4">
            <section class="hb-surface p-5" aria-labelledby="offer-contact-heading">
                <h2 id="offer-contact-heading" class="text-base font-semibold text-hb-navy">Your offer contact</h2>
                <p class="mt-3 text-sm font-medium text-hb-navy">{{ $offer['contact']['name'] }}</p>
                <p class="text-sm text-hb-muted">{{ $offer['contact']['role'] }}</p>
                <p class="mt-3 text-sm leading-relaxed text-hb-muted">{{ $offer['contact']['note'] }}</p>
                <a href="{{ route('candidate.messages.index') }}" class="hb-btn-outline mt-4 w-full text-hb-blue">
                    Message Jamie
                </a>
            </section>

            <section class="hb-surface p-5" aria-labelledby="after-accept-heading">
                <h2 id="after-accept-heading" class="text-base font-semibold text-hb-navy">After you accept</h2>
                <div class="mt-3">
                    <x-badge variant="neutral" :dot="true">{{ $offer['after_accept']['status'] }}</x-badge>
                </div>
                <ol class="mt-4 space-y-2 text-sm text-hb-text">
                    @foreach ($offer['after_accept']['steps'] as $index => $step)
                        <li><span class="font-semibold text-hb-blue">{{ $index + 1 }}.</span> {{ $step }}</li>
                    @endforeach
                </ol>
                <p class="mt-4 text-xs text-hb-muted">{{ $offer['after_accept']['note'] }}</p>
            </section>

            <a href="{{ route('candidate.applications.show', $offer['application_id']) }}" class="inline-flex text-sm font-semibold text-hb-blue hover:underline">
                ← View application
            </a>
        </aside>
    </div>

    @include('layouts.modal', [
        'modalId' => 'offer-accept-modal',
        'modalTitle' => 'Confirm accept preview',
        'modalSize' => 'md',
        'modalBody' => '<p class="text-sm text-hb-muted">This is a frontend confirmation preview. Confirming does <strong>not</strong> accept the Meridian offer or notify Jamie Rivera.</p><p class="mt-3 text-sm text-hb-text">Offer: Lead Product Designer · MS-108 · $165,000 USD base.</p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>Cancel</button><button type="button" class="hb-btn-primary" data-offer-confirm-accept>Confirm preview acceptance</button>',
    ])

    @include('layouts.modal', [
        'modalId' => 'offer-decline-modal',
        'modalTitle' => 'Decline offer preview',
        'modalSize' => 'md',
        'modalBody' => '<p class="text-sm text-hb-muted">Frontend preview only. Declining here does not withdraw your Meridian application on the server.</p><label class="hb-label mt-4" for="decline-reason">Optional note</label><textarea id="decline-reason" class="hb-input" rows="3" data-offer-decline-reason placeholder="Optional context for your records (local only)"></textarea>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>Cancel</button><button type="button" class="hb-btn-primary" data-offer-confirm-decline>Confirm preview decline</button>',
    ])

    <p class="mt-8 text-xs text-hb-muted">{{ $offer['footer'] }}</p>
@endsection
