@props([
    'value' => 0,
    'max' => 100,
    'label' => null,
])

@php
    $max = max(1, (int) $max);
    $value = max(0, min((int) $value, $max));
    $percent = (int) round(($value / $max) * 100);
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if ($label)
        <div class="mb-1.5 flex items-center justify-between gap-3 text-xs font-medium text-hb-muted">
            <span>{{ $label }}</span>
            <span dir="ltr">{{ $percent }}%</span>
        </div>
    @endif
    <div
        class="h-2 overflow-hidden rounded-full bg-hb-border/60"
        role="progressbar"
        aria-valuemin="0"
        aria-valuemax="{{ $max }}"
        aria-valuenow="{{ $value }}"
        @if ($label) aria-label="{{ $label }}" @endif
    >
        <div class="h-full rounded-full bg-hb-blue transition-[width]" style="width: {{ $percent }}%"></div>
    </div>
</div>
