@props([
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'hb-surface overflow-hidden']) }}>
    @isset($header)
        <div class="border-b border-hb-border px-5 py-4">
            {{ $header }}
        </div>
    @endisset

    <div @class(['px-5 py-4' => $padding])>
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="border-t border-hb-border px-5 py-4">
            {{ $footer }}
        </div>
    @endisset
</div>
