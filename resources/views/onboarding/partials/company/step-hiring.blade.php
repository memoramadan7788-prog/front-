<div class="space-y-5 hidden" data-onboarding-panel data-step="3" hidden>
    <div>
        <h2 class="text-base font-semibold text-hb-navy">Hiring preferences</h2>
        <p class="mt-1 text-sm text-hb-muted">Choose how this workspace will hire. You can change this later in settings.</p>
    </div>

    <fieldset data-field="hiring_model">
        <legend class="hb-label">Hiring model <span class="text-hb-error">*</span></legend>
        <div class="mt-2 grid gap-3" role="radiogroup" aria-label="Hiring model" data-hiring-model-group>
            @foreach ([
                ['value' => 'direct', 'title' => 'Direct hiring', 'copy' => 'Post roles and manage applicants in your company workspace.'],
                ['value' => 'agency', 'title' => 'Agency support', 'copy' => 'Partner with Hirebridge specialists for sourced shortlists.'],
                ['value' => 'both', 'title' => 'Direct hiring and agency support', 'copy' => 'Combine your direct pipeline with specialist recruitment support.', 'selected' => true],
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
        Job publishing becomes available after company review. You can save draft roles now once Backend Team workspace activation is connected.
    </div>
</div>
