@extends('layouts.hall')

@section('title', __('hall.landing_title'))
@section('no-nav', true)

@section('content')
@php
    $serviceMeta = [
        ['icon' => 'briefcase', 'target' => 'route:jobs.index'],
        ['icon' => 'building', 'target' => 'route:workspace'],
        ['icon' => 'users', 'target' => 'scene:lobby'],
        ['icon' => 'calendar', 'target' => 'route:candidate.interviews.index'],
        ['icon' => 'chat', 'target' => 'route:lounge'],
        ['icon' => 'bot', 'target' => 'route:assistant'],
    ];
    $services = [];
    foreach (__('hall.services.items') as $index => $item) {
        $services[] = array_merge($item, $serviceMeta[$index] ?? ['icon' => 'briefcase', 'target' => 'route:jobs.index']);
    }
@endphp

<style>
    /* arrival after the entrance zoom: fade in from the white flash */
    @keyframes landing-arrive { from { opacity: 0; transform: scale(1.03); } to { opacity: 1; transform: scale(1); } }
    .landing-arrive { animation: landing-arrive 1s cubic-bezier(.22, 1, .36, 1) both; }
    @media (prefers-reduced-motion: reduce) { .landing-arrive { animation: none; } }
</style>
<div id="landingRoot" class="min-h-screen overflow-hidden bg-white text-slate-900">
   

    <main>
        {{-- Hero Slider (ITIDA style) --}}
        @php
            $slideCopy = __('hall.slides');
            $slides = [
                [
                    'image' => 'images/halls/talentbridge-hero.webp',
                    'title' => $slideCopy[0]['title'] ?? '',
                    'text'  => $slideCopy[0]['text'] ?? '',
                    'cta'   => ['label' => $slideCopy[0]['cta'] ?? '', 'href' => \App\Support\Hall::url('route:jobs.index') !== '#' ? \App\Support\Hall::url('route:jobs.index') : route('frontend.account.register', ['type' => 'candidate'])],
                ],
                [
                    'image' => 'images/halls/companies.webp',
                    'title' => $slideCopy[1]['title'] ?? '',
                    'text'  => $slideCopy[1]['text'] ?? '',
                    'cta'   => ['label' => $slideCopy[1]['cta'] ?? '', 'href' => '#companies'],
                ],
                [
                    'image' => 'images/halls/candidates.webp',
                    'title' => $slideCopy[2]['title'] ?? '',
                    'text'  => $slideCopy[2]['text'] ?? '',
                    'cta'   => ['label' => $slideCopy[2]['cta'] ?? '', 'href' => route('frontend.account.register', ['type' => 'candidate'])],
                ],
                [
                    'image' => 'images/halls/exterior.webp',
                    'title' => $slideCopy[3]['title'] ?? '',
                    'text'  => $slideCopy[3]['text'] ?? '',
                    'cta'   => ['label' => $slideCopy[3]['cta'] ?? '', 'href' => '#services'],
                ],
            ];
        @endphp

        <style>
    
            @keyframes hero-zoom { from { transform: scale(1); } to { transform: scale(1.12); } }
            .hero-slide .hero-img { transform: scale(1); }
            .hero-slide.is-active .hero-img { animation: hero-zoom 8s ease-out forwards; }

           
            .hero-slide .hero-caption { opacity: 0; transform: translateX(-32px); transition: opacity .7s ease .3s, transform .7s ease .3s; }
            .hero-slide.is-active .hero-caption { opacity: 1; transform: translateX(0); }

            @media (prefers-reduced-motion: reduce) {
                .hero-slide.is-active .hero-img { animation: none; }
                .hero-slide, .hero-slide .hero-caption { transition: none !important; }
            }
        </style>

        <section id="home" class="relative isolate overflow-hidden bg-slate-900" aria-roledescription="carousel" aria-label="{{ __('hall.carousel_label') }}">
            <div id="heroSlider" class="relative h-[420px] sm:h-[480px] lg:h-[520px]">

                @foreach ($slides as $i => $slide)
                    <div class="hero-slide absolute inset-0 transition-opacity duration-1000 {{ $i === 0 ? 'is-active opacity-100 z-10' : 'opacity-0 z-0' }}"
                         data-slide aria-hidden="{{ $i === 0 ? 'false' : 'true' }}">

                     
                        <div class="absolute inset-0 overflow-hidden bg-gradient-to-br from-sky-700 via-sky-500 to-slate-800">
                            <img src="{{ asset($slide['image']) }}" alt="" class="hero-img h-full w-full object-cover object-center"
                                 onerror="this.style.display='none'" {{ $i > 0 ? 'loading=lazy' : '' }}>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-r from-slate-900/60 via-slate-900/20 to-transparent"></div>

                      
                        <div class="relative mx-auto flex h-full max-w-7xl items-center px-5 lg:px-8">
                            <div class="hero-caption max-w-2xl pb-16">
                                <h2 class="inline-block bg-white px-4 py-2 text-2xl font-light text-sky-600 sm:text-3xl lg:text-4xl">
                                    {{ $slide['title'] }}
                                </h2>
                                <p class="mt-3 block max-w-xl bg-sky-600 px-4 py-3 text-sm font-medium leading-6 text-white sm:text-base lg:text-lg">
                                    {{ $slide['text'] }}
                                </p>
                                <a href="{{ $slide['cta']['href'] }}"
                                   class="mt-6 inline-flex items-center gap-2 rounded-full bg-white px-7 py-3 text-sm font-bold text-slate-800 shadow-lg transition hover:bg-sky-50 hover:text-sky-600">
                                    {{ $slide['cta']['label'] }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach

            
                <button type="button" data-prev aria-label="{{ __('hall.prev_slide') }}"
                        class="absolute start-3 top-1/2 z-20 -translate-y-1/2 rounded-full p-2 text-white/90 transition hover:bg-white/20 hover:text-white sm:start-8">
                    <svg class="h-9 w-9 sm:h-11 sm:w-11 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="m15 5-7 7 7 7"/></svg>
                </button>
                <button type="button" data-next aria-label="{{ __('hall.next_slide') }}"
                        class="absolute end-3 top-1/2 z-20 -translate-y-1/2 rounded-full p-2 text-white/90 transition hover:bg-white/20 hover:text-white sm:end-8">
                    <svg class="h-9 w-9 sm:h-11 sm:w-11 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="m9 5 7 7-7 7"/></svg>
                </button>

              
                <div class="absolute bottom-20 start-1/2 z-20 flex -translate-x-1/2 gap-2.5 sm:bottom-24 rtl:translate-x-1/2">
                    @foreach ($slides as $i => $slide)
                        <button type="button" data-dot="{{ $i }}" aria-label="{{ __('hall.go_to_slide', ['number' => $i + 1]) }}"
                                class="h-3 w-3 rounded-full border border-white/70 transition {{ $i === 0 ? 'bg-sky-500 border-sky-500' : 'bg-white/60' }}"></button>
                    @endforeach
                </div>
            </div>
        </section>

        <script>
        (function () {
            const root = document.getElementById('heroSlider');
            if (!root) return;

            const slides = [...root.querySelectorAll('[data-slide]')];
            const dots   = [...root.querySelectorAll('[data-dot]')];
            const INTERVAL = 6000;
            let current = 0, timer = null;

            function show(index) {
                current = (index + slides.length) % slides.length;
                slides.forEach((s, i) => {
                    const active = i === current;
                    s.classList.toggle('is-active', active);
                    s.classList.toggle('opacity-100', active);
                    s.classList.toggle('z-10', active);
                    s.classList.toggle('opacity-0', !active);
                    s.classList.toggle('z-0', !active);
                    s.setAttribute('aria-hidden', active ? 'false' : 'true');
                });
                dots.forEach((d, i) => {
                    const active = i === current;
                    d.classList.toggle('bg-sky-500', active);
                    d.classList.toggle('border-sky-500', active);
                    d.classList.toggle('bg-white/60', !active);
                });
            }

            const next = () => show(current + 1);
            const prev = () => show(current - 1);

            function start() { stop(); timer = setInterval(next, INTERVAL); }
            function stop()  { if (timer) clearInterval(timer); timer = null; }

            root.querySelector('[data-next]').addEventListener('click', () => { next(); start(); });
            root.querySelector('[data-prev]').addEventListener('click', () => { prev(); start(); });
            dots.forEach(d => d.addEventListener('click', () => { show(+d.dataset.dot); start(); }));

           
            root.addEventListener('mouseenter', stop);
            root.addEventListener('mouseleave', start);

           
            let startX = null;
            root.addEventListener('touchstart', e => { startX = e.touches[0].clientX; stop(); }, { passive: true });
            root.addEventListener('touchend', e => {
                if (startX === null) return;
                const dx = e.changedTouches[0].clientX - startX;
                if (Math.abs(dx) > 50) dx < 0 ? next() : prev();
                startX = null; start();
            });

        
            document.addEventListener('visibilitychange', () => document.hidden ? stop() : start());

            start();
        })();
        </script>

        {{-- Public job search: existing Laravel jobs route and query parameter contract. --}}
        <section class="bg-white px-5 py-10 lg:px-8" aria-labelledby="landing-search-title">
            <div class="mx-auto max-w-6xl rounded-3xl border border-slate-200 bg-slate-50 p-5 shadow-sm sm:p-8">
                <div class="mb-6 max-w-2xl">
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-sky-600">{{ __('hall.landing_features.search.eyebrow') }}</p>
                    <h2 id="landing-search-title" class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ __('hall.landing_features.search.title') }}</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ __('hall.landing_features.search.body') }}</p>
                </div>
                <form method="GET" action="{{ route('frontend.jobs.index') }}" class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] sm:items-end" role="search">
                    <div>
                        <label for="landing-job-query" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('hall.landing_features.search.role_label') }}</label>
                        <input id="landing-job-query" name="q" type="search" maxlength="100" value="{{ request('q') }}" autocomplete="organization-title" placeholder="{{ __('hall.landing_features.search.role_placeholder') }}" class="min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-200">
                    </div>
                    <div>
                        <label for="landing-job-location" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('hall.landing_features.search.location_label') }}</label>
                        <input id="landing-job-location" name="location" type="search" maxlength="100" value="{{ request('location') }}" autocomplete="address-level2" placeholder="{{ __('hall.landing_features.search.location_placeholder') }}" class="min-h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-200">
                    </div>
                    <button type="submit" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-sky-600 px-7 text-sm font-bold text-white transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">{{ __('hall.landing_features.search.submit') }}</button>
                </form>
            </div>
        </section>

        {{-- Company discovery is intentionally unavailable until a public directory contract exists. --}}
        <section id="company-strip" class="bg-white px-5 py-8 lg:px-8" aria-labelledby="company-strip-title">
            <div class="mx-auto max-w-7xl rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-8 text-center">
                <h2 id="company-strip-title" class="text-xl font-bold text-slate-900">{{ __('hall.landing_features.companies.title') }}</h2>
                <p class="mx-auto mt-2 max-w-2xl text-sm leading-6 text-slate-600" role="status">{{ __('hall.landing_features.companies.unavailable') }}</p>
            </div>
        </section>

        {{-- Icon-led services, inspired by the reference layout --}}
        <section id="services" class="relative z-20 -mt-14 bg-transparent px-5 pb-12 pt-0 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="grid grid-cols-2 gap-x-4 gap-y-7 sm:grid-cols-3 lg:grid-cols-6">
                    @foreach ($services as $service)
                            @php
                                $serviceUrl = \App\Support\Hall::url($service['target']);
                                $isSoon = !\App\Support\Hall::ready($service['target']);
                                $requiresLogin = in_array($service['target'], [
                                    'route:workspace', 'scene:lobby', 'route:candidate.interviews.index',
                                    'route:lounge', 'route:assistant',
                                ], true);
                                if ($requiresLogin && !auth()->check()) {
                                    // companies start by registering, everything else by logging in
                                    $serviceUrl = $service['target'] === 'route:workspace'
                                        ? route('frontend.account.register', ['type' => 'company'])
                                        : route('frontend.account.login');
                                    $isSoon = false;
                                }
                            @endphp
                            <a href="{{ $serviceUrl }}" class="group relative flex flex-col items-center text-center">
                                <span class="absolute left-0 right-0 top-[45px] -z-10 hidden h-1.5 bg-sky-600 sm:block"></span>
                                <span class="-mt-1 mb-4 grid h-24 w-24 place-items-center rounded-full bg-sky-500 text-white shadow-sm ring-8 ring-white transition duration-300 group-hover:-translate-y-1 group-hover:bg-sky-600 group-hover:shadow-lg group-hover:shadow-sky-500/20 sm:h-27 sm:w-27">
                                    @switch($service['icon'])
                                        @case('briefcase')
                                            <svg class="h-11 w-11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18M10 12v2h4v-2"/></svg>
                                            @break
                                        @case('building')
                                            <svg class="h-11 w-11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 7h2m2 0h2M9 11h2m2 0h2M9 15h2m2 0h2M10 21v-3h4v3"/></svg>
                                            @break
                                        @case('users')
                                            <svg class="h-11 w-11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                            @break
                                        @case('calendar')
                                            <svg class="h-11 w-11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18m-12 5 2 2 4-4"/></svg>
                                            @break
                                        @case('chat')
                                            <svg class="h-11 w-11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M21 11.5a7.5 7.5 0 0 1-7.5 7.5H6l-3 2v-6.5A7.5 7.5 0 1 1 21 11.5Z"/><path d="M8 11h8m-8 3h5"/></svg>
                                            @break
                                        @case('bot')
                                            <svg class="h-11 w-11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="4" y="8" width="16" height="12" rx="3"/><path d="M12 4v4m-3 5h.01M15 13h.01M9 17h6"/><circle cx="12" cy="3" r="1"/></svg>
                                            @break
                                    @endswitch
                                </span>
                                <span class="text-sm font-bold leading-5 text-slate-800 transition group-hover:text-sky-600">{{ $service['label'] }}@if ($isSoon) <span class="ms-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500 align-middle">{{ __('hall.soon') }}</span>@endif</span>
                                <span class="mt-1 max-w-[170px] text-xs leading-5 text-slate-500">{{ $service['description'] }}</span>
                            </a>
                                            @endforeach
                </div>
            </div>
        </section>

        {{-- Service labels are required by the page brief; service descriptions await backend content. --}}
        <section class="bg-slate-50 px-5 py-14 lg:px-8" aria-labelledby="service-models-title">
            <div class="mx-auto max-w-7xl">
                <div class="mx-auto mb-8 max-w-2xl text-center">
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-sky-600">{{ __('hall.landing_features.models.eyebrow') }}</p>
                    <h2 id="service-models-title" class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ __('hall.landing_features.models.title') }}</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600">{{ __('hall.landing_features.models.body') }}</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach (['candidates', 'direct', 'agency'] as $model)
                        <article class="flex min-h-44 flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h3 class="text-lg font-bold text-slate-900">{{ __('hall.landing_features.models.' . $model) }}</h3>
                            <p class="mt-3 text-sm leading-6 text-slate-600" role="status">{{ __('hall.landing_features.models.unavailable') }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Workflow ordering and copy must come from a documented backend contract. --}}
        <section class="bg-white px-5 py-14 lg:px-8" aria-labelledby="workflow-title">
            <div class="mx-auto max-w-7xl">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-sky-600">{{ __('hall.landing_features.workflow.eyebrow') }}</p>
                    <h2 id="workflow-title" class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ __('hall.landing_features.workflow.title') }}</h2>
                    <p class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-8 text-sm leading-6 text-slate-600" role="status">{{ __('hall.landing_features.workflow.unavailable') }}</p>
                </div>
            </div>
        </section>

        {{-- Categories load through the existing Laravel lookup route; only documented id/name fields are used. --}}
        <section class="bg-slate-50 px-5 py-14 lg:px-8" aria-labelledby="specialties-title">
            <div class="mx-auto max-w-7xl">
                <div class="mx-auto mb-8 max-w-2xl text-center">
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-sky-600">{{ __('hall.landing_features.specialties.eyebrow') }}</p>
                    <h2 id="specialties-title" class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ __('hall.landing_features.specialties.title') }}</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600">{{ __('hall.landing_features.specialties.body') }}</p>
                </div>
                <div data-landing-categories
                     data-categories-url="{{ route('frontend.lookup.categories') }}"
                     data-jobs-url="{{ route('frontend.jobs.index') }}"
                     class="min-h-24 rounded-2xl border border-slate-200 bg-white p-5 sm:p-7">
                    <div data-category-state="loading" class="flex min-h-14 items-center justify-center gap-3 text-sm text-slate-600" role="status">
                        <span class="h-5 w-5 animate-spin rounded-full border-2 border-sky-600 border-t-transparent" aria-hidden="true"></span>
                        <span>{{ __('hall.landing_features.specialties.loading') }}</span>
                    </div>
                    <p data-category-state="empty" class="hidden py-4 text-center text-sm text-slate-600" role="status" aria-hidden="true">{{ __('hall.landing_features.specialties.empty') }}</p>
                    <div data-category-state="error" class="hidden py-4 text-center" role="alert" aria-hidden="true">
                        <p class="text-sm text-rose-700">{{ __('hall.landing_features.specialties.error') }}</p>
                        <button type="button" data-category-retry class="mt-3 rounded-lg px-4 py-2 text-sm font-semibold text-sky-700 underline underline-offset-4 focus:outline-none focus:ring-2 focus:ring-sky-500">{{ __('hall.landing_features.specialties.retry') }}</button>
                    </div>
                    <div data-category-state="success" class="hidden" aria-hidden="true">
                        <div data-category-list class="flex flex-wrap justify-center gap-3"></div>
                    </div>
                    <noscript><p class="py-4 text-center text-sm text-slate-600">{{ __('hall.landing_features.specialties.no_script') }}</p></noscript>
                </div>
            </div>
        </section>

        {{-- Public aggregate pipeline data is not documented; never request candidate application records here. --}}
        <section class="bg-white px-5 py-14 lg:px-8" aria-labelledby="pipeline-preview-title">
            <div class="mx-auto max-w-7xl">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-sky-600">{{ __('hall.landing_features.pipeline.eyebrow') }}</p>
                    <h2 id="pipeline-preview-title" class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ __('hall.landing_features.pipeline.title') }}</h2>
                    <p class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-8 text-sm leading-6 text-slate-600" role="status">{{ __('hall.landing_features.pipeline.unavailable') }}</p>
                </div>
            </div>
        </section>

        {{-- Candidate and company pathways (ITIDA-style cards) --}}
        <section class="bg-white px-5 pb-14 pt-10 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="mx-auto mb-20 max-w-2xl text-center">
                    <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-sky-600">{{ __('hall.journeys.eyebrow') }}</p>
                    <h2 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ __('hall.journeys.title') }}</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-500">{{ __('hall.journeys.body') }}</p>
                </div>

                <div class="mx-auto grid max-w-4xl gap-x-8 gap-y-24 md:grid-cols-2">
                    {{-- Candidates --}}
                    <article id="candidates" class="group relative flex flex-col bg-slate-100 px-7 pb-6 pt-24 text-center">
                        <span class="absolute -top-20 start-1/2 h-40 w-40 -translate-x-1/2 overflow-hidden rounded-full border-4 border-sky-600 bg-white shadow-sm rtl:translate-x-1/2">
                            <img src="{{ asset('images/halls/candidates.webp') }}" alt="{{ __('hall.journeys.candidate_alt') }}" loading="lazy"
                                 class="h-full w-full object-cover object-center transition duration-700 group-hover:scale-110" onerror="this.style.display='none'">
                        </span>
                        <a href="{{ route('frontend.account.register', ['type' => 'candidate']) }}" class="text-lg font-medium leading-7 text-sky-600 hover:underline">{{ __('hall.journeys.candidate_title') }}</a>
                        <p class="mt-4 text-base leading-8 text-slate-800">
                            {{ __('hall.journeys.candidate_body') }}
                        </p>
                        <div class="mt-auto flex justify-end pt-6">
                            <a href="{{ route('frontend.account.register', ['type' => 'candidate']) }}" class="inline-flex items-center gap-2 text-lg font-medium text-slate-900 transition hover:text-sky-600">
                                {{ __('hall.journeys.candidate_cta') }}
                                <span class="grid h-7 w-7 place-items-center rounded-full border border-slate-500 transition group-hover:border-sky-600">
                                    <svg class="h-4 w-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14m-5-5 5 5-5 5"/></svg>
                                </span>
                            </a>
                        </div>
                    </article>

                    {{-- Companies --}}
                    <article id="companies" class="group relative flex flex-col bg-slate-100 px-7 pb-6 pt-24 text-center">
                        <span class="absolute -top-20 start-1/2 h-40 w-40 -translate-x-1/2 overflow-hidden rounded-full border-4 border-sky-600 bg-white shadow-sm rtl:translate-x-1/2">
                            <img src="{{ asset('images/halls/companies.webp') }}" alt="{{ __('hall.journeys.company_alt') }}" loading="lazy"
                                 class="h-full w-full object-cover object-center transition duration-700 group-hover:scale-110" onerror="this.style.display='none'">
                        </span>
                        @php
                            $companyUrl = auth()->check()
                                ? route('frontend.workspace')
                                : route('frontend.account.register', ['type' => 'company']);
                        @endphp
                        <a href="{{ $companyUrl }}" class="text-lg font-medium leading-7 text-sky-600 hover:underline">{{ __('hall.journeys.company_title') }}</a>
                        <p class="mt-4 text-base leading-8 text-slate-800">
                            {{ __('hall.journeys.company_body') }}
                        </p>
                        <div class="mt-auto flex justify-end pt-6">
                            <a href="{{ $companyUrl }}" class="inline-flex items-center gap-2 text-lg font-medium text-slate-900 transition hover:text-sky-600">
                                {{ auth()->check() ? __('hall.journeys.company_workspace_cta') : __('hall.journeys.company_cta') }}
                                <span class="grid h-7 w-7 place-items-center rounded-full border border-slate-500 transition group-hover:border-sky-600">
                                    <svg class="h-4 w-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14m-5-5 5 5-5 5"/></svg>
                                </span>
                            </a>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        {{-- Why TalentBridge --}}
        @php
            $benefitIcons = [
                'M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm10 10v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75',
                'M5 3h14a1 1 0 0 1 1 1v17H4V4a1 1 0 0 1 1-1Zm4 4h2m2 0h2M9 11h2m2 0h2M9 15h2m2 0h2',
                'M12 3 4 6v6c0 4.5 3.2 8 8 9 4.8-1 8-4.5 8-9V6l-8-3Zm-3 9 2 2 4-4',
                'M4 6h16v14H4V6Zm12-3v4M8 3v4M4 11h16m-11 4 2 2 4-4',
            ];
            $benefits = [];
            foreach (__('hall.benefits.items') as $index => $item) {
                $benefits[] = array_merge($item, ['icon' => $benefitIcons[$index] ?? $benefitIcons[0]]);
            }
        @endphp
        <section class="bg-sky-600 px-5 py-14 text-white lg:px-8" aria-labelledby="why-title">
            <div class="mx-auto max-w-7xl">
                <div class="mx-auto mb-10 max-w-2xl text-center">
                    <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-sky-100">{{ __('hall.benefits.eyebrow') }}</p>
                    <h2 id="why-title" class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">{{ __('hall.benefits.title') }}</h2>
                </div>
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4 lg:gap-0">
                    @foreach ($benefits as $b)
                        <div class="flex flex-col items-center px-6 text-center {{ !$loop->last ? 'lg:border-e lg:border-white/20' : '' }}">
                            <span class="grid h-16 w-16 place-items-center rounded-full bg-white/15 ring-4 ring-white/10">
                                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $b['icon'] }}"/></svg>
                            </span>
                            <h3 class="mt-5 text-base font-bold">{{ $b['title'] }}</h3>
                            <p class="mt-2 max-w-[240px] text-sm leading-6 text-sky-50/90">{{ $b['text'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </main>


  
</div>
<script>
    try {
        if (sessionStorage.getItem('hallEntered')) {
            document.getElementById('landingRoot')?.classList.add('landing-arrive');
            sessionStorage.removeItem('hallEntered');
        }
    } catch (e) {}
</script>
@endsection
