@props([
    'name' => 'slot',
    'slots' => [],
    'value' => null,
    'label' => 'Available times',
])

<div {{ $attributes->merge(['class' => 'space-y-3', 'data-slot-picker' => true]) }}>
    <div class="flex items-center justify-between gap-3">
        <p class="text-sm font-semibold text-hb-navy">{{ $label }}</p>
        <span class="rounded-full bg-hb-pending-soft px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-hb-pending">
            Available times
        </span>
    </div>

    <input type="hidden" name="{{ $name }}" value="{{ $value }}" data-slot-value>

    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3" role="listbox" aria-label="{{ $label }}">
        @foreach ($slots as $slotItem)
            @php
                $slotValue = $slotItem['value'] ?? '';
                $slotLabel = $slotItem['label'] ?? $slotValue;
                $unavailable = (bool) ($slotItem['unavailable'] ?? false);
                $selected = ! $unavailable && (string) $value === (string) $slotValue;
                $state = $slotItem['state'] ?? ($unavailable ? 'taken' : 'available');
            @endphp

            <button
                type="button"
                role="option"
                data-slot-option
                data-slot-value="{{ $slotValue }}"
                data-slot-state="{{ $state }}"
                @if (! empty($slotItem['conflict_on_confirm']))
                    data-slot-conflict="1"
                @endif
                aria-pressed="{{ $selected ? 'true' : 'false' }}"
                @if ($unavailable)
                    disabled
                    aria-disabled="true"
                @endif
                @class([
                    'rounded-hb border px-3 py-3 text-start text-sm transition-colors',
                    'cursor-not-allowed border-hb-border bg-hb-page text-hb-muted opacity-60' => $unavailable,
                    'border-hb-blue bg-hb-info-soft ring-2 ring-hb-blue/30 text-hb-navy' => $selected && ! $unavailable,
                    'border-hb-border bg-hb-surface text-hb-text hover:border-hb-blue/50' => ! $selected && ! $unavailable,
                ])
            >
                <span class="font-medium">{{ $slotLabel }}</span>
                @if (! empty($slotItem['meta']))
                    <span class="mt-1 block text-xs text-hb-muted">{{ $slotItem['meta'] }}</span>
                @endif
                @if (! empty($slotItem['state_label']))
                    <span class="mt-1 block text-xs font-medium {{ $unavailable ? 'text-hb-error' : 'text-hb-muted' }}">{{ $slotItem['state_label'] }}</span>
                @elseif ($unavailable)
                    <span class="mt-1 block text-xs font-medium text-hb-error">{{ __('company.interviews.slot_unavailable') }}</span>
                @endif
            </button>
        @endforeach
    </div>
</div>
