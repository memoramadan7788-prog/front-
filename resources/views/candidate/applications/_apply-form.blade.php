@php
    /** @var array<string, mixed> $flow */
    $profile = $flow['profile'];
    $defaults = $flow['defaults'];
    $job = $flow['job'];
    $formId = $formId ?? 'candidate-apply-form';
@endphp

<form
    id="{{ $formId }}"
    class="space-y-6"
    data-candidate-apply-form
    data-submitted-url="{{ route('candidate.applications.submitted', ['job' => $job['id']]) }}"
    data-storage-key="hb_candidate_apply_draft_{{ $job['id'] }}"
    novalidate
>
    <div data-apply-panel="questions">
        <section class="hb-surface p-5 sm:p-6" aria-labelledby="{{ $formId }}-materials-heading">
            <h2 id="{{ $formId }}-materials-heading" class="text-lg font-semibold text-hb-navy">Your materials</h2>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="hb-label" for="{{ $formId }}-name">Full name <span class="text-hb-error">*</span></label>
                    <input id="{{ $formId }}-name" name="full_name" type="text" class="hb-input" value="{{ $profile['name'] }}" required data-apply-field="full_name" autocomplete="name">
                    <p class="hb-field-error hidden" data-error-for="full_name" role="alert"></p>
                </div>
                <div>
                    <label class="hb-label" for="{{ $formId }}-email">Email <span class="text-hb-error">*</span></label>
                    <input id="{{ $formId }}-email" name="email" type="email" class="hb-input" value="{{ $profile['email'] }}" required data-apply-field="email" autocomplete="email">
                    <p class="hb-field-error hidden" data-error-for="email" role="alert"></p>
                </div>
            </div>

            <div class="mt-4">
                <p class="hb-label" id="{{ $formId }}-resume-label">Resume <span class="text-hb-error">*</span></p>
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
                            Imported from your profile · Updated {{ $profile['resume']['updated'] }}
                        </p>
                    </div>
                    <input
                        type="file"
                        id="{{ $formId }}-resume"
                        class="sr-only"
                        accept=".pdf,.doc,.docx,application/pdf"
                        data-apply-resume-input
                        aria-label="Choose a different resume"
                    >
                </div>
                <p class="hb-field-error hidden" data-error-for="resume" role="alert"></p>
                <button type="button" class="hb-btn-outline mt-3" data-apply-resume-pick>
                    Choose a different resume
                </button>
            </div>

            <div class="mt-4">
                <label class="hb-label" for="{{ $formId }}-portfolio">Portfolio URL</label>
                <input id="{{ $formId }}-portfolio" name="portfolio" type="url" class="hb-input" value="{{ $profile['portfolio'] }}" data-apply-field="portfolio" placeholder="https://">
                <p class="hb-field-error hidden" data-error-for="portfolio" role="alert"></p>
            </div>
        </section>

        <section class="hb-surface mt-6 p-5 sm:p-6" aria-labelledby="{{ $formId }}-questions-heading">
            <h2 id="{{ $formId }}-questions-heading" class="text-lg font-semibold text-hb-navy">A few role-specific questions</h2>

            <div class="mt-5">
                <label class="hb-label" for="{{ $formId }}-hybrid">Can you work hybrid in Austin? <span class="text-hb-error">*</span></label>
                <select id="{{ $formId }}-hybrid" name="hybrid" class="hb-input" required data-apply-field="hybrid">
                    <option value="">Select an option</option>
                    @foreach ($flow['hybrid_options'] as $option)
                        <option value="{{ $option }}" @selected($option === $defaults['hybrid'])>{{ $option }}</option>
                    @endforeach
                </select>
                <p class="hb-field-error hidden" data-error-for="hybrid" role="alert"></p>
            </div>

            <div class="mt-4">
                <label class="hb-label" for="{{ $formId }}-interest">What interests you about this role? <span class="text-hb-error">*</span></label>
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
                <label class="hb-label" for="{{ $formId }}-salary">Expected annual base salary · USD</label>
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

            <div class="mt-5 space-y-3">
                <label class="flex items-start gap-3 text-sm text-hb-text">
                    <input
                        type="checkbox"
                        name="consent"
                        class="hb-checkbox mt-0.5"
                        value="1"
                        @checked($defaults['consent'])
                        data-apply-field="consent"
                        required
                    >
                    <span>
                        I consent to {{ $job['company'] }} processing my application materials for this role under its applicant privacy notice.
                        <span class="text-hb-error">*</span>
                    </span>
                </label>
                <p class="hb-field-error hidden" data-error-for="consent" role="alert"></p>

                <label class="flex items-start gap-3 text-sm text-hb-text">
                    <input
                        type="checkbox"
                        name="talent_pool"
                        class="hb-checkbox mt-0.5"
                        value="1"
                        @checked($defaults['talent_pool'])
                        data-apply-field="talent_pool"
                    >
                    <span>Keep me in {{ $job['company'] }}’s talent pool for future opportunities. Optional.</span>
                </label>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <button type="button" class="text-sm font-semibold text-hb-blue hover:underline" data-apply-save-draft>
                    Save draft
                </button>
                <button type="button" class="hb-btn-primary" data-apply-review>
                    Review application
                </button>
            </div>
            <p class="mt-3 hidden text-sm text-hb-success" data-apply-draft-status role="status"></p>
            <p class="mt-3 hidden text-sm text-hb-error" data-apply-form-error role="alert"></p>
        </section>
    </div>

    <div data-apply-panel="review" class="hidden" hidden>
        <section class="hb-surface p-5 sm:p-6" aria-labelledby="{{ $formId }}-review-heading">
            <h2 id="{{ $formId }}-review-heading" class="text-lg font-semibold text-hb-navy">Review &amp; submit</h2>
            <p class="mt-1 text-sm text-hb-muted">
                Frontend preview only — confirming continues to a demonstration confirmation screen. Nothing is sent to a server.
            </p>

            <dl class="mt-5 space-y-3 rounded-lg border border-hb-border bg-hb-page px-4 py-4 text-sm">
                <div class="flex flex-wrap justify-between gap-2">
                    <dt class="font-medium text-hb-muted">Profile</dt>
                    <dd class="text-end text-hb-navy" data-review-profile>{{ $profile['name'] }} · {{ $profile['email'] }}</dd>
                </div>
                <div class="flex flex-wrap justify-between gap-2">
                    <dt class="font-medium text-hb-muted">Resume</dt>
                    <dd class="text-end text-hb-navy" data-review-resume>{{ $profile['resume']['filename'] }} · {{ $profile['resume']['size'] }}</dd>
                </div>
                <div class="flex flex-wrap justify-between gap-2">
                    <dt class="font-medium text-hb-muted">Portfolio</dt>
                    <dd class="text-end text-hb-navy" data-review-portfolio>{{ $profile['portfolio'] }}</dd>
                </div>
                <div class="flex flex-wrap justify-between gap-2">
                    <dt class="font-medium text-hb-muted">Hybrid</dt>
                    <dd class="text-end text-hb-navy" data-review-hybrid>{{ $defaults['hybrid'] }}</dd>
                </div>
                <div class="flex flex-col gap-1">
                    <dt class="font-medium text-hb-muted">Motivation</dt>
                    <dd class="text-hb-navy" data-review-interest>{{ $defaults['interest'] }}</dd>
                </div>
                <div class="flex flex-wrap justify-between gap-2">
                    <dt class="font-medium text-hb-muted">Salary</dt>
                    <dd class="text-end text-hb-navy" data-review-salary>{{ $defaults['salary'] }}</dd>
                </div>
            </dl>

            <div
                class="mt-4 hidden rounded-lg border border-hb-error/30 bg-hb-error-soft px-4 py-3 text-sm text-hb-error"
                data-apply-loading-error
                role="alert"
            ></div>

            <div class="mt-6 flex flex-wrap gap-2">
                <button type="button" class="hb-btn-outline" data-apply-back>Back to questions</button>
                <button type="submit" class="hb-btn-primary" data-apply-submit>
                    <span data-apply-submit-label>Confirm preview submission</span>
                    <span class="hidden" data-apply-submit-loading>Preparing preview…</span>
                </button>
            </div>
            <p class="mt-3 text-xs text-hb-muted">
                This continues to the Frame 17 frontend confirmation preview. It does not create a real application.
            </p>
        </section>
    </div>
</form>
