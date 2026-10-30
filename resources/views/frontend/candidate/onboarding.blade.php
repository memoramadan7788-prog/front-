@extends('frontend.layouts.app')

@section('title', __('onboarding.candidate.page_title'))

@section('content')
<div class="mx-auto max-w-6xl space-y-6" data-candidate-onboarding>
    <header class="rounded-3xl bg-gradient-to-br from-hb-navy to-hb-blue p-6 text-white sm:p-9">
        <p class="text-sm font-semibold uppercase tracking-wider text-white/70">{{ __('onboarding.candidate.eyebrow') }}</p>
        <h1 class="mt-2 text-3xl font-bold">{{ __('onboarding.candidate.title') }}</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-white/80">{{ __('onboarding.candidate.basics.intro') }}</p>
    </header>

    <nav class="grid gap-3 sm:grid-cols-3" aria-label="{{ __('onboarding.candidate.form_aria') }}">
        @foreach (['basics', 'resume', 'skills_preferences'] as $index => $name)
            <a href="{{ route('frontend.candidate.onboarding', ['step' => $index + 1]) }}" @class(['rounded-2xl border p-4 text-sm font-semibold transition', 'border-hb-blue bg-blue-50 text-hb-blue' => $step === $index + 1, 'border-hb-border bg-white text-hb-muted' => $step !== $index + 1])>
                <span class="me-2 inline-flex h-7 w-7 items-center justify-center rounded-full bg-hb-page">{{ $index + 1 }}</span>{{ __('onboarding.candidate.stepper.'.$name) }}
            </a>
        @endforeach
    </nav>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem]">
        <section class="rounded-2xl border border-hb-border bg-white p-5 shadow-sm sm:p-7">
            @if (session('success_key')) <x-alert variant="success">{{ __('onboarding.candidate.saved') }}</x-alert> @endif
            @if ($errors->any()) <x-alert variant="error">{{ $errors->first() }}</x-alert> @endif
            @if (!$profileLoaded) <x-alert variant="warning">{{ __('onboarding.candidate.load_error') }}</x-alert> @endif
            @if ($step === 3 && !$preferencesLoaded) <x-alert variant="warning">{{ __('onboarding.candidate.preferences_unavailable') }}</x-alert> @endif
            @if ($step === 3 && !$skillsLoaded) <x-alert variant="warning">{{ __('onboarding.candidate.skills_unavailable') }}</x-alert> @endif

            @if ($step === 1)
                <h2 class="text-xl font-bold text-hb-navy">{{ __('onboarding.candidate.steps.basics') }}</h2>
                <form method="POST" action="{{ route('frontend.candidate.onboarding.save') }}" class="mt-5 space-y-4" data-loading-form>@csrf<input type="hidden" name="step" value="1">
                    <label class="block text-sm font-medium">{{ __('onboarding.candidate.basics.professional_headline') }}<input name="headline" value="{{ old('headline', data_get($profile, 'headline')) }}" maxlength="255" class="mt-1 w-full rounded-xl border border-hb-border px-3 py-2.5" /></label>
                    <div class="grid gap-4 sm:grid-cols-2"><label class="block text-sm font-medium">{{ __('onboarding.candidate.basics.location') }}<input name="location" value="{{ old('location', data_get($profile, 'location')) }}" maxlength="255" class="mt-1 w-full rounded-xl border border-hb-border px-3 py-2.5" /></label><label class="block text-sm font-medium">{{ __('onboarding.candidate.basics.phone') }}<input name="phone" value="{{ old('phone', data_get($profile, 'phone')) }}" maxlength="30" class="mt-1 w-full rounded-xl border border-hb-border px-3 py-2.5" /></label></div>
                    <label class="block text-sm font-medium">{{ __('onboarding.candidate.basics.years_experience') }}<input type="number" min="0" max="80" name="years_experience" value="{{ old('years_experience', data_get($profile, 'years_experience')) }}" class="mt-1 w-full rounded-xl border border-hb-border px-3 py-2.5" /></label>
                    <label class="block text-sm font-medium">{{ __('onboarding.candidate.basics.short_bio') }}<textarea name="bio" rows="4" maxlength="5000" class="mt-1 w-full rounded-xl border border-hb-border px-3 py-2.5">{{ old('bio', data_get($profile, 'bio')) }}</textarea></label>
                    <button data-loading-submit class="rounded-xl bg-hb-blue px-5 py-3 font-semibold text-white"><span data-submit-idle>{{ __('common.save_changes') }}</span><span data-submit-loading class="hidden" aria-hidden="true">{{ __('common.please_wait') }}</span></button>
                </form>
            @elseif ($step === 2)
                <h2 class="text-xl font-bold text-hb-navy">{{ __('onboarding.candidate.resume.title') }}</h2><p class="mt-2 text-sm text-hb-muted">{{ __('onboarding.candidate.resume.intro') }}</p>
                <form method="POST" enctype="multipart/form-data" action="{{ route('frontend.candidate.onboarding.save') }}" class="mt-5 space-y-4" data-loading-form>@csrf<input type="hidden" name="step" value="2"><label class="block text-sm font-medium">{{ __('onboarding.candidate.resume.label') }}<input required type="file" name="resume" accept=".pdf,.doc,.docx" class="mt-2 block w-full rounded-xl border border-hb-border p-3" /></label><p class="text-xs text-hb-muted">{{ __('onboarding.candidate.resume.hint') }}</p><button data-loading-submit class="rounded-xl bg-hb-blue px-5 py-3 font-semibold text-white"><span data-submit-idle>{{ __('onboarding.candidate.resume.title') }}</span><span data-submit-loading class="hidden" aria-hidden="true">{{ __('common.please_wait') }}</span></button></form>
                @if ($resumeCount > 0)<p class="mt-4 text-sm text-hb-muted">{{ trans_choice('onboarding.candidate.resume_count', $resumeCount, ['count' => $resumeCount]) }}</p>@endif
            @else
                <h2 class="text-xl font-bold text-hb-navy">{{ __('onboarding.candidate.stepper.skills_preferences') }}</h2><p class="mt-2 text-sm text-hb-muted">{{ __('onboarding.candidate.skills.intro') }}</p>
                <form method="POST" action="{{ route('frontend.candidate.onboarding.save') }}" class="mt-5 space-y-4" data-loading-form>@csrf<input type="hidden" name="step" value="3"><label class="block text-sm font-medium">{{ __('onboarding.candidate.preferred_roles') }}<input name="desired_titles" value="{{ old('desired_titles', implode(', ', (array) data_get($preferences, 'desired_titles', []))) }}" maxlength="1000" class="mt-1 w-full rounded-xl border border-hb-border px-3 py-2.5" /></label><label class="block text-sm font-medium">{{ __('onboarding.candidate.skills.home_location') }}<input name="location" value="{{ old('location', data_get($preferences, 'location')) }}" maxlength="255" class="mt-1 w-full rounded-xl border border-hb-border px-3 py-2.5" /></label><label class="block text-sm font-medium">{{ __('onboarding.candidate.skills.work_model') }}<select name="work_model" class="mt-1 w-full rounded-xl border border-hb-border px-3 py-2.5"><option value="">—</option>@foreach (['onsite', 'remote', 'hybrid'] as $model)<option value="{{ $model }}" @selected(old('work_model', data_get($preferences, 'work_model')) === $model)>{{ __('onboarding.candidate.skills.work_models.'.$model) }}</option>@endforeach</select></label><label class="block text-sm font-medium">{{ __('onboarding.candidate.skills.salary_min') }}<input type="number" min="0" step="1" name="min_salary" value="{{ old('min_salary', data_get($preferences, 'min_salary')) }}" class="mt-1 w-full rounded-xl border border-hb-border px-3 py-2.5" /></label><button data-loading-submit class="rounded-xl bg-hb-blue px-5 py-3 font-semibold text-white"><span data-submit-idle>{{ __('onboarding.candidate.finish_setup') }}</span><span data-submit-loading class="hidden" aria-hidden="true">{{ __('common.please_wait') }}</span></button></form>
                <div class="mt-5"><h3 class="text-sm font-bold text-hb-navy">{{ __('onboarding.candidate.skills.skills') }}</h3>@if (count($skills) > 0)<ul class="mt-2 flex flex-wrap gap-2">@foreach ($skills as $skill)<li class="rounded-full bg-blue-50 px-3 py-1.5 text-sm text-hb-blue">{{ data_get($skill, 'name', data_get($skill, 'skill.name', '')) }}</li>@endforeach</ul>@else<p class="mt-2 text-sm text-hb-muted">{{ __('onboarding.candidate.skills_empty') }}</p>@endif<a class="mt-3 inline-flex text-sm font-semibold text-hb-blue underline" href="{{ route('frontend.candidate.skills.add') }}">{{ __('onboarding.candidate.skills.manage') }}</a></div>
            @endif
        </section>

        <aside class="space-y-4">
            <section class="rounded-2xl border border-hb-border bg-white p-5"><h2 class="font-bold text-hb-navy">{{ __('onboarding.candidate.profile_so_far') }}</h2><p class="mt-3 text-2xl font-bold text-hb-blue">{{ $completion }}%</p><div class="mt-3 h-2 overflow-hidden rounded-full bg-hb-page"><div class="h-full rounded-full bg-hb-blue" style="width: {{ $completion }}%"></div></div><p class="mt-2 text-xs text-hb-muted">{{ __('onboarding.candidate.percent_complete', ['percent' => $completion]) }}</p><p class="mt-4 text-xs text-hb-muted">{{ trans_choice('onboarding.candidate.resume_count', $resumeCount, ['count' => $resumeCount]) }}</p></section>
            <section class="rounded-2xl bg-blue-50 p-5"><h2 class="font-bold text-hb-navy">{{ __('onboarding.candidate.in_control') }}</h2><p class="mt-2 text-sm leading-6 text-hb-muted">{{ __('onboarding.candidate.salary_privacy') }}</p></section>
        </aside>
    </div>
</div>
@endsection
