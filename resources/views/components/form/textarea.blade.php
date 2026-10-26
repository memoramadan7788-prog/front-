@props([
    'label' => null,
    'name' => null,
    'required' => false,
    'error' => null,
    'hint' => null,
    'rows' => 4,
])

@php
    $id = $attributes->get('id') ?? ($name ? 'textarea-'.$name : 'textarea-'.uniqid());
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
    <textarea
        id="{{ $id }}"
        @if ($name) name="{{ $name }}" @endif
        rows="{{ $rows }}"
        @if ($required) required @endif
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->merge(['class' => 'hb-input']) }}
    >{{ $slot }}</textarea>
    @if ($hint && ! $error)
        <p class="mt-1.5 text-xs text-hb-muted">{{ $hint }}</p>
    @endif
    @if ($error)
        <p id="{{ $id }}-error" class="hb-field-error" role="alert">{{ $error }}</p>
    @endif
</div>
