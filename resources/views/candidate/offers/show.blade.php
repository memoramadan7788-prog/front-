@extends('layouts.app')

@section('title', $pageTitle ?? __('candidate.offers.detail_page_title'))
@section('breadcrumb', __('candidate.workspace') . ' / ' . __('navigation.candidate.offers'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.offers.offer_from', ['company' => $offer['company']]) }}</h1>
    <p class="mt-1 text-sm text-hb-muted">
        {{ __('candidate.offers.offer_meta', ['title' => $offer['title'], 'code' => $offer['code'], 'date' => $offer['received_on']]) }}
    </p>
    <div class="mt-3 flex flex-wrap items-center gap-2">
        <x-badge :variant="\App\Support\Frontend\OfferLifecyclePresentation::badgeVariant($offer['status_key'] ?? 'awaiting')" :dot="true">{{ $offer['status'] }}</x-badge>
        <x-badge variant="neutral" :dot="true">{{ $offer['source'] }}</x-badge>
        @if (! empty($offer['currency']))
            <x-badge variant="neutral" :dot="true">{{ $offer['currency'] }}</x-badge>
        @endif
    </div>
@endsection

@section('content')

    <div class="mb-6 rounded-lg border border-hb-blue/20 bg-hb-info-soft px-4 py-3 text-sm text-hb-blue" role="status">
        {{ $offer['banner'] }}
    </div>

    @if (($offer['status_key'] ?? '') === 'expired')
        <x-alert variant="warning" class="mb-6" data-offer-expired>
            <p class="font-semibold text-hb-navy">{{ __('candidate.offers.expired_title') }}</p>
            <p class="mt-1 text-hb-text">{{ __('candidate.offers.expired_body', ['date' => $offer['deadline'] ?? '']) }}</p>
        </x-alert>
    @endif

    <div
        class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]"
        data-candidate-offer
        data-offer-id="{{ $offer['id'] }}"
        data-can-respond="{{ ! empty($offer['can_accept']) ? '1' : '0' }}"
        data-blocked-reason="{{ $offer['response_blocked_reason'] ?? '' }}"
    >
        <div class="space-y-6">
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="offer-details-heading">
                <p class="text-sm text-hb-muted">{{ $offer['headline'] }}</p>
                <h2 id="offer-details-heading" class="mt-1 text-xl font-semibold text-hb-navy">{{ $offer['title'] }}</h2>
                <p class="mt-2 text-sm text-hb-muted">{{ $offer['meta'] }}</p>
                <p class="mt-2 text-sm text-hb-text">{{ __('candidate.offers.proposed_start', ['date' => $offer['start_date']]) }}</p>

                <dl class="mt-5 space-y-3 rounded-lg border border-hb-border bg-hb-page px-4 py-4 text-sm">
                    @foreach ($offer['compensation'] as $row)
                        <div class="flex flex-col gap-1 sm:flex-row sm:justify-between sm:gap-4">
                            <dt class="font-medium text-hb-muted">{{ $row['label'] }}</dt>
                            <dd class="text-hb-navy sm:text-end">{{ __('candidate.offers.compensation_details', ['value' => $row['value']]) }}</dd>
                        </div>
                    @endforeach
                </dl>
                <p class="mt-3 text-xs text-hb-muted">{{ $offer['compensation_note'] }}</p>
            </section>

            <section class="hb-surface p-5 sm:p-6" aria-labelledby="offer-docs-heading">
                <h2 id="offer-docs-heading" class="text-lg font-semibold text-hb-navy">{{ __('candidate.offers.offer_documents') }}</h2>
                <ul class="mt-4 divide-y divide-hb-border rounded-lg border border-hb-border">
                    @foreach ($offer['documents'] as $doc)
                        <li class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 text-sm">
                            <div>
                                <p class="font-medium text-hb-navy">{{ $doc['name'] }}</p>
                                <p class="text-hb-muted">{{ $doc['status'] }}</p>
                            </div>
                            <button type="button" class="font-semibold text-hb-blue hover:underline" data-offer-doc disabled title="{{ __('candidate.applications.view_documents_soon') }}">
                                {{ $doc['action'] }} ↗
                            </button>
                        </li>
                    @endforeach
                </ul>

                <label class="mt-5 flex items-start gap-3 text-sm text-hb-text">
                    <input type="checkbox" class="hb-checkbox mt-0.5" data-offer-reviewed>
                    <span>{{ __('candidate.offers.reviewed_documents') }}</span>
                </label>
                <p class="hb-field-error hidden" data-offer-reviewed-error role="alert"></p>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    @if (! empty($offer['can_accept']))
                        <button type="button" class="hb-btn-primary" data-open-modal="offer-accept-modal">{{ __('candidate.offers.accept') }}</button>
                        <button type="button" class="text-sm font-semibold text-hb-blue hover:underline" data-open-modal="offer-decline-modal">
                            {{ __('candidate.offers.decline') }}
                        </button>
                    @else
                        <button type="button" class="hb-btn-primary" disabled data-offer-accept-blocked title="{{ $offer['response_blocked_reason'] ?? '' }}">
                            {{ __('candidate.offers.accept_unavailable_short') }}
                        </button>
                        <button type="button" class="text-sm font-semibold text-hb-muted" disabled data-offer-decline-blocked>
                            {{ __('candidate.offers.decline_unavailable_short') }}
                        </button>
                    @endif
                    <a href="{{ route('candidate.messages.index') }}" class="hb-btn-outline text-hb-blue">{{ __('candidate.offers.ask') }}</a>
                </div>
                @if (! empty($offer['response_blocked_reason']))
                    <p class="mt-3 text-sm text-hb-text" data-offer-blocked-reason>{{ $offer['response_blocked_reason'] }}</p>
                @endif
                <p class="mt-3 text-xs text-hb-muted">
                    {{ __('candidate.offers.accept_note') }}
                </p>
                <p class="mt-2 text-xs text-hb-muted">{{ $offer['decision_authority_note'] ?? __('candidate.offers.decision_authority') }}</p>
                <p class="mt-3 hidden text-sm text-hb-success" data-offer-decision-status role="status"></p>
            </section>
        </div>

        <aside class="space-y-4">
            <section class="hb-surface p-5" aria-labelledby="offer-contact-heading">
                <h2 id="offer-contact-heading" class="text-base font-semibold text-hb-navy">{{ __('candidate.offers.offer_contact') }}</h2>
                <p class="mt-3 text-sm font-medium text-hb-navy">{{ $offer['contact']['name'] }}</p>
                <p class="text-sm text-hb-muted">{{ $offer['contact']['role'] }}</p>
                <p class="mt-3 text-sm leading-relaxed text-hb-muted">{{ $offer['contact']['note'] }}</p>
                <a href="{{ route('candidate.messages.index') }}" class="hb-btn-outline mt-4 w-full text-hb-blue">
                    {{ __('candidate.offers.message_contact') }}
                </a>
            </section>

            <section class="hb-surface p-5" aria-labelledby="after-accept-heading">
                <h2 id="after-accept-heading" class="text-base font-semibold text-hb-navy">{{ __('candidate.offers.after_accept') }}</h2>
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
                {{ __('candidate.offers.view_application') }}
            </a>
        </aside>
    </div>

    @include('layouts.modal', [
        'modalId' => 'offer-accept-modal',
        'modalTitle' => __('candidate.offers.accept'),
        'modalSize' => 'md',
        'modalBody' => view('candidate.offers._accept-modal-body')->render(),
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button><button type="button" class="hb-btn-primary" disabled title="'.e(__('candidate.offers.coming_soon')).'">'.e(__('candidate.offers.accept_unavailable')).'</button>',
    ])

    @include('layouts.modal', [
        'modalId' => 'offer-decline-modal',
        'modalTitle' => __('candidate.offers.decline'),
        'modalSize' => 'md',
        'modalBody' => view('candidate.offers._decline-modal-body')->render(),
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>'.e(__('common.actions.close')).'</button><button type="button" class="hb-btn-primary" disabled title="'.e(__('candidate.offers.coming_soon')).'">'.e(__('candidate.offers.decline_unavailable')).'</button>',
    ])

    <p class="mt-8 text-xs text-hb-muted">{{ $offer['footer'] }}</p>
@endsection
