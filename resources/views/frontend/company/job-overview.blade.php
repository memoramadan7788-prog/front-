@extends('frontend.layouts.app')

@section('title', __('Job overview'))

@php
    $jobId = data_get($vacancy, 'id');
    $displayValue = static fn (mixed $value, string $fallback = '—'): string => is_scalar($value) ? (string) $value : $fallback;
    $applications = \App\Support\Rows::from($applications ?? []);
    $stageCounts = collect($applications)->groupBy(fn ($application) => strtolower($displayValue(data_get($application, 'status'), 'unknown')))->map->count();
    $sourceCounts = collect($applications)->map(fn ($application) => $displayValue(data_get($application, 'source') ?? data_get($application, 'source_name'), ''))->filter(fn ($source) => $source !== '')->countBy();
    $hiringTeam = \App\Support\Rows::from(data_get($vacancy, 'hiring_team', data_get($vacancy, 'team', [])));
    $publicationUrl = data_get($vacancy, 'public_url') ?? data_get($vacancy, 'publication_url');
@endphp

@section('page_header')
    <div>
        <p class="text-sm font-semibold text-hb-blue">{{ __('Job overview') }}</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-hb-navy">{{ $displayValue(data_get($vacancy, 'title'), __('Untitled role')) }}</h1>
    </div>
@endsection

@section('page_actions')
    <x-button variant="light" :href="route('frontend.company.vacancies.index')">{{ __('Back to jobs') }}</x-button>
    <x-button variant="primary" :href="route('frontend.company.vacancies.applications', ['vacancyId' => $jobId])">{{ __('View applicants') }}</x-button>
@endsection

