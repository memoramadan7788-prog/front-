<div class="space-y-5 hidden" data-onboarding-panel data-step="3" hidden>
    <div>
        <h2 class="text-base font-semibold text-hb-navy">{{ __('onboarding.company.hiring.title') }}</h2>
        <p class="mt-1 text-sm text-hb-muted">{{ __('onboarding.company.hiring.intro') }}</p>
    </div>

    <fieldset data-field="hiring_model">
        <legend class="hb-label">{{ __('onboarding.company.hiring.model') }} <span class="text-hb-error">*</span></legend>
        <div class="mt-2 grid gap-3" role="radiogroup" aria-label="{{ __('onboarding.company.hiring.model_aria') }}" data-hiring-model-group>
            @foreach ([
                ['value' => 'direct', 'title' => __('onboarding.company.hiring.models.direct.title'), 'copy' => __('onboarding.company.hiring.models.direct.copy')],
                ['value' => 'agency', 'title' => __('onboarding.company.hiring.models.agency.title'), 'copy' => __('onboarding.company.hiring.models.agency.copy')],
                ['value' => 'both', 'title' => __('onboarding.company.hiring.models.both.title'), 'copy' => __('onboarding.company.hiring.models.both.copy'), 'selected' => true],
            ] as $option)
                <button
                    type="button"
                    role="radio"
                    aria-checked="{{ ! empty($option['selected']) ? 'true' : 'false' }}"
                    data-hiring-model="{{ $option['value'] }}"
                    data-active="{{ ! empty($option['selected']) ? 'true' : 'false' }}"
                    @class([
                        'rounded-xl border px-4 py-3 text-start transition-colors',
                        'border-hb-blue bg-hb-info-soft' => ! empty($option['selected']),
                        'border-hb-border bg-hb-surface' => empty($option['selected']),
                    ])
                >
                    <span @class(['block text-sm font-semibold', 'text-hb-blue' => ! empty($option['selected']), 'text-hb-navy' => empty($option['selected'])])>
                        {{ $option['title'] }}
                    </span>
                    <span class="mt-1 block text-sm text-hb-muted">{{ $option['copy'] }}</span>
                </button>
            @endforeach
        </div>
        <input type="hidden" name="hiring_model" value="both" data-hiring-model-input>
        <p class="hb-field-error hidden" role="alert"></p>
    </fieldset>

    <div class="rounded-lg border border-hb-border bg-hb-page px-4 py-3 text-sm text-hb-muted">
        {{ __('onboarding.company.publish_after_review_short') }}
    </div>
</div>
