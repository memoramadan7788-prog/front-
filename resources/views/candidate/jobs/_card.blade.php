@php
    /** @var array<string, mixed> $job */
@endphp
<article
    class="border-b border-hb-border px-5 py-5 last:border-b-0"
    data-candidate-job-card
    data-job-id="{{ $job['id'] }}"
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
                <a href="{{ route('jobs.show', $job['id']) }}" class="hover:text-hb-blue">{{ $job['title'] }}</a>
            </h3>
            <p class="mt-1 text-sm text-hb-muted">
                {{ $job['company'] }} · {{ $job['code'] }}
            </p>
            <p class="mt-2 text-sm text-hb-text">
                {{ $job['work_model'] }} · {{ $job['location'] }} · {{ $job['employment'] }} · {{ $job['salary_label'] }}
            </p>
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
        @if (($job['primary_cta'] ?? '') === 'view_application')
            <a
                href="{{ route('candidate.applications.show', $job['id']) }}"
                class="text-sm font-semibold text-hb-blue hover:underline"
            >
                {{ $job['primary_cta_label'] ?? __('candidate.jobs.view_application_arrow') }}
            </a>
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
