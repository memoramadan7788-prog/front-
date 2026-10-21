{{--
    Shared modal structure (Figma dialogs / Page 19-style overlays).

    Usage (include):
        @include('layouts.modal', [
            'modalId' => 'apply-modal',
            'modalTitle' => 'Apply to role',
            'modalSize' => 'md', // sm|md|lg|xl
        ])
        ... put body via $modalBody HTML string, or nest with @section in a dedicated preview.

    Full interactive components land in Stage 2. This file defines the structural chrome only.
--}}
@php
    $modalId = $modalId ?? 'hb-modal';
    $modalTitle = $modalTitle ?? 'Dialog';
    $modalSize = $modalSize ?? 'md';
    $modalOpen = $modalOpen ?? false;
    $sizeClass = match ($modalSize) {
        'sm' => 'max-w-md',
        'lg' => 'max-w-3xl',
        'xl' => 'max-w-5xl',
        default => 'max-w-xl',
    };
@endphp

<div
    id="{{ $modalId }}"
    class="{{ $modalOpen ? 'flex' : 'hidden' }} fixed inset-0 z-50 items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $modalId }}-title"
    data-hb-modal
>
    <div class="absolute inset-0 bg-hb-navy/50" data-hb-modal-overlay tabindex="-1"></div>

    <div class="relative z-10 flex w-full {{ $sizeClass }} flex-col rounded-hb border border-hb-border bg-hb-surface shadow-hb-md">
        <div class="flex items-start justify-between gap-4 border-b border-hb-border px-5 py-4">
            <h2 id="{{ $modalId }}-title" class="text-base font-semibold text-hb-navy">
                {{ $modalTitle }}
            </h2>
            <button
                type="button"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-hb-muted hover:bg-hb-page hover:text-hb-text"
                data-hb-modal-close
                aria-label="Close dialog"
            >
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="px-5 py-4 text-sm text-hb-text">
            {!! $modalBody ?? '' !!}
        </div>

        @isset($modalFooter)
            <div class="flex items-center justify-end gap-2 border-t border-hb-border px-5 py-4">
                {!! $modalFooter !!}
            </div>
        @endisset
    </div>
</div>
