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

    <title>@yield('title', 'Account access · Hirebridge')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-hb-surface text-hb-text">
    <div class="grid min-h-screen lg:grid-cols-[minmax(0,528px)_minmax(0,1fr)]">
        {{-- Brand story panel — Figma 07 Authentication --}}
        <aside class="relative hidden flex-col justify-between bg-hb-navy px-10 py-12 text-white lg:flex">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5" aria-label="Hirebridge home">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-hb-blue text-sm font-semibold">h</span>
                    <span class="text-lg font-semibold tracking-tight">hirebridge</span>
                </a>

                <div class="mt-16 max-w-sm space-y-6">
                    <p class="text-xs font-semibold tracking-[0.14em] text-hb-nav-muted uppercase">
                        One connection. Your next chapter.
                    </p>
                    <h1 class="text-3xl font-semibold leading-tight">
                        @yield('brand_headline', 'Good work starts with the right connection.')
                    </h1>
                    <p class="text-sm leading-relaxed text-hb-nav-muted">
                        @yield('brand_copy', 'Find your next role, build your team, or partner with a recruiter. One clear workspace for every step.')
                    </p>
                </div>
            </div>

            <div class="space-y-4 text-sm text-hb-nav-muted">
                <p class="font-medium text-white">Designed around people.</p>
                <ul class="space-y-2">
                    <li>Transparent opportunities</li>
                    <li>Thoughtful hiring journeys</li>
                    <li>Your information, in your control</li>
                </ul>
                <p class="pt-6 text-xs">Hirebridge · Privacy · Help</p>
            </div>
        </aside>

        <div class="flex min-h-screen flex-col bg-hb-page">
            <div class="flex items-center justify-between px-4 py-5 sm:px-8 lg:hidden">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5" aria-label="Hirebridge home">
                    <span class="hb-brand-mark">h</span>
                    <span class="text-lg font-semibold text-hb-navy">hirebridge</span>
                </a>
                @include('partials.language-switch')
            </div>

            <div class="flex flex-1 items-center justify-center px-4 py-10 sm:px-8">
                <div class="w-full @yield('form_max_width', 'max-w-md')">
                    <p class="mb-3 text-xs font-semibold tracking-[0.12em] text-hb-muted uppercase">
                        @yield('eyebrow', '07 / Account access')
                    </p>
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
