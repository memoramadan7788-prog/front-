{{-- F9B workflow-derived access, nominations, stages (Frame 51 patterns; no dedicated Figma frame verified). --}}
@php
    $defaults = $wizard['defaults'] ?? [];
@endphp

<div class="mt-8 border-t border-hb-border pt-6" data-wizard-access-block>
    <h3 class="text-base font-semibold text-hb-navy">{{ __('company.jobs.visibility_heading') }}</h3>
    <p class="mt-1 text-sm text-hb-muted">{{ __('company.jobs.visibility_intro') }}</p>

    <fieldset class="mt-4 space-y-3" data-field="visibility">
        <legend class="hb-label">{{ __('company.jobs.visibility_label') }} <span class="text-hb-error">*</span></legend>
        <label class="flex items-start gap-2.5 text-sm text-hb-text">
            <input type="radio" name="visibility" value="public" class="mt-0.5 rounded border-hb-border text-hb-blue" data-wizard-field data-visibility-radio required @checked(($defaults['visibility'] ?? 'public') === 'public')>
            <span>
                <span class="font-medium text-hb-navy">{{ __('company.jobs.visibility_public') }}</span>
                <span class="mt-0.5 block text-hb-muted">{{ __('company.jobs.visibility_public_hint') }}</span>
            </span>
        </label>
        <label class="flex items-start gap-2.5 text-sm text-hb-text">
            <input type="radio" name="visibility" value="private" class="mt-0.5 rounded border-hb-border text-hb-blue" data-wizard-field data-visibility-radio @checked(($defaults['visibility'] ?? '') === 'private')>
            <span>
                <span class="font-medium text-hb-navy">{{ __('company.jobs.visibility_private') }}</span>
                <span class="mt-0.5 block text-hb-muted">{{ __('company.jobs.visibility_private_hint') }}</span>
            </span>
        </label>
        <p class="hb-field-error hidden" data-field-error role="alert"></p>
    </fieldset>

    <div class="mt-4 hidden space-y-3" data-private-mode-block hidden>
        <fieldset class="space-y-3" data-field="private_apply_mode">
            <legend class="hb-label">{{ __('company.jobs.private_mode_label') }} <span class="text-hb-error">*</span></legend>
            <label class="flex items-start gap-2.5 text-sm text-hb-text">
                <input type="radio" name="private_apply_mode" value="all_employees" class="mt-0.5 rounded border-hb-border text-hb-blue" data-wizard-field data-private-mode-radio @checked(($defaults['private_apply_mode'] ?? 'all_employees') === 'all_employees')>
                <span>
                    <span class="font-medium text-hb-navy">{{ __('company.jobs.private_mode_employees') }}</span>
                    <span class="mt-0.5 block text-hb-muted">{{ __('company.jobs.private_mode_employees_hint') }}</span>
                </span>
            </label>
            <label class="flex items-start gap-2.5 text-sm text-hb-text">
                <input type="radio" name="private_apply_mode" value="nominated_only" class="mt-0.5 rounded border-hb-border text-hb-blue" data-wizard-field data-private-mode-radio @checked(($defaults['private_apply_mode'] ?? '') === 'nominated_only')>
                <span>
                    <span class="font-medium text-hb-navy">{{ __('company.jobs.private_mode_nominated') }}</span>
                    <span class="mt-0.5 block text-hb-muted">{{ __('company.jobs.private_mode_nominated_hint') }}</span>
                </span>
            </label>
        </fieldset>

        <div class="hidden rounded-lg border border-hb-border bg-hb-page p-4" data-nomination-block hidden>
            <p class="text-sm font-semibold text-hb-navy">{{ __('company.jobs.nominations_heading') }}</p>
            <p class="mt-1 text-xs text-hb-muted">{{ __('company.jobs.nominations_intro') }}</p>
            <ul class="mt-3 space-y-2">
                @foreach ($wizard['nominee_options'] ?? [] as $nominee)
                    <li>
                        <label class="flex items-start gap-2.5 text-sm text-hb-text">
                            <input
                                type="checkbox"
                                name="nominee_{{ $nominee['id'] }}"
                                value="1"
                                class="hb-checkbox mt-0.5"
                                data-wizard-field
                                data-nominee-checkbox
                                @checked(! empty($defaults['nominee_'.$nominee['id']]))
                            >
                            <span>
                                <span class="font-medium text-hb-navy">{{ $nominee['name'] }}</span>
                                <span class="block text-xs text-hb-muted" dir="ltr">{{ $nominee['email'] }}</span>
                            </span>
                        </label>
                    </li>
                @endforeach
            </ul>
            <p class="mt-2 text-xs text-hb-muted">{{ __('company.jobs.nominations_local_note') }}</p>
        </div>
    </div>
