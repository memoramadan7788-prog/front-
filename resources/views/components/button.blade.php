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
        'primary' => 'bg-hb-blue text-white hover:bg-hb-blue/90 focus-visible:outline-hb-blue',
        'secondary' => 'border border-hb-border bg-hb-surface text-hb-text hover:bg-hb-page',
        'outline' => 'border border-hb-border bg-hb-surface text-hb-text hover:bg-hb-page',
        'ghost' => 'bg-transparent text-hb-text hover:bg-hb-page',
        'tertiary' => 'bg-transparent text-hb-blue hover:underline',
        'destructive' => 'bg-hb-error text-white hover:bg-hb-error/90 focus-visible:outline-hb-error',
    ];

    $sizes = [
        'sm' => 'rounded-md px-3 py-1.5 text-xs font-semibold',
        'md' => 'rounded-lg px-4 py-2.5 text-sm font-semibold',
        'lg' => 'rounded-lg px-5 py-3 text-base font-semibold',
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
