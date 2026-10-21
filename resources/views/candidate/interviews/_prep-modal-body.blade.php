@php
    /** @var array<string, mixed>|null $interview */
@endphp

@if ($interview)
    <p class="text-sm text-hb-muted">
        {{ __('candidate.interviews.prep_for') }} <span class="font-medium text-hb-navy">{{ $interview['title'] }}</span>
        · {{ $interview['company'] }} · {{ $interview['code'] }}
    </p>
    <ul class="mt-4 list-disc space-y-2 ps-5 text-sm text-hb-text">
        <li>Review the role description and recent product updates for {{ $interview['company'] }}.</li>
        <li>Choose two portfolio projects that show problem framing and design decisions.</li>
        <li>Test camera, microphone, and a quiet space 15 minutes before the call.</li>
        <li>Prepare questions for {{ $interview['interviewer_short'] ?? 'your interviewer' }}.</li>
    </ul>
    <p class="mt-4 text-xs text-hb-muted">
        {{ __('candidate.interviews.prep_footer') }}
    </p>
@else
    <p class="text-sm text-hb-muted">{{ __('candidate.interviews.prep_select_interview') }}</p>
@endif
