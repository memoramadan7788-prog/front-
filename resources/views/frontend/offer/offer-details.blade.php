@extends('frontend.layouts.app')

@section('title', __('Offer details'))

@php
    $offer = is_array($offer ?? null) ? \App\Support\Rows::get($offer, 'data', $offer) : [];
    $offerId = data_get($offer, 'id');
    $offerStatus = strtolower((string) data_get($offer, 'status', 'pending'));
    $canRespond = in_array($offerStatus, ['sent', 'pending'], true);
    $documents = \App\Support\Rows::from(data_get($offer, 'documents', data_get($offer, 'offer_documents', [])));
    $recruiter = data_get($offer, 'recruiter') ?? data_get($offer, 'hiring_contact') ?? data_get($offer, 'company.contact');
    $recruiterEmail = is_array($recruiter) ? data_get($recruiter, 'email') : null;
    $jobTitle = data_get($offer, 'job.title') ?? data_get($offer, 'job_title') ?? __('Offer');
@endphp

@section('page_header')
    <div>
        <p class="text-sm font-semibold text-hb-blue">{{ __('Offer') }}</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-hb-navy">{{ $jobTitle }}</h1>
    </div>
@endsection

@section('page_actions')
    <x-button variant="light" size="md" :href="route('frontend.candidate.offers.index')">{{ __('Back to offers') }}</x-button>
@endsection

