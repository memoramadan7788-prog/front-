@php
    $eligibility = $eligibility ?? null;
    $job = $job ?? [];
@endphp

@if (is_array($eligibility) && empty($eligibility['can_apply']))
    <x-alert variant="warning" class="mb-6" data-eligibility-banner data-reason="{{ $eligibility['reason_key'] ?? '' }}">
        <p class="font-semibold text-hb-navy">{{ __('jobs.eligibility.restricted_title') }}</p>
        <p class="mt-1 text-hb-text">{{ $eligibility['reason'] ?? __('jobs.eligibility.restricted_generic') }}</p>
        <p class="mt-2 text-xs text-hb-muted">{{ __('jobs.eligibility.authority_note') }}</p>
        @if (! empty($eligibility['membership_path']))
            <p class="mt-3">
                <a href="{{ route('candidate.membership') }}" class="font-semibold text-hb-blue hover:underline">
                    {{ __('jobs.eligibility.open_membership') }}
                </a>
            </p>
        @endif
        @if (($eligibility['reason_key'] ?? '') === 'nomination_required')
            <p class="mt-2 text-sm text-hb-muted">{{ __('jobs.eligibility.nomination_hint') }}</p>
        @endif
        @if (! empty($job['visibility']))
            <p class="mt-2 text-xs text-hb-muted">
                {{ __('jobs.eligibility.job_meta', [
                    'visibility' => __('jobs.visibility.'.$job['visibility']),
                    'mode' => __('jobs.application_mode.'.($job['application_mode'] ?? 'open')),
                ]) }}
            </p>
        @endif
    </x-alert>
@endif
