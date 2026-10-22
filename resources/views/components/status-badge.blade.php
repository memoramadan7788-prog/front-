@props([
    'status' => 'applied',
])

@php
    $map = [
        'applied' => ['variant' => 'info', 'label' => __('common.lifecycle.applied')],
        'screening' => ['variant' => 'pending', 'label' => __('common.lifecycle.screening')],
        'interview' => ['variant' => 'info', 'label' => __('common.lifecycle.interview')],
        'offer' => ['variant' => 'pending', 'label' => __('common.lifecycle.offer')],
        'hired' => ['variant' => 'success', 'label' => __('common.lifecycle.hired')],
        'rejected' => ['variant' => 'error', 'label' => __('common.lifecycle.rejected')],
        'withdrawn' => ['variant' => 'neutral', 'label' => __('common.lifecycle.withdrawn')],
    ];
    $config = $map[$status] ?? $map['applied'];
    $label = trim((string) $slot) !== '' ? $slot : $config['label'];
@endphp

<x-badge :variant="$config['variant']" :dot="true" {{ $attributes }}>
    {{ $label }}
</x-badge>
