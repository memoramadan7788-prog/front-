@php
    $themeOptions = [
        'light' => __('common.theme_light'),
        'dark' => __('common.theme_dark'),
        'system' => __('common.theme_system'),
    ];
@endphp

<div
    class="inline-flex items-center gap-1 rounded-lg border border-hb-border bg-hb-surface p-0.5 text-xs font-semibold"
    role="group"
    aria-label="{{ __('common.theme_switch') }}"
    data-theme-switch
>
    @foreach ($themeOptions as $value => $label)
        <button
            type="button"
            class="rounded-md px-2 py-1 text-hb-muted hover:text-hb-text"
            data-theme-option="{{ $value }}"
            aria-pressed="false"
            title="{{ $label }}"
        >
            @if ($value === 'light')
                <span class="sr-only">{{ $label }}</span>
                <span aria-hidden="true">☀</span>
            @elseif ($value === 'dark')
                <span class="sr-only">{{ $label }}</span>
                <span aria-hidden="true">☾</span>
            @else
                <span class="sr-only">{{ $label }}</span>
                <span aria-hidden="true">◐</span>
            @endif
        </button>
    @endforeach
</div>
