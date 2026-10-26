@extends('frontend.layouts.public')

@section('title', 'Page not found · TalentBridge')

@section('content')
    <section class="bg-hb-page">
        <div class="hb-container py-24 text-center">
            <p class="text-xs font-semibold tracking-[0.14em] text-hb-blue uppercase">404</p>
            <h1 class="mt-3 text-3xl font-semibold text-hb-navy">We couldn’t find that page</h1>
            <p class="mx-auto mt-3 max-w-md text-sm text-hb-muted">
                The demo link may be incorrect, or the screen isn’t available yet.
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <x-button variant="primary" :href="route('frontend.home')">Back to home</x-button>
                <x-button variant="secondary" :href="route('frontend.jobs.index')">Browse jobs</x-button>
            </div>
        </div>
    </section>
@endsection
