@extends('layouts.app')

@section('title', $pageTitle ?? __('company.jobs.create_page_title'))
@section('breadcrumb', __('company.jobs.create_breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('company.jobs.create_title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted" data-wizard-subtitle>
        {{ __('company.jobs.draft_subtitle', ['code' => $wizard['code'], 'title' => $wizard['title_default'], 'time' => $wizard['draft_saved_at']]) }}
    </p>
@endsection

@section('page_actions')
    <a href="{{ route('company.jobs.index') }}" class="hb-btn-outline">{{ __('company.jobs.back_to_jobs') }}</a>
@endsection

@section('content')
    @php
        $organizationContext = $organizationContext ?? \App\Support\Frontend\MockCompanyData::organizationContext();
        $canPublishJobs = (bool) ($organizationContext['capabilities']['can_publish_jobs'] ?? false);
        $canCreateJobs = (bool) ($organizationContext['capabilities']['can_create_jobs'] ?? false);
        $wizardI18n = [
            'draft_subtitle' => __('company.jobs.draft_subtitle'),
            'code' => $wizard['code'],
            'title_default' => $wizard['title_default'],
            'continue_review' => __('company.jobs.continue_review'),
            'edit_role_details' => __('company.jobs.edit_role_details'),
            'draft_saved' => __('company.jobs.js.draft_saved'),
            'preview_ready' => __('company.jobs.js.preview_ready'),
            'required_field' => __('company.jobs.js.required_field'),
            'back' => __('common.actions.back'),
            'visibility_public' => __('company.jobs.visibility_public'),
            'visibility_private' => __('company.jobs.visibility_private'),
            'private_mode_employees' => __('company.jobs.private_mode_employees'),
            'private_mode_nominated' => __('company.jobs.private_mode_nominated'),
            'mode_open' => __('company.jobs.application_mode_open'),
            'none_selected' => __('company.jobs.none_selected'),
            'stage_screening' => __('company.jobs.stage_screening'),
            'stage_exam' => __('company.jobs.stage_exam'),
            'stage_interview' => __('company.jobs.stage_interview'),
            'stage_offer' => __('company.jobs.stage_offer'),
            'cv_required' => __('company.jobs.cv_required'),
            'ai_consent_required' => __('company.jobs.ai_consent_required'),
            'cover_note_required' => __('company.jobs.cover_note_required'),
            'nomination_required_pick' => __('company.jobs.nomination_required_pick'),
        ];
    @endphp
    @include('partials.company-organization-banner', ['organizationContext' => $organizationContext])
    <div
        data-job-wizard
        data-job-id="{{ $wizard['job_id'] ?? 'new' }}"
        data-storage-key="{{ $wizard['storage_key'] ?? 'hb_company_job_draft_new' }}"
        data-defaults="{{ json_encode($wizard['defaults'], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-i18n="{{ json_encode($wizardI18n, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
        data-org-can-create="{{ $canCreateJobs ? '1' : '0' }}"
        data-org-can-publish="{{ $canPublishJobs ? '1' : '0' }}"
    >
    {{-- Wizard steps --}}
    <div class="mb-6 flex flex-wrap gap-4 border-b border-hb-border text-sm font-semibold" role="tablist" aria-label="{{ __('company.jobs.wizard_steps_aria') }}">
        @foreach ($wizard['steps'] as $index => $step)
            <button
                type="button"
                role="tab"
                id="wizard-tab-{{ $step['key'] }}"
                data-wizard-step-tab="{{ $step['key'] }}"
                data-step-index="{{ $index }}"
                aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                class="border-b-2 px-1 pb-3 {{ $index === 0 ? 'border-hb-blue text-hb-blue' : 'border-transparent text-hb-muted hover:text-hb-navy' }}"
            >
                {{ $step['label'] }}
            </button>
        @endforeach
    </div>

    <form class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]" data-wizard-form novalidate>
        <div class="space-y-6 min-w-0">
            {{-- Step 1: Role details --}}
            <section class="hb-surface p-5 sm:p-6" data-wizard-panel="role" aria-labelledby="role-details-heading">
                <h2 id="role-details-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.jobs.role_details') }}</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2" data-field="title">
                        <label for="job-title" class="hb-label">{{ __('company.jobs.job_title') }} <span class="text-hb-error">*</span></label>
                        <input id="job-title" name="title" type="text" class="hb-input" value="{{ $wizard['defaults']['title'] }}" required maxlength="120" data-wizard-field>
                        <p class="hb-field-error hidden" data-field-error role="alert"></p>
                    </div>
                    <div data-field="department">
                        <label for="job-department" class="hb-label">{{ __('company.jobs.department') }} <span class="text-hb-error">*</span></label>
                        <select id="job-department" name="department" class="hb-input" required data-wizard-field>
                            @foreach ($wizard['departments'] as $dept)
                                <option value="{{ $dept }}" @selected($dept === $wizard['defaults']['department'])>{{ $dept }}</option>
                            @endforeach
                        </select>
                        <p class="hb-field-error hidden" data-field-error role="alert"></p>
                    </div>
                    <div data-field="employment">
                        <label for="job-employment" class="hb-label">{{ __('company.jobs.employment_type') }} <span class="text-hb-error">*</span></label>
                        <select id="job-employment" name="employment" class="hb-input" required data-wizard-field>
                            @foreach ($wizard['employment_types'] as $type)
                                <option value="{{ $type }}" @selected($type === $wizard['defaults']['employment'])>{{ $type }}</option>
                            @endforeach
                        </select>
                        <p class="hb-field-error hidden" data-field-error role="alert"></p>
                    </div>
                    <div data-field="location">
                        <label for="job-location" class="hb-label">{{ __('company.jobs.location') }} <span class="text-hb-error">*</span></label>
                        <input id="job-location" name="location" type="text" class="hb-input" value="{{ $wizard['defaults']['location'] }}" required data-wizard-field>
                        <p class="hb-field-error hidden" data-field-error role="alert"></p>
                    </div>
                    <div data-field="work_model">
                        <label for="job-work-model" class="hb-label">{{ __('company.jobs.work_model') }} <span class="text-hb-error">*</span></label>
                        <select id="job-work-model" name="work_model" class="hb-input" required data-wizard-field>
                            @foreach ($wizard['work_models'] as $model)
                                <option value="{{ $model }}" @selected($model === $wizard['defaults']['work_model'])>{{ $model }}</option>
                            @endforeach
                        </select>
                        <p class="hb-field-error hidden" data-field-error role="alert"></p>
                    </div>
                    <div data-field="salary_min">
                        <label for="job-salary-min" class="hb-label">{{ __('company.jobs.salary_min') }} <span class="text-hb-error">*</span></label>
                        <input id="job-salary-min" name="salary_min" type="text" class="hb-input" value="{{ $wizard['defaults']['salary_min'] }}" required data-wizard-field>
                        <p class="hb-field-error hidden" data-field-error role="alert"></p>
                    </div>
                    <div data-field="salary_max">
                        <label for="job-salary-max" class="hb-label">{{ __('company.jobs.salary_max') }} <span class="text-hb-error">*</span></label>
                        <input id="job-salary-max" name="salary_max" type="text" class="hb-input" value="{{ $wizard['defaults']['salary_max'] }}" required data-wizard-field>
                        <p class="hb-field-error hidden" data-field-error role="alert"></p>
                    </div>
                    <div data-field="category">
                        <label for="job-category" class="hb-label">{{ __('company.jobs.category') }} <span class="text-hb-error">*</span></label>
                        <select id="job-category" name="category" class="hb-input" required data-wizard-field>
                            @foreach ($wizard['categories'] ?? [] as $category)
                                <option value="{{ $category }}" @selected($category === ($wizard['defaults']['category'] ?? ''))>{{ $category }}</option>
                            @endforeach
                        </select>
                        <p class="hb-field-error hidden" data-field-error role="alert"></p>
                    </div>
                    <div data-field="openings_count">
                        <label for="job-openings" class="hb-label">{{ __('company.jobs.openings_count') }} <span class="text-hb-error">*</span></label>
                        <input id="job-openings" name="openings_count" type="number" min="1" max="99" class="hb-input" value="{{ $wizard['defaults']['openings_count'] ?? '1' }}" required data-wizard-field>
                        <p class="hb-field-error hidden" data-field-error role="alert"></p>
                    </div>
                    <div data-field="deadline">
                        <label for="job-deadline" class="hb-label">{{ __('company.jobs.deadline') }}</label>
                        <input id="job-deadline" name="deadline" type="text" class="hb-input" value="{{ $wizard['defaults']['deadline'] ?? '' }}" placeholder="{{ __('company.jobs.deadline_placeholder') }}" data-wizard-field>
                    </div>
                </div>
                <p class="mt-4 text-xs text-hb-muted">
                    {{ __('company.jobs.salary_hint') }}
                </p>

                <div class="mt-6">
                    <h3 class="text-sm font-semibold text-hb-navy">{{ __('company.jobs.skills_heading') }}</h3>
                    <p class="mt-1 text-xs text-hb-muted">{{ __('company.jobs.skills_intro') }}</p>
                    <ul class="mt-3 space-y-2">
                        @foreach ($wizard['skill_options'] ?? [] as $skill)
                            @php $key = str_replace([' ', '&'], ['_', ''], strtolower($skill['value'])); @endphp
                            <li class="flex flex-wrap items-center gap-3 rounded-lg border border-hb-border bg-hb-page px-3 py-2 text-sm">
                                <label class="inline-flex items-center gap-2 text-hb-text">
                                    <input type="checkbox" name="skill_{{ $key }}" value="1" class="hb-checkbox" data-wizard-field @checked($wizard['defaults']['skill_'.$key] ?? false)>
                                    <span>{{ $skill['label'] }}</span>
                                </label>
                                <label class="ms-auto inline-flex items-center gap-2 text-xs text-hb-muted">
                                    <input type="checkbox" name="skill_{{ $key }}_required" value="1" class="hb-checkbox" data-wizard-field @checked($wizard['defaults']['skill_'.$key.'_required'] ?? false)>
                                    <span>{{ __('company.jobs.skill_required') }}</span>
                                </label>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>

            {{-- Step 2: Description (shown with role cards on edit steps per Frame 27) --}}
            <section class="hb-surface p-5 sm:p-6" data-wizard-panel="description" aria-labelledby="role-description-heading">
                <h2 id="role-description-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.jobs.role_description') }}</h2>
                <div class="mt-5 space-y-4">
                    <div data-field="about">
                        <label for="job-about" class="hb-label">{{ __('company.jobs.about_opportunity') }} <span class="text-hb-error">*</span></label>
                        <textarea id="job-about" name="about" rows="5" class="hb-input" required maxlength="2000" data-wizard-field data-char-count-input>{{ $wizard['defaults']['about'] }}</textarea>
                        <div class="mt-1.5 flex justify-between gap-3 text-xs text-hb-muted">
                            <span>{{ __('company.jobs.about_public_hint') }}</span>
                            <span data-char-count>0 / 2000</span>
                        </div>
                        <p class="hb-field-error hidden" data-field-error role="alert"></p>
                    </div>
                    <div data-field="requirements">
                        <label for="job-requirements" class="hb-label">{{ __('company.jobs.requirements') }} <span class="text-hb-error">*</span></label>
                        <textarea id="job-requirements" name="requirements" rows="5" class="hb-input" required maxlength="2000" data-wizard-field>{{ $wizard['defaults']['requirements'] }}</textarea>
                        <p class="hb-field-error hidden" data-field-error role="alert"></p>
                    </div>
                    <div data-field="benefits">
                        <label for="job-benefits" class="hb-label">{{ __('company.jobs.benefits') }}</label>
                        <textarea id="job-benefits" name="benefits" rows="3" class="hb-input" maxlength="1000" data-wizard-field>{{ $wizard['defaults']['benefits'] }}</textarea>
                    </div>
                </div>
            </section>

            {{-- Step 3: Hiring process (shown with role cards on edit steps per Frame 27) --}}
            <section class="hb-surface p-5 sm:p-6" data-wizard-panel="hiring" aria-labelledby="ownership-heading">
                <h2 id="ownership-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.jobs.ownership_sourcing') }}</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div data-field="owner">
                        <label for="job-owner" class="hb-label">{{ __('company.jobs.requisition_owner') }} <span class="text-hb-error">*</span></label>
                        <select id="job-owner" name="owner" class="hb-input" required data-wizard-field>
                            @foreach ($wizard['owners'] as $owner)
                                <option value="{{ $owner }}" @selected($owner === $wizard['defaults']['owner'])>{{ $owner }}</option>
                            @endforeach
                        </select>
                        <p class="hb-field-error hidden" data-field-error role="alert"></p>
                    </div>
                    <div data-field="hiring_manager">
                        <label for="job-hiring-manager" class="hb-label">{{ __('company.jobs.hiring_manager') }} <span class="text-hb-error">*</span></label>
                        <select id="job-hiring-manager" name="hiring_manager" class="hb-input" required data-wizard-field>
                            @foreach ($wizard['hiring_managers'] as $manager)
                                <option value="{{ $manager }}" @selected($manager === $wizard['defaults']['hiring_manager'])>{{ $manager }}</option>
                            @endforeach
                        </select>
                        <p class="hb-field-error hidden" data-field-error role="alert"></p>
                    </div>
                </div>

                <fieldset class="mt-5 space-y-3">
                    <legend class="hb-label">{{ __('company.jobs.sourcing_options') }}</legend>
                    <label class="flex items-start gap-2.5 text-sm text-hb-text">
                        <input type="checkbox" name="publish_to_search" class="hb-checkbox mt-0.5" value="1" @checked($wizard['defaults']['publish_to_search']) data-wizard-field>
                        <span>{{ __('company.jobs.publish_to_search') }}</span>
                    </label>
                    <label class="flex items-start gap-2.5 text-sm text-hb-text">
                        <input type="checkbox" name="agency_submissions" class="hb-checkbox mt-0.5" value="1" @checked($wizard['defaults']['agency_submissions']) data-wizard-field>
                        <span>{{ __('company.jobs.agency_submissions') }}</span>
                    </label>
                    <label class="flex items-start gap-2.5 text-sm text-hb-text">
                        <input type="checkbox" name="accommodation_info" class="hb-checkbox mt-0.5" value="1" @checked($wizard['defaults']['accommodation_info']) data-wizard-field>
                        <span>{{ __('company.jobs.accommodation_info') }}</span>
                    </label>
                </fieldset>

                @include('company.jobs.wizard._access-and-stages', ['wizard' => $wizard])
            </section>

            {{-- Step 4: Review --}}
            <section class="hb-surface p-5 sm:p-6 hidden" data-wizard-panel="review" hidden aria-labelledby="review-heading">
                <h2 id="review-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.jobs.review_publish') }}</h2>
                <p class="mt-1 text-sm text-hb-muted">{{ __('company.jobs.review_confirm') }}</p>
                <dl class="mt-5 space-y-3 text-sm" data-review-summary>
                    <div class="grid gap-1 sm:grid-cols-[10rem_minmax(0,1fr)]">
                        <dt class="text-hb-muted">{{ __('company.jobs.review_title') }}</dt>
                        <dd class="font-medium text-hb-navy" data-review="title"></dd>
                    </div>
                    <div class="grid gap-1 sm:grid-cols-[10rem_minmax(0,1fr)]">
                        <dt class="text-hb-muted">{{ __('company.jobs.review_department') }}</dt>
                        <dd class="font-medium text-hb-navy" data-review="department"></dd>
                    </div>
                    <div class="grid gap-1 sm:grid-cols-[10rem_minmax(0,1fr)]">
                        <dt class="text-hb-muted">{{ __('company.jobs.review_location_model') }}</dt>
                        <dd class="font-medium text-hb-navy" data-review="location_model"></dd>
                    </div>
                    <div class="grid gap-1 sm:grid-cols-[10rem_minmax(0,1fr)]">
                        <dt class="text-hb-muted">{{ __('company.jobs.review_salary') }}</dt>
                        <dd class="font-medium text-hb-navy" data-review="salary"></dd>
                    </div>
                    <div class="grid gap-1 sm:grid-cols-[10rem_minmax(0,1fr)]">
                        <dt class="text-hb-muted">{{ __('company.jobs.review_owner') }}</dt>
                        <dd class="font-medium text-hb-navy" data-review="owner"></dd>
                    </div>
                    <div class="grid gap-1 sm:grid-cols-[10rem_minmax(0,1fr)]">
                        <dt class="text-hb-muted">{{ __('company.jobs.review_hiring_manager') }}</dt>
                        <dd class="font-medium text-hb-navy" data-review="hiring_manager"></dd>
                    </div>
                    <div class="grid gap-1 sm:grid-cols-[10rem_minmax(0,1fr)]">
                        <dt class="text-hb-muted">{{ __('company.jobs.review_visibility') }}</dt>
                        <dd class="font-medium text-hb-navy" data-review="visibility"></dd>
                    </div>
                    <div class="grid gap-1 sm:grid-cols-[10rem_minmax(0,1fr)]">
                        <dt class="text-hb-muted">{{ __('company.jobs.review_application_mode') }}</dt>
                        <dd class="font-medium text-hb-navy" data-review="application_mode"></dd>
                    </div>
                    <div class="grid gap-1 sm:grid-cols-[10rem_minmax(0,1fr)]">
                        <dt class="text-hb-muted">{{ __('company.jobs.review_openings') }}</dt>
                        <dd class="font-medium text-hb-navy" data-review="openings"></dd>
                    </div>
                    <div class="grid gap-1 sm:grid-cols-[10rem_minmax(0,1fr)]">
                        <dt class="text-hb-muted">{{ __('company.jobs.review_deadline') }}</dt>
                        <dd class="font-medium text-hb-navy" data-review="deadline"></dd>
                    </div>
                    <div class="grid gap-1 sm:grid-cols-[10rem_minmax(0,1fr)]">
                        <dt class="text-hb-muted">{{ __('company.jobs.review_stages') }}</dt>
                        <dd class="font-medium text-hb-navy" data-review="stages"></dd>
                    </div>
                    <div class="grid gap-1 sm:grid-cols-[10rem_minmax(0,1fr)]">
                        <dt class="text-hb-muted">{{ __('company.jobs.review_nominations') }}</dt>
                        <dd class="font-medium text-hb-navy" data-review="nominations"></dd>
                    </div>
                    <div class="grid gap-1 sm:grid-cols-[10rem_minmax(0,1fr)]">
                        <dt class="text-hb-muted">{{ __('company.jobs.review_requirements') }}</dt>
                        <dd class="font-medium text-hb-navy" data-review="requirements_flags"></dd>
                    </div>
                </dl>
                <div class="mt-5 rounded-lg border border-hb-info-soft bg-hb-info-soft px-4 py-3 text-sm text-hb-navy" role="status">
                    {{ __('company.jobs.publish_requires_approval') }}
                </div>
                @if (! $canPublishJobs)
                    <div class="mt-3 rounded-lg border border-hb-pending/30 bg-hb-pending-soft px-4 py-3 text-sm text-hb-navy" role="status" data-org-publish-gate>
                        {{ __('company.organization.publish_jobs_unavailable') }}
                    </div>
                @endif
            </section>

            <div
                class="hidden rounded-lg border border-hb-info-soft bg-hb-info-soft px-4 py-3 text-sm text-hb-navy"
                data-wizard-status
                role="status"
            ></div>

            {{-- Footer actions --}}
            <div class="flex flex-col gap-3 border-t border-hb-border pt-5 sm:flex-row sm:flex-wrap sm:items-center">
                <button type="button" class="hb-btn-outline" data-wizard-save-draft @if (! $canCreateJobs) disabled title="{{ __('company.organization.create_jobs_unavailable') }}" @endif>{{ __('company.jobs.save_draft') }}</button>
                <button type="button" class="hb-btn-outline" data-wizard-preview>{{ __('company.jobs.preview') }}</button>
                <button type="button" class="hb-btn-outline" data-wizard-back hidden>{{ __('common.actions.back') }}</button>
                <button type="button" class="hb-btn-primary" data-wizard-continue @if (! $canCreateJobs) disabled @endif>{{ __('company.jobs.continue_review') }}</button>
                <span class="sr-only" data-wizard-continue-label>{{ __('company.jobs.continue_review') }}</span>
                <button
                    type="button"
                    class="hb-btn-primary opacity-50"
                    data-wizard-publish
                    disabled
                    title="{{ $canPublishJobs ? __('company.jobs.publish_disabled_title') : __('company.organization.publish_jobs_unavailable') }}"
                    data-org-can-publish="{{ $canPublishJobs ? '1' : '0' }}"
                >
                    {{ __('company.jobs.publish') }}
                </button>
            </div>
        </div>

        <aside class="space-y-4">
            <section class="hb-surface p-5" aria-labelledby="checklist-heading">
                <div class="flex items-center justify-between gap-2">
                    <h2 id="checklist-heading" class="text-base font-semibold text-hb-navy">{{ __('company.jobs.publication_checklist') }}</h2>
                    <x-badge variant="pending" :dot="true">{{ __('company.jobs.draft_not_public') }}</x-badge>
                </div>
                <ul class="mt-4 space-y-2.5 text-sm" data-publication-checklist>
                    @foreach ($wizard['checklist'] as $item)
                        <li class="flex items-start gap-2" data-check-item="{{ $item['key'] }}" data-done="{{ $item['done'] ? 'true' : 'false' }}">
                            <span class="mt-0.5 inline-flex h-4 w-4 items-center justify-center rounded-full border text-[10px] {{ $item['done'] ? 'border-hb-blue bg-hb-blue text-white' : 'border-hb-border text-transparent' }}" data-check-icon aria-hidden="true">
                                {{ $item['done'] ? '✓' : '○' }}
                            </span>
                            <span class="{{ $item['done'] ? 'text-hb-navy' : 'text-hb-muted' }}" data-check-label>{{ $item['label'] }}</span>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-4 text-xs text-hb-muted">
                    {{ __('company.jobs.checklist_footer') }}
                </p>
            </section>

            <section class="rounded-lg border border-hb-info-soft bg-hb-info-soft p-4 text-sm text-hb-navy" aria-label="{{ __('company.jobs.accessibility_tip_aria') }}">
                <p class="font-semibold">{{ __('company.jobs.accessible_descriptions') }}</p>
                <p class="mt-1 text-hb-muted">
                    {{ __('company.jobs.accessible_descriptions_hint') }}
                </p>
            </section>
        </aside>
    </form>

    <p class="mt-8 text-xs text-hb-muted">{{ $wizard['footer'] }}</p>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const root = document.querySelector('[data-job-wizard]');
            const form = document.querySelector('[data-wizard-form]');
            if (!root || !form) return;

            const i18n = JSON.parse(root.getAttribute('data-i18n') || '{}');

            function replacePlaceholders(template, replacements) {
                return Object.entries(replacements).reduce(
                    (text, [key, value]) => text.replaceAll(`:${key}`, String(value)),
                    template ?? '',
                );
            }

            function formatDraftSubtitle(title, savedAt) {
                return replacePlaceholders(i18n.draft_subtitle, {
                    code: i18n.code,
                    title: title || i18n.title_default,
                    time: savedAt,
                });
            }

            const storageKey = root.getAttribute('data-storage-key');
            const steps = ['role', 'description', 'hiring', 'review'];
            const panels = Object.fromEntries(steps.map((key) => [key, form.querySelector(`[data-wizard-panel="${key}"]`)]));
            const tabs = [...document.querySelectorAll('[data-wizard-step-tab]')];
            const statusEl = document.querySelector('[data-wizard-status]');
            const subtitle = document.querySelector('[data-wizard-subtitle]');
            const continueBtn = document.querySelector('[data-wizard-continue]');
            const backBtn = document.querySelector('[data-wizard-back]');
            const saveBtn = document.querySelector('[data-wizard-save-draft]');
            const previewBtn = document.querySelector('[data-wizard-preview]');
            let stepIndex = 0;

            function fieldValue(name) {
                const el = form.elements.namedItem(name);
                if (!el) return '';
                if (el instanceof RadioNodeList) return el.value;
                if (el.type === 'checkbox') return el.checked;
                return el.value?.trim?.() ?? el.value ?? '';
            }

            function setStatus(message) {
                if (!statusEl) return;
                statusEl.textContent = message;
                statusEl.classList.remove('hidden');
            }

            function persistDraft() {
                if (!storageKey) return;
                const data = {};
                form.querySelectorAll('[data-wizard-field]').forEach((el) => {
                    if (!el.name) return;
                    data[el.name] = el.type === 'checkbox' ? el.checked : el.value;
                });
                data._savedAt = new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
                window.localStorage.setItem(storageKey, JSON.stringify(data));
                if (subtitle) {
                    subtitle.textContent = formatDraftSubtitle(fieldValue('title'), data._savedAt);
                }
            }

            function restoreDraft() {
                if (!storageKey) return;
                try {
                    const raw = window.localStorage.getItem(storageKey);
                    if (!raw) return;
                    const data = JSON.parse(raw);
                    Object.entries(data).forEach(([key, value]) => {
                        if (key.startsWith('_')) return;
                        const el = form.elements.namedItem(key);
                        if (!el) return;
                        if (el.type === 'checkbox') el.checked = Boolean(value);
                        else el.value = value;
                    });
                    if (data._savedAt && subtitle) {
                        subtitle.textContent = formatDraftSubtitle(data.title, data._savedAt);
                    }
                } catch (_) {}
            }

            function updateCharCount() {
                form.querySelectorAll('[data-char-count-input]').forEach((input) => {
                    const counter = input.parentElement?.querySelector('[data-char-count]');
                    if (!counter) return;
                    const max = Number(input.getAttribute('maxlength') || 2000);
                    counter.textContent = `${input.value.length} / ${max}`;
                });
            }

            function syncAccessBlocks() {
                const visibility = fieldValue('visibility') || 'public';
                const privateBlock = form.querySelector('[data-private-mode-block]');
                const nominationBlock = form.querySelector('[data-nomination-block]');
                const isPrivate = visibility === 'private';
                if (privateBlock) {
                    privateBlock.classList.toggle('hidden', !isPrivate);
                    privateBlock.hidden = !isPrivate;
                }
                const mode = fieldValue('private_apply_mode') || 'all_employees';
                const showNom = isPrivate && mode === 'nominated_only';
                if (nominationBlock) {
                    nominationBlock.classList.toggle('hidden', !showNom);
                    nominationBlock.hidden = !showNom;
                }
                const examAttach = form.querySelector('[data-exam-attach]');
                const examOn = Boolean(fieldValue('stage_exam'));
                if (examAttach) {
                    examAttach.classList.toggle('hidden', !examOn);
                }
            }

            function updateChecklist() {
                const checks = {
                    role: Boolean(fieldValue('title') && fieldValue('location') && fieldValue('department') && fieldValue('category') && fieldValue('openings_count')),
                    salary: Boolean(fieldValue('salary_min') && fieldValue('salary_max')),
                    description: Boolean(fieldValue('about') && fieldValue('requirements')),
                    visibility: Boolean(fieldValue('visibility')),
                    stages: Boolean(fieldValue('stage_screening') || fieldValue('stage_interview') || fieldValue('stage_exam') || fieldValue('stage_offer')),
                    privacy: Boolean(fieldValue('accommodation_info')),
                    hiring: Boolean(fieldValue('owner') && fieldValue('hiring_manager') && stepIndex >= 2),
                    approval: false,
                };
                Object.entries(checks).forEach(([key, done]) => {
                    const item = document.querySelector(`[data-check-item="${key}"]`);
                    if (!item) return;
                    item.setAttribute('data-done', done ? 'true' : 'false');
                    const icon = item.querySelector('[data-check-icon]');
                    const label = item.querySelector('[data-check-label]');
                    if (icon) {
                        icon.textContent = done ? '✓' : '○';
                        icon.className = `mt-0.5 inline-flex h-4 w-4 items-center justify-center rounded-full border text-[10px] ${done ? 'border-hb-blue bg-hb-blue text-white' : 'border-hb-border text-transparent'}`;
                    }
                    if (label) label.className = done ? 'text-hb-navy' : 'text-hb-muted';
                });
            }

            function syncReview() {
                const set = (key, value) => {
                    const el = form.querySelector(`[data-review="${key}"]`);
                    if (el) el.textContent = value || '—';
                };
                set('title', fieldValue('title'));
                set('department', fieldValue('department'));
                set('location_model', `${fieldValue('location')} · ${fieldValue('work_model')}`);
                set('salary', `${fieldValue('salary_min')} – ${fieldValue('salary_max')}`);
                set('owner', fieldValue('owner'));
                set('hiring_manager', fieldValue('hiring_manager'));
                const visibility = fieldValue('visibility') || 'public';
                set('visibility', visibility === 'private' ? i18n.visibility_private : i18n.visibility_public);
                let modeLabel = i18n.mode_open;
                if (visibility === 'private') {
                    modeLabel = fieldValue('private_apply_mode') === 'nominated_only'
                        ? i18n.private_mode_nominated
                        : i18n.private_mode_employees;
                }
                set('application_mode', modeLabel);
                set('openings', fieldValue('openings_count'));
                set('deadline', fieldValue('deadline') || i18n.none_selected);
                const stages = [];
                if (fieldValue('stage_screening')) stages.push(i18n.stage_screening);
                if (fieldValue('stage_exam')) stages.push(i18n.stage_exam);
                if (fieldValue('stage_interview')) stages.push(`${i18n.stage_interview} ×${fieldValue('stage_interview_rounds') || '1'}`);
                if (fieldValue('stage_offer')) stages.push(i18n.stage_offer);
                set('stages', stages.join(' · ') || i18n.none_selected);
                const nominees = [...form.querySelectorAll('[data-nominee-checkbox]:checked')].map((el) => {
                    const label = el.closest('label')?.querySelector('.font-medium');
                    return label?.textContent?.trim() || el.name;
                });
                set('nominations', visibility === 'private' && fieldValue('private_apply_mode') === 'nominated_only'
                    ? (nominees.join(', ') || i18n.nomination_required_pick)
                    : i18n.none_selected);
                const reqs = [];
                if (fieldValue('cv_required')) reqs.push(i18n.cv_required);
                if (fieldValue('ai_consent_required')) reqs.push(i18n.ai_consent_required);
                if (fieldValue('cover_note_required')) reqs.push(i18n.cover_note_required);
                set('requirements_flags', reqs.join(' · ') || i18n.none_selected);
            }

            function validateStep(index) {
                const key = steps[index];
                const panel = panels[key];
                if (!panel || key === 'review') return true;
                let valid = true;
                panel.querySelectorAll('[required]').forEach((el) => {
                    const wrap = el.closest('[data-field]');
                    const error = wrap?.querySelector('[data-field-error]');
                    const ok = el.checkValidity();
                    if (!ok) {
                        valid = false;
                        if (error) {
                            error.textContent = el.validationMessage || i18n.required_field;
                            error.classList.remove('hidden');
                        }
                        el.setAttribute('aria-invalid', 'true');
                    } else if (error) {
                        error.classList.add('hidden');
                        el.removeAttribute('aria-invalid');
                    }
                });
                if (key === 'hiring' && fieldValue('visibility') === 'private'
                    && fieldValue('private_apply_mode') === 'nominated_only') {
                    const picked = form.querySelectorAll('[data-nominee-checkbox]:checked').length > 0;
                    const nomBlock = form.querySelector('[data-nomination-block]');
                    if (!picked) {
                        valid = false;
                        setStatus(i18n.nomination_required_pick);
                        nomBlock?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
                if (!valid) {
                    const first = panel.querySelector('[aria-invalid="true"]');
                    first?.focus();
                }
                return valid;
            }

            function showStep(index) {
                stepIndex = Math.max(0, Math.min(steps.length - 1, index));
                const onReview = stepIndex === steps.length - 1;
                // Frame 27 shows Role details + Description + Ownership cards together on edit steps.
                ['role', 'description', 'hiring'].forEach((key) => {
                    const panel = panels[key];
                    if (!panel) return;
                    panel.classList.toggle('hidden', onReview);
                    panel.hidden = onReview;
                });
                if (panels.review) {
                    panels.review.classList.toggle('hidden', !onReview);
                    panels.review.hidden = !onReview;
                }
                steps.forEach((key) => {
                    const tab = tabs.find((t) => t.getAttribute('data-wizard-step-tab') === key);
                    const active = key === steps[stepIndex];
                    if (tab) {
                        tab.setAttribute('aria-selected', active ? 'true' : 'false');
                        tab.classList.toggle('border-hb-blue', active);
                        tab.classList.toggle('text-hb-blue', active);
                        tab.classList.toggle('border-transparent', !active);
                        tab.classList.toggle('text-hb-muted', !active);
                    }
                });
                if (backBtn) backBtn.hidden = stepIndex === 0;
                if (continueBtn) {
                    continueBtn.textContent = onReview ? i18n.edit_role_details : i18n.continue_review;
                }
                if (onReview) {
                    syncReview();
                } else {
                    const target = panels[steps[stepIndex]];
                    target?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                updateChecklist();
            }

            function validateEditSteps() {
                return validateStep(0) && validateStep(1) && validateStep(2);
            }

            tabs.forEach((tab) => {
                tab.addEventListener('click', () => {
                    const next = Number(tab.getAttribute('data-step-index') || 0);
                    if (next > stepIndex) {
                        for (let i = stepIndex; i < next; i++) {
                            if (!validateStep(i)) return;
                        }
                    }
                    persistDraft();
                    showStep(next);
                });
            });

            continueBtn?.addEventListener('click', () => {
                if (stepIndex === steps.length - 1) {
                    showStep(0);
                    return;
                }
                if (!validateEditSteps()) {
                    if (!validateStep(0)) showStep(0);
                    else if (!validateStep(1)) showStep(1);
                    else showStep(2);
                    return;
                }
                persistDraft();
                showStep(steps.length - 1);
            });

            backBtn?.addEventListener('click', () => {
                persistDraft();
                showStep(stepIndex - 1);
            });

            saveBtn?.addEventListener('click', () => {
                persistDraft();
                setStatus(i18n.draft_saved);
            });

            previewBtn?.addEventListener('click', () => {
                if (!validateEditSteps()) {
                    if (!validateStep(0)) showStep(0);
                    else if (!validateStep(1)) showStep(1);
                    else showStep(2);
                    return;
                }
                syncReview();
                showStep(steps.length - 1);
                setStatus(i18n.preview_ready);
            });

            form.querySelectorAll('[data-wizard-field]').forEach((el) => {
                el.addEventListener('input', () => {
                    updateCharCount();
                    updateChecklist();
                    syncAccessBlocks();
                    persistDraft();
                });
                el.addEventListener('change', () => {
                    updateChecklist();
                    syncAccessBlocks();
                    persistDraft();
                });
            });

            restoreDraft();
            updateCharCount();
            syncAccessBlocks();
            showStep(0);
        })();
    </script>
@endpush
