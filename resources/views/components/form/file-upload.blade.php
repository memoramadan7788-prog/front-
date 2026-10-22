@props([
    'label' => null,
    'name' => null,
    'required' => false,
    'error' => null,
    'hint' => null,
])

@php
    $id = $attributes->get('id') ?? ($name ? 'file-'.$name : 'file-'.uniqid());
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
    <input
        id="{{ $id }}"
        type="file"
        @if ($name) name="{{ $name }}" @endif
        @if ($required) required @endif
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->merge(['class' => 'hb-input file:me-3 file:rounded-md file:border-0 file:bg-hb-info-soft file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-hb-blue']) }}
    >
    @if ($hint && ! $error)
        <p class="mt-1.5 text-xs text-hb-muted">{{ $hint }}</p>
    @endif
    @if ($error)
        <p id="{{ $id }}-error" class="hb-field-error" role="alert">{{ $error }}</p>
    @endif
</div>