@section('content')
<div class="space-y-5">
    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="{{ __('Job stage counts') }}">
        @foreach (['submitted', 'screening', 'interviewing', 'offered'] as $stage)
            <article class="rounded-xl border border-hb-border bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ \Illuminate\Support\Str::headline($stage) }}</p>
                @if ($applicationsAvailable)
                    <p class="mt-2 text-2xl font-bold text-hb-navy">{{ $stageCounts->get($stage, 0) }}</p>
                    <p class="mt-1 text-xs text-hb-muted">{{ __('Applicants in this stage') }}</p>
                @else
                    <p class="mt-2 text-sm font-semibold text-amber-800">{{ __('Unavailable') }}</p>
                    <p class="mt-1 text-xs text-hb-muted">{{ __('Applicant data could not be loaded.') }}</p>
                @endif
            </article>
        @endforeach
    </section>

    <div class="flex gap-2 overflow-x-auto border-b border-hb-border" role="tablist" aria-label="{{ __('Job overview sections') }}">
        @foreach (['overview' => __('Overview'), 'pipeline' => __('Pipeline'), 'team' => __('Hiring team'), 'publication' => __('Publication')] as $tab => $label)
            <button type="button" id="job-tab-{{ $tab }}" role="tab" aria-controls="job-panel-{{ $tab }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" tabindex="{{ $loop->first ? '0' : '-1' }}" data-job-tab="{{ $tab }}" class="shrink-0 border-b-2 px-4 py-3 text-sm font-semibold {{ $loop->first ? 'border-hb-blue text-hb-blue' : 'border-transparent text-hb-muted hover:text-hb-navy' }}">{{ $label }}</button>
        @endforeach
    </div>

    <section id="job-panel-overview" role="tabpanel" aria-labelledby="job-tab-overview" data-job-panel="overview" class="grid gap-5 lg:grid-cols-[minmax(0,1.3fr)_minmax(18rem,0.7fr)]">
        <x-card>
            <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('Role details') }}</h2></x-slot:header>
            <div class="space-y-4">
                <p class="whitespace-pre-line text-sm leading-7 text-hb-text">{{ $displayValue(data_get($vacancy, 'description'), __('No role description was returned.')) }}</p>
                <dl class="grid gap-4 border-t border-hb-border pt-4 sm:grid-cols-2">
                    @foreach (['location' => __('Location'), 'work_mode' => __('Work mode'), 'employment_type' => __('Employment type'), 'department' => __('Department'), 'openings_count' => __('Openings'), 'deadline' => __('Application deadline')] as $key => $label)
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ $label }}</dt>
                            <dd class="mt-1 text-sm font-medium text-hb-navy">{{ $key === 'deadline' ? \App\Support\Rows::date($displayValue(data_get($vacancy, $key), ''), 'Y-m-d') : $displayValue(data_get($vacancy, $key)) }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </x-card>
        <x-card>
            <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('Sourcing') }}</h2></x-slot:header>
            @if ($applicationsAvailable && $sourceCounts->isNotEmpty())
                <ul class="space-y-3">
                    @foreach ($sourceCounts as $source => $count)
                        <li class="flex items-center justify-between gap-3 rounded-lg bg-slate-50 px-3 py-2 text-sm">
                            <span class="font-medium text-hb-text">{{ $source }}</span>
                            <span class="font-bold text-hb-navy">{{ $count }}</span>
                        </li>
                    @endforeach
                </ul>
            @elseif (! $applicationsAvailable)
                <p class="text-sm text-amber-800">{{ __('Sourcing metrics are unavailable because applicant data failed to load.') }}</p>
            @else
                <p class="text-sm text-hb-muted">{{ __('The API did not include source attribution for these applicants.') }}</p>
            @endif
        </x-card>
    </section>

    <section id="job-panel-pipeline" role="tabpanel" aria-labelledby="job-tab-pipeline" data-job-panel="pipeline" class="hidden">
        <x-card>
            <x-slot:header>
                <div>
                    <h2 class="text-lg font-bold text-hb-navy">{{ __('Applicant pipeline') }}</h2>
                    <p class="mt-1 text-sm text-hb-muted">{{ __('Counts use application statuses returned by the hiring API.') }}</p>
                </div>
            </x-slot:header>
            @if (! $applicationsAvailable)
                <p class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900" role="alert">{{ __('Applicant pipeline could not be loaded. Try again later.') }}</p>
            @elseif (count($applications))
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($stageCounts as $stage => $count)
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ \Illuminate\Support\Str::headline($stage) }}</p>
                            <p class="mt-2 text-2xl font-bold text-hb-navy">{{ $count }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-5 overflow-x-auto">
                    <table class="w-full min-w-[36rem] text-start text-sm">
                        <thead><tr class="border-b border-hb-border text-xs uppercase text-hb-muted"><th class="px-3 py-3">{{ __('company.applicants.col_name') }}</th><th class="px-3 py-3">{{ __('Stage') }}</th><th class="px-3 py-3">{{ __('Source') }}</th><th class="px-3 py-3">{{ __('Applied') }}</th></tr></thead>
                        <tbody>
                            @foreach ($applications as $application)
                                <tr class="border-b border-hb-border/70">
                                    <td class="px-3 py-3 font-medium text-hb-navy">{{ $displayValue(data_get($application, 'candidate.name') ?? data_get($application, 'candidate_name'), __('company.applicants.col_name')) }}</td>
                                    <td class="px-3 py-3">{{ $displayValue(data_get($application, 'status')) }}</td>
                                    <td class="px-3 py-3">{{ $displayValue(data_get($application, 'source') ?? data_get($application, 'source_name')) }}</td>
                                    <td class="px-3 py-3">{{ \App\Support\Rows::date($displayValue(data_get($application, 'created_at'), ''), 'Y-m-d') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-hb-muted">{{ __('No applicants have been returned for this job yet.') }}</p>
            @endif
        </x-card>
    </section>

    <section id="job-panel-team" role="tabpanel" aria-labelledby="job-tab-team" data-job-panel="team" class="hidden">
        <x-card>
            <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('Hiring team') }}</h2></x-slot:header>
            @if (count($hiringTeam))
                <ul class="grid gap-3 sm:grid-cols-2">
                    @foreach ($hiringTeam as $member)
                        <li class="rounded-xl border border-hb-border p-4">
                            <p class="font-semibold text-hb-navy">{{ $displayValue(data_get($member, 'name')) }}</p>
                            <p class="mt-1 text-sm text-hb-muted">{{ $displayValue(data_get($member, 'role') ?? data_get($member, 'title'), __('Hiring team member')) }}</p>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="rounded-lg bg-slate-50 p-4 text-sm text-hb-muted">{{ __('No hiring-team information was included in the job response. Team editing is not available in the connected API.') }}</p>
            @endif
        </x-card>
    </section>

    <section id="job-panel-publication" role="tabpanel" aria-labelledby="job-tab-publication" data-job-panel="publication" class="hidden">
        <x-card>
            <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('Publication information') }}</h2></x-slot:header>
            <dl class="grid gap-4 sm:grid-cols-2">
                @foreach (['status' => __('Publication status'), 'visibility' => __('Visibility'), 'published_at' => __('Published at'), 'deadline' => __('Closes at'), 'created_at' => __('Created')] as $key => $label)
                    <div class="rounded-lg bg-slate-50 p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ $label }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-hb-navy">{{ str_ends_with($key, '_at') || $key === 'deadline' ? \App\Support\Rows::date($displayValue(data_get($vacancy, $key), '')) : $displayValue(data_get($vacancy, $key)) }}</dd>
                    </div>
                @endforeach
            </dl>
            @if (is_string($publicationUrl) && in_array(parse_url($publicationUrl, PHP_URL_SCHEME), ['http', 'https'], true))
                <a class="mt-4 inline-flex text-sm font-semibold text-hb-blue hover:underline" href="{{ $publicationUrl }}" target="_blank" rel="noopener noreferrer">{{ __('Open published job') }}</a>
            @else
                <p class="mt-4 text-sm text-hb-muted">{{ __('No public URL was included in the job response.') }}</p>
            @endif
        </x-card>
    </section>
</div>

<script>
    (() => {
        const tabs = [...document.querySelectorAll('[data-job-tab]')];
        const panels = [...document.querySelectorAll('[data-job-panel]')];
        tabs.forEach((tab) => tab.addEventListener('click', () => {
            tabs.forEach((item) => {
                const active = item === tab;
                item.setAttribute('aria-selected', String(active));
                item.tabIndex = active ? 0 : -1;
                item.classList.toggle('border-hb-blue', active);
                item.classList.toggle('text-hb-blue', active);
                item.classList.toggle('border-transparent', !active);
                item.classList.toggle('text-hb-muted', !active);
            });
            panels.forEach((panel) => panel.classList.toggle('hidden', panel.dataset.jobPanel !== tab.dataset.jobTab));
        }));
    })();
</script>
@endsection
