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

    @include('frontend.partials.theme-boot')
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('frontend.partials.js-i18n')
    @stack('head')
</head>
<body class="min-h-screen bg-hb-surface text-hb-text">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-hb-blue focus:px-3 focus:py-2 focus:text-white">
        {{ __('common.skip_to_content') }}
    </a>

    <header class="sticky top-0 z-40 isolate border-b border-hb-border/80 bg-hb-surface/90 shadow-hb-sm backdrop-blur-md">
        <div class="hb-container flex h-[76px] items-center justify-between gap-6">
            <a href="{{ route('frontend.home') }}" class="flex shrink-0 items-center gap-2.5" aria-label="{{ __('common.brand_home') }}">
                @include('frontend.partials.brand-logo', ['onDark' => false])
            </a>

            <nav class="hidden items-center gap-8 md:flex" aria-label="{{ __('navigation.primary') }}">
                <a href="{{ route('frontend.jobs.index') }}" @class(['hb-nav-link', 'text-hb-blue' => request()->routeIs('frontend.jobs.*')])>{{ __('navigation.find_job') }}</a>
                <a href="{{ route('frontend.about') }}" @class(['hb-nav-link', 'text-hb-blue' => request()->routeIs('frontend.about')])>{{ __('navigation.for_employers') }}</a>
                <a href="{{ route('frontend.about') }}#engagements" class="hb-nav-link">{{ __('navigation.agency_services') }}</a>
            </nav>

            <div class="flex items-center gap-2 sm:gap-3">
                @include('frontend.partials.theme-switch')
                @include('frontend.partials.language-switch')

                @guest
                    <a href="{{ route('frontend.account.login') }}" class="hb-btn-ghost hidden sm:inline-flex">{{ __('common.actions.sign_in') }}</a>
                    <a href="{{ route('frontend.account.register') }}" class="hb-btn-primary">{{ __('common.actions.get_started') }}</a>
                @endguest
                @auth
                    <a href="{{ route('frontend.workspace') }}" class="hb-btn-primary">{{ __('navigation.virtual_hall') }}</a>
                @endauth

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
                <a href="{{ route('frontend.jobs.index') }}" class="hb-nav-link py-1">{{ __('navigation.find_job') }}</a>
                <a href="{{ route('frontend.about') }}" class="hb-nav-link py-1">{{ __('navigation.for_employers') }}</a>
                <a href="{{ route('frontend.about') }}#engagements" class="hb-nav-link py-1">{{ __('navigation.agency_services') }}</a>
                <a href="{{ route('frontend.contact') }}" class="hb-nav-link py-1">{{ __('navigation.contact') }}</a>
                @guest
                    <a href="{{ route('frontend.account.login') }}" class="hb-nav-link py-1 sm:hidden">{{ __('common.actions.sign_in') }}</a>
                    <a href="{{ route('frontend.account.register') }}" class="hb-btn-primary w-full sm:hidden">{{ __('common.actions.get_started') }}</a>
                @endguest
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
                        @include('frontend.partials.brand-logo', ['onDark' => false])
                    </div>
                    <p class="max-w-xs text-sm text-hb-muted">{{ __('common.footer.tagline') }}</p>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-hb-navy">{{ __('common.footer.candidates') }}</h2>
                    <ul class="mt-3 space-y-2 text-sm text-hb-muted">
                        <li><a href="{{ route('frontend.jobs.index') }}" class="hover:text-hb-blue">{{ __('navigation.find_job') }}</a></li>
                        <li><span class="opacity-70">{{ __('navigation.candidate.applications') }}</span></li>
                        <li><span class="opacity-70">{{ __('common.help_support') }}</span></li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-hb-navy">{{ __('common.footer.employers') }}</h2>
                    <ul class="mt-3 space-y-2 text-sm text-hb-muted">
                        <li><a href="{{ route('frontend.about') }}" class="hover:text-hb-blue">{{ __('navigation.for_employers') }}</a></li>
                        <li><a href="{{ route('frontend.about') }}#engagements" class="hover:text-hb-blue">{{ __('navigation.agency_services') }}</a></li>
                        <li><a href="{{ route('frontend.contact') }}" class="hover:text-hb-blue">{{ __('navigation.contact') }}</a></li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-hb-navy">TalentBridge</h2>
                    <ul class="mt-3 space-y-2 text-sm text-hb-muted">
                        <li><a href="{{ route('frontend.about') }}" class="hover:text-hb-blue">{{ __('public.about.page_title') }}</a></li>
                        <li><span class="opacity-70">{{ __('common.privacy') }}</span></li>
                        <li><span class="opacity-70">{{ __('common.help') }}</span></li>
                    </ul>
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-3 border-t border-hb-border pt-6 text-xs text-hb-muted sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} TalentBridge. {{ __('common.footer.rights') }}</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
