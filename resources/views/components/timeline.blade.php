@props([
    'steps' => [],
])

<ol {{ $attributes->merge(['class' => 'flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-center sm:gap-2']) }}>
    @foreach ($steps as $index => $step)
        @php
            $status = $step['status'] ?? 'pending'; // completed|current|pending
            $label = $step['label'] ?? 'Step';
            $isLast = $index === count($steps) - 1;

            $dotClass = match ($status) {
                'completed' => 'bg-hb-success text-white',
                'current' => 'bg-hb-blue text-white ring-4 ring-hb-info-soft',
                default => 'border border-hb-border bg-hb-surface text-hb-muted',
            };
            $labelClass = match ($status) {
                'completed' => 'text-hb-success',
                'current' => 'font-semibold text-hb-blue',
                default => 'text-hb-muted',
            };
        @endphp

        <li class="flex min-w-0 items-center gap-2">
            <span
                class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[11px] font-semibold {{ $dotClass }}"
                @if ($status === 'current') aria-current="step" @endif
            >
                @if ($status === 'completed')
                    ✓
                @elseif ($status === 'current')
                    ●
                @else
                    ○
                @endif
            </span>
            <span class="text-sm {{ $labelClass }}">{{ $label }}</span>

            @unless ($isLast)
                <span class="mx-1 hidden h-px w-8 bg-hb-border sm:inline-block" aria-hidden="true"></span>
            @endunless
        </li>
    @endforeach
</ol>
