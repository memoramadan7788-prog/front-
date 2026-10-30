@php
    $meta = $meta ?? null;
    $current = (int) data_get($meta, 'current_page', 0);
    $last = (int) data_get($meta, 'last_page', 0);
    $total = data_get($meta, 'total');
    $from = data_get($meta, 'from');
    $to = data_get($meta, 'to');
    $aria = $ariaLabel ?? __('Pagination');
    $showStatus = $showStatus ?? false;
    $prevLabel = $prevLabel ?? __('Previous');
    $nextLabel = $nextLabel ?? __('Next');
@endphp

@if (! empty($meta) && ($last > 1 || ($showStatus && is_numeric($total) && (int) $total > 0)))
    <nav class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" aria-label="{{ $aria }}">
        @if ($showStatus && is_numeric($total))
            <p class="text-sm text-hb-muted" data-pagination-status>
                @if ($from !== null && $to !== null)
                    {{ __('public.jobs.pagination_status', ['from' => (int) $from, 'to' => (int) $to, 'total' => (int) $total]) }}
                @else
                    {{ __('public.jobs.match_count', ['count' => (int) $total]) }}
                @endif
            </p>
        @else
            <span></span>
        @endif

        @if ($last > 1)
            <div class="flex items-center gap-3 text-sm">
                @if ($current > 1)
                    <x-button variant="light" size="sm" :href="request()->fullUrlWithQuery(['page' => $current - 1])">{{ $prevLabel }}</x-button>
                @else
                    <span class="inline-flex min-w-[5.5rem]"></span>
                @endif
                <span class="text-hb-muted" data-pagination-pages>{{ $current }} / {{ $last }}</span>
                @if ($current < $last)
                    <x-button variant="light" size="sm" :href="request()->fullUrlWithQuery(['page' => $current + 1])">{{ $nextLabel }}</x-button>
                @else
                    <span class="inline-flex min-w-[5.5rem]"></span>
                @endif
            </div>
        @endif
    </nav>
@endif
