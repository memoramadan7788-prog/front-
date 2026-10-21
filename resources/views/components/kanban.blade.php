@props([
    'columns' => [],
    'footer' => null,
])

<div {{ $attributes->merge(['class' => 'space-y-4']) }}>
    <div class="flex gap-4 overflow-x-auto pb-2">
        @foreach ($columns as $column)
            @php
                $title = $column['title'] ?? 'Column';
                $count = $column['count'] ?? count($column['cards'] ?? []);
                $cards = $column['cards'] ?? [];
            @endphp

            <section class="flex w-72 shrink-0 flex-col rounded-hb bg-hb-page/80 p-3" aria-label="{{ $title }}">
                <header class="mb-3 flex items-center justify-between px-1">
                    <h3 class="text-sm font-semibold text-hb-navy">{{ $title }}</h3>
                    <span class="text-xs font-medium text-hb-muted">{{ $count }}</span>
                </header>

                <ul class="flex flex-1 flex-col gap-3">
                    @forelse ($cards as $card)
                        <li>
                            <article @class([
                                'rounded-hb border bg-hb-surface p-3 shadow-hb-sm',
                                'border-hb-blue ring-1 ring-hb-blue/20' => ! empty($card['highlighted']),
                                'border-hb-border' => empty($card['highlighted']),
                            ])>
                                <h4 class="text-sm font-semibold text-hb-text">{{ $card['name'] ?? 'Candidate' }}</h4>
                                @if (! empty($card['meta']))
                                    <p class="mt-1 text-xs text-hb-muted">{{ $card['meta'] }}</p>
                                @endif
                                @if (! empty($card['source']))
                                    <p class="mt-2 text-xs text-hb-muted">
                                        <span class="me-1 inline-block h-1.5 w-1.5 rounded-full bg-hb-muted align-middle" aria-hidden="true"></span>
                                        {{ $card['source'] }}
                                    </p>
                                @endif
                                @if (! empty($card['owner']))
                                    <p class="mt-2 text-xs text-hb-muted">{{ $card['owner'] }}</p>
                                @endif
                                @if (! empty($card['flag']))
                                    <p class="mt-2 text-xs font-medium text-hb-blue">{{ $card['flag'] }}</p>
                                @endif
                                @if (! empty($card['badge']))
                                    <div class="mt-2">
                                        <x-badge :variant="$card['badge']['variant'] ?? 'neutral'" :dot="true">
                                            {{ $card['badge']['label'] ?? '' }}
                                        </x-badge>
                                    </div>
                                @endif
                            </article>
                        </li>
                    @empty
                        <li class="rounded-hb border border-dashed border-hb-border px-3 py-6 text-center text-xs text-hb-muted">
                            No candidates
                        </li>
                    @endforelse
                </ul>
            </section>
        @endforeach
    </div>

    @if ($footer)
        <div class="flex flex-col gap-2 text-sm text-hb-muted sm:flex-row sm:items-center sm:justify-between">
            <p>{{ $footer }}</p>
            {{ $slot }}
        </div>
    @elseif (trim((string) $slot) !== '')
        {{ $slot }}
    @endif
</div>
