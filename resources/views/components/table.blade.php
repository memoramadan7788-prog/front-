@props([
    'empty' => null,
])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-hb border border-hb-border bg-hb-surface']) }}>
    <div class="w-full overflow-x-auto">
        <table class="min-w-full divide-y divide-hb-border text-start text-sm">
            @isset($head)
                <thead class="bg-hb-page/80 text-xs font-semibold tracking-wide text-hb-muted uppercase">
                    <tr>
                        {{ $head }}
                    </tr>
                </thead>
            @endisset

            <tbody class="divide-y divide-hb-border bg-hb-surface text-hb-text">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if ($empty)
        <div class="border-t border-hb-border px-5 py-10 text-center">
            <p class="text-sm font-medium text-hb-navy">{{ $empty }}</p>
        </div>
    @endif

    @isset($emptyState)
        <div class="border-t border-hb-border px-5 py-10 text-center">
            {{ $emptyState }}
        </div>
    @endisset
</div>
