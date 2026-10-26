@extends('frontend.layouts.public')

@section('title', __('common.unavailable.title').' · TalentBridge')

@section('content')
    <section class="bg-hb-page">
        <div class="hb-container py-24 text-center">
            <p class="text-xs font-semibold tracking-[0.14em] text-hb-blue uppercase">503</p>
            <h1 class="mt-3 text-3xl font-semibold text-hb-navy">{{ __('common.unavailable.title') }}</h1>
            <p class="mx-auto mt-3 max-w-md text-sm text-hb-muted">{{ __('common.unavailable.body') }}</p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <x-button variant="primary" :href="url()->current()">{{ __('common.unavailable.retry') }}</x-button>
            </div>
        </div>
    </section>
@endsection