@section('content')
<div class="grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(18rem,0.75fr)]">
    <div class="space-y-5">
        @if (session('success_key') === 'flash.offer.accepted')
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900" role="status">{{ __('Offer accepted. Review the next steps below and contact your recruiter with any questions.') }}</div>
        @endif
        <x-card>
            <x-slot:header>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-lg font-bold text-hb-navy">{{ __('Offer summary') }}</h2>
                    <x-badge :variant="\App\Support\Rows::badgeVariant($offerStatus)" :dot="true">{{ \Illuminate\Support\Str::headline($offerStatus) }}</x-badge>
                </div>
            </x-slot:header>
            @include('frontend.partials.record', ['record' => $offer])
        </x-card>

        <x-card>
            <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('Offer documents') }}</h2></x-slot:header>
            @if (count($documents))
                <ul class="space-y-3">
                    @foreach ($documents as $document)
                        @php
                            $documentName = data_get($document, 'name') ?? data_get($document, 'filename') ?? data_get($document, 'title') ?? __('Offer document');
                            $documentUrl = data_get($document, 'url') ?? data_get($document, 'download_url');
                        @endphp
                        <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-hb-border p-4">
                            <span>
                                <span class="block text-sm font-semibold text-hb-navy">{{ $documentName }}</span>
                                <span class="mt-1 block text-xs text-hb-muted">{{ data_get($document, 'type') ?? __('Review the document before responding.') }}</span>
                            </span>
                            @if (is_string($documentUrl) && in_array(parse_url($documentUrl, PHP_URL_SCHEME), ['http', 'https'], true))
                                <a href="{{ $documentUrl }}" target="_blank" rel="noopener noreferrer" class="text-sm font-semibold text-hb-blue hover:underline">{{ __('Open document') }}</a>
                            @else
                                <span class="text-xs text-hb-muted">{{ __('Document preview unavailable') }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="rounded-xl bg-slate-50 p-4 text-sm text-hb-muted">{{ __('No offer documents were included in the offer response.') }}</p>
            @endif
        </x-card>

        @if ($canRespond)
            <section class="rounded-2xl border border-sky-200 bg-sky-50 p-5">
                <h2 class="font-bold text-sky-950">{{ __('Review before accepting') }}</h2>
                <form method="POST" action="{{ route('frontend.candidate.offers.accept', ['offerId' => $offerId]) }}" class="mt-4 space-y-4">
                    @csrf
                    <label class="flex items-start gap-3 text-sm leading-6 text-slate-700">
                        <input type="checkbox" name="offer_reviewed" value="1" required @checked(old('offer_reviewed')) class="mt-1 size-4 rounded border-slate-300 text-hb-blue focus:ring-hb-blue">
                        <span>{{ __('I have reviewed the offer details and attached documents before accepting.') }}</span>
                    </label>
                    @error('offer_reviewed')<p class="text-sm text-red-700" role="alert">{{ $message }}</p>@enderror
                    <div class="flex flex-wrap gap-3">
                        <x-button type="submit">{{ __('Accept offer') }}</x-button>
                        <x-button variant="destructive" :href="route('frontend.candidate.offers.reject', ['offerId' => $offerId])">{{ __('Decline offer') }}</x-button>
                    </div>
                </form>
            </section>
        @endif
    </div>

    <aside class="space-y-5">
        <x-card>
            <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('Ask a question') }}</h2></x-slot:header>
            @if (filter_var($recruiterEmail, FILTER_VALIDATE_EMAIL))
                <p class="text-sm text-hb-muted">{{ __('Questions open a new message in your email app; they are not sent through this portal.') }}</p>
                <button type="button" data-open-offer-question class="mt-4 rounded-lg bg-hb-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-hb-navy">{{ __('Ask a question') }}</button>
                <p class="mt-3 text-xs text-hb-muted" data-question-status role="status" aria-live="polite"></p>
                <dialog data-offer-question-dialog class="w-[min(34rem,calc(100%-2rem))] rounded-2xl border border-hb-border p-0 shadow-2xl backdrop:bg-slate-950/40">
                    <form class="space-y-4 p-5" data-offer-question data-email="{{ $recruiterEmail }}" data-subject="{{ __('Question about :job offer', ['job' => $jobTitle]) }}">
                        <div>
                            <h3 class="text-lg font-bold text-hb-navy">{{ __('Ask about this offer') }}</h3>
                            <p class="mt-1 text-sm text-hb-muted">{{ __('Your email app will open with a draft. The message will not be sent by this portal.') }}</p>
                        </div>
                        <label class="block text-sm font-medium text-hb-text">
                            {{ __('Your question') }}
                            <textarea name="question" rows="4" required maxlength="2000" class="mt-1.5 w-full rounded-lg border border-hb-border px-3 py-2 text-sm focus:border-hb-blue focus:outline-none focus:ring-2 focus:ring-hb-blue/20" placeholder="{{ __('Ask about a term, benefit, or next step…') }}"></textarea>
                        </label>
                        @error('question')<p class="text-sm text-red-700" role="alert">{{ $message }}</p>@enderror
                        <div class="flex justify-end gap-2">
                            <button type="button" data-close-offer-question class="rounded-lg border border-hb-border px-4 py-2 text-sm font-semibold text-hb-navy">{{ __('Cancel') }}</button>
                            <x-button type="submit">{{ __('Open email draft') }}</x-button>
                        </div>
                    </form>
                </dialog>
            @else
                <p class="text-sm text-hb-muted">{{ __('Recruiter contact details are not available in this offer response, so a question cannot be addressed yet.') }}</p>
            @endif
        </x-card>

        <x-card>
            <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('Recruiter contact') }}</h2></x-slot:header>
            @if (is_array($recruiter))
                <p class="font-semibold text-hb-navy">{{ data_get($recruiter, 'name') ?? __('Hiring contact') }}</p>
                @if (filter_var($recruiterEmail, FILTER_VALIDATE_EMAIL))
                    <a class="mt-2 inline-flex text-sm font-semibold text-hb-blue hover:underline" href="mailto:{{ $recruiterEmail }}">{{ $recruiterEmail }}</a>
                @else
                    <p class="mt-2 text-sm text-hb-muted">{{ __('Email address not provided.') }}</p>
                @endif
            @else
                <p class="text-sm text-hb-muted">{{ __('Recruiter contact details were not included in the offer response.') }}</p>
            @endif
        </x-card>

        <section class="rounded-2xl border border-hb-border bg-white p-5 shadow-sm">
            <h2 class="font-bold text-hb-navy">{{ __('After you accept') }}</h2>
            <ol class="mt-4 space-y-4">
                @foreach ([__('Confirm your start date and any remaining paperwork.'), __('Share required onboarding documents through the employer’s secure process.'), __('Agree on your first-day schedule and point of contact.')] as $step)
                    <li class="flex gap-3 text-sm text-hb-text">
                        <span class="grid size-7 shrink-0 place-items-center rounded-full bg-sky-100 text-xs font-bold text-sky-800">{{ $loop->iteration }}</span>
                        <span class="pt-1">{{ $step }}</span>
                    </li>
                @endforeach
            </ol>
        </section>
    </aside>
</div>

<script>
    (() => {
        const form = document.querySelector('[data-offer-question]');
        if (!form) return;
        const dialog = document.querySelector('[data-offer-question-dialog]');
        document.querySelector('[data-open-offer-question]')?.addEventListener('click', () => dialog.showModal());
        document.querySelector('[data-close-offer-question]')?.addEventListener('click', () => dialog.close());
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            if (!form.reportValidity()) return;
            const subject = encodeURIComponent(form.dataset.subject);
            const body = encodeURIComponent(form.elements.question.value.trim());
            window.location.href = `mailto:${form.dataset.email}?subject=${subject}&body=${body}`;
            document.querySelector('[data-question-status]').textContent = @json(__('Your email app should open with a draft. The message has not been sent by this portal.'));
            dialog.close();
        });
    })();
</script>
@endsection
