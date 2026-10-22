@php
    $locale = str_replace('_', '-', app()->getLocale());
    $isRtl = in_array(app()->getLocale(), ['ar'], true);

    /**
     * Frontend-only role shell selection for layout development.
     * Not authorization owns real authz.
     */
    $uiRole = $uiRole ?? request()->query('role', 'candidate');
    $allowedRoles = ['candidate', 'company', 'interviewer', 'admin'];
    if (! in_array($uiRole, $allowedRoles, true)) {
        $uiRole = 'candidate';
    }

    $sidebarPartial = match ($uiRole) {
        'company' => 'partials.sidebar-company',
        'interviewer' => 'partials.sidebar-interviewer',
        'admin' => 'partials.sidebar-admin',
        default => 'partials.sidebar-candidate',
    };
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Workspace · Hirebridge')</title>

    @include('partials.theme-boot')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.js-i18n')
    @stack('head')
</head>
<body class="min-h-screen bg-hb-page text-hb-text">
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
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-hb-blue text-sm font-semibold">h</span>
                <span class="text-base font-semibold tracking-tight">{{ __('common.brand') }}</span>
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
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-hb-blue text-sm font-semibold">h</span>
                <span class="text-base font-semibold tracking-tight">{{ __('common.brand') }}</span>
            </div>

            <div class="flex-1 overflow-y-auto py-4">
                @include($sidebarPartial)
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            @include('partials.topbar')

            <main id="main-content" class="flex-1" tabindex="-1">
                <div class="px-4 py-6 sm:px-6 lg:px-8">
                    @hasSection('breadcrumb')
                        @include('partials.breadcrumb', [
                            'breadcrumb' => trim($__env->yieldContent('breadcrumb')),
                            'uiRole' => $uiRole,
                        ])
                    @endif

                    @hasSection('page_header')
                        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
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

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @stack('modals')
    @stack('scripts')
</body>
</html>
