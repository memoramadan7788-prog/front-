@extends('frontend.layouts.public')

@section('title', $pageTitle ?? __('public.about.page_title'))

@section('content')
    {{-- Figma 04 · Specialist recruitment (8:31475) --}}
    <section class="relative overflow-hidden bg-hb-page">
        <div class="pointer-events-none absolute -top-32 start-1/2 h-[28rem] w-[56rem] -translate-x-1/2 rounded-full bg-hb-blue/10 blur-3xl" aria-hidden="true"></div>
        <div class="hb-container relative py-14 lg:py-24">
            <div class="max-w-3xl">
                <p class="inline-flex items-center gap-2 rounded-full border border-hb-border bg-hb-surface px-3.5 py-1.5 text-xs font-semibold tracking-[0.12em] text-hb-blue uppercase shadow-hb-sm">
                    <span class="h-1.5 w-1.5 rounded-full bg-hb-blue"></span>
                    {{ __('public.about.eyebrow') }}
                </p>
                <h1 class="mt-6 text-4xl font-semibold tracking-tight text-hb-navy sm:text-5xl lg:text-6xl">{{ __('public.about.title') }}</h1>
                <p class="mt-5 text-xl text-hb-muted">{{ __('public.about.intro') }}</p>
                <p class="mt-4 max-w-2xl text-base leading-relaxed text-hb-muted">
                    {{ __('public.about.body') }}
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <x-button size="lg" variant="primary" :href="route('frontend.contact')">{{ __('public.about.cta_contact') }}</x-button>
                    <x-button size="lg" variant="secondary" :href="route('frontend.about') . '#engagements'">{{ __('public.about.cta_direct') }}</x-button>
                </div>
            </div>

            <div class="hb-surface mt-14 flex flex-col gap-4 p-7 shadow-hb-md lg:mt-20 lg:flex-row lg:items-center lg:justify-between lg:p-9">
                <div class="max-w-2xl">
                    <h2 class="text-2xl font-semibold text-hb-navy">{{ __('public.about.control_title') }}</h2>
                    <p class="mt-3 text-base leading-relaxed text-hb-muted">
                        {{ __('public.about.control_body') }}
                    </p>
                </div>
                <p class="inline-flex shrink-0 items-center self-start rounded-full bg-hb-info-soft px-4 py-2 text-sm font-semibold text-hb-blue lg:self-auto">{{ __('public.about.control_tag') }}</p>
            </div>

            <div id="engagements" class="mt-20 scroll-mt-28">
                <h2 class="text-3xl font-semibold tracking-tight text-hb-navy">{{ __('public.about.engagements_title') }}</h2>
                <div class="mt-8 grid gap-5 lg:grid-cols-3">
                    @foreach (__('public.about.engagement_cards') as $card)
                        <article class="hb-surface group flex flex-col p-7 transition duration-200 hover:-translate-y-1 hover:border-hb-blue/40 hover:shadow-hb-md">
                            <span class="grid h-11 w-11 place-items-center rounded-xl bg-hb-info-soft text-sm font-bold text-hb-blue">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3 class="mt-5 text-xl font-semibold text-hb-navy">{{ $card['title'] }}</h3>
                            <p class="mt-1 text-xs font-semibold tracking-wide text-hb-blue uppercase">{{ $card['eyebrow'] }}</p>
                            <p class="mt-3 flex-1 text-sm leading-relaxed text-hb-muted">{{ $card['body'] }}</p>
                            <a href="{{ route('frontend.contact') }}" class="mt-6 inline-flex items-center gap-1.5 text-sm font-semibold text-hb-blue">
                                {{ __('public.about.discuss_model') }}
                                <span aria-hidden="true" class="transition group-hover:translate-x-1 rtl:rotate-180 rtl:group-hover:-translate-x-1">&rarr;</span>
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>

            <div class="mt-20">
                <h2 class="text-3xl font-semibold tracking-tight text-hb-navy">{{ __('public.about.process_title') }}</h2>
                <ol class="mt-8 grid gap-4 md:grid-cols-2">
                    @foreach (__('public.about.process_steps') as $step)
                        <li class="hb-surface relative flex gap-4 p-6">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-hb-accent text-sm font-bold text-white shadow-hb-sm">{{ $loop->iteration }}</span>
                            <div>
                                <p class="font-semibold text-hb-navy">{{ $step['title'] }}</p>
                                <p class="mt-2 text-sm leading-relaxed text-hb-muted">{{ $step['we'] }}</p>
                                <p class="mt-1 text-sm leading-relaxed text-hb-muted">{{ $step['you'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div class="mt-20 grid gap-6 lg:grid-cols-2">
                <div class="hb-surface p-7">
                    <h2 class="text-xl font-semibold text-hb-navy">{{ __('public.about.specialize_title') }}</h2>
                    <ul class="mt-5 grid gap-3 text-sm text-hb-text sm:grid-cols-2">
                        @foreach (__('public.about.specialize_list') as $item)
                            <li class="flex items-start gap-2.5">
                                <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-hb-success-soft text-hb-success">
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                                </span>
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="hb-surface p-7">
                    <h2 class="text-xl font-semibold text-hb-navy">{{ __('public.about.submission_title') }}</h2>
                    <p class="mt-3 text-sm leading-relaxed text-hb-muted">
                        {{ __('public.about.submission_body') }}
                    </p>
                    <h3 class="mt-6 font-semibold text-hb-navy">{{ __('public.about.visibility_title') }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-hb-muted">
                        {{ __('public.about.visibility_body') }}
                    </p>
                </div>
            </div>

            <div class="mt-6 flex gap-4 rounded-hb border border-hb-border bg-hb-info-soft/60 p-6">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-hb-surface text-hb-blue shadow-hb-sm" aria-hidden="true">%</span>
                <div>
                    <h2 class="text-lg font-semibold text-hb-navy">{{ __('public.about.fees_title') }}</h2>
                    <p class="mt-2 text-sm leading-relaxed text-hb-muted">
                        {{ __('public.about.fees_body') }}
                    </p>
                </div>
            </div>

            <div class="relative mt-20 overflow-hidden rounded-3xl bg-hb-sidebar p-8 text-white shadow-hb-lg lg:flex lg:items-center lg:justify-between lg:gap-8 lg:p-12">
                <div class="pointer-events-none absolute -end-20 -top-24 h-72 w-72 rounded-full bg-hb-blue/30 blur-3xl" aria-hidden="true"></div>
                <div class="relative">
                    <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl">{{ __('public.about.cta_title') }}</h2>
                    <p class="mt-3 max-w-xl text-sm leading-relaxed text-hb-nav-muted">{{ __('public.about.cta_body') }}</p>
                </div>
                <x-button class="relative mt-6 lg:mt-0" size="lg" variant="primary" :href="route('frontend.contact')">{{ __('public.about.cta_button') }}</x-button>
            </div>
        </div>
    </section>
@endsection
