@props(['title' => null, 'subtitle' => null, 'compact' => false])

@php
    $name = trim((string) (auth()->user()?->name ?? ''));
    $first = $name !== '' ? explode(' ', $name)[0] : null;
@endphp

<section @class([
    'relative overflow-hidden rounded-2xl bg-hb-sidebar text-white shadow-hb-sm',
    'mb-0 px-4 py-3.5 sm:px-5 sm:py-4' => $compact,
    'mb-6 p-7 sm:p-9' => ! $compact,
])>
    @unless ($compact)
        <div class="pointer-events-none absolute -end-16 -top-20 h-64 w-64 rounded-full bg-hb-blue/35 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-24 start-1/3 h-56 w-56 rounded-full bg-sky-400/20 blur-3xl" aria-hidden="true"></div>
    @endunless
    <div class="relative max-w-2xl">
        <h2 @class([
            'font-semibold tracking-tight',
            'text-lg sm:text-xl' => $compact,
            'text-2xl sm:text-3xl' => ! $compact,
        ])>
            {{ $title ?? ($first ? __('Welcome back, :name', ['name' => $first]) : __('Welcome back')) }}
        </h2>
        @if ($subtitle)
            <p @class([
                'leading-relaxed text-hb-nav-muted',
                'mt-1 text-xs sm:text-sm' => $compact,
                'mt-2 text-sm sm:text-base' => ! $compact,
            ])>{{ $subtitle }}</p>
        @endif
    </div>
</section>
