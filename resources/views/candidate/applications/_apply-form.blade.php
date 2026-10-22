@php
    /** @var array<string, mixed> $flow */
    $profile = $flow['profile'];
    $defaults = $flow['defaults'];
    $job = $flow['job'];
    $flags = $flow['requirements_flags'] ?? ['cv_required' => true, 'ai_consent_required' => true, 'cover_note_required' => false];
    $formId = $formId ?? 'candidate-apply-form';
@endphp

<form
    id="{{ $formId }}"
    class="space-y-6"
    data-candidate-apply-form
    data-submitted-url="{{ route('candidate.applications.submitted', ['job' => $job['id']]) }}"
    data-storage-key="hb_candidate_apply_draft_{{ $job['id'] }}"
    data-cv-required="{{ ! empty($flags['cv_required']) ? '1' : '0' }}"
    data-ai-consent-required="{{ ! empty($flags['ai_consent_required']) ? '1' : '0' }}"
    data-cover-note-required="{{ ! empty($flags['cover_note_required']) ? '1' : '0' }}"
    novalidate
>
    <div data-apply-panel="questions">
        <section class="hb-surface p-5 sm:p-6" aria-labelledby="{{ $formId }}-materials-heading">
            <h2 id="{{ $formId }}-materials-heading" class="text-lg font-semibold text-hb-navy">{{ __('candidate.applications.your_materials') }}</h2>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="hb-label" for="{{ $formId }}-name">{{ __('candidate.applications.full_name') }} <span class="text-hb-error">*</span></label>
                    <input id="{{ $formId }}-name" name="full_name" type="text" class="hb-input" value="{{ $profile['name'] }}" required data-apply-field="full_name" autocomplete="name">
                    <p class="hb-field-error hidden" data-error-for="full_name" role="alert"></p>
                </div>
                <div>
                    <label class="hb-label" for="{{ $formId }}-email">{{ __('candidate.applications.email') }} <span class="text-hb-error">*</span></label>
                    <input id="{{ $formId }}-email" name="email" type="email" class="hb-input" value="{{ $profile['email'] }}" required data-apply-field="email" autocomplete="email">
                    <p class="hb-field-error hidden" data-error-for="email" role="alert"></p>
                </div>
            </div>

            <div class="mt-4">
                <p class="hb-label" id="{{ $formId }}-resume-label">{{ __('candidate.applications.resume') }} <span class="text-hb-error">*</span></p>
                <div
                    class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-hb-border bg-hb-page px-4 py-3"
                    data-apply-resume
                    data-resume-filename="{{ $profile['resume']['filename'] }}"
                    data-resume-size="{{ $profile['resume']['size'] }}"
                    data-resume-updated="{{ $profile['resume']['updated'] }}"
                    aria-labelledby="{{ $formId }}-resume-label"
                >
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-hb-navy">
                            <span aria-hidden="true">✓</span>
                            <span data-resume-label>{{ $profile['resume']['filename'] }} · {{ $profile['resume']['size'] }}</span>
                        </p>
                        <p class="mt-1 text-xs text-hb-muted" data-resume-meta>
                            {{ __('candidate.applications.imported_from_profile', ['date' => $profile['resume']['updated']]) }}
                        </p>
                    </div>
                    <input
                        type="file"
                        id="{{ $formId }}-resume"
                        class="sr-only"
                        accept=".pdf,.doc,.docx,application/pdf"
                        data-apply-resume-input
                        aria-label="{{ __('candidate.applications.choose_resume_aria') }}"
                    >
                </div>
                <p class="hb-field-error hidden" data-error-for="resume" role="alert"></p>
                <button type="button" class="hb-btn-outline mt-3" data-apply-resume-pick>
                    {{ __('candidate.applications.choose_resume') }}
                </button>
            </div>

            <div class="mt-4">
                <label class="hb-label" for="{{ $formId }}-portfolio">{{ __('candidate.applications.portfolio_url') }}</label>
                <input id="{{ $formId }}-portfolio" name="portfolio" type="url" class="hb-input" value="{{ $profile['portfolio'] }}" data-apply-field="portfolio" placeholder="https://">
                <p class="hb-field-error hidden" data-error-for="portfolio" role="alert"></p>
            </div>
        </section>

        <section class="hb-surface mt-6 p-5 sm:p-6" aria-labelledby="{{ $formId }}-questions-heading">
            <h2 id="{{ $formId }}-questions-heading" class="text-lg font-semibold text-hb-navy">{{ __('candidate.applications.role_questions') }}</h2>

            <div class="mt-5">
                <label class="hb-label" for="{{ $formId }}-hybrid">{{ __('candidate.applications.hybrid_austin') }} <span class="text-hb-error">*</span></label>
                <select id="{{ $formId }}-hybrid" name="hybrid" class="hb-input" required data-apply-field="hybrid">
                    <option value="">{{ __('candidate.applications.select_option') }}</option>
                    @foreach ($flow['hybrid_options'] as $option)
                        <option value="{{ $option }}" @selected($option === $defaults['hybrid'])>{{ $option }}</option>
                    @endforeach
                </select>
                <p class="hb-field-error hidden" data-error-for="hybrid" role="alert"></p>
            </div>

            <div class="mt-4">
                <label class="hb-label" for="{{ $formId }}-interest">{{ __('candidate.applications.interest_role') }} <span class="text-hb-error">*</span></label>
                <textarea
                    id="{{ $formId }}-interest"
                    name="interest"
                    rows="4"
                    class="hb-input"
                    required
                    data-apply-field="interest"
                    minlength="20"
                >{{ $defaults['interest'] }}</textarea>
                <p class="hb-field-error hidden" data-error-for="interest" role="alert"></p>
            </div>

            <div class="mt-4">
                <label class="hb-label" for="{{ $formId }}-salary">{{ __('candidate.applications.expected_salary') }}</label>
                <input
                    id="{{ $formId }}-salary"
                    name="salary"
                    type="text"
                    class="hb-input"
                    value="{{ $defaults['salary'] }}"
                    data-apply-field="salary"
                    placeholder="$140,000 – $160,000"
                >
                <p class="hb-field-error hidden" data-error-for="salary" role="alert"></p>
            </div>

            <div class="mt-4" data-field="cover_note">
                <label class="hb-label" for="{{ $formId }}-cover">
                    {{ __('candidate.applications.cover_note') }}
                    @if (! empty($flags['cover_note_required']))
                        <span class="text-hb-error">*</span>
                    @endif
                </label>
                <textarea
                    id="{{ $formId }}-cover"
                    name="cover_note"
                    rows="3"
                    class="hb-input"
                    data-apply-field="cover_note"
                    @if (! empty($flags['cover_note_required'])) required @endif
                >{{ $defaults['cover_note'] ?? '' }}</textarea>
                <p class="hb-field-error hidden" data-error-for="cover_note" role="alert"></p>
            </div>

            <div class="mt-5 space-y-3">
                <label class="flex items-start gap-3 text-sm text-hb-text">
                    <input
                        type="checkbox"
                        name="consent"
                        class="hb-checkbox mt-0.5"
                        value="1"
                        @checked($defaults['consent'] ?? false)
                        data-apply-field="consent"
                        required
                    >
                    <span>
                        {{ __('candidate.applications.consent_processing', ['company' => $job['company']]) }}
                        <span class="text-hb-error">*</span>
                    </span>
                </label>
                <p class="hb-field-error hidden" data-error-for="consent" role="alert"></p>

                @if (! empty($flags['ai_consent_required']))
                    <label class="flex items-start gap-3 text-sm text-hb-text">
                        <input
                            type="checkbox"
                            name="ai_consent"
                            class="hb-checkbox mt-0.5"
                            value="1"
                            @checked($defaults['ai_consent'] ?? false)
                            data-apply-field="ai_consent"
                            required
                        >
                        <span>
                            {{ __('candidate.applications.ai_consent', ['company' => $job['company']]) }}
                            <span class="text-hb-error">*</span>
                        </span>
                    </label>
                    <p class="hb-field-error hidden" data-error-for="ai_consent" role="alert"></p>
                @endif

                <label class="flex items-start gap-3 text-sm text-hb-text">
                    <input
                        type="checkbox"
                        name="talent_pool"
                        class="hb-checkbox mt-0.5"
                        value="1"
                        @checked($defaults['talent_pool'])
                        data-apply-field="talent_pool"
                    >
                    <span>{{ __('candidate.applications.talent_pool', ['company' => $job['company']]) }}</span>
                </label>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <button type="button" class="text-sm font-semibold text-hb-blue hover:underline" data-apply-save-draft>
                    {{ __('candidate.applications.save_draft') }}
                </button>
                <button type="button" class="hb-btn-primary" data-apply-review>
                    {{ __('candidate.applications.review') }}
                </button>
            </div>
            <p class="mt-3 hidden text-sm text-hb-success" data-apply-draft-status role="status"></p>
            <p class="mt-3 hidden text-sm text-hb-error" data-apply-form-error role="alert"></p>
        </section>
    </div>

    <div data-apply-panel="review" class="hidden" hidden>
        <section class="hb-surface p-5 sm:p-6" aria-labelledby="{{ $formId }}-review-heading">
            <h2 id="{{ $formId }}-review-heading" class="text-lg font-semibold text-hb-navy">{{ __('candidate.applications.review_submit_heading') }}</h2>
            <p class="mt-1 text-sm text-hb-muted">
                {{ __('candidate.applications.review_intro') }}
            </p>

            <dl class="mt-5 space-y-3 rounded-lg border border-hb-border bg-hb-page px-4 py-4 text-sm">
                <div class="flex flex-wrap justify-between gap-2">
                    <dt class="font-medium text-hb-muted">{{ __('candidate.applications.review_profile') }}</dt>
                    <dd class="text-end text-hb-navy" data-review-profile>{{ $profile['name'] }} · {{ $profile['email'] }}</dd>
                </div>
                <div class="flex flex-wrap justify-between gap-2">
                    <dt class="font-medium text-hb-muted">{{ __('candidate.applications.review_resume') }}</dt>
                    <dd class="text-end text-hb-navy" data-review-resume>{{ $profile['resume']['filename'] }} · {{ $profile['resume']['size'] }}</dd>
                </div>
                <div class="flex flex-wrap justify-between gap-2">
                    <dt class="font-medium text-hb-muted">{{ __('candidate.applications.review_portfolio') }}</dt>
                    <dd class="text-end text-hb-navy" data-review-portfolio>{{ $profile['portfolio'] }}</dd>
                </div>
                <div class="flex flex-wrap justify-between gap-2">
                    <dt class="font-medium text-hb-muted">{{ __('candidate.applications.review_hybrid') }}</dt>
                    <dd class="text-end text-hb-navy" data-review-hybrid>{{ $defaults['hybrid'] }}</dd>
                </div>
                <div class="flex flex-col gap-1">
                    <dt class="font-medium text-hb-muted">{{ __('candidate.applications.review_motivation') }}</dt>
                    <dd class="text-hb-navy" data-review-interest>{{ $defaults['interest'] }}</dd>
                </div>
                <div class="flex flex-wrap justify-between gap-2">
                    <dt class="font-medium text-hb-muted">{{ __('candidate.applications.review_salary') }}</dt>
                    <dd class="text-end text-hb-navy" data-review-salary>{{ $defaults['salary'] }}</dd>
                </div>
                <div class="flex flex-col gap-1">
                    <dt class="font-medium text-hb-muted">{{ __('candidate.applications.cover_note') }}</dt>
                    <dd class="text-hb-navy" data-review-cover>{{ $defaults['cover_note'] ?? '—' }}</dd>
                </div>
                @if (! empty($flags['ai_consent_required']))
                    <div class="flex flex-wrap justify-between gap-2">
                        <dt class="font-medium text-hb-muted">{{ __('candidate.applications.review_ai_consent') }}</dt>
                        <dd class="text-end text-hb-navy" data-review-ai-consent>{{ ! empty($defaults['ai_consent']) ? __('candidate.applications.consent_yes') : __('candidate.applications.consent_no') }}</dd>
                    </div>
                @endif
            </dl>

            <div
                class="mt-4 hidden rounded-lg border border-hb-error/30 bg-hb-error-soft px-4 py-3 text-sm text-hb-error"
                data-apply-loading-error
                role="alert"
            ></div>

            <div class="mt-6 flex flex-wrap gap-2">
                <button type="button" class="hb-btn-outline" data-apply-back>{{ __('candidate.applications.back_to_questions') }}</button>
                <button type="submit" class="hb-btn-primary" data-apply-submit>
                    <span data-apply-submit-label>{{ __('candidate.applications.submit') }}</span>
                    <span class="hidden" data-apply-submit-loading>{{ __('candidate.applications.submitting') }}</span>
                </button>
            </div>
        </section>
    </div>
</form>
