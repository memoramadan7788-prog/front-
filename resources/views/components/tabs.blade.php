@props([
    'id' => 'hb-tabs',
    'items' => [],
    'active' => null,
])

@php
    $tabItems = $items;
    $activeKey = $active ?? ($tabItems[0]['key'] ?? null);
@endphp

<div {{ $attributes->merge(['class' => 'w-full', 'data-tabs' => true]) }} id="{{ $id }}">
    <div class="border-b border-hb-border" role="tablist" aria-label="Content tabs">
        <div class="flex flex-wrap gap-1">
            @foreach ($tabItems as $index => $tab)
                @php
                    $key = (string) ($tab['key'] ?? $index);
                    $label = $tab['label'] ?? $key;
                    $selected = $key === (string) $activeKey;
                    $panelId = $id.'-panel-'.$key;
                    $tabId = $id.'-tab-'.$key;
                @endphp
                <button
                    type="button"
                    id="{{ $tabId }}"
                    role="tab"
                    aria-selected="{{ $selected ? 'true' : 'false' }}"
                    aria-controls="{{ $panelId }}"
                    tabindex="{{ $selected ? '0' : '-1' }}"
                    class="border-b-2 px-3 py-2.5 text-sm font-semibold transition-colors {{ $selected ? 'border-hb-blue text-hb-blue' : 'border-transparent text-hb-muted hover:text-hb-text' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="pt-4 space-y-0">
        {{ $slot }}
    </div>
</div>
