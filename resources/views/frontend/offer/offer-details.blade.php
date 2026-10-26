@extends('frontend.layouts.app')

@section('title', __('Offer'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Offer') }}</h1>
@endsection

@section('page_actions')
    <x-button variant="light" size="md" :href="route('frontend.candidate.offers.index')">{{ __('Back') }}</x-button>
@endsection

@section('content')

<x-card>
    @include('frontend.partials.record', ['record' => $offer ?? []])
</x-card>
@if (in_array(data_get($offer ?? [], 'status'), ['sent', 'pending'], true))
<div class="mt-6 flex flex-wrap gap-3">
    <form method="POST" action="{{ route('frontend.candidate.offers.accept', ['offerId' => data_get($offer, 'id')]) }}" onsubmit="return confirm('{{ __('Accept this offer?') }}')">@csrf<x-button type="submit">{{ __('Accept offer') }}</x-button></form>
    <x-button variant="destructive" :href="route('frontend.candidate.offers.reject', ['offerId' => data_get($offer, 'id')])">{{ __('Decline offer') }}</x-button>
</div>
@endif
@endsection
