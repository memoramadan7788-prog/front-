@php
    use App\Frontend\Auth\ApiSession;

    $workspace = $workspace ?? 'public';
    $job = is_array($job ?? null) ? $job : [];
    $jobId = data_get($job, 'id');
    $hasJobId = is_scalar($jobId) && (string) $jobId !== '';

    $backHref = $workspace === 'candidate'
        ? route('frontend.candidate.vacancies.index')
        : route('frontend.jobs.index');
    $backLabel = $workspace === 'candidate'
        ? __('Discover jobs')
        : __('All jobs');

    $companyName = data_get($job, 'company.name', data_get($job, 'company_name'));
    $companyName = is_scalar($companyName) ? (string) $companyName : '';
    $companyOverview = data_get($job, 'company.description', data_get($job, 'company_overview'));
    $companyOverview = is_scalar($companyOverview) ? (string) $companyOverview : '';
    $description = data_get($job, 'description');
    $description = is_scalar($description) ? (string) $description : '';
    $location = data_get($job, 'location');
    $location = is_scalar($location) ? (string) $location : '';
    $workMode = data_get($job, 'work_mode');
    $workMode = is_scalar($workMode) ? (string) $workMode : '';
    $employmentType = data_get($job, 'employment_type');
    $employmentType = is_scalar($employmentType) ? (string) $employmentType : '';
    $experienceLevel = data_get($job, 'experience_level');
    $experienceLevel = is_scalar($experienceLevel) ? (string) $experienceLevel : '';
    $industry = data_get($job, 'company.industry');
    $industry = is_scalar($industry) ? (string) $industry : '';

    $salaryMin = data_get($job, 'salary.min', data_get($job, 'salary_min'));
    $salaryMax = data_get($job, 'salary.max', data_get($job, 'salary_max'));
    $salaryMin = is_scalar($salaryMin) ? $salaryMin : null;
    $salaryMax = is_scalar($salaryMax) ? $salaryMax : null;
    $salaryCurrency = data_get($job, 'salary.currency', data_get($job, 'currency'));
    $salaryPeriod = data_get($job, 'salary.period', data_get($job, 'salary_period'));
    $salaryRange = data_get($job, 'salary.range');
    $hasSalaryRange = ($salaryMin !== null && $salaryMin !== '')
        || ($salaryMax !== null && $salaryMax !== '');
    $salaryCurrency = is_scalar($salaryCurrency) ? (string) $salaryCurrency : null;
    $salaryPeriod = is_scalar($salaryPeriod) ? (string) $salaryPeriod : null;
    $salaryRange = is_scalar($salaryRange) ? (string) $salaryRange : '';
    $salaryMinText = $salaryMin !== null && $salaryMin !== ''
        ? (is_numeric($salaryMin) ? \App\Support\Rows::money($salaryMin) : (string) $salaryMin)
        : '';
    $salaryMaxText = $salaryMax !== null && $salaryMax !== ''
        ? (is_numeric($salaryMax)
            ? \App\Support\Rows::money($salaryMax, $salaryCurrency, $salaryPeriod)
            : (string) $salaryMax)
        : '';

    $skills = data_get($job, 'skills', data_get($job, 'required_skills', []));
    $skills = is_array($skills) ? array_values(array_filter($skills, 'is_scalar')) : [];
    $responsibilities = data_get($job, 'responsibilities', []);
    $responsibilities = is_array($responsibilities) ? array_values(array_filter($responsibilities, 'is_scalar')) : [];
    $requirements = data_get($job, 'requirements', []);
    $requirements = is_array($requirements) ? array_values(array_filter($requirements, 'is_scalar')) : [];
    $benefits = data_get($job, 'benefits', []);
    $benefits = is_array($benefits) ? array_values(array_filter($benefits, 'is_scalar')) : [];

    $hiringContact = data_get($job, 'hiring_contact', data_get($job, 'recruiter', []));
    $hiringContact = is_array($hiringContact) ? $hiringContact : [];
    $contactName = data_get($hiringContact, 'name', data_get($job, 'contact_name'));
    $contactName = is_scalar($contactName) ? (string) $contactName : '';
    $contactTitle = data_get($hiringContact, 'title', data_get($job, 'contact_title'));
    $contactTitle = is_scalar($contactTitle) ? (string) $contactTitle : '';
    $contactEmail = data_get($hiringContact, 'email', data_get($job, 'contact_email'));
    $contactEmail = is_scalar($contactEmail) ? (string) $contactEmail : '';

    $salaryText = $salaryRange !== ''
        ? $salaryRange
        : ($hasSalaryRange
            ? trim($salaryMinText . ($salaryMinText !== '' && $salaryMaxText !== '' ? ' – ' : '') . $salaryMaxText)
            : __('jobs.details.salary_not_listed'));

    $isCandidate = $workspace === 'candidate'
        || (ApiSession::check() && ApiSession::accountType() === 'candidate');
