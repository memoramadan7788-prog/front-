@props([
    'label' => null,
    'name' => null,
    'required' => false,
    'error' => null,
])

@php
    $id = $attributes->get('id') ?? ($name ? 'select-'.$name : 'select-'.uniqid());
@endphp

<div class="w-full">
    @if ($label)
        <label for="{{ $id }}" class="hb-label">
            {{ $label }}
            @if ($required)
                <span class="text-hb-error" aria-hidden="true">*</span>
            @endif
        </label>
    @endif
    <select
        id="{{ $id }}"
        @if ($name) name="{{ $name }}" @endif
        @if ($required) required @endif
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->merge(['class' => 'hb-input']) }}
    >
        {{ $slot }}
    </select>
    @if ($error)
        <p id="{{ $id }}-error" class="hb-field-error" role="alert">{{ $error }}</p>
    @endif
</div>
