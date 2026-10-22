{{-- F9C / WF4 — AI screening presentation (Frame 51). Not a real AI assessment. --}}
@php
    /** @var array<string, mixed> $ai */
    $ai = $ai ?? [];
    $applicantName = $applicantName ?? '';
    $canOverride = in_array($ai['hr_review_status'] ?? '', ['required', 'in_progress'], true)
        && in_array($ai['run_status'] ?? '', ['completed', 'failed', 'unavailable'], true);
@endphp

<section class="hb-surface p-5 sm:p-6" aria-labelledby="ai-screening-heading" data-ai-screening-panel data-run-status="{{ $ai['run_status'] ?? '' }}" data-match-band="{{ $ai['match_band'] ?? '' }}">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 id="ai-screening-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.applicants.ai.heading') }}</h2>
            <p class="mt-1 text-sm text-hb-muted">{{ __('company.applicants.ai.intro') }}</p>
        </div>
        <x-badge :variant="$ai['badge_variant'] ?? 'neutral'" :dot="true">{{ $ai['badge_label'] ?? '' }}</x-badge>
    </div>

    <p class="mt-3 text-xs text-hb-muted">{{ $ai['advisory_note'] ?? '' }}</p>

    @if (($ai['run_status'] ?? '') === 'failed')
        <x-alert variant="warning" class="mt-4" data-ai-failed>
            <p class="font-semibold text-hb-navy">{{ __('company.applicants.ai.failed_title') }}</p>
            <p class="mt-1 text-hb-text">{{ __('company.applicants.ai.failed_body') }}</p>
            <p class="mt-2 text-xs text-hb-muted">{{ $ai['not_rejection_note'] ?? '' }}</p>
        </x-alert>
    @elseif (($ai['run_status'] ?? '') === 'unavailable')
        <x-alert variant="info" class="mt-4">
            <p class="font-semibold text-hb-navy">{{ __('company.applicants.ai.unavailable_title') }}</p>
            <p class="mt-1 text-hb-text">{{ __('company.applicants.ai.unavailable_body') }}</p>
        </x-alert>
    @elseif (($ai['run_status'] ?? '') === 'pending')
        <x-alert variant="info" class="mt-4" data-ai-pending>
            <p class="font-semibold text-hb-navy">{{ __('company.applicants.ai.pending_title') }}</p>
            <p class="mt-1 text-hb-text">{{ __('company.applicants.ai.pending_body') }}</p>
        </x-alert>
    @else
        <dl class="mt-4 grid gap-3 sm:grid-cols-3 text-sm">
            <div class="rounded-lg border border-hb-border bg-hb-page px-3 py-3">
                <dt class="text-hb-muted">{{ __('company.applicants.ai.score') }}</dt>
                <dd class="mt-1 text-lg font-semibold text-hb-navy" dir="ltr">{{ $ai['score_label'] ?? '—' }}</dd>
            </div>
            <div class="rounded-lg border border-hb-border bg-hb-page px-3 py-3">
                <dt class="text-hb-muted">{{ __('company.applicants.ai.threshold') }}</dt>
                <dd class="mt-1 font-semibold text-hb-navy" dir="ltr">{{ number_format((float) ($ai['advisory_threshold'] ?? 80), 0) }}%</dd>
                <dd class="mt-1 text-xs text-hb-muted">{{ __('company.applicants.ai.threshold_hint') }}</dd>
            </div>
            <div class="rounded-lg border border-hb-border bg-hb-page px-3 py-3">
                <dt class="text-hb-muted">{{ __('company.applicants.ai.recommendation') }}</dt>
                <dd class="mt-1 font-semibold text-hb-navy">{{ $ai['recommendation_label'] ?? '—' }}</dd>
            </div>
        </dl>

        @if (! empty($ai['below_threshold']))
            <p class="mt-3 text-sm text-hb-text" data-ai-low-match>{{ __('company.applicants.ai.low_match_note') }}</p>
        @endif

        @if (! empty($ai['summary']))
            <p class="mt-4 text-sm text-hb-text">{{ $ai['summary'] }}</p>
        @endif

        <div class="mt-4 grid gap-4 sm:grid-cols-2 text-sm">
            <div>
                <p class="font-semibold text-hb-navy">{{ __('company.applicants.ai.matched_skills') }}</p>
                @if (! empty($ai['matched_skills']))
                    <ul class="mt-2 list-disc space-y-1 ps-5 text-hb-text">
                        @foreach ($ai['matched_skills'] as $skill)
                            <li>{{ $skill }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-2 text-hb-muted">{{ __('company.applicants.ai.none_listed') }}</p>
                @endif
            </div>
            <div>
                <p class="font-semibold text-hb-navy">{{ __('company.applicants.ai.missing_skills') }}</p>
                @if (! empty($ai['missing_skills']))
                    <ul class="mt-2 list-disc space-y-1 ps-5 text-hb-text">
                        @foreach ($ai['missing_skills'] as $skill)
                            <li>{{ $skill }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-2 text-hb-muted">{{ __('company.applicants.ai.none_listed') }}</p>
                @endif
            </div>
        </div>
    @endif

    <div class="mt-5 rounded-lg border border-hb-border bg-hb-page px-4 py-3 text-sm" data-hr-review-status="{{ $ai['hr_review_status'] ?? '' }}">
        <p class="font-semibold text-hb-navy">{{ __('company.applicants.ai.hr_heading') }}</p>
        <p class="mt-1 text-hb-muted">
            @if (($ai['hr_review_status'] ?? '') === 'completed')
                {{ __('company.applicants.ai.hr_completed', [
                    'reviewer' => $ai['reviewer_name'] ?? __('company.applicants.ai.reviewer_unknown'),
                    'decision' => $ai['human_decision_label'] ?? '—',
                ]) }}
            @elseif (($ai['hr_review_status'] ?? '') === 'required')
                {{ __('company.applicants.ai.hr_required') }}
            @elseif (($ai['hr_review_status'] ?? '') === 'in_progress')
                {{ __('company.applicants.ai.hr_in_progress') }}
            @else
                {{ __('company.applicants.ai.hr_none') }}
            @endif
        </p>
        @if (! empty($ai['override_reason']))
            <p class="mt-2 text-hb-text">
                <span class="font-medium">{{ __('company.applicants.ai.override_reason_label') }}:</span>
                {{ $ai['override_reason'] }}
            </p>
        @endif
    </div>

    @if ($canOverride)
        <div class="mt-4 flex flex-wrap gap-2">
            <button type="button" class="hb-btn-outline" data-open-modal="ai-override-modal">
                {{ __('company.applicants.ai.override_preview') }}
            </button>
        </div>
    @endif

    <p class="mt-4 text-xs text-hb-muted" dir="ltr">{{ __('company.applicants.ai.model_meta', ['version' => $ai['model_version'] ?? 'sample']) }}</p>
</section>
