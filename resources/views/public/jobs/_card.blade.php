@php
    /** @var array<string, mixed> $job */
    $postedRank = $postedRank ?? 0;
@endphp
<article
    class="hb-surface flex flex-col p-5"
    data-job-card
    data-job-id="{{ $job['id'] }}"
    data-title="{{ strtolower($job['title'].' '.$job['company'].' '.$job['keyword']) }}"
    data-company="{{ strtolower($job['company']) }}"
    data-location="{{ strtolower($job['location']) }}"
    data-work-model="{{ $job['work_model'] }}"
    data-employment="{{ $job['employment'] }}"
    data-discipline="{{ $job['discipline'] }}"
    data-salary-min="{{ $job['salary_min'] }}"
    data-salary-max="{{ $job['salary_max'] }}"
    data-posted-rank="{{ $postedRank }}"
>
    <div class="flex items-start justify-between gap-3">
        <div>
            <h3 class="text-base font-semibold text-hb-navy">
                <a href="{{ route('jobs.show', $job['id']) }}" class="hover:text-hb-blue">{{ $job['title'] }}</a>
            </h3>
            <p class="mt-1 text-sm text-hb-muted">
                @if (($job['company_slug'] ?? null) === 'northstar-labs')
                    <a href="{{ route('companies.show', $job['company_slug']) }}" class="hover:text-hb-blue">{{ $job['company'] }}</a>
                @else
                    {{ $job['company'] }}
                @endif
                · {{ $job['code'] }}
            </p>
            <p class="mt-2 text-sm text-hb-text">
                {{ \App\Support\Frontend\MockCandidateData::workModelLabel($job['work_model']) }} · {{ $job['location'] }} · {{ \App\Support\Frontend\MockCandidateData::employmentLabel($job['employment']) }} · {{ $job['salary_label'] }}
            </p>
        </div>
        <x-badge :variant="$job['source'] === 'Direct hire' ? 'info' : 'neutral'" :dot="true">
            {{ $job['source'] }}
        </x-badge>
    </div>
    <div class="mt-4 flex items-center justify-between gap-3 text-sm">
        <p class="text-xs text-hb-muted">{{ $job['employment'] }} · {{ $job['posted'] }} · {{ __('public.jobs.card_salary_note') }}</p>
        <a href="{{ route('jobs.show', $job['id']) }}" class="shrink-0 font-semibold text-hb-blue hover:underline">{{ __('public.jobs.card_view_role') }}</a>
    </div>
</article>
