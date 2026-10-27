@props([
    'label',
    'value' => '—',
    'meta' => null,
    'href' => null,
    'available' => true,
    'icon' => 'chart',
    'kpiKey' => null,
])

@php
    $icons = [
        'applications' => '<path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/>',
        'progress' => '<path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/>',
        'interviews' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>',
        'offers' => '<path d="M20 12v7a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-7"/><path d="M12 15V3"/><path d="m7 8 5-5 5 5"/>',
        'assessments' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="m9 15 2 2 4-4"/>',
        'profile' => '<path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/>',
        'chart' => '<path d="M3 3v18h18"/><path d="M7 14v4"/><path d="M12 10v8"/><path d="M17 6v12"/>',
    ];
    $iconPath = $icons[$icon] ?? $icons['chart'];
    $classes = 'hb-surface group relative block overflow-hidden p-5 sm:p-6 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40';
    $kpiAttrs = [];
    if (filled($kpiKey)) {
        $kpiAttrs['data-kpi'] = (string) $kpiKey;
        $kpiAttrs['data-kpi-available'] = $available ? '1' : '0';
        $kpiAttrs['data-kpi-value'] = (string) $value;
    }
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(array_merge(['class' => $classes.' transition duration-200 hover:-translate-y-0.5 hover:border-hb-blue/40 hover:shadow-hb-md'], $kpiAttrs)) }}>
        <span class="absolute inset-x-0 top-0 h-1 origin-left scale-x-0 bg-hb-accent transition duration-300 group-hover:scale-x-100" aria-hidden="true"></span>
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-sm font-semibold text-hb-navy">{{ $label }}</p>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-hb-navy tabular-nums" @unless($available) aria-label="{{ __('candidate.kpi.unavailable') }}" @endunless>{{ $value }}</p>
                @if ($meta)
                    <p class="mt-2 text-sm leading-relaxed text-hb-muted">{{ $meta }}</p>
                @endif
            </div>
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-hb-info-soft text-hb-blue" aria-hidden="true">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $iconPath !!}</svg>
            </span>
        </div>
    </a>
@else
    <div {{ $attributes->merge(array_merge(['class' => $classes], $kpiAttrs)) }}>
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-sm font-semibold text-hb-navy">{{ $label }}</p>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-hb-navy tabular-nums">{{ $value }}</p>
                @if ($meta)
                    <p class="mt-2 text-sm leading-relaxed text-hb-muted">{{ $meta }}</p>
                @endif
            </div>
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-hb-info-soft text-hb-blue" aria-hidden="true">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $iconPath !!}</svg>
            </span>
        </div>
    </div>
@endif
