@props([
    'class' => 'h-3.5 w-3.5',
])

{{-- Chevron-down path; rotation driven by parent [aria-expanded] rules in app.css. --}}
<svg {{ $attributes->merge(['class' => trim($class.' transition-transform duration-200 ease-out'), 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true', 'data-chevron' => true]) }}>
    <path d="m6 9 6 6 6-6" />
</svg>

