{{--
    Shared sidebar navigation renderer.
    $eyebrow, $workspaceLabel, $items[] with keys: label, route?, pending?, active?
--}}
@php
    $eyebrow = $eyebrow ?? __('navigation.workspace');
    $workspaceLabel = $workspaceLabel ?? __('navigation.candidate.workspace');
    $items = $items ?? [];
@endphp

<nav class="flex h-full flex-col px-3" aria-label="{{ $eyebrow }}">
    <div class="px-2">
        <p class="text-[10px] font-semibold tracking-[0.14em] text-hb-nav-muted uppercase">{{ $eyebrow }}</p>
        <button
            type="button"
            class="mt-1 flex w-full items-center justify-between gap-2 rounded-md px-0 py-1 text-start text-xs text-hb-nav-muted hover:text-white"
            aria-label="{{ __('navigation.workspace_switcher') }}"
            disabled
        >
            <span>{{ $workspaceLabel }}</span>
            <span aria-hidden="true">⌄</span>
        </button>
    </div>

    <ul class="mt-4 flex-1 space-y-0.5 text-sm">
        @foreach ($items as $item)
            @php
                $label = $item['label'] ?? '';
                $routeName = $item['route'] ?? null;
                $pending = (bool) ($item['pending'] ?? false);
                $isActive = (bool) ($item['active'] ?? false);

                if (! $isActive && $routeName && \Illuminate\Support\Facades\Route::has($routeName)) {
                    $isActive = request()->routeIs($routeName);
                }

                $baseClass = 'flex items-center gap-2 rounded-md px-2 py-2';
                $activeClass = $isActive
                    ? 'bg-white/10 font-medium text-white'
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
                        <span class="w-3 shrink-0 text-center" aria-hidden="true">{{ $isActive ? '●' : '' }}</span>
                        <span class="min-w-0 flex-1">{{ $label }}</span>
                    </a>
                @endif
            </li>
        @endforeach
    </ul>

    <div class="mt-auto space-y-1 border-t border-white/10 px-2 pt-4 text-xs text-hb-nav-muted">
        <p>{{ __('common.help_support') }}</p>
        <p>{{ __('common.privacy_security') }}</p>
    </div>
</nav>
