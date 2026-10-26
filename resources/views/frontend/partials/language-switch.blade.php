@php
    use App\Support\Frontend\SafeInternalRedirect;

    $current = app()->getLocale() === 'ar' ? 'ar' : 'en';
    // Preserve the active page; LocaleController re-validates this target.
    $redirect = SafeInternalRedirect::sanitize(request()->getRequestUri());
@endphp

<div
    class="inline-flex items-center gap-1 rounded-lg border border-hb-border bg-hb-surface p-0.5 text-xs font-semibold"
    role="group"
    aria-label="{{ __('common.language') }}"
    data-language-switch
>
    <a
        href="{{ route('frontend.locale.switch', ['locale' => 'en', 'redirect' => $redirect]) }}"
        class="rounded-md px-2 py-1 {{ $current === 'en' ? 'bg-hb-info-soft text-hb-blue' : 'text-hb-muted hover:text-hb-text' }}"
        hreflang="en"
        lang="en"
        aria-current="{{ $current === 'en' ? 'true' : 'false' }}"
        aria-label="{{ __('common.language_english') }}"
        data-lang-option="en"
    >
        EN
    </a>
    <a
        href="{{ route('frontend.locale.switch', ['locale' => 'ar', 'redirect' => $redirect]) }}"
        class="rounded-md px-2 py-1 {{ $current === 'ar' ? 'bg-hb-info-soft text-hb-blue' : 'text-hb-muted hover:text-hb-text' }}"
        hreflang="ar"
        lang="ar"
        aria-current="{{ $current === 'ar' ? 'true' : 'false' }}"
        aria-label="{{ __('common.language_arabic') }}"
        data-lang-option="ar"
    >
        AR
    </a>
</div>
