@props([
    'label',
    'value',
    'meta' => null,
    'href' => null,
])

@php
    $isArrow = trim((string) $value) === '→';
    $classes = 'hb-surface group relative block overflow-hidden p-6';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes.' transition duration-200 hover:-translate-y-1 hover:border-hb-blue/40 hover:shadow-hb-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40']) }}>
        <span class="absolute inset-x-0 top-0 h-1 origin-left scale-x-0 bg-hb-accent transition duration-300 group-hover:scale-x-100" aria-hidden="true"></span>
        <div class="flex items-start justify-between gap-3">
            <p class="text-sm font-semibold text-hb-navy">{{ $label }}</p>
            @if ($isArrow)
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-hb-info-soft text-hb-blue transition group-hover:bg-hb-accent group-hover:text-white" aria-hidden="true">
                    <span class="rtl:rotate-180">&rarr;</span>
                </span>
            @endif
        </div>
        @unless ($isArrow)
            <p class="mt-3 text-3xl font-semibold tracking-tight text-hb-navy">{{ $value }}</p>
        @endunless
        @if ($meta)
            <p class="mt-2 text-sm leading-relaxed text-hb-muted">{{ $meta }}</p>
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
