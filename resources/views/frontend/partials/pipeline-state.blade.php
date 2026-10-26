@props([
    'loadError' => null,
    'loadStatus' => null,
    'emptyTitle' => null,
    'emptyBody' => null,
    'ctaLabel' => null,
    'ctaHref' => null,
    'secondaryLabel' => null,
    'secondaryHref' => null,
])

@if (filled($loadError))
    <div class="rounded-xl border border-hb-error/30 bg-hb-error/5 px-5 py-6" role="alert">
        <p class="text-sm font-semibold text-hb-navy">{{ __('candidate.pipeline.load_error_title') }}</p>
        <p class="mt-2 text-sm text-hb-muted">{{ $loadError }}</p>
        @if ((int) $loadStatus === 403)
            <p class="mt-2 text-xs text-hb-muted">{{ __('candidate.pipeline.forbidden_hint') }}</p>
        @endif
        <div class="mt-4">
            <x-button variant="light" size="sm" :href="request()->url()">{{ __('candidate.pipeline.retry') }}</x-button>
        </div>
    </div>
@else
    <div class="rounded-xl border border-dashed border-hb-border bg-hb-page/40 px-5 py-10 text-center" role="status">
        <p class="text-sm font-semibold text-hb-navy">{{ $emptyTitle }}</p>
        @if (filled($emptyBody))
            <p class="mx-auto mt-2 max-w-md text-sm text-hb-muted">{{ $emptyBody }}</p>
        @endif
        <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
            @if (filled($ctaHref) && filled($ctaLabel))
                <x-button :href="$ctaHref">{{ $ctaLabel }}</x-button>
            @endif
            @if (filled($secondaryHref) && filled($secondaryLabel))
                <x-button variant="light" :href="$secondaryHref">{{ $secondaryLabel }}</x-button>
            @endif
        </div>
    </div>
@endif
