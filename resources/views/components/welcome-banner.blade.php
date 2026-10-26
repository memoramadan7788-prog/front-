@props(['title' => null, 'subtitle' => null])

@php
    $name = trim((string) (auth()->user()?->name ?? ''));
    $first = $name !== '' ? explode(' ', $name)[0] : null;
@endphp

<section class="relative mb-6 overflow-hidden rounded-3xl bg-hb-sidebar p-7 text-white shadow-hb-md sm:p-9">
    <div class="pointer-events-none absolute -end-16 -top-20 h-64 w-64 rounded-full bg-hb-blue/35 blur-3xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -bottom-24 start-1/3 h-56 w-56 rounded-full bg-sky-400/20 blur-3xl" aria-hidden="true"></div>
    <div class="relative max-w-2xl">
        <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl">
            {{ $title ?? ($first ? __('Welcome back, :name', ['name' => $first]) : __('Welcome back')) }}
        </h2>
        @if ($subtitle)
            <p class="mt-2 text-sm leading-relaxed text-hb-nav-muted sm:text-base">{{ $subtitle }}</p>
        @endif
    </div>
</section>
