@props([
    'steps' => [],
    'current' => 1,
    'variant' => 'markers',
])

@php
    /** @var array<int, array{label: string, description?: string}> $steps */
    $current = max(1, (int) $current);
    $variant = in_array($variant, ['markers', 'underline'], true) ? $variant : 'markers';
@endphp

<nav {{ $attributes->merge(['class' => 'w-full', 'aria-label' => 'Onboarding progress']) }} data-onboarding-stepper data-stepper-variant="{{ $variant }}">
    @if ($variant === 'underline')
        <ol class="flex flex-wrap items-center gap-x-1 gap-y-2 border-b border-hb-border text-sm">
            @foreach ($steps as $index => $step)
                @php
                    $number = $index + 1;
                    $isComplete = $number < $current;
                    $isCurrent = $number === $current;
                @endphp
                <li
                    class="relative"
                    data-stepper-item
                    data-step="{{ $number }}"
                    @if ($isCurrent) aria-current="step" @endif
                >
                    <span
                        @class([
                            'inline-flex items-center gap-1.5 px-1 pb-3 font-medium',
                            'border-b-2 border-hb-blue text-hb-navy' => $isCurrent,
                            'border-b-2 border-transparent text-hb-muted' => ! $isCurrent,
                        ])
                        data-stepper-label
                    >
                        @if ($isComplete)
                            <span class="text-hb-success" data-stepper-marker aria-hidden="true">✓</span>
                        @else
                            <span class="sr-only" data-stepper-marker>{{ $number }}</span>
                        @endif
                        {{ $step['label'] }}
                    </span>
                    @if (! $loop->last)
                        <span class="mx-2 hidden text-hb-border sm:inline" aria-hidden="true">—</span>
                    @endif
                </li>
            @endforeach
        </ol>
    @else
        <ol class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-2">
            @foreach ($steps as $index => $step)
                @php
                    $number = $index + 1;
                    $isComplete = $number < $current;
                    $isCurrent = $number === $current;
                @endphp
                <li
                    class="flex min-w-0 flex-1 items-start gap-2.5"
                    data-stepper-item
                    data-step="{{ $number }}"
                    @if ($isCurrent) aria-current="step" @endif
                >
                    <span
                        @class([
                            'inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-semibold',
                            'bg-hb-success text-white' => $isComplete,
                            'bg-hb-blue text-white' => $isCurrent,
                            'bg-hb-page text-hb-muted ring-1 ring-hb-border' => ! $isComplete && ! $isCurrent,
                        ])
                        data-stepper-marker
                        aria-hidden="true"
                    >
                        @if ($isComplete)
                            ✓
                        @else
                            {{ $number }}
                        @endif
                    </span>
                    <span class="min-w-0">
                        <span @class([
                            'block text-sm font-semibold',
                            'text-hb-navy' => $isCurrent || $isComplete,
                            'text-hb-muted' => ! $isCurrent && ! $isComplete,
                        ]) data-stepper-label>{{ $step['label'] }}</span>
                        @if (! empty($step['description']))
                            <span class="mt-0.5 block text-xs text-hb-muted">{{ $step['description'] }}</span>
                        @endif
                    </span>
                </li>
            @endforeach
        </ol>
    @endif
</nav>