@endphp

<a href="{{ $backHref }}" class="text-sm font-semibold text-hb-blue hover:underline">&larr; {{ $backLabel }}</a>

<div class="mt-5 grid gap-6 lg:grid-cols-[minmax(0,1fr)_19rem] lg:items-start">
    <div class="min-w-0">
        <header class="hb-surface p-6 sm:p-8">
            <div class="flex flex-wrap gap-2">
                @if ($workMode !== '')
                    <x-badge variant="info">{{ $workMode }}</x-badge>
                @endif
                @if ($employmentType !== '')
                    <x-badge>{{ $employmentType }}</x-badge>
                @endif
                @if ($experienceLevel !== '')
                    <x-badge>{{ $experienceLevel }}</x-badge>
                @endif
            </div>

            <h1 class="mt-4 text-3xl font-semibold tracking-tight text-hb-navy sm:text-4xl">
                {{ is_scalar(data_get($job, 'title')) ? data_get($job, 'title') : __('jobs.details.role') }}
            </h1>
            <p class="mt-2 text-sm text-hb-muted">
                @if ($companyName !== '')
                    <span class="font-medium text-hb-text">{{ $companyName }}</span>
                @endif
                @if ($location !== '')
                    @if ($companyName !== '')<span aria-hidden="true"> · </span>@endif
                    {{ $location }}
                @endif
            </p>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                @if ($isCandidate && $hasJobId)
                    <form method="GET" action="{{ route('frontend.candidate.vacancies.apply', ['vacancyId' => $jobId]) }}" class="flex flex-wrap items-center gap-3">
                        <label class="flex max-w-xl items-start gap-2 text-sm leading-6 text-hb-muted">
                            <input type="checkbox" name="consent" value="1" required class="mt-1 size-4 rounded border-hb-border text-hb-blue focus:ring-hb-blue">
                            <span>{{ __('jobs.details.consent_notice') }}</span>
                        </label>
                        <button type="submit" class="hb-btn-primary">{{ __('Apply now') }}</button>
                    </form>
                @elseif (! ApiSession::check())
                    <x-button size="lg" :href="route('frontend.account.login')">{{ __('Sign in to apply') }}</x-button>
                @endif

                @if ($hasJobId)
                    <button
                        type="button"
                        class="hb-btn-outline"
                        data-save-job
                        data-job-id="{{ (string) $jobId }}"
                        data-save-label="{{ __('jobs.details.save_job') }}"
                        data-saved-label="{{ __('jobs.details.saved_job') }}"
                        data-save-error="{{ __('jobs.details.save_unavailable') }}"
                        aria-pressed="false"
                    >{{ __('jobs.details.save_job') }}</button>
                @endif
            </div>
            @if ($hasJobId)
                <p class="mt-2 text-xs text-hb-muted" data-save-job-status aria-live="polite"></p>
            @endif
        </header>

        <section class="hb-surface mt-6 p-6 sm:p-8" aria-labelledby="job-journey-title">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 id="job-journey-title" class="text-lg font-semibold text-hb-navy">{{ __('jobs.details.journey_title') }}</h2>
                <span class="text-xs text-hb-muted">{{ __('jobs.details.journey_hint') }}</span>
            </div>
            <ol class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach (['applied', 'review', 'interview', 'offer'] as $stage)
                    <li class="flex items-center gap-3 rounded-xl border border-hb-border bg-hb-page/50 p-3">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-hb-blue/10 text-sm font-semibold text-hb-blue">{{ $loop->iteration }}</span>
                        <span class="text-sm font-medium text-hb-text">{{ __("jobs.details.stages.{$stage}") }}</span>
                    </li>
                @endforeach
            </ol>
        </section>

        <section class="hb-surface mt-6 p-6 sm:p-8" aria-labelledby="role-details-title">
            <h2 id="role-details-title" class="text-lg font-semibold text-hb-navy">{{ __('jobs.details.role_details') }}</h2>
            @if ($description !== '')
                <div class="mt-4 whitespace-pre-line text-sm leading-7 text-hb-text">{{ $description }}</div>
            @else
                <p class="mt-4 text-sm text-hb-muted">{{ __('jobs.details.description_unavailable') }}</p>
            @endif

            @foreach ([
                'responsibilities' => ['jobs.details.responsibilities', $responsibilities],
                'requirements' => ['jobs.details.requirements', $requirements],
                'benefits' => ['jobs.details.benefits', $benefits],
            ] as [$key, $section])
                @if (count($section))
                    <div class="mt-6">
                        <h3 class="text-sm font-semibold text-hb-navy">{{ __($key) }}</h3>
                        <ul class="mt-3 list-disc space-y-2 ps-5 text-sm leading-6 text-hb-text">
                            @foreach ($section as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach

            @if (count($skills))
                <div class="mt-6">
                    <h3 class="text-sm font-semibold text-hb-navy">{{ __('jobs.details.core_skills') }}</h3>
                    <ul class="mt-3 flex flex-wrap gap-2">
                        @foreach ($skills as $skill)
                            <li><x-badge>{{ $skill }}</x-badge></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>
    </div>

    <aside class="grid gap-6 sm:grid-cols-2 lg:sticky lg:top-24 lg:grid-cols-1">
        <section class="hb-surface p-6" aria-labelledby="salary-title">
            <h2 id="salary-title" class="text-sm font-semibold text-hb-muted">{{ __('jobs.details.salary_title') }}</h2>
            <p class="mt-2 text-xl font-semibold text-hb-navy">{{ $salaryText }}</p>
            @if ($salaryCurrency && ! $hasSalaryRange && $salaryRange === '')
                <p class="mt-1 text-xs text-hb-muted">{{ $salaryCurrency }}</p>
            @endif
        </section>

        <section class="hb-surface p-6" aria-labelledby="company-overview-title">
            <h2 id="company-overview-title" class="text-lg font-semibold text-hb-navy">{{ __('jobs.details.company_overview') }}</h2>
            <p class="mt-1 text-sm font-medium text-hb-text">{{ $companyName !== '' ? $companyName : __('jobs.details.company_name_unavailable') }}</p>
            @if ($companyOverview !== '')
                <p class="mt-3 whitespace-pre-line text-sm leading-6 text-hb-muted">{{ $companyOverview }}</p>
            @else
                <p class="mt-3 text-sm text-hb-muted">{{ __('jobs.details.company_overview_unavailable') }}</p>
            @endif
            @if ($industry !== '')
                <p class="mt-4 text-xs text-hb-muted">{{ __('jobs.details.industry') }}: {{ $industry }}</p>
            @endif
        </section>

        <section class="hb-surface p-6" aria-labelledby="hiring-contact-title">
            <h2 id="hiring-contact-title" class="text-lg font-semibold text-hb-navy">{{ __('jobs.details.hiring_contact') }}</h2>
            @if ($contactName !== '')
                <p class="mt-3 text-sm font-medium text-hb-text">{{ $contactName }}</p>
            @endif
            @if ($contactTitle !== '')
                <p class="mt-1 text-sm text-hb-muted">{{ $contactTitle }}</p>
            @endif
            @if ($contactEmail !== '')
                <a class="mt-4 inline-flex font-semibold text-hb-blue hover:underline" href="mailto:{{ $contactEmail }}">{{ __('jobs.details.contact_hiring_team') }}</a>
            @else
                <p class="mt-3 text-sm text-hb-muted">{{ __('jobs.details.contact_unavailable') }}</p>
            @endif
        </section>
    </aside>
</div>

@pushOnce('scripts', 'public-job-bookmarks')
    <script>
        (() => {
            const storageKey = 'talentbridge:saved-jobs';
            const buttons = document.querySelectorAll('[data-save-job]');

            buttons.forEach((button) => {
                const status = button.closest('header')?.querySelector('[data-save-job-status]');
                const saveLabel = button.dataset.saveLabel;
                const savedLabel = button.dataset.savedLabel;

                const render = (saved) => {
                    button.textContent = saved ? savedLabel : saveLabel;
                    button.setAttribute('aria-pressed', saved ? 'true' : 'false');
                    if (status) {
                        status.textContent = saved ? savedLabel : '';
                    }
                };

                try {
                    const savedJobs = JSON.parse(localStorage.getItem(storageKey) || '[]');
                    if (!Array.isArray(savedJobs)) {
                        throw new TypeError('Saved jobs storage must contain a list.');
                    }
                    render(savedJobs.includes(button.dataset.jobId));
                } catch {
                    button.disabled = true;
                    button.title = button.dataset.saveError;
                    if (status) {
                        status.textContent = button.dataset.saveError;
                    }
                    return;
                }

                button.addEventListener('click', () => {
                    try {
                        const savedJobs = JSON.parse(localStorage.getItem(storageKey) || '[]');
                        if (!Array.isArray(savedJobs)) {
                            throw new TypeError('Saved jobs storage must contain a list.');
                        }

                        const jobId = button.dataset.jobId;
                        const nextSavedJobs = savedJobs.includes(jobId)
                            ? savedJobs.filter((id) => id !== jobId)
                            : [...savedJobs, jobId];

                        localStorage.setItem(storageKey, JSON.stringify(nextSavedJobs));
                        render(nextSavedJobs.includes(jobId));
                    } catch {
                        button.disabled = true;
                        button.title = button.dataset.saveError;
                        if (status) {
                            status.textContent = button.dataset.saveError;
                        }
                    }
                });
            });
        })();
    </script>
@endPushOnce
