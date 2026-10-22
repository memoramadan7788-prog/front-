@php
    /** @var array<string, mixed> $job */
    $cta = $job['primary_cta'] ?? 'review_apply';
    $eligibility = $job['eligibility'] ?? null;
    $visibility = $job['visibility'] ?? 'public';
@endphp
<article
    class="border-b border-hb-border px-5 py-5 last:border-b-0"
    data-candidate-job-card
    data-job-id="{{ $job['id'] }}"
    data-visibility="{{ $visibility }}"
    data-application-mode="{{ $job['application_mode'] ?? 'open' }}"
    data-can-apply="{{ ! empty($eligibility['can_apply']) ? '1' : '0' }}"
    data-title="{{ strtolower($job['title'].' '.$job['company'].' '.$job['keyword'].' '.$job['code']) }}"
    data-location="{{ strtolower($job['location'].' '.$job['work_model']) }}"
    data-work-model="{{ $job['work_model'] }}"
    data-employment="{{ $job['employment'] }}"
    data-discipline="{{ $job['discipline'] }}"
    data-salary-min="{{ $job['salary_min'] }}"
    data-salary-max="{{ $job['salary_max'] }}"
    data-recommended-rank="{{ $job['recommended_rank'] ?? 99 }}"
    data-posted-rank="{{ $job['posted_rank'] ?? 0 }}"
    data-company="{{ strtolower($job['company']) }}"
>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <h3 class="text-base font-semibold text-hb-navy">
                @if ($visibility === 'public')
                    <a href="{{ route('jobs.show', $job['id']) }}" class="hover:text-hb-blue">{{ $job['title'] }}</a>
                @elseif (! empty($eligibility['can_view']))
                    <span>{{ $job['title'] }}</span>
                @else
                    <span>{{ $job['title'] }}</span>
                @endif
            </h3>
            <p class="mt-1 text-sm text-hb-muted">
                {{ $job['company'] }} · {{ $job['code'] }}
            </p>
            <p class="mt-2 text-sm text-hb-text">
                {{ \App\Support\Frontend\MockCandidateData::workModelLabel($job['work_model']) }} · {{ $job['location'] }} · {{ \App\Support\Frontend\MockCandidateData::employmentLabel($job['employment']) }} · {{ $job['salary_label'] }}
            </p>
            <div class="mt-2 flex flex-wrap gap-2">
                <x-badge :variant="$visibility === 'private' ? 'pending' : 'neutral'" :dot="true">
                    {{ __('jobs.visibility.'.$visibility) }}
                </x-badge>
                @if (($job['application_mode'] ?? 'open') !== 'open')
                    <x-badge variant="info" :dot="true">
                        {{ __('jobs.application_mode.'.$job['application_mode']) }}
                    </x-badge>
                @endif
            </div>
        </div>
        <x-badge :variant="$job['source'] === 'Direct hire' ? 'info' : 'pending'" :dot="true">
            {{ $job['source'] }}
        </x-badge>
    </div>

    @if (! empty($job['match_reason']))
        <p class="mt-3 text-sm leading-relaxed text-hb-muted">{{ $job['match_reason'] }}</p>
    @endif

    <div class="mt-4 flex flex-wrap items-center gap-4">
        <button
            type="button"
            class="text-sm font-semibold text-hb-text hover:text-hb-blue"
            data-save-job="{{ $job['id'] }}"
            aria-pressed="{{ ! empty($job['saved']) ? 'true' : 'false' }}"
        >
            <span data-save-label>{{ ! empty($job['saved']) ? __('candidate.jobs.saved') : __('candidate.jobs.save_job') }}</span>
        </button>
        @if ($cta === 'view_application')
            <a
                href="{{ route('candidate.applications.show', $job['id']) }}"
                class="text-sm font-semibold text-hb-blue hover:underline"
            >
                {{ $job['primary_cta_label'] ?? __('candidate.jobs.view_application_arrow') }}
            </a>
        @elseif ($cta === 'membership')
            <a href="{{ route('candidate.membership') }}" class="text-sm font-semibold text-hb-blue hover:underline">
                {{ $job['primary_cta_label'] ?? __('jobs.eligibility.open_membership') }}
            </a>
        @elseif (in_array($cta, ['unavailable', 'nomination_blocked', 'employee_blocked'], true))
            <span class="text-sm font-semibold text-hb-muted" aria-disabled="true">
                {{ $job['primary_cta_label'] ?? __('jobs.eligibility.unavailable') }}
            </span>
        @else
            <a
                href="{{ route('candidate.applications.apply', $job['id']) }}"
                class="text-sm font-semibold text-hb-blue hover:underline"
            >
                {{ $job['primary_cta_label'] ?? __('candidate.jobs.review_apply_arrow') }}
            </a>
            @if (($job['id'] ?? '') === 'gc-041')
                <button
                    type="button"
                    class="text-sm font-semibold text-hb-muted hover:text-hb-blue"
                    data-open-modal="candidate-apply-modal"
                >
                    {{ __('candidate.jobs.apply_in_modal') }}
                </button>
            @endif
        @endif
    </div>
</article>
