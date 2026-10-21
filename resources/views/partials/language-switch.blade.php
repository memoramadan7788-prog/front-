@php
    $current = app()->getLocale() === 'ar' ? 'ar' : 'en';
@endphp

<div
    class="inline-flex items-center gap-1 rounded-lg border border-hb-border bg-hb-surface p-0.5 text-xs font-semibold"
    role="group"
    aria-label="Language preview"
>
    <button
        type="button"
        class="rounded-md px-2 py-1 {{ $current === 'en' ? 'bg-hb-info-soft text-hb-blue' : 'text-hb-muted' }}"
        data-lang-option="en"
        aria-pressed="{{ $current === 'en' ? 'true' : 'false' }}"
    >
        EN
    </button>
    <button
        type="button"
        class="rounded-md px-2 py-1 {{ $current === 'ar' ? 'bg-hb-info-soft text-hb-blue' : 'text-hb-muted' }}"
        data-lang-option="ar"
        aria-pressed="{{ $current === 'ar' ? 'true' : 'false' }}"
    >
        AR
    </button>
</div>
