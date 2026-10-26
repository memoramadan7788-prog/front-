@php
    use App\Support\Frontend\TopbarAccount;

    $account = TopbarAccount::fromSession();
    $topbarUser = array_merge($account, is_array($topbarUser ?? null) ? $topbarUser : []);
    $profilePanelId = 'profile-menu-panel';
    $profileRoute = $topbarUser['profile_route'] ?? null;
    $securityRoute = $topbarUser['security_route'] ?? 'frontend.account.change-password';
@endphp

<header class="sticky top-0 z-30 flex h-[72px] items-center justify-between gap-4 border-b border-hb-border bg-hb-surface/90 px-4 shadow-hb-sm backdrop-blur-md sm:px-6 lg:px-8 isolate">
    <div class="flex min-w-0 flex-1 items-center gap-3">
        <button
            type="button"
            class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-hb-border text-hb-text lg:hidden"
            data-mobile-sidebar-toggle
            aria-expanded="false"
            aria-controls="mobile-sidebar-drawer"
            aria-label="{{ __('common.open_navigation') }}"
        >
            <span class="flex flex-col gap-1" aria-hidden="true">
                <span class="block h-0.5 w-4 bg-current"></span>
                <span class="block h-0.5 w-4 bg-current"></span>
                <span class="block h-0.5 w-3 bg-current"></span>
            </span>
        </button>

        <label class="relative hidden min-w-0 max-w-md flex-1 pointer-events-none sm:block">
            <span class="sr-only">{{ __('common.search_label') }}</span>
            <span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-hb-muted" aria-hidden="true">⌕</span>
            <input
                type="search"
                name="q"
                placeholder="{{ __('common.search_placeholder') }}"
                class="pointer-events-auto w-full min-h-11 rounded-full border border-hb-border bg-hb-page py-2.5 ps-9 pe-3 text-sm text-hb-text placeholder:text-hb-muted focus:border-hb-blue focus:bg-hb-surface"
                autocomplete="off"
                aria-describedby="search-contract-note"
                readonly
                title="{{ __('common.search_preview_note') }}"
            >
        </label>
        <p id="search-contract-note" class="sr-only">{{ __('common.search_preview_note') }}</p>
    </div>

    <div class="flex items-center gap-1 sm:gap-2">
        @include('frontend.partials.theme-switch')
        @include('frontend.partials.language-switch')
        @include('frontend.partials.notifications-bell')

        <div class="relative">
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-hb-page focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40"
                data-dropdown-trigger
                aria-haspopup="true"
                aria-expanded="false"
                aria-controls="{{ $profilePanelId }}"
                id="{{ $profilePanelId }}-trigger"
            >
                @if (filled($topbarUser['avatar_url'] ?? null))
                    <img
                        src="{{ $topbarUser['avatar_url'] }}"
                        alt=""
                        class="h-8 w-8 rounded-full object-cover"
                        referrerpolicy="no-referrer"
                    >
                @else
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-hb-navy text-xs font-semibold text-white dark:bg-hb-blue dark:text-hb-page" aria-hidden="true">
                        {{ $topbarUser['initials'] }}
                    </span>
                @endif
                <span class="hidden min-w-0 sm:block">
                    <span class="block truncate text-sm font-medium text-hb-text">{{ $topbarUser['name'] }}</span>
                    <span class="block truncate text-[11px] text-hb-muted">{{ $topbarUser['role_label'] }}</span>
                </span>
                <x-icon.chevron class="hidden h-4 w-4 shrink-0 text-hb-muted sm:inline" />
            </button>

            <div
                id="{{ $profilePanelId }}"
                class="absolute end-0 z-50 mt-2 hidden w-64 overflow-hidden rounded-hb border border-hb-border bg-hb-surface shadow-hb-md"
                role="menu"
                aria-labelledby="{{ $profilePanelId }}-trigger"
                data-dropdown-panel
                data-account-menu
            >
                <div class="border-b border-hb-border px-4 py-3">
                    <p class="text-sm font-semibold text-hb-navy">{{ $topbarUser['name'] }}</p>
                    @if (filled($topbarUser['email'] ?? null))
                        <p class="mt-0.5 truncate text-xs text-hb-muted">{{ $topbarUser['email'] }}</p>
                    @endif
                    <p class="mt-1 text-xs font-medium text-hb-blue">{{ $topbarUser['role_label'] }}</p>
                </div>
                <ul class="py-1 text-sm">
                    <li>
                        @if ($profileRoute)
                            <a
                                href="{{ route($profileRoute) }}"
                                class="block px-4 py-2 text-hb-text hover:bg-hb-page focus-visible:bg-hb-page focus-visible:outline-none"
                                role="menuitem"
                            >
                                {{ __('common.profile_settings') }}
                            </a>
                        @else
                            <span class="block cursor-not-allowed px-4 py-2 text-hb-muted" role="menuitem" aria-disabled="true">
                                {{ __('common.profile_settings') }} <span class="text-[10px] uppercase">{{ __('common.soon') }}</span>
                            </span>
                        @endif
                    </li>
                    <li>
                        <a
                            href="{{ route($securityRoute) }}"
                            class="block px-4 py-2 text-hb-text hover:bg-hb-page focus-visible:bg-hb-page focus-visible:outline-none"
                            role="menuitem"
                        >
                            {{ __('common.account_security') }}
                        </a>
                    </li>
                    <li>
                        <span
                            class="block cursor-not-allowed px-4 py-2 text-hb-muted"
                            role="menuitem"
                            aria-disabled="true"
                            title="{{ __('common.notification_preferences_pending_hint') }}"
                        >
                            {{ __('common.notification_preferences') }}
                            <span class="ms-1 text-[10px] font-semibold uppercase tracking-wide">{{ __('common.pending_feature') }}</span>
                        </span>
                    </li>
                    <li class="border-t border-hb-border">
                        <form method="POST" action="{{ route('frontend.account.logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-start text-hb-error hover:bg-hb-page focus-visible:bg-hb-page focus-visible:outline-none" role="menuitem">
                                {{ __('common.sign_out') }}
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
