@extends('frontend.layouts.app')

@section('title', __('Decline offer'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Decline offer') }}</h1>
@endsection

@section('content')
<x-card class="max-w-2xl">
    <x-slot:header>
        <h2 class="text-lg font-bold text-hb-navy">{{ __('Decline offer') }}</h2>
    </x-slot:header>

    
    <form method="POST" action="{{ route('frontend.candidate.offers.reject.submit', ['offerId' => $offerId]) }}" class="space-y-5">
        @csrf
        <x-form.textarea name="reason" :label="__('Reason (optional)')" rows="4" :error="$errors->first('reason')">{{ old('reason', data_get([], 'reason', '')) }}</x-form.textarea>
        <div class="flex flex-wrap items-center gap-3 pt-1">
            <x-button type="submit">{{ __('Decline offer') }}</x-button>
            <x-button variant="light" :href="route('frontend.candidate.offers.index')">{{ __('Cancel') }}</x-button>
        </div>
    </form>
    
</x-card>
@endsection
