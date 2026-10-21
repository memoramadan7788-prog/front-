@extends('layouts.app')

@section('title', $pageTitle ?? 'Offers · Hirebridge')
@section('breadcrumb', '24 / My workspace / Offers')

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">Offers</h1>
    <p class="mt-1 text-sm text-hb-muted">Review decisions that need your attention. Frontend preview only.</p>
@endsection

@section('content')
    <div class="hb-mock-banner mb-6 rounded-lg text-start">
        Frontend UI only — opening an offer does not change application status on the server.
    </div>

    <div class="space-y-4">
        @forelse ($offers as $offer)
            <article class="hb-surface p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-hb-navy">Your offer from {{ $offer['company'] }}</h2>
                        <p class="mt-1 text-sm text-hb-muted">
                            {{ $offer['title'] }} · {{ $offer['code'] }} · Offer received {{ $offer['received_on'] }}
                        </p>
                        <p class="mt-2 text-sm text-hb-text">Review by {{ $offer['deadline'] }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <x-badge variant="pending" :dot="true">{{ $offer['status'] }}</x-badge>
                        <x-badge variant="neutral" :dot="true">{{ $offer['source'] }}</x-badge>
                    </div>
                </div>
                <a href="{{ route('candidate.offers.show', $offer['id']) }}" class="hb-btn-primary mt-5 inline-flex">
                    Review offer
                </a>
            </article>
        @empty
            <div class="hb-surface px-5 py-10 text-center" role="status">
                <p class="font-semibold text-hb-navy">No offers yet</p>
                <p class="mt-2 text-sm text-hb-muted">When an employer extends an offer, it will appear here.</p>
            </div>
        @endforelse
    </div>
@endsection
