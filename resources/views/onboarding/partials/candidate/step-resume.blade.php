<div class="space-y-5 hidden" data-onboarding-panel data-step="2" hidden>
    <div>
        <h2 class="text-base font-semibold text-hb-navy">{{ __('onboarding.candidate.resume.title') }}</h2>
        <p class="mt-1 text-sm text-hb-muted">{{ __('onboarding.candidate.resume.intro') }}</p>
    </div>

    <div data-field="resume">
        <label for="candidate-resume" class="hb-label">{{ __('onboarding.candidate.resume.label') }} <span class="text-hb-error">*</span></label>
        <div class="rounded-xl border border-dashed border-hb-border bg-hb-page px-4 py-6 text-center">
            <input
                id="candidate-resume"
                name="resume"
                type="file"
                accept=".pdf,.doc,.docx,application/pdf"
                class="mx-auto block w-full max-w-sm text-sm text-hb-muted file:me-3 file:rounded-lg file:border-0 file:bg-hb-surface file:px-3 file:py-2 file:text-sm file:font-semibold file:text-hb-navy file:shadow-hb-sm"
                data-file-input
                data-file-preview="resume"
                data-resume-filename="Maya_Chen_Resume.pdf"
                required
                aria-describedby="candidate-resume-hint candidate-resume-error"
            >
            <p id="candidate-resume-hint" class="mt-3 text-xs text-hb-muted">{{ __('onboarding.candidate.resume.hint') }}</p>
            <div class="mt-3 rounded-lg border border-hb-border bg-hb-surface px-3 py-2 text-start text-sm" data-file-chip>
                <p class="font-medium text-hb-navy" data-file-name>Maya_Chen_Resume.pdf</p>
                <p class="text-xs text-hb-muted" data-file-meta>{{ __('onboarding.candidate.resume.selected_file') }}</p>
            </div>
        </div>
        <p id="candidate-resume-error" class="hb-field-error hidden" role="alert"></p>
    </div>

    <div class="rounded-xl border border-hb-border bg-hb-surface p-4">
        <p class="text-sm font-semibold text-hb-navy">{{ __('onboarding.candidate.resume.experience_reviewed') }}</p>
        <ul class="mt-3 space-y-2 text-sm text-hb-muted">
            <li class="flex gap-2"><span class="text-hb-success" aria-hidden="true">✓</span> Senior Product Designer · Northstar Labs (demo)</li>
            <li class="flex gap-2"><span class="text-hb-success" aria-hidden="true">✓</span> Product Designer · Atlas Health (demo)</li>
            <li class="flex gap-2"><span class="text-hb-success" aria-hidden="true">✓</span> UX Designer · Meridian Studio (demo)</li>
        </ul>
        <p class="mt-3 text-xs text-hb-muted">{{ __('onboarding.candidate.resume.profile_build_hint') }}</p>
    </div>
</div>
