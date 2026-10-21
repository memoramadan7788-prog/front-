@php
    $current = app()->getLocale() === 'ar' ? 'ar' : 'en';
@endphp

<div
    class="inline-flex items-center gap-1 rounded-lg border border-hb-border bg-hb-surface p-0.5 text-xs font-semibold"
    role="group"
    aria-label="{{ __('common.language') }}"
>
    <a
        href="{{ route('locale.switch', 'en') }}"
        class="rounded-md px-2 py-1 {{ $current === 'en' ? 'bg-hb-info-soft text-hb-blue' : 'text-hb-muted hover:text-hb-text' }}"
        hreflang="en"
        lang="en"
        aria-current="{{ $current === 'en' ? 'true' : 'false' }}"
        data-lang-option="en"
    >
        EN
    </a>
    <a
        href="{{ route('locale.switch', 'ar') }}"
        class="rounded-md px-2 py-1 {{ $current === 'ar' ? 'bg-hb-info-soft text-hb-blue' : 'text-hb-muted hover:text-hb-text' }}"
        hreflang="ar"
        lang="ar"
        aria-current="{{ $current === 'ar' ? 'true' : 'false' }}"
        data-lang-option="ar"
    >
        AR
    </a>
</div>
