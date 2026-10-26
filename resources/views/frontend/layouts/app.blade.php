@php
    use App\Support\Frontend\WorkspaceNavigator;

    $locale = str_replace('_', '-', app()->getLocale());
    $isRtl = in_array(app()->getLocale(), ['ar'], true);

    // Shell from authenticated ApiSession identity (account_type + roles) — not URL/query.
    // Link visibility is not authorization; route middleware remains the gate.
    $uiRole = WorkspaceNavigator::resolveShell();
    $sidebarPartial = WorkspaceNavigator::sidebarPartial();
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Workspace') | TalentBridge</title>

    @include('frontend.partials.theme-boot')
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('frontend.partials.js-i18n')
    @stack('head')
</head>
<body class="min-h-screen bg-hb-page text-hb-text antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-hb-blue focus:px-3 focus:py-2 focus:text-white">
        {{ __('common.skip_to_content') }}
    </a>

    {{-- Mobile sidebar drawer (closed: inert + aria-hidden via JS; pointer-events none in CSS) --}}
    <div
        class="fixed inset-0 z-40 hidden bg-hb-navy/40 lg:hidden"
        data-mobile-sidebar-backdrop
        hidden
        aria-hidden="true"
    ></div>
    <aside
        id="mobile-sidebar-drawer"
        class="fixed inset-y-0 start-0 z-50 flex w-56 -translate-x-full flex-col bg-hb-sidebar text-white transition-transform duration-200 rtl:translate-x-full lg:hidden"
        data-mobile-sidebar
        aria-label="{{ __('navigation.workspace') }}"
        aria-hidden="true"
        inert
    >
        <div class="flex h-[72px] items-center justify-between border-b border-white/10 px-5">
            <div class="flex items-center gap-2.5">
                <a href="{{ route('frontend.home') }}" class="flex items-center gap-2.5">@include('frontend.partials.brand-logo', ['onDark' => true])</a>
            </div>
            <button
                type="button"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-hb-nav-muted hover:bg-white/10 hover:text-white"
                data-mobile-sidebar-close
                aria-label="{{ __('common.close_navigation') }}"
            >
                &times;
            </button>
        </div>
        <div class="flex-1 overflow-y-auto py-4">
            @include($sidebarPartial)
        </div>
    </aside>

    <div class="flex min-h-screen">
        {{-- Figma Role navigation · 224px navy rail --}}
        <aside
            class="hidden w-56 shrink-0 flex-col bg-hb-sidebar text-white lg:flex"
            aria-label="{{ __('navigation.workspace') }}"
            data-ui-role="{{ $uiRole }}"
        >
            <div class="flex h-[72px] items-center gap-2.5 border-b border-white/10 px-5">
                <a href="{{ route('frontend.home') }}" class="flex items-center gap-2.5">@include('frontend.partials.brand-logo', ['onDark' => true])</a>
            </div>

            <div class="flex-1 overflow-y-auto py-4">
                @include($sidebarPartial)
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            @include('frontend.partials.topbar')

            <main id="main-content" class="flex-1" tabindex="-1">
                <div class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-10">
                    @hasSection('breadcrumb')
                        @include('frontend.partials.breadcrumb', [
                            'breadcrumb' => trim($__env->yieldContent('breadcrumb')),
                            'uiRole' => $uiRole,
                        ])
                    @endif

                    @hasSection('page_header')
                        <div class="mb-8 flex flex-col gap-4 border-b border-hb-border pb-6 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                @yield('page_header')
                            </div>
                            @hasSection('page_actions')
                                <div class="flex shrink-0 flex-wrap items-center gap-2">
                                    @yield('page_actions')
                                </div>
                            @endif
                        </div>
                    @endif

                    @if (session('success_key'))
                        <x-alert variant="success" class="mb-4" role="status">{{ __(session('success_key'), session('success_replace', [])) }}</x-alert>
                    @elseif (session('success'))
                        <x-alert variant="success" class="mb-4" role="status">{{ session('success') }}</x-alert>
                    @endif
                    @if ($errors->has('error'))
                        <x-alert variant="error" class="mb-4" role="alert">{{ $errors->first('error') }}</x-alert>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @stack('modals')
    @stack('scripts')
</body>
</html>
