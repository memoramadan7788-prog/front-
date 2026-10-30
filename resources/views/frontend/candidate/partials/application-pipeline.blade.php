@php
    $pipeline = is_array($pipeline ?? null) ? $pipeline : [];
    $mode = (string) ($pipeline['mode'] ?? 'unknown');
    $stages = is_array($pipeline['stages'] ?? null) ? $pipeline['stages'] : [];
    $compact = (bool) ($compact ?? true);
    $stageLabel = function (string $key) {
        $full = 'candidate.analytics.status.'.$key;
        $translated = __($full);

        return $translated !== $full ? $translated : $key;
    };
@endphp

@if ($mode === 'unknown' || $stages === [])
    <span class="text-xs text-hb-muted" data-pipeline-mode="unknown">{{ __('candidate.applications.pipeline_unavailable') }}</span>
@elseif ($mode === 'terminal')
    @php $current = (string) ($pipeline['current'] ?? ''); @endphp
    <div class="hb-apps-pipeline hb-apps-pipeline--terminal inline-flex items-center gap-2" data-pipeline-mode="terminal" data-pipeline-current="{{ $current }}" title="{{ __('candidate.applications.pipeline_snapshot_title') }}">
        <span class="hb-apps-pipeline__dot hb-apps-pipeline__dot--current" aria-hidden="true"></span>
        <span class="text-xs font-medium text-hb-navy">{{ $stageLabel($current) }}</span>
        <span class="sr-only">{{ __('candidate.applications.pipeline_current_sr', ['stage' => $stageLabel($current)]) }}</span>
    </div>
@else
    <ol
        class="hb-apps-pipeline {{ $compact ? 'hb-apps-pipeline--compact' : 'hb-apps-pipeline--full' }} flex items-center gap-1"
        data-pipeline-mode="snapshot"
        data-pipeline-current="{{ $pipeline['current'] ?? '' }}"
        aria-label="{{ __('candidate.applications.pipeline_snapshot_title') }}"
    >
        @foreach ($stages as $stage)
            @php
                $key = (string) ($stage['key'] ?? '');
                $state = (string) ($stage['state'] ?? 'idle');
                $isCurrent = $state === 'current';
            @endphp
            <li class="flex items-center gap-1">
                <span
                    class="hb-apps-pipeline__dot {{ $isCurrent ? 'hb-apps-pipeline__dot--current' : 'hb-apps-pipeline__dot--idle' }}"
                    @if ($isCurrent) aria-current="step" @endif
                    title="{{ $stageLabel($key) }}"
                ></span>
                @unless ($compact)
                    <span class="text-[11px] {{ $isCurrent ? 'font-semibold text-hb-navy' : 'text-hb-muted' }}">{{ $stageLabel($key) }}</span>
                @endunless
                @if (! $loop->last)
                    <span class="hb-apps-pipeline__rail" aria-hidden="true"></span>
                @endif
            </li>
        @endforeach
        <span class="sr-only">{{ __('candidate.applications.pipeline_current_sr', ['stage' => $stageLabel((string) ($pipeline['current'] ?? ''))]) }}</span>
    </ol>
@endif
