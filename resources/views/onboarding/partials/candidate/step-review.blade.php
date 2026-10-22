<div class="space-y-5 hidden" data-onboarding-panel data-step="4" hidden>
    <div>
        <h2 class="text-base font-semibold text-hb-navy">{{ __('onboarding.candidate.review.title') }}</h2>
        <p class="mt-1 text-sm text-hb-muted">{{ __('onboarding.candidate.review.intro') }}</p>
    </div>

    <dl class="space-y-3 rounded-xl border border-hb-border bg-hb-page p-4 text-sm">
        <div class="flex justify-between gap-4">
            <dt class="text-hb-muted">{{ __('onboarding.candidate.review.headline') }}</dt>
            <dd class="font-medium text-hb-navy text-end" data-review="headline">Senior Product Designer</dd>
        </div>
        <div class="flex justify-between gap-4">
            <dt class="text-hb-muted">{{ __('onboarding.candidate.review.location') }}</dt>
            <dd class="font-medium text-hb-navy text-end" data-review="location">Austin, TX</dd>
        </div>
        <div class="flex justify-between gap-4">
            <dt class="text-hb-muted">{{ __('onboarding.candidate.review.preferred_roles') }}</dt>
            <dd class="font-medium text-hb-navy text-end" data-review="roles">{{ __('onboarding.candidate.review.empty_value') }}</dd>
        </div>
        <div class="flex justify-between gap-4">
            <dt class="text-hb-muted">{{ __('onboarding.candidate.review.skills') }}</dt>
            <dd class="font-medium text-hb-navy text-end" data-review="skills">{{ __('onboarding.candidate.review.empty_value') }}</dd>
        </div>
        <div class="flex justify-between gap-4">
            <dt class="text-hb-muted">{{ __('onboarding.candidate.review.work_model') }}</dt>
            <dd class="font-medium text-hb-navy text-end" data-review="work_model">{{ __('onboarding.candidate.review.empty_value') }}</dd>
        </div>
        <div class="flex justify-between gap-4">
            <dt class="text-hb-muted">{{ __('onboarding.candidate.review.resume') }}</dt>
            <dd class="font-medium text-hb-navy text-end" data-review="resume">Maya_Chen_Resume.pdf</dd>
        </div>
        <div class="flex justify-between gap-4">
            <dt class="text-hb-muted">{{ __('onboarding.candidate.review.visibility') }}</dt>
            <dd class="font-medium text-hb-navy text-end">{{ __('onboarding.candidate.review.visibility_value') }}</dd>
        </div>
    </dl>

    <div class="rounded-xl border border-hb-border bg-hb-page p-4" data-onboarding-membership-cta>
        <h3 class="text-sm font-semibold text-hb-navy">{{ __('onboarding.candidate.review.membership_heading') }}</h3>
        <p class="mt-1 text-sm text-hb-muted">{{ __('onboarding.candidate.review.membership_body') }}</p>
        <a href="{{ route('candidate.membership') }}" class="mt-3 inline-flex text-sm font-semibold text-hb-blue hover:underline">
            {{ __('onboarding.candidate.review.membership_cta') }} →
        </a>
    </div>
</div>
