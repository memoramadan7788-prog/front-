@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'required' => false,
    'error' => null,
    'hint' => null,
])

@php
    $id = $attributes->get('id') ?? ($name ? 'field-'.$name : 'field-'.uniqid());
    $describedBy = collect([
        $error ? $id.'-error' : null,
        $hint ? $id.'-hint' : null,
    ])->filter()->implode(' ');
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
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
        @if ($name) name="{{ $name }}" @endif
        type="{{ $type }}"
        @if ($required) required @endif
        @if ($error) aria-invalid="true" @endif
        @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->except('class')->merge(['class' => 'hb-input']) }}
    >
    @if ($hint && ! $error)
        <p id="{{ $id }}-hint" class="mt-1.5 text-xs text-hb-muted">{{ $hint }}</p>
    @endif
    @if ($error)
        <p id="{{ $id }}-error" class="hb-field-error" role="alert">{{ $error }}</p>
    @endif
</div>
