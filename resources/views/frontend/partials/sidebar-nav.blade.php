{{--
    Shared sidebar navigation renderer.
    $eyebrow, $workspaceLabel, $items[] with keys: label, route?, pending?, active?
--}}
@php
    $eyebrow = $eyebrow ?? __('navigation.workspace');
    $workspaceLabel = $workspaceLabel ?? __('navigation.candidate.workspace');
    $items = $items ?? [];
    $navListId = 'sidebar-nav-list-'.str_replace('.', '', uniqid('', true));
@endphp

<nav class="flex h-full flex-col px-3" aria-label="{{ $eyebrow }}">
    <div class="px-2">
        <p class="text-[10px] font-semibold tracking-[0.14em] text-hb-nav-muted uppercase">{{ $eyebrow }}</p>
        <button
            type="button"
            class="mt-1 flex w-full items-center justify-between gap-2 rounded-md px-0 py-1 text-start text-xs text-hb-nav-muted transition-colors hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/30"
            data-sidebar-nav-toggle
            aria-expanded="true"
            aria-controls="{{ $navListId }}"
            aria-label="{{ __('navigation.toggle_nav', ['workspace' => $workspaceLabel]) }}"
        >
            <span class="min-w-0 flex-1 truncate">{{ $workspaceLabel }}</span>
            <x-icon.chevron class="h-3.5 w-3.5 shrink-0 text-current" />
        </button>
    </div>

    <ul
        id="{{ $navListId }}"
        class="mt-4 flex-1 space-y-0.5 text-sm"
        data-sidebar-nav-list
    >
        @foreach ($items as $item)
            @php
                $label = $item['label'] ?? '';
                $routeName = $item['route'] ?? null;
                $pending = (bool) ($item['pending'] ?? false);
                $isActive = (bool) ($item['active'] ?? false);

                if (! $isActive && $routeName && \Illuminate\Support\Facades\Route::has($routeName)) {
                    $isActive = request()->routeIs($routeName);
                }

                $baseClass = 'flex items-center gap-2 rounded-xl px-3 py-2.5 transition-colors';
                $activeClass = $isActive
                    ? 'bg-white/10 font-semibold text-white shadow-sm ring-1 ring-white/15'
                    : 'text-hb-nav-muted hover:bg-white/5 hover:text-white';
            @endphp

            <li>
                @if ($pending || ! $routeName || ! \Illuminate\Support\Facades\Route::has($routeName))
                    <span
                        class="{{ $baseClass }} cursor-not-allowed text-hb-nav-muted/70"
                        title="{{ __('navigation.pending_route') }}"
                        aria-disabled="true"
                    >
                        <span class="w-3 shrink-0 text-center" aria-hidden="true">{{ $isActive ? '●' : '' }}</span>
                        <span class="min-w-0 flex-1">{{ $label }}</span>
                        <span class="rounded bg-white/10 px-1.5 py-0.5 text-[10px] uppercase tracking-wide text-hb-nav-muted">{{ __('common.soon') }}</span>
                    </span>
                @else
                    <a
                        href="{{ route($routeName, $item['params'] ?? []) }}"
                        class="{{ $baseClass }} {{ $activeClass }}"
                        @if ($isActive) aria-current="page" @endif
                    >
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $isActive ? 'bg-sky-400' : 'bg-white/20' }}" aria-hidden="true"></span>
                        <span class="min-w-0 flex-1">{{ $label }}</span>
                    </a>
                @endif
            </li>
        @endforeach
    </ul>

    {{-- Help / Privacy intentionally omitted until real destinations exist --}}
</nav>
