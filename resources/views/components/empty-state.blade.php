@props([
    'title',
    'actionLabel' => null,
    'actionHref' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-6 py-12 text-center']) }}>
    <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-hb-info-soft text-hb-blue" aria-hidden="true">
        <span class="text-xl font-semibold">⌀</span>
    </div>
    <h2 class="text-base font-semibold text-hb-navy">{{ $title }}</h2>
    @if (trim((string) $slot) !== '')
        <p class="mt-2 max-w-md text-sm leading-normal text-hb-muted">{{ $slot }}</p>
    @endif
    @if ($actionLabel && $actionHref)
        <a href="{{ $actionHref }}" class="hb-btn-primary mt-5">{{ $actionLabel }}</a>
    @endif
</div>
