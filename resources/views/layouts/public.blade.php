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

    <title>@yield('title', 'Hirebridge')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-hb-surface text-hb-text">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-hb-blue focus:px-3 focus:py-2 focus:text-white">
        Skip to content
    </a>

    <header class="sticky top-0 z-40 border-b border-hb-border/80 bg-hb-surface/95 backdrop-blur">
        <div class="hb-container flex h-[88px] items-center justify-between gap-6">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0" aria-label="Hirebridge home">
                <span class="hb-brand-mark" aria-hidden="true">h</span>
                <span class="text-lg font-semibold tracking-tight text-hb-navy">hirebridge</span>
            </a>

            <nav class="hidden items-center gap-8 md:flex" aria-label="Primary">
                <a href="{{ route('jobs.index') }}" @class(['hb-nav-link', 'text-hb-blue' => request()->routeIs('jobs.*')])>Find a job</a>
                <a href="{{ route('about') }}" @class(['hb-nav-link', 'text-hb-blue' => request()->routeIs('about')])>For employers</a>
                <a href="{{ route('about') }}#engagements" class="hb-nav-link">Agency services</a>
                <a href="{{ route('companies.show', 'northstar-labs') }}" @class(['hb-nav-link', 'text-hb-blue' => request()->routeIs('companies.*')])>Companies</a>
            </nav>

            <div class="flex items-center gap-2 sm:gap-3">
                @include('partials.language-switch')

                <a href="{{ route('login') }}" class="hb-btn-ghost hidden sm:inline-flex">Sign in</a>
                <a href="{{ route('register') }}" class="hb-btn-primary">Get started</a>

                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-hb-border md:hidden"
                    data-public-nav-toggle
                    aria-expanded="false"
                    aria-controls="public-mobile-nav"
                    aria-label="Open menu"
                >
                    <span class="block h-0.5 w-4 bg-hb-text"></span>
                </button>
            </div>
        </div>

        <nav id="public-mobile-nav" class="hidden border-t border-hb-border md:hidden" aria-label="Mobile">
            <div class="hb-container flex flex-col gap-3 py-4">
                <a href="{{ route('jobs.index') }}" class="hb-nav-link py-1">Find a job</a>
                <a href="{{ route('about') }}" class="hb-nav-link py-1">For employers</a>
                <a href="{{ route('about') }}#engagements" class="hb-nav-link py-1">Agency services</a>
                <a href="{{ route('companies.show', 'northstar-labs') }}" class="hb-nav-link py-1">Companies</a>
                <a href="{{ route('contact') }}" class="hb-nav-link py-1">Contact</a>
                <a href="{{ route('login') }}" class="hb-nav-link py-1 sm:hidden">Sign in</a>
                <a href="{{ route('register') }}" class="hb-btn-primary w-full sm:hidden">Get started</a>
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
                        <span class="text-lg font-semibold text-hb-navy">hirebridge</span>
                    </div>
                    <p class="max-w-xs text-sm text-hb-muted">Better connections. Clearer next steps.</p>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-hb-navy">Candidates</h2>
                    <ul class="mt-3 space-y-2 text-sm text-hb-muted">
                        <li><a href="{{ route('jobs.index') }}" class="hover:text-hb-blue">Find a job</a></li>
                        <li><span class="opacity-70" title="Requires authentication — later stage">Your applications</span></li>
                        <li><span class="opacity-70">Career support</span></li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-hb-navy">Employers</h2>
                    <ul class="mt-3 space-y-2 text-sm text-hb-muted">
                        <li><a href="{{ route('about') }}" class="hover:text-hb-blue">Direct hiring</a></li>
                        <li><a href="{{ route('about') }}#engagements" class="hover:text-hb-blue">Agency services</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-hb-blue">Contact our team</a></li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-hb-navy">Hirebridge</h2>
                    <ul class="mt-3 space-y-2 text-sm text-hb-muted">
                        <li><a href="{{ route('about') }}" class="hover:text-hb-blue">About us</a></li>
                        <li><span class="opacity-70">Privacy notice</span></li>
                        <li><span class="opacity-70">Terms of service</span></li>
                    </ul>
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-3 border-t border-hb-border pt-6 text-xs text-hb-muted sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} Hirebridge. Concept product · Demo content.</p>
                <p>Accessibility · Cookie preferences</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
