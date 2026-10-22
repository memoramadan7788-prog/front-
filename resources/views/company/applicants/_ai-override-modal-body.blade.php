@php
    $ai = $applicant['ai_screening'] ?? [];
@endphp

<div data-ai-override-form>
    <p class="text-sm text-hb-muted">{{ __('company.applicants.ai.override_intro', ['name' => $applicant['name']]) }}</p>

    <dl class="mt-4 space-y-2 rounded-lg border border-hb-border bg-hb-page px-3 py-3 text-sm">
        <div class="flex justify-between gap-2">
            <dt class="text-hb-muted">{{ __('company.applicants.ai.current_recommendation') }}</dt>
            <dd class="font-medium text-hb-navy">{{ $ai['recommendation_label'] ?? ($ai['badge_label'] ?? '—') }}</dd>
        </div>
        @if (! empty($ai['score_label']))
            <div class="flex justify-between gap-2">
                <dt class="text-hb-muted">{{ __('company.applicants.ai.score') }}</dt>
                <dd class="font-medium text-hb-navy" dir="ltr">{{ $ai['score_label'] }}</dd>
            </div>
        @endif
        <div class="flex justify-between gap-2">
            <dt class="text-hb-muted">{{ __('company.applicants.ai.reviewer_context') }}</dt>
            <dd class="font-medium text-hb-navy">Olivia Bennett · HR</dd>
        </div>
    </dl>

    <fieldset class="mt-4 space-y-2">
        <legend class="hb-label">{{ __('company.applicants.ai.proposed_decision') }} <span class="text-hb-error">*</span></legend>
        <label class="flex items-start gap-2 text-sm text-hb-text">
            <input type="radio" name="ai_override_decision" value="override_advance" class="mt-0.5" data-override-decision checked>
            <span>{{ __('company.applicants.ai.decision_override_advance') }}</span>
        </label>
        <label class="flex items-start gap-2 text-sm text-hb-text">
            <input type="radio" name="ai_override_decision" value="override_hold" class="mt-0.5" data-override-decision>
            <span>{{ __('company.applicants.ai.decision_override_hold') }}</span>
        </label>
    </fieldset>

    <label class="mt-4 block">
        <span class="hb-label">{{ __('company.applicants.ai.reason_required') }} <span class="text-hb-error">*</span></span>
        <textarea
            class="hb-input mt-1 w-full"
            rows="3"
            data-override-reason
            required
            aria-describedby="ai-override-reason-error"
            placeholder="{{ __('company.applicants.ai.reason_placeholder') }}"
        ></textarea>
        <p id="ai-override-reason-error" class="hb-field-error hidden" data-override-reason-error role="alert"></p>
    </label>

    <p class="mt-3 hidden text-sm text-hb-blue" data-override-feedback role="status"></p>
    <p class="mt-3 text-xs text-hb-muted">{{ __('company.applicants.ai.preview_only') }}</p>
</div>
