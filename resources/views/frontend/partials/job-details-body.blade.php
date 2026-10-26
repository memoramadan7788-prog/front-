@php
    use App\Frontend\Auth\ApiSession;

    $workspace = $workspace ?? 'public';
    $job = is_array($job ?? null) ? $job : [];
    $jobId = data_get($job, 'id');

    $backHref = $workspace === 'candidate'
        ? route('frontend.candidate.vacancies.index')
        : route('frontend.jobs.index');
    $backLabel = $workspace === 'candidate'
        ? __('Discover jobs')
        : __('All jobs');

    $salMin = data_get($job, 'salary.min', data_get($job, 'salary_min'));
    $salMax = data_get($job, 'salary.max', data_get($job, 'salary_max'));
    $salCur = data_get($job, 'salary.currency', data_get($job, 'currency'));
    $salPer = data_get($job, 'salary.period', data_get($job, 'salary_period'));

    $isCandidate = $workspace === 'candidate'
        || (ApiSession::check() && ApiSession::accountType() === 'candidate');
@endphp

<a href="{{ $backHref }}" class="text-sm font-semibold text-hb-blue hover:underline">&larr; {{ $backLabel }}</a>
<h1 class="mt-4 text-3xl font-semibold tracking-tight text-hb-navy sm:text-4xl">{{ data_get($job, 'title') }}</h1>
<p class="mt-1 text-hb-muted">
    {{ data_get($job, 'company.name', data_get($job, 'company_name', '')) }}
    @if (data_get($job, 'location'))
        · {{ data_get($job, 'location') }}
    @endif
</p>
<div class="mt-4 flex flex-wrap gap-2">
    @if (data_get($job, 'work_mode'))
        <x-badge variant="info">{{ data_get($job, 'work_mode') }}</x-badge>
    @endif
    @if (data_get($job, 'employment_type'))
        <x-badge>{{ data_get($job, 'employment_type') }}</x-badge>
    @endif
    @if ($salMin || $salMax)
        <x-badge variant="success">{{ \App\Support\Rows::money($salMin) }} – {{ \App\Support\Rows::money($salMax, $salCur, $salPer) }}</x-badge>
    @endif
</div>
<div class="hb-surface mt-8 whitespace-pre-line p-6 text-sm leading-7 text-hb-text lg:p-8">{{ data_get($job, 'description') }}</div>
<div class="mt-8 flex flex-wrap gap-3">
    @if ($isCandidate && $jobId)
        <x-button size="lg" :href="route('frontend.candidate.vacancies.apply', ['vacancyId' => $jobId])">{{ __('Apply now') }}</x-button>
    @elseif (! ApiSession::check())
        <x-button size="lg" :href="route('frontend.account.login')">{{ __('Sign in to apply') }}</x-button>
    @endif
</div>
