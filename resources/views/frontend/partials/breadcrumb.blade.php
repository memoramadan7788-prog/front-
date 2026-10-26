{{--
    Semantic breadcrumb trail.
    Expects $breadcrumb (string) and optional $uiRole.
--}}
@php
    use App\Support\Frontend\BreadcrumbNavigator;
    use App\Support\Frontend\WorkspaceNavigator;

    $breadcrumb = (string) ($breadcrumb ?? '');
    // Prefer layout-resolved shell; never trust ?role= query for navigation context.
    $uiRole = is_string($uiRole ?? null) && $uiRole !== ''
        ? $uiRole
        : WorkspaceNavigator::resolveShell();
    $segments = BreadcrumbNavigator::segments($breadcrumb, $uiRole);
@endphp

@if ($segments !== [])
    <nav class="mb-2" aria-label="{{ __('navigation.breadcrumb') }}">
        <ol class="flex flex-wrap items-center gap-x-1.5 gap-y-1 text-xs font-medium tracking-wide text-hb-muted uppercase">
            @foreach ($segments as $index => $segment)
                <li class="inline-flex min-w-0 items-center gap-x-1.5">
                    @if ($index > 0)
                        <span class="text-hb-muted/70" aria-hidden="true">/</span>
                    @endif
                    @if (! empty($segment['href']) && ! $segment['current'])
                        <a
                            href="{{ $segment['href'] }}"
                            class="truncate text-hb-muted underline-offset-2 hover:text-hb-blue hover:underline focus-visible:rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40"
                        >
                            {{ $segment['label'] }}
                        </a>
                    @else
                        <span
                            class="truncate {{ $segment['current'] ? 'text-hb-text' : 'text-hb-muted' }}"
                            @if ($segment['current']) aria-current="page" @endif
                        >
                            {{ $segment['label'] }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
