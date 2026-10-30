@props([
    'label',
    'value' => '—',
    'meta' => null,
    'stat' => null,
    'href' => null,
    'available' => true,
    'icon' => 'chart',
    'kpiKey' => null,
    'tone' => 'blue',
    'spark' => null,
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

    $tones = [
        'blue' => 'bg-sky-50 text-sky-600 dark:bg-sky-950/50 dark:text-sky-300',
        'amber' => 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-300',
        'emerald' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-300',
        'rose' => 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-300',
        'violet' => 'bg-violet-50 text-violet-600 dark:bg-violet-950/40 dark:text-violet-300',
        'teal' => 'bg-teal-50 text-teal-600 dark:bg-teal-950/40 dark:text-teal-300',
        'slate' => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
    ];
    $toneClass = $available ? ($tones[$tone] ?? $tones['blue']) : $tones['slate'];

    $classes = 'hb-surface hb-kpi-card group relative flex h-full flex-col overflow-hidden p-4 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40 sm:p-4.5';
    $kpiAttrs = [];
    if (filled($kpiKey)) {
        $kpiAttrs['data-kpi'] = (string) $kpiKey;
        $kpiAttrs['data-kpi-available'] = $available ? '1' : '0';
        $kpiAttrs['data-kpi-value'] = (string) $value;
    }

    $sparkValues = [];
    if ($available && is_array($spark)) {
        foreach ($spark as $point) {
            if (is_numeric($point)) {
                $sparkValues[] = max(0, (float) $point);
            }
        }
    }
    $sparkMax = $sparkValues !== [] ? max(max($sparkValues), 1) : 1;
    $sparkTone = match ($tone) {
        'amber' => 'hb-kpi-spark--amber',
        'emerald' => 'hb-kpi-spark--emerald',
        'rose' => 'hb-kpi-spark--rose',
        'violet' => 'hb-kpi-spark--violet',
        'teal' => 'hb-kpi-spark--teal',
        default => 'hb-kpi-spark--blue',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(array_merge(['class' => $classes.' transition duration-200 hover:-translate-y-0.5 hover:border-hb-blue/35 hover:shadow-hb-md'], $kpiAttrs)) }}>
@else
    <div {{ $attributes->merge(array_merge(['class' => $classes], $kpiAttrs)) }}>
@endif
        <div class="flex items-start justify-between gap-2">
            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-hb-muted">{{ $label }}</p>
            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full {{ $toneClass }}" aria-hidden="true">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $iconPath !!}</svg>
            </span>
        </div>

        <p class="mt-2.5 text-2xl font-bold tracking-tight text-hb-navy tabular-nums sm:text-[1.75rem] sm:leading-none" @unless($available) aria-label="{{ __('candidate.kpi.unavailable') }}" @endunless>{{ $value }}</p>

        @if ($available && filled($stat))
            <p class="mt-1.5 line-clamp-1 text-[11px] font-medium text-hb-muted">{{ $stat }}</p>
        @elseif ($available && filled($meta))
            <p class="mt-1.5 line-clamp-1 text-[11px] text-hb-muted">{{ $meta }}</p>
        @elseif (! $available)
            <p class="mt-1.5 line-clamp-2 text-[11px] font-medium text-hb-muted">{{ $meta ?: __('candidate.kpi.unavailable') }}</p>
        @endif

        @if ($sparkValues !== [])
            <div class="hb-kpi-spark {{ $sparkTone }} mt-auto flex items-end gap-0.5 pt-2.5" aria-hidden="true" data-kpi-spark>
                @foreach ($sparkValues as $point)
                    @php $h = (int) max(14, round(($point / $sparkMax) * 100)); @endphp
                    <span style="height: {{ $h }}%"></span>
                @endforeach
            </div>
        @else
            <div class="mt-auto pt-2.5" aria-hidden="true"></div>
        @endif
@if ($href)
    </a>
@else
    </div>
@endif
