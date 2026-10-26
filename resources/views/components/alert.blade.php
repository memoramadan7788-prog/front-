@props([
    'variant' => 'info',
])

@php
    $variants = [
        'success' => 'border-hb-success/25 bg-hb-success-soft text-hb-success',
        'warning' => 'border-hb-pending/30 bg-hb-pending-soft text-hb-pending',
        'error' => 'border-hb-error/30 bg-hb-error-soft text-hb-error',
        'info' => 'border-hb-blue/25 bg-hb-info-soft text-hb-blue',
    ];
@endphp

<div
    {{ $attributes->merge([
        'class' => 'rounded-xl border px-4 py-3 text-sm shadow-hb-sm '.($variants[$variant] ?? $variants['info']),
        'role' => $attributes->get('role', 'status'),
    ]) }}
>
    {{ $slot }}
</div>
