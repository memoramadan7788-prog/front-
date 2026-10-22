@props([
    'lines' => 3,
])

<div {{ $attributes->merge(['class' => 'animate-pulse space-y-3', 'aria-hidden' => 'true']) }}>
    @for ($i = 0; $i < (int) $lines; $i++)
        <div @class([
            'h-3 rounded bg-hb-border/70',
            'w-3/4' => $i === 0,
            'w-full' => $i > 0 && $i < ((int) $lines - 1),
            'w-5/6' => $i === ((int) $lines - 1) && (int) $lines > 1,
        ])></div>
    @endfor
    <span class="sr-only">{{ __('common.loading') }}</span>
</div>
