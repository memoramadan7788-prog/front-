@extends('frontend.layouts.public')

@section('title', __('Find your next opportunity') . ' | TalentBridge')

@section('content')
    <section class="relative overflow-hidden bg-hb-page">
        <div class="pointer-events-none absolute -top-40 start-1/2 h-[30rem] w-[60rem] -translate-x-1/2 rounded-full bg-hb-blue/15 blur-3xl" aria-hidden="true"></div>
        <div class="hb-container relative py-24 text-center lg:py-32">
            <p class="mx-auto inline-flex items-center gap-2 rounded-full border border-hb-border bg-hb-surface px-3.5 py-1.5 text-xs font-semibold tracking-[0.12em] text-hb-blue uppercase shadow-hb-sm">
                <span class="h-1.5 w-1.5 rounded-full bg-hb-blue"></span> TalentBridge
            </p>
            <h1 class="mx-auto mt-6 max-w-3xl text-4xl font-semibold tracking-tight text-hb-navy sm:text-6xl">{{ __('Find your next opportunity') }}</h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-hb-muted">{{ __('Discover jobs that match your skills and take the next step in your career.') }}</p>
            <div class="mt-10 flex flex-wrap justify-center gap-3">
                <x-button size="lg" :href="route('frontend.jobs.index')">{{ __('Explore jobs') }}</x-button>
                @guest
                    <x-button size="lg" variant="secondary" :href="route('frontend.account.register', ['type' => 'candidate'])">{{ __('Create account') }}</x-button>
                @endguest
            </div>
        </div>
    </section>
@endsection
