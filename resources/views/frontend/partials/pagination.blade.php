@if (! empty($meta) && ($meta['last_page'] ?? 1) > 1)
    @php
        $current = (int) $meta['current_page'];
        $last = (int) $meta['last_page'];
    @endphp
    <nav class="mt-4 flex items-center justify-between gap-3 text-sm" aria-label="{{ __('Pagination') }}">
        @if ($current > 1)
            <x-button variant="light" size="sm" :href="request()->fullUrlWithQuery(['page' => $current - 1])">{{ __('Previous') }}</x-button>
        @else
            <span></span>
        @endif
        <span class="text-hb-muted">{{ $current }} / {{ $last }}</span>
        @if ($current < $last)
            <x-button variant="light" size="sm" :href="request()->fullUrlWithQuery(['page' => $current + 1])">{{ __('Next') }}</x-button>
        @else
            <span></span>
        @endif
    </nav>
@endif
