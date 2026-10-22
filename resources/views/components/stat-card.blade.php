@props([
    'label',
    'value',
    'meta' => null,
    'href' => null,
])

@php
    $classes = 'hb-surface block p-5';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes.' transition hover:border-hb-blue/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40']) }}>
        <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ $label }}</p>
        <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ $value }}</p>
        @if ($meta)
            <p class="mt-2 text-sm text-hb-muted">{{ $meta }}</p>
        @endif
    </a>
@else
    <div {{ $attributes->merge(['class' => $classes]) }}>
        <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ $label }}</p>
        <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ $value }}</p>
        @if ($meta)
            <p class="mt-2 text-sm text-hb-muted">{{ $meta }}</p>
        @endif
    </div>
@endif
