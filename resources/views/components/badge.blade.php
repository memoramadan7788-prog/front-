@props([
    'variant' => 'neutral',
    'dot' => false,
])

@php
    $variants = [
        'success' => 'bg-hb-success-soft text-hb-success',
        'pending' => 'bg-hb-pending-soft text-hb-pending',
        'error' => 'bg-hb-error-soft text-hb-error',
        'info' => 'bg-hb-info-soft text-hb-blue',
        'neutral' => 'bg-hb-page text-hb-muted border border-hb-border',
    ];
    $dotColors = [
        'success' => 'bg-hb-success',
        'pending' => 'bg-hb-pending',
        'error' => 'bg-hb-error',
        'info' => 'bg-hb-blue',
        'neutral' => 'bg-hb-muted',
    ];
@endphp

<span {{ $attributes->merge([
    'class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium '.($variants[$variant] ?? $variants['neutral']),
]) }}>
    @if ($dot)
        <span class="h-1.5 w-1.5 rounded-full {{ $dotColors[$variant] ?? $dotColors['neutral'] }}" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>
