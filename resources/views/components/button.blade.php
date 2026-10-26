@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'loading' => false,
    'disabled' => false,
])

@php
    $variants = [
        'primary' => 'bg-hb-accent text-white shadow-sm hover:bg-hb-accent-dark focus-visible:outline-hb-blue',
        'cta' => 'bg-hb-accent text-white shadow-sm hover:bg-hb-accent-dark focus-visible:outline-hb-blue',
        'light' => 'border border-hb-border bg-white text-hb-navy hover:bg-hb-page',
        'hall-ghost' => 'border border-white/70 bg-white/10 text-white backdrop-blur hover:bg-white/25',
        'secondary' => 'border border-hb-blue bg-hb-surface text-hb-blue hover:bg-hb-info-soft focus-visible:outline-hb-blue',
        'outline' => 'border border-hb-blue bg-hb-surface text-hb-blue hover:bg-hb-info-soft focus-visible:outline-hb-blue',
        'ghost' => 'bg-transparent text-hb-text hover:bg-hb-page',
        'tertiary' => 'bg-transparent text-hb-blue hover:underline',
        'destructive' => 'bg-hb-error text-white hover:bg-hb-error/90 focus-visible:outline-hb-error',
    ];

    $sizes = [
        'sm' => 'min-h-9 rounded-full px-3 py-1.5 text-xs font-semibold',
        'md' => 'min-h-11 rounded-full px-4 py-2.5 text-sm font-semibold',
        'lg' => 'min-h-12 rounded-full px-5 py-3 text-base font-semibold',
    ];

    $isDisabled = $disabled || $loading;
    $classes = trim(implode(' ', [
        'inline-flex items-center justify-center gap-2 transition-colors focus-visible:outline-2 focus-visible:outline-offset-2',
        $variants[$variant] ?? $variants['primary'],
        $sizes[$size] ?? $sizes['md'],
        $isDisabled ? 'cursor-not-allowed opacity-50' : '',
    ]));
@endphp

@if ($href && ! $isDisabled)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($loading)
            <span class="inline-block h-3.5 w-3.5 animate-spin rounded-full border-2 border-current border-r-transparent" aria-hidden="true"></span>
            <span class="sr-only">Loading</span>
        @endif
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        @disabled($isDisabled)
        @if ($loading) aria-busy="true" @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if ($loading)
            <span class="inline-block h-3.5 w-3.5 animate-spin rounded-full border-2 border-current border-r-transparent" aria-hidden="true"></span>
            <span class="sr-only">Loading</span>
        @endif
        {{ $slot }}
    </button>
@endif
