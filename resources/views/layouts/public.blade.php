@php
    $locale = str_replace('_', '-', app()->getLocale());
    $isRtl = in_array(app()->getLocale(), ['ar'], true);
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', __('public.home.page_title'))</title>

    @include('partials.theme-boot')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.js-i18n')
    @stack('head')
</head>
<body class="min-h-screen bg-hb-surface text-hb-text">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-hb-blue focus:px-3 focus:py-2 focus:text-white">
        {{ __('common.skip_to_content') }}
    </a>

    <header class="sticky top-0 z-40 border-b border-hb-border/80 bg-hb-surface/95 backdrop-blur">
        <div class="hb-container flex h-[88px] items-center justify-between gap-6">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5" aria-label="{{ __('common.brand_home') }}">
                <span class="hb-brand-mark" aria-hidden="true">h</span>
                <span class="text-lg font-semibold tracking-tight text-hb-navy">{{ __('common.brand') }}</span>
            </a>

            <nav class="hidden items-center gap-8 md:flex" aria-label="{{ __('navigation.primary') }}">
                <a href="{{ route('jobs.index') }}" @class(['hb-nav-link', 'text-hb-blue' => request()->routeIs('jobs.*')])>{{ __('navigation.find_job') }}</a>
                <a href="{{ route('about') }}" @class(['hb-nav-link', 'text-hb-blue' => request()->routeIs('about')])>{{ __('navigation.for_employers') }}</a>
                <a href="{{ route('about') }}#engagements" class="hb-nav-link">{{ __('navigation.agency_services') }}</a>
                <a href="{{ route('companies.show', 'northstar-labs') }}" @class(['hb-nav-link', 'text-hb-blue' => request()->routeIs('companies.*')])>{{ __('navigation.companies') }}</a>
            </nav>

            <div class="flex items-center gap-2 sm:gap-3">
                @include('partials.theme-switch')
                @include('partials.language-switch')

                <a href="{{ route('login') }}" class="hb-btn-ghost hidden sm:inline-flex">{{ __('common.actions.sign_in') }}</a>
                <a href="{{ route('register') }}" class="hb-btn-primary">{{ __('common.actions.get_started') }}</a>

                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-hb-border md:hidden"
                    data-public-nav-toggle
                    aria-expanded="false"
                    aria-controls="public-mobile-nav"
                    aria-label="{{ __('common.open_menu') }}"
                >
                    <span class="block h-0.5 w-4 bg-hb-text"></span>
                </button>
            </div>
        </div>

        <nav id="public-mobile-nav" class="hidden border-t border-hb-border md:hidden" aria-label="{{ __('navigation.mobile') }}">
            <div class="hb-container flex flex-col gap-3 py-4">
                <a href="{{ route('jobs.index') }}" class="hb-nav-link py-1">{{ __('navigation.find_job') }}</a>
                <a href="{{ route('about') }}" class="hb-nav-link py-1">{{ __('navigation.for_employers') }}</a>
                <a href="{{ route('about') }}#engagements" class="hb-nav-link py-1">{{ __('navigation.agency_services') }}</a>
                <a href="{{ route('companies.show', 'northstar-labs') }}" class="hb-nav-link py-1">{{ __('navigation.companies') }}</a>
                <a href="{{ route('contact') }}" class="hb-nav-link py-1">{{ __('navigation.contact') }}</a>
                <a href="{{ route('login') }}" class="hb-nav-link py-1 sm:hidden">{{ __('common.actions.sign_in') }}</a>
                <a href="{{ route('register') }}" class="hb-btn-primary w-full sm:hidden">{{ __('common.actions.get_started') }}</a>
            </div>
        </nav>
    </header>

    <main id="main-content">
        @yield('content')
    </main>

    <footer class="border-t border-hb-border bg-hb-page">
        <div class="hb-container py-12 lg:py-16">
            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
                <div class="space-y-4">
                    <div class="flex items-center gap-2.5">
                        <span class="hb-brand-mark" aria-hidden="true">h</span>
                        <span class="text-lg font-semibold text-hb-navy">{{ __('common.brand') }}</span>
                    </div>
                    <p class="max-w-xs text-sm text-hb-muted">{{ __('common.footer.tagline') }}</p>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-hb-navy">{{ __('common.footer.candidates') }}</h2>
                    <ul class="mt-3 space-y-2 text-sm text-hb-muted">
                        <li><a href="{{ route('jobs.index') }}" class="hover:text-hb-blue">{{ __('navigation.find_job') }}</a></li>
                        <li><span class="opacity-70">{{ __('navigation.candidate.applications') }}</span></li>
                        <li><span class="opacity-70">{{ __('common.help_support') }}</span></li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-hb-navy">{{ __('common.footer.employers') }}</h2>
                    <ul class="mt-3 space-y-2 text-sm text-hb-muted">
                        <li><a href="{{ route('about') }}" class="hover:text-hb-blue">{{ __('navigation.for_employers') }}</a></li>
                        <li><a href="{{ route('about') }}#engagements" class="hover:text-hb-blue">{{ __('navigation.agency_services') }}</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-hb-blue">{{ __('navigation.contact') }}</a></li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-hb-navy">Hirebridge</h2>
                    <ul class="mt-3 space-y-2 text-sm text-hb-muted">
                        <li><a href="{{ route('about') }}" class="hover:text-hb-blue">{{ __('public.about.page_title') }}</a></li>
                        <li><span class="opacity-70">{{ __('common.privacy') }}</span></li>
                        <li><span class="opacity-70">{{ __('common.help') }}</span></li>
                    </ul>
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-3 border-t border-hb-border pt-6 text-xs text-hb-muted sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} Hirebridge. {{ __('common.footer.rights') }}</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
