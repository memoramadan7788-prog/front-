@extends('frontend.layouts.app')

@section('title', __('company.jobs.create_page_title'))

@section('page_header')
    <div>
        <p class="text-xs font-medium uppercase tracking-wide text-hb-muted">{{ __('company.jobs.create_breadcrumb') }}</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-hb-navy">{{ __('company.jobs.create_title') }}</h1>
        <p class="mt-1 text-sm text-hb-muted">{{ __('company.jobs.wizard_intro') }}</p>
    </div>
@endsection

@section('content')
@php
    $fields = is_array($fields ?? null) ? $fields : [];
    $step = (int) ($step ?? 1);
    $gaps = is_array($publicationGaps ?? null) ? $publicationGaps : [];
    $val = fn (string $key, $default = '') => old($key, $fields[$key] ?? $default);
@endphp

<div class="mx-auto max-w-3xl space-y-6" data-create-job-wizard data-step="{{ $step }}">
    <nav aria-label="{{ __('company.jobs.wizard_steps_aria') }}">
        <ol class="grid gap-2 sm:grid-cols-4">
            @foreach ($steps as $index => $key)
                @php $n = $index + 1; $active = $step === $n; $done = $step > $n; @endphp
                <li class="rounded-xl border px-3 py-2 text-sm {{ $active ? 'border-hb-accent bg-hb-accent/10 text-hb-navy font-semibold' : ($done ? 'border-hb-border bg-hb-page/50 text-hb-navy' : 'border-hb-border text-hb-muted') }}">
                    <span class="block text-[10px] uppercase tracking-wide text-hb-muted">{{ __('company.jobs.step_label', ['n' => $n]) }}</span>
                    {{ __('company.jobs.step_'.$key) }}
                </li>
            @endforeach
        </ol>
    </nav>

    @if ($errors->any())
        <div class="rounded-xl border border-hb-error/30 bg-hb-error/5 px-4 py-3 text-sm text-hb-navy" role="alert">
            <p class="font-medium">{{ __('company.jobs.form_error_title') }}</p>
            <ul class="mt-2 list-disc ps-5">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('frontend.company.vacancies.create.submit') }}" class="hb-surface space-y-5 rounded-2xl p-5 sm:p-6" data-submit-once data-wizard-form>
        @csrf
        <input type="hidden" name="step" value="{{ $step }}">

        @if ($step === 1)
            <section aria-labelledby="wizard-role-heading" class="space-y-4">
                <div>
                    <h2 id="wizard-role-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.jobs.role_details') }}</h2>
                    <p class="mt-1 text-sm text-hb-muted">{{ __('company.jobs.role_details_intro') }}</p>
                </div>
                <div>
                    <label class="hb-label" for="title">{{ __('company.jobs.job_title') }} <span class="text-hb-error">*</span></label>
                    <input id="title" name="title" type="text" class="hb-input" required maxlength="255" value="{{ $val('title') }}" autocomplete="off">
                    @error('title')<p class="hb-field-error">{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="hb-label" for="category_id">{{ __('company.jobs.category') }}</label>
                        <select id="category_id" name="category_id" class="hb-input">
                            <option value="">{{ __('company.jobs.select_option') }}</option>
                            {{-- Upstream HEAD used Rows::from($categories) for envelope-safe options; keep that with wizard state. --}}
                            @foreach (\App\Support\Rows::from($categories ?? []) as $opt)
                                @continue(! filled(data_get($opt, 'id')))
                                <option
                                    value="{{ data_get($opt, 'id') }}"
                                    @selected((string) $val('category_id') === (string) data_get($opt, 'id'))
                                >{{ data_get($opt, 'name', data_get($opt, 'title', data_get($opt, 'id'))) }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-hb-muted">{{ __('company.jobs.category_publish_hint') }}</p>
                        @error('category_id')<p class="hb-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="hb-label" for="department">{{ __('company.jobs.department') }}</label>
                        <input id="department" name="department" type="text" class="hb-input" maxlength="100" value="{{ $val('department') }}">
                    </div>
                    <div>
                        <label class="hb-label" for="employment_type">{{ __('company.jobs.employment_type') }} <span class="text-hb-error">*</span></label>
                        <select id="employment_type" name="employment_type" class="hb-input" required>
                            <option value="">{{ __('company.jobs.select_option') }}</option>
                            @foreach ($employmentTypes as $type)
                                <option value="{{ $type }}" @selected($val('employment_type') === $type)>{{ __('company.jobs.employment.'.$type) }}</option>
                            @endforeach
                        </select>
                        @error('employment_type')<p class="hb-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="hb-label" for="work_mode">{{ __('company.jobs.work_model') }} <span class="text-hb-error">*</span></label>
                        <select id="work_mode" name="work_mode" class="hb-input" required>
                            <option value="">{{ __('company.jobs.select_option') }}</option>
                            @foreach ($workModes as $mode)
                                <option value="{{ $mode }}" @selected($val('work_mode') === $mode)>{{ __('company.jobs.work.'.$mode) }}</option>
                            @endforeach
                        </select>
                        @error('work_mode')<p class="hb-field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="hb-label" for="location">{{ __('company.jobs.location') }}</label>
                        <input id="location" name="location" type="text" class="hb-input" value="{{ $val('location') }}" autocomplete="address-level2">
                    </div>
                    <div>
                        <label class="hb-label" for="openings_count">{{ __('company.jobs.openings_count') }}</label>
                        <input id="openings_count" name="openings_count" type="number" min="1" class="hb-input" value="{{ $val('openings_count', 1) }}">
                    </div>
                    <div>
                        <label class="hb-label" for="experience_years">{{ __('company.jobs.experience_years') }}</label>
                        <input id="experience_years" name="experience_years" type="number" min="0" max="50" class="hb-input" value="{{ $val('experience_years') }}">
                    </div>
                    <div>
                        <label class="hb-label" for="deadline">{{ __('company.jobs.deadline') }}</label>
                        <input id="deadline" name="deadline" type="date" class="hb-input" value="{{ $val('deadline') }}">
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="hb-label" for="salary_min">{{ __('company.jobs.salary_min_label') }}</label>
                        <input id="salary_min" name="salary_min" type="number" min="0" step="1" class="hb-input" value="{{ $val('salary_min') }}">
                    </div>
                    <div>
                        <label class="hb-label" for="salary_max">{{ __('company.jobs.salary_max_label') }}</label>
                        <input id="salary_max" name="salary_max" type="number" min="0" step="1" class="hb-input" value="{{ $val('salary_max') }}">
                    </div>
                    <div>
                        <label class="hb-label" for="currency">{{ __('company.jobs.currency') }}</label>
                        <select id="currency" name="currency" class="hb-input">
                            <option value="">{{ __('company.jobs.select_option') }}</option>
                            @foreach ($currencies as $cur)
                                <option value="{{ $cur }}" @selected($val('currency') === $cur)>{{ $cur }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="hb-label" for="salary_period">{{ __('company.jobs.salary_period') }}</label>
                        <select id="salary_period" name="salary_period" class="hb-input">
                            <option value="">{{ __('company.jobs.select_option') }}</option>
                            @foreach ($salaryPeriods as $period)
                                <option value="{{ $period }}" @selected($val('salary_period') === $period)>{{ __('company.jobs.period.'.$period) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <p class="text-xs text-hb-muted">{{ __('company.jobs.salary_hint_generic') }}</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="hb-label" for="visibility">{{ __('company.jobs.visibility_label') }} <span class="text-hb-error">*</span></label>
                        <select id="visibility" name="visibility" class="hb-input" required>
                            <option value="public" @selected($val('visibility', 'public') === 'public')>{{ __('company.jobs.visibility_public') }}</option>
                            <option value="private" @selected($val('visibility') === 'private')>{{ __('company.jobs.visibility_private') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="hb-label" for="private_apply_mode">{{ __('company.jobs.private_mode_label') }}</label>
                        <select id="private_apply_mode" name="private_apply_mode" class="hb-input">
                            <option value="">{{ __('company.jobs.select_option') }}</option>
                            <option value="all_employees" @selected($val('private_apply_mode') === 'all_employees')>{{ __('company.jobs.private_mode_employees') }}</option>
                            <option value="nominated_only" @selected($val('private_apply_mode') === 'nominated_only')>{{ __('company.jobs.private_mode_nominated') }}</option>
                        </select>
                    </div>
                </div>
            </section>
        @endif

        @if ($step === 2)
            <section aria-labelledby="wizard-desc-heading" class="space-y-4">
                <div>
                    <h2 id="wizard-desc-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.jobs.role_description') }}</h2>
                    <p class="mt-1 text-sm text-hb-muted">{{ __('company.jobs.description_contract_note') }}</p>
                </div>
                <div>
                    <label class="hb-label" for="description">{{ __('company.jobs.about_opportunity') }} <span class="text-hb-error">*</span></label>
                    <textarea id="description" name="description" rows="8" class="hb-input" required minlength="20" maxlength="20000">{{ $val('description') }}</textarea>
                    <p class="mt-1 text-xs text-hb-muted">{{ __('company.jobs.about_public_hint') }}</p>
                    @error('description')<p class="hb-field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="hb-label" for="requirements_text">{{ __('company.jobs.requirements') }}</label>
                    <textarea id="requirements_text" name="requirements_text" rows="5" class="hb-input" maxlength="10000">{{ $val('requirements_text') }}</textarea>
                    <p class="mt-1 text-xs text-hb-muted">{{ __('company.jobs.requirements_merge_note') }}</p>
                </div>
                <div>
                    <label class="hb-label" for="benefits_text">{{ __('company.jobs.benefits') }}</label>
                    <textarea id="benefits_text" name="benefits_text" rows="4" class="hb-input" maxlength="5000">{{ $val('benefits_text') }}</textarea>
                    <p class="mt-1 text-xs text-hb-muted">{{ __('company.jobs.benefits_merge_note') }}</p>
                </div>
                <p class="rounded-lg border border-hb-pending/30 bg-hb-pending-soft/30 px-3 py-2 text-xs text-hb-navy" role="note">{{ __('company.jobs.accessible_descriptions_hint') }}</p>
            </section>
        @endif

        @if ($step === 3)
            <section aria-labelledby="wizard-hiring-heading" class="space-y-4">
                <div>
                    <h2 id="wizard-hiring-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.jobs.hiring_process') }}</h2>
                    <p class="mt-1 text-sm text-hb-muted">{{ __('company.jobs.hiring_intro') }}</p>
                </div>
                <div>
                    <label class="hb-label" for="hiring_model">{{ __('company.jobs.hiring_model_label') }} <span class="text-hb-error">*</span></label>
                    <select id="hiring_model" name="hiring_model" class="hb-input" required>
                        @foreach ($hiringModels as $model)
                            <option value="{{ $model }}" @selected($val('hiring_model', 'direct') === $model)>{{ __('company.jobs.hiring_model.'.$model) }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-hb-muted">{{ __('company.jobs.hiring_model_help') }}</p>
                    @error('hiring_model')<p class="hb-field-error">{{ $message }}</p>@enderror
                </div>

                <div class="rounded-xl border border-dashed border-hb-border px-4 py-3 text-sm text-hb-navy" data-schema-blocked="ownership">
                    <p class="font-medium">{{ __('company.jobs.ownership_blocked_title') }}</p>
                    <p class="mt-1 text-hb-muted">{{ __('company.jobs.ownership_blocked_body') }}</p>
                    <ul class="mt-2 list-disc ps-5 text-hb-muted">
                        <li>{{ __('company.jobs.requisition_owner') }}</li>
                        <li>{{ __('company.jobs.hiring_manager') }}</li>
                        <li>{{ __('company.jobs.publish_to_search') }}</li>
                        <li>{{ __('company.jobs.agency_submissions') }}</li>
                    </ul>
                </div>
            </section>
        @endif

        @if ($step === 4)
            <section aria-labelledby="wizard-review-heading" class="space-y-5">
                <div>
                    <h2 id="wizard-review-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.jobs.review_publish') }}</h2>
                    <p class="mt-1 text-sm text-hb-muted">{{ __('company.jobs.review_confirm') }}</p>
                </div>

                <div class="space-y-3 rounded-xl border border-hb-border bg-hb-page/40 p-4 text-sm">
                    <p class="text-xs uppercase tracking-wide text-hb-muted">{{ __('company.jobs.role_details') }}</p>
                    <p class="mt-1 font-semibold text-hb-navy">{{ $val('title') ?: '—' }}</p>
                    <p class="text-hb-muted">{{ $categoryName ?: __('company.jobs.category_unset') }} · {{ $val('department') ?: '—' }}</p>
                    <p class="text-hb-muted">{{ $val('location') ?: '—' }} / {{ $val('work_mode') ? __('company.jobs.work.'.$val('work_mode')) : '—' }}</p>
                    <p class="text-hb-muted">{{ $val('employment_type') ? __('company.jobs.employment.'.$val('employment_type')) : '—' }}</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <input type="hidden" name="goto_step" id="goto_step" value="1">
                    <button type="submit" name="wizard_action" value="goto" class="rounded-lg border border-hb-border px-3 py-1.5 text-sm text-hb-navy hover:bg-hb-page" onclick="document.getElementById('goto_step').value='1'">{{ __('company.jobs.edit_step', ['n' => 1]) }}</button>
                    <button type="submit" name="wizard_action" value="goto" class="rounded-lg border border-hb-border px-3 py-1.5 text-sm text-hb-navy hover:bg-hb-page" onclick="document.getElementById('goto_step').value='2'">{{ __('company.jobs.edit_step', ['n' => 2]) }}</button>
                    <button type="submit" name="wizard_action" value="goto" class="rounded-lg border border-hb-border px-3 py-1.5 text-sm text-hb-navy hover:bg-hb-page" onclick="document.getElementById('goto_step').value='3'">{{ __('company.jobs.edit_step', ['n' => 3]) }}</button>
                </div>

                <div class="rounded-xl border border-hb-border bg-hb-page/40 p-4 text-sm">
                    <p class="text-xs uppercase tracking-wide text-hb-muted">{{ __('company.jobs.role_description') }}</p>
                    <pre class="mt-2 whitespace-pre-wrap font-sans text-hb-navy">{{ $composedDescription !== '' ? $composedDescription : '—' }}</pre>
                </div>

                <div class="rounded-xl border border-hb-border bg-hb-page/40 p-4 text-sm">
                    <p class="text-xs uppercase tracking-wide text-hb-muted">{{ __('company.jobs.hiring_process') }}</p>
                    <p class="mt-1 text-hb-navy">{{ __('company.jobs.hiring_model_label') }}: {{ $val('hiring_model') ? __('company.jobs.hiring_model.'.$val('hiring_model')) : '—' }}</p>
                    <p class="mt-2 text-xs text-hb-muted">{{ __('company.jobs.ownership_blocked_body') }}</p>
                </div>

                <div class="rounded-xl border border-hb-border p-4" data-publication-checklist>
                    <h3 class="text-sm font-semibold text-hb-navy">{{ __('company.jobs.publication_checklist') }}</h3>
                    <ul class="mt-3 space-y-1 text-sm">
                        @foreach (['title' => 'title', 'description' => 'description', 'category' => 'category', 'work_mode' => 'work_mode', 'employment_type' => 'employment_type'] as $gapKey => $labelKey)
                            @php $ok = ! in_array($gapKey, $gaps, true); @endphp
                            <li class="{{ $ok ? 'text-hb-navy' : 'text-hb-error' }}">
                                {{ $ok ? '✓' : '○' }} {{ __('company.jobs.checklist.'.$labelKey) }}
                            </li>
                        @endforeach
                    </ul>
                    @unless ($canPublish)
                        <p class="mt-3 text-xs text-hb-error">{{ __('company.jobs.publish_checklist_incomplete') }}</p>
                    @endunless
                </div>
            </section>
        @endif

        {{-- Preserve fields across steps via hidden inputs for non-active steps --}}
        @if ($step !== 1)
            @foreach (['title','category_id','department','employment_type','work_mode','location','openings_count','salary_min','salary_max','currency','salary_period','experience_years','deadline','visibility','private_apply_mode'] as $k)
                <input type="hidden" name="{{ $k }}" value="{{ $val($k) }}">
            @endforeach
        @endif
        @if ($step !== 2)
            <input type="hidden" name="description" value="{{ $val('description') }}">
            <input type="hidden" name="requirements_text" value="{{ $val('requirements_text') }}">
            <input type="hidden" name="benefits_text" value="{{ $val('benefits_text') }}">
        @endif
        @if ($step !== 3)
            <input type="hidden" name="hiring_model" value="{{ $val('hiring_model', 'direct') }}">
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-hb-border pt-4">
            <div class="flex flex-wrap gap-2">
                @if ($step > 1)
                    <x-button type="submit" name="wizard_action" value="back" variant="light">{{ __('company.jobs.back') }}</x-button>
                @else
                    <x-button variant="light" :href="route('frontend.company.vacancies.index')">{{ __('company.jobs.back_to_jobs') }}</x-button>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($step < 4)
                    <x-button type="submit" name="wizard_action" value="next" data-prefs-submit>{{ __('company.jobs.next') }}</x-button>
                @else
                    <x-button type="submit" name="wizard_action" value="preview" variant="light">{{ __('company.jobs.preview') }}</x-button>
                    <x-button type="submit" name="wizard_action" value="save_draft" variant="light" data-prefs-submit>{{ __('company.jobs.save_draft') }}</x-button>
                    <x-button
                        type="submit"
                        name="wizard_action"
                        value="publish"
                        data-prefs-submit
                        :disabled="! $canPublish"
                        title="{{ $canPublish ? '' : __('company.jobs.publish_disabled_title') }}"
                    >{{ __('company.jobs.publish') }}</x-button>
                @endif
            </div>
        </div>
    </form>
</div>
@endsection
