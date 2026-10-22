@props([
    'label' => null,
    'name' => null,
    'value' => '1',
])

@php
    $id = $attributes->get('id') ?? ($name ? 'check-'.$name : 'check-'.uniqid());
@endphp

<label for="{{ $id }}" class="inline-flex items-start gap-2 text-sm text-hb-text">
    <input
        id="{{ $id }}"
        type="checkbox"
        @if ($name) name="{{ $name }}" @endif
        value="{{ $value }}"
        {{ $attributes->merge(['class' => 'hb-checkbox mt-0.5']) }}
    >
    @if ($label)
        <span>{{ $label }}</span>
    @else
        <span>{{ $slot }}</span>
    @endif
</label>