</div>

<div class="mt-8 border-t border-hb-border pt-6" data-wizard-stages-block>
    <h3 class="text-base font-semibold text-hb-navy">{{ __('company.jobs.stages_heading') }}</h3>
    <p class="mt-1 text-sm text-hb-muted">{{ __('company.jobs.stages_intro') }}</p>
    <p class="mt-2 text-xs text-hb-muted">{{ __('company.jobs.stages_design_note') }}</p>

    <div class="mt-4 space-y-3">
        <label class="flex items-start gap-2.5 text-sm text-hb-text">
            <input type="checkbox" name="stage_screening" value="1" class="hb-checkbox mt-0.5" data-wizard-field @checked($defaults['stage_screening'] ?? true)>
            <span>{{ __('company.jobs.stage_screening') }}</span>
        </label>
        <div class="rounded-lg border border-hb-border bg-hb-page p-3">
            <label class="flex items-start gap-2.5 text-sm text-hb-text">
                <input type="checkbox" name="stage_exam" value="1" class="hb-checkbox mt-0.5" data-wizard-field data-stage-exam @checked($defaults['stage_exam'] ?? false)>
                <span>{{ __('company.jobs.stage_exam') }}</span>
            </label>
            <div class="mt-3 {{ empty($defaults['stage_exam']) ? 'hidden' : '' }}" data-exam-attach>
                <label for="stage-exam-id" class="hb-label">{{ __('company.jobs.stage_exam_attach') }}</label>
                <select id="stage-exam-id" name="stage_exam_id" class="hb-input mt-1" data-wizard-field>
                    @foreach ($wizard['exam_options'] ?? [] as $exam)
                        <option value="{{ $exam['value'] }}" @selected(($defaults['stage_exam_id'] ?? '') === $exam['value'])>{{ $exam['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="rounded-lg border border-hb-border bg-hb-page p-3">
            <label class="flex items-start gap-2.5 text-sm text-hb-text">
                <input type="checkbox" name="stage_interview" value="1" class="hb-checkbox mt-0.5" data-wizard-field @checked($defaults['stage_interview'] ?? true)>
                <span>{{ __('company.jobs.stage_interview') }}</span>
            </label>
            <div class="mt-3">
                <label for="stage-interview-rounds" class="hb-label">{{ __('company.jobs.stage_interview_rounds') }}</label>
                <input id="stage-interview-rounds" name="stage_interview_rounds" type="number" min="1" max="5" class="hb-input mt-1 w-24" value="{{ $defaults['stage_interview_rounds'] ?? '1' }}" data-wizard-field>
            </div>
        </div>
        <label class="flex items-start gap-2.5 text-sm text-hb-text">
            <input type="checkbox" name="stage_offer" value="1" class="hb-checkbox mt-0.5" data-wizard-field @checked($defaults['stage_offer'] ?? true)>
            <span>{{ __('company.jobs.stage_offer') }}</span>
        </label>
    </div>
</div>

<div class="mt-8 border-t border-hb-border pt-6">
    <h3 class="text-base font-semibold text-hb-navy">{{ __('company.jobs.apply_requirements_heading') }}</h3>
    <div class="mt-3 space-y-2">
        <label class="flex items-start gap-2.5 text-sm text-hb-text">
            <input type="checkbox" name="cv_required" value="1" class="hb-checkbox mt-0.5" data-wizard-field @checked($defaults['cv_required'] ?? true)>
            <span>{{ __('company.jobs.cv_required') }}</span>
        </label>
        <label class="flex items-start gap-2.5 text-sm text-hb-text">
            <input type="checkbox" name="ai_consent_required" value="1" class="hb-checkbox mt-0.5" data-wizard-field @checked($defaults['ai_consent_required'] ?? true)>
            <span>{{ __('company.jobs.ai_consent_required') }}</span>
        </label>
        <label class="flex items-start gap-2.5 text-sm text-hb-text">
            <input type="checkbox" name="cover_note_required" value="1" class="hb-checkbox mt-0.5" data-wizard-field @checked($defaults['cover_note_required'] ?? false)>
            <span>{{ __('company.jobs.cover_note_required') }}</span>
        </label>
    </div>
</div>
