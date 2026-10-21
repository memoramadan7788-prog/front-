@extends('layouts.public')

@section('title', $pageTitle ?? __('public.about.page_title'))

@section('content')
    {{-- Figma 04 · Specialist recruitment (8:31475) --}}
    <section class="bg-hb-page">
        <div class="hb-container py-10 lg:py-14">
            <p class="text-xs font-semibold tracking-[0.12em] text-hb-muted uppercase">
                {{ __('public.about.eyebrow') }}
            </p>
            <div class="mt-4 max-w-3xl">
                <h1 class="text-3xl font-semibold text-hb-navy sm:text-4xl">{{ __('public.about.title') }}</h1>
                <p class="mt-3 text-lg text-hb-muted">{{ __('public.about.intro') }}</p>
                <p class="mt-4 text-sm leading-relaxed text-hb-muted">
                    {{ __('public.about.body') }}
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <x-button variant="primary" :href="route('contact')">{{ __('public.about.cta_contact') }}</x-button>
                    <x-button variant="secondary" :href="route('about') . '#engagements'">{{ __('public.about.cta_direct') }}</x-button>
                </div>
            </div>

            <div class="mt-12 hb-surface p-6 lg:p-8">
                <h2 class="text-xl font-semibold text-hb-navy">{{ __('public.about.control_title') }}</h2>
                <p class="mt-3 max-w-3xl text-sm text-hb-muted">
                    {{ __('public.about.control_body') }}
                </p>
                <p class="mt-4 text-sm font-medium text-hb-blue">{{ __('public.about.control_tag') }}</p>
            </div>

            <div id="engagements" class="mt-12">
                <h2 class="text-2xl font-semibold text-hb-navy">{{ __('public.about.engagements_title') }}</h2>
                <div class="mt-6 grid gap-4 lg:grid-cols-3">
                    @foreach (__('public.about.engagement_cards') as $card)
                        <article class="hb-surface p-6">
                            <h3 class="text-lg font-semibold text-hb-navy">{{ $card['title'] }}</h3>
                            <p class="mt-1 text-sm font-medium text-hb-muted">{{ $card['eyebrow'] }}</p>
                            <p class="mt-3 text-sm text-hb-muted">{{ $card['body'] }}</p>
                            <a href="{{ route('contact') }}" class="mt-4 inline-block text-sm font-semibold text-hb-blue hover:underline">{{ __('public.about.discuss_model') }}</a>
                        </article>
                    @endforeach
                </div>
            </div>

            <div class="mt-14">
                <h2 class="text-2xl font-semibold text-hb-navy">{{ __('public.about.process_title') }}</h2>
                <ol class="mt-6 space-y-4">
                    @foreach (__('public.about.process_steps') as $step)
                        <li class="hb-surface p-5">
                            <p class="font-semibold text-hb-navy">{{ $step['title'] }}</p>
                            <p class="mt-2 text-sm text-hb-muted">{{ $step['we'] }}</p>
                            <p class="mt-1 text-sm text-hb-muted">{{ $step['you'] }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div class="mt-14 grid gap-8 lg:grid-cols-2">
                <div>
                    <h2 class="text-xl font-semibold text-hb-navy">{{ __('public.about.specialize_title') }}</h2>
                    <ul class="mt-4 space-y-2 text-sm text-hb-text">
                        @foreach (__('public.about.specialize_list') as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-hb-navy">{{ __('public.about.submission_title') }}</h2>
                    <p class="mt-3 text-sm text-hb-muted">
                        {{ __('public.about.submission_body') }}
                    </p>
                    <h3 class="mt-6 font-semibold text-hb-navy">{{ __('public.about.visibility_title') }}</h3>
                    <p class="mt-2 text-sm text-hb-muted">
                        {{ __('public.about.visibility_body') }}
                    </p>
                </div>
            </div>

            <div class="mt-12 hb-surface p-6">
                <h2 class="text-lg font-semibold text-hb-navy">{{ __('public.about.fees_title') }}</h2>
                <p class="mt-2 text-sm text-hb-muted">
                    {{ __('public.about.fees_body') }}
                </p>
            </div>

            <div class="mt-12 rounded-hb bg-hb-navy p-8 text-white lg:flex lg:items-center lg:justify-between lg:gap-8">
                <div>
                    <h2 class="text-2xl font-semibold">{{ __('public.about.cta_title') }}</h2>
                    <p class="mt-2 text-sm text-hb-nav-muted">{{ __('public.about.cta_body') }}</p>
                </div>
                <x-button class="mt-6 lg:mt-0" variant="primary" :href="route('contact')">{{ __('public.about.cta_button') }}</x-button>
            </div>
        </div>
    </section>
@endsection
