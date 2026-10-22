@php
    /** @var array<string, mixed> $interview */
@endphp

<section class="hb-surface p-5 sm:p-6" aria-labelledby="interview-detail-heading" data-interview-card="{{ $interview['id'] }}">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 id="interview-detail-heading" class="text-lg font-semibold text-hb-navy">{{ $interview['title'] }}</h2>
            <p class="mt-1 text-sm text-hb-muted">
                {{ $interview['company'] }} · {{ $interview['role'] }} · {{ $interview['code'] }}
            </p>
        </div>
        <x-badge
            :variant="match ($interview['status_key'] ?? '') {
                'completed' => 'success',
                'cancelled' => 'neutral',
                default => 'info',
            }"
            :dot="true"
        >{{ $interview['status'] }}</x-badge>
        @if (! empty($interview['round_label']))
            <x-badge variant="neutral" :dot="true">{{ $interview['round_label'] }}</x-badge>
        @endif
    </div>

    <div class="mt-5 space-y-2 text-sm text-hb-text">
        <p class="text-base font-semibold text-hb-navy">{{ $interview['when'] }}</p>
        <p class="text-hb-muted">{{ $interview['time'] }}</p>
        <p class="pt-2">{{ $interview['interviewer'] ?? $interview['interviewer_short'] ?? '' }}</p>
        <p class="text-hb-muted">{{ $interview['format'] ?? '' }}</p>
        <p class="text-hb-muted">{{ $interview['type'] ?? 'Remote' }} · {{ $interview['duration'] ?? '' }}</p>
        @if (! empty($interview['timezone_label']))
            <p class="text-hb-muted">{{ __('candidate.interviews.timezone_label', ['tz' => $interview['timezone_label']]) }}</p>
        @endif
    </div>

    @if (($interview['scope'] ?? '') === 'upcoming')
        <div class="mt-4 rounded-lg border border-hb-border bg-hb-page px-3 py-3 text-sm" data-reschedule-eligibility data-can-reschedule="{{ ! empty($interview['can_reschedule']) ? '1' : '0' }}">
            <p class="font-semibold text-hb-navy">{{ __('candidate.interviews.scheduling_rules') }}</p>
            <p class="mt-1 text-hb-muted">{{ $interview['cutoff_note'] ?? '' }}</p>
            <p class="mt-1 text-hb-text">{{ $interview['reschedule_reason'] ?? '' }}</p>
            <p class="mt-2 text-xs text-hb-muted">{{ $interview['authority_note'] ?? '' }}</p>
        </div>
        <div class="mt-5 flex flex-wrap gap-2">
            <button type="button" class="hb-btn-primary" data-open-modal="interview-prep-modal">{{ __('candidate.interviews.view_preparation') }}</button>
            @if (! empty($interview['can_reschedule']))
                <button type="button" class="hb-btn-outline" data-open-modal="interview-reschedule-modal" data-reschedule-open>
                    {{ __('candidate.interviews.reschedule') }}
                </button>
            @else
                <button
                    type="button"
                    class="hb-btn-outline"
                    disabled
                    data-reschedule-blocked
                    title="{{ $interview['reschedule_reason'] ?? '' }}"
                >
                    {{ __('candidate.interviews.reschedule_unavailable_short') }}
                </button>
            @endif
        </div>
    @elseif (($interview['status_key'] ?? '') === 'cancelled')
        <x-alert variant="info" class="mt-5" data-interview-cancelled>
            <p class="font-semibold text-hb-navy">{{ __('candidate.interviews.cancelled_title') }}</p>
            <p class="mt-1 text-hb-text">{{ $interview['note'] ?? __('candidate.interviews.cancelled_body') }}</p>
        </x-alert>
    @endif

    @if (! empty($interview['prep']))
        <div class="mt-6 border-t border-hb-border pt-5">
            <h3 class="text-base font-semibold text-hb-navy">{{ __('candidate.interviews.preparation_checklist') }}</h3>
            <ul class="mt-3 space-y-2" data-prep-checklist data-prep-storage="hb_interview_prep_{{ $interview['id'] }}">
                @foreach ($interview['prep'] as $index => $item)
                    <li>
                        <label class="flex items-start gap-3 text-sm text-hb-text">
                            <input
                                type="checkbox"
                                class="hb-checkbox mt-0.5"
                                data-prep-item="{{ $index }}"
                                @checked(! empty($item['done']))
                            >
                            <span>{{ $item['label'] }}</span>
                        </label>
                    </li>
                @endforeach
            </ul>
            <button type="button" class="hb-btn-outline mt-4" data-open-modal="interview-prep-modal">
                {{ __('candidate.interviews.open_interview_guide') }}
            </button>
            <p class="mt-2 text-xs text-hb-muted">{{ $interview['guide_note'] ?? __('candidate.interviews.checklist_saved') }}</p>
        </div>
    @endif

    @if (! empty($interview['application_id']))
        <a href="{{ route('candidate.applications.show', $interview['application_id']) }}" class="mt-4 inline-flex text-sm font-semibold text-hb-blue hover:underline">
            {{ __('candidate.applications.view_application') }}
        </a>
    @endif
</section>
