@extends('layouts.public')

@section('title', $pageTitle ?? __('public.home.page_title'))

@section('content')
    {{-- Figma 01 · Public · Home (8:31126) --}}

    {{-- Search hero --}}
    <section class="bg-hb-surface">
        <div class="hb-container grid items-center gap-10 py-12 lg:grid-cols-2 lg:gap-16 lg:py-16">
            <div class="space-y-6">
                <div>
                    <p class="text-xs font-bold tracking-[0.08em] text-hb-blue uppercase">{{ __('public.home.hero_eyebrow') }}</p>
                    <h1 class="mt-4 text-4xl font-semibold leading-tight text-hb-navy sm:text-5xl lg:text-[56px] lg:leading-[1.15]">
                        {{ __('public.home.hero_line_1') }}<br>{{ __('public.home.hero_line_2') }}
                    </h1>
                    <p class="mt-4 max-w-xl text-base leading-relaxed text-hb-muted sm:text-lg">
                        {{ __('public.home.hero_body') }}
                    </p>
                </div>

                <form action="{{ route('jobs.index') }}" method="get" class="flex flex-col gap-2 rounded-lg border border-hb-border p-2 sm:flex-row sm:items-end">
                    <label class="min-w-0 flex-1 px-2 py-1">
                        <span class="block text-[11px] font-semibold tracking-wide text-hb-muted uppercase">{{ __('public.home.search_role') }}</span>
                        <input type="search" name="q" value="Product designer" class="mt-1 w-full border-0 bg-transparent p-0 text-sm text-hb-text focus:ring-0" placeholder="Product designer">
                    </label>
                    <label class="min-w-0 flex-1 border-t border-hb-border px-2 py-1 sm:border-t-0 sm:border-s">
                        <span class="block text-[11px] font-semibold tracking-wide text-hb-muted uppercase">{{ __('public.home.search_location') }}</span>
                        <input type="text" name="location" value="Austin, TX" class="mt-1 w-full border-0 bg-transparent p-0 text-sm text-hb-text focus:ring-0" placeholder="Austin, TX">
                    </label>
                    <x-button type="submit" variant="primary" class="shrink-0">{{ __('public.home.find_jobs') }}</x-button>
                </form>

                <p class="text-xs text-hb-muted">{{ __('public.home.popular') }}</p>

                <div class="flex flex-wrap items-center gap-4">
                    <x-button variant="secondary" :href="route('about')">{{ __('auth.register.hiring') }}</x-button>
                    <p class="text-sm text-hb-muted">{{ __('public.home.hiring_hint') }}</p>
                </div>
            </div>

            <figure class="overflow-hidden rounded-lg">
                <img
                    src="{{ asset('images/public/collaborative-workplace.jpg') }}"
                    alt="{{ __('public.home.hero_img_alt') }}"
                    class="aspect-[512/392] w-full object-cover"
                    width="512"
                    height="392"
                >
                <figcaption class="bg-hb-navy px-6 py-6 text-base font-medium text-white">
                    {{ __('public.home.hero_figcaption') }}
                </figcaption>
            </figure>
        </div>
    </section>

    {{-- Employer trust --}}
    <section id="companies" class="border-t border-hb-border bg-hb-page">
        <div class="hb-container py-10">
            <p class="text-center text-xs font-semibold tracking-[0.12em] text-hb-muted uppercase">
                {{ __('public.home.trust_heading') }}
            </p>
            <div class="mt-6 flex flex-wrap items-center justify-center gap-x-10 gap-y-4 text-sm font-semibold tracking-wide text-hb-navy/70 uppercase">
                @foreach ($demoCompanies as $company)
                    <span>{{ $company }}</span>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Featured opportunities --}}
    <section id="jobs" class="border-t border-hb-border bg-hb-surface">
        <div class="hb-container py-16">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-hb-navy sm:text-3xl">{{ __('public.home.hero_line_2_section') }}</h2>
                    <p class="mt-2 text-sm text-hb-muted">{{ __('public.home.featured_intro') }}</p>
                </div>
                <x-button variant="secondary" :href="route('jobs.index')">{{ __('public.home.explore_all_jobs') }}</x-button>
            </div>

            <div class="mt-10 grid gap-4 lg:grid-cols-3">
                @foreach ($featuredJobs as $job)
                    @include('public.jobs._card', ['job' => $job])
                @endforeach
            </div>
            <p class="mt-4 text-xs text-hb-muted"></p>
        </div>
    </section>

    {{-- Hiring models --}}
    <section id="employers" class="border-t border-hb-border bg-hb-page">
        <div class="hb-container py-16">
            <h2 class="text-2xl font-semibold text-hb-navy sm:text-3xl">{{ __('public.home.employers_title') }}</h2>
            <div class="mt-10 grid gap-6 lg:grid-cols-3">
                @foreach (['candidate', 'direct', 'agency'] as $cardKey)
                    @php($card = __('public.home.cards.'.$cardKey))
                    <article class="hb-surface p-6">
                        <p class="text-xs font-semibold text-hb-blue">{{ $card['eyebrow'] }}</p>
                        <h3 class="mt-2 text-lg font-semibold text-hb-navy">{{ $card['title'] }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-hb-muted">
                            {{ $card['body'] }}
                        </p>
                        <ul class="mt-4 space-y-2 text-sm text-hb-text">
                            @foreach ($card['bullets'] as $bullet)
                                <li>✓ {{ $bullet }}</li>
                            @endforeach
                        </ul>
                        @if ($cardKey === 'candidate')
                            <x-button class="mt-6" variant="primary" :href="route('jobs.index')">{{ $card['cta'] }}</x-button>
                        @elseif ($cardKey === 'direct')
                            <x-button class="mt-6" variant="secondary" :href="route('about')">{{ $card['cta'] }}</x-button>
                        @else
                            <x-button class="mt-6" variant="secondary" :href="route('about') . '#engagements'">{{ $card['cta'] }}</x-button>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section class="border-t border-hb-border bg-hb-surface">
        <div class="hb-container py-16">
            <h2 class="text-2xl font-semibold text-hb-navy">{{ __('public.home.how_title') }}</h2>
            <div class="mt-10 grid gap-8 md:grid-cols-3">
                @foreach (__('public.home.how_steps') as $step)
                    <div>
                        <p class="text-sm font-semibold text-hb-blue">{{ $step['num'] }}</p>
                        <h3 class="mt-2 text-lg font-semibold text-hb-navy">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-sm text-hb-muted">{{ $step['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Disciplines --}}
    <section class="border-t border-hb-border bg-hb-page">
        <div class="hb-container py-16">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <h2 class="text-2xl font-semibold text-hb-navy">{{ __('public.home.disciplines_title') }}</h2>
                <a href="{{ route('jobs.index') }}" class="text-sm font-semibold text-hb-blue hover:underline">{{ __('public.home.browse_disciplines') }}</a>
            </div>
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (__('public.home.disciplines') as $discipline)
                    <a href="{{ route('jobs.index', ['discipline' => $discipline['title']]) }}" class="hb-surface block p-5 transition hover:border-hb-blue/40">
                        <h3 class="font-semibold text-hb-navy">{{ $discipline['title'] }}</h3>
                        <p class="mt-2 text-sm text-hb-muted">{{ $discipline['body'] }}</p>
                        <p class="mt-4 text-sm font-semibold text-hb-blue">{{ __('public.home.explore_roles') }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Employer value --}}
    <section class="border-t border-hb-border bg-hb-surface">
        <div class="hb-container grid items-center gap-10 py-16 lg:grid-cols-2">
            @php($pipeline = __('public.home.pipeline'))
            <x-card>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="font-semibold text-hb-navy">{{ $pipeline['demo_title'] }}</h3>
                        <p class="mt-1 text-sm text-hb-muted">{{ $pipeline['demo_meta'] }}</p>
                    </div>
                    <x-badge variant="success" :dot="true">{{ $pipeline['open'] }}</x-badge>
                </div>
                <div class="mt-6 grid grid-cols-5 gap-2 text-center text-xs">
                    @foreach (array_combine($pipeline['stages'], ['3', '3', '3', '2', '1']) as $label => $n)
                        <div class="rounded-lg bg-hb-page px-1 py-3">
                            <p class="font-semibold text-hb-navy">{{ $n }}</p>
                            <p class="mt-1 text-hb-muted">{{ $label }}</p>
                        </div>
                    @endforeach
                </div>
                <p class="mt-6 text-sm text-hb-text">
                    {{ $pipeline['next_label'] }}<br>
                    <span class="text-hb-muted">{{ $pipeline['next_when'] }}</span>
                </p>
            </x-card>
            <div>
                <h2 class="text-2xl font-semibold text-hb-navy">{{ __('public.home.shared_view_title') }}</h2>
                <p class="mt-3 text-sm leading-relaxed text-hb-muted">
                    {{ __('public.home.shared_view_body') }}
                </p>
                <p class="mt-4 text-sm font-medium text-hb-text">{{ __('public.home.shared_view_tags') }}</p>
                <x-button class="mt-6" variant="primary" :href="route('about')">{{ __('public.home.see_hiring') }}</x-button>
            </div>
        </div>
    </section>

    {{-- Testimonial --}}
    <section class="border-t border-hb-border bg-hb-page">
        <div class="hb-container max-w-3xl py-16 text-center">
            <p class="text-xs font-semibold tracking-[0.12em] text-hb-muted uppercase">{{ __('public.home.testimonial_eyebrow') }}</p>
            <blockquote class="mt-6 text-xl font-medium leading-relaxed text-hb-navy sm:text-2xl">
                {{ __('public.home.testimonial_quote') }}
            </blockquote>
            <p class="mt-4 text-sm text-hb-muted">{{ __('public.home.testimonial_attribution') }}</p>
        </div>
    </section>

    {{-- Commitment --}}
    <section class="border-t border-hb-border bg-hb-surface">
        <div class="hb-container py-16">
            <h2 class="text-2xl font-semibold text-hb-navy">{{ __('public.home.commitment_title') }}</h2>
            <div class="mt-10 grid gap-8 md:grid-cols-3">
                @foreach (__('public.home.commitments') as $item)
                    <div>
                        <h3 class="font-semibold text-hb-navy">{{ $item['title'] }}</h3>
                        <p class="mt-2 text-sm text-hb-muted">{{ $item['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="border-t border-hb-border bg-hb-navy">
        <div class="hb-container flex flex-col items-start justify-between gap-6 py-16 lg:flex-row lg:items-center">
            <div>
                <h2 class="text-2xl font-semibold text-white sm:text-3xl">{{ __('public.home.final_title') }}</h2>
                <p class="mt-2 text-sm text-hb-nav-muted">{{ __('public.home.final_body') }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <x-button variant="primary" :href="route('jobs.index')">{{ __('public.home.final_cta_jobs') }}</x-button>
                <a href="{{ route('contact') }}" class="inline-flex items-center justify-center rounded-lg border border-white/30 px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/10">
                    {{ __('public.home.final_cta_team') }}
                </a>
            </div>
        </div>
    </section>
@endsection
