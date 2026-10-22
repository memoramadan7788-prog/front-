@extends('layouts.app')

@section('title', $pageTitle ?? __('candidate.offers.page_title'))
@section('breadcrumb', __('candidate.workspace') . ' / ' . __('navigation.candidate.offers'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.offers.index_title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.offers.index_intro') }}</p>
@endsection

@section('content')

    <div class="space-y-4">
        @forelse ($offers as $offer)
            <article class="hb-surface p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-hb-navy">{{ __('candidate.offers.offer_from', ['company' => $offer['company']]) }}</h2>
                        <p class="mt-1 text-sm text-hb-muted">
                            {{ __('candidate.offers.offer_meta', ['title' => $offer['title'], 'code' => $offer['code'], 'date' => $offer['received_on']]) }}
                        </p>
                        <p class="mt-2 text-sm text-hb-text">{{ __('candidate.offers.review_by', ['date' => $offer['deadline']]) }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <x-badge variant="pending" :dot="true">{{ $offer['status'] }}</x-badge>
                        <x-badge variant="neutral" :dot="true">{{ $offer['source'] }}</x-badge>
                    </div>
                </div>
                <a href="{{ route('candidate.offers.show', $offer['id']) }}" class="hb-btn-primary mt-5 inline-flex">
                    {{ __('candidate.offers.review') }}
                </a>
            </article>
        @empty
            <div class="hb-surface" role="status">
                <x-empty-state
                    :title="__('candidate.offers.empty_title')"
                    :action-label="__('candidate.offers.empty_browse_jobs')"
                    :action-href="route('candidate.jobs.index')"
                >
                    {{ __('candidate.offers.empty_body') }}
                </x-empty-state>
            </div>
        @endforelse
    </div>
@endsection
