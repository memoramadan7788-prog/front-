<div class="space-y-5 hidden" data-onboarding-panel data-step="1" hidden>
    <div>
        <h2 class="text-base font-semibold text-hb-navy">Basics</h2>
        <p class="mt-1 text-sm text-hb-muted">Start with who you are. You can refine this anytime from Profile &amp; resume.</p>
    </div>

    <div data-field="photo" class="flex flex-wrap items-center gap-4">
        <div
            class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full bg-hb-info-soft text-sm font-semibold text-hb-blue ring-1 ring-hb-border"
            data-photo-preview
            aria-hidden="true"
        >
            MC
        </div>
        <div class="min-w-0 flex-1">
            <label for="candidate-photo" class="hb-label">Profile photo</label>
            <input
                id="candidate-photo"
                name="photo"
                type="file"
                accept="image/png,image/jpeg,image/webp"
                class="block w-full text-sm text-hb-muted file:me-3 file:rounded-lg file:border-0 file:bg-hb-page file:px-3 file:py-2 file:text-sm file:font-semibold file:text-hb-navy hover:file:bg-hb-border/40"
                data-file-input
                data-file-preview="photo"
            >
            <p class="mt-1.5 text-xs text-hb-muted">PNG, JPG, or WebP · Preview only — files are not uploaded.</p>
            <p class="mt-1 text-xs font-medium text-hb-navy hidden" data-file-name></p>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div data-field="headline" class="sm:col-span-2">
            <label for="candidate-headline" class="hb-label">Professional headline <span class="text-hb-error">*</span></label>
            <input
                id="candidate-headline"
                name="headline"
                type="text"
                class="hb-input"
                value="Senior Product Designer"
                required
                maxlength="120"
                data-preview-source="headline"
                aria-describedby="candidate-headline-error"
            >
            <p id="candidate-headline-error" class="hb-field-error hidden" role="alert"></p>
        </div>

        <div data-field="location">
            <label for="candidate-location" class="hb-label">Location <span class="text-hb-error">*</span></label>
            <input
                id="candidate-location"
                name="location"
                type="text"
                class="hb-input"
                value="Austin, TX"
                required
                data-preview-source="location"
                aria-describedby="candidate-location-error"
            >
            <p id="candidate-location-error" class="hb-field-error hidden" role="alert"></p>
        </div>

        <div data-field="phone">
            <label for="candidate-phone" class="hb-label">Phone</label>
            <input id="candidate-phone" name="phone" type="tel" class="hb-input" value="+1 (512) 555-0142" autocomplete="tel">
        </div>

        <div data-field="years_experience">
            <label for="candidate-years" class="hb-label">Years of experience <span class="text-hb-error">*</span></label>
            <input
                id="candidate-years"
                name="years_experience"
                type="number"
                min="0"
                max="50"
                class="hb-input"
                value="8"
                required
                inputmode="numeric"
                data-preview-source="years"
                aria-describedby="candidate-years-error"
            >
            <p id="candidate-years-error" class="hb-field-error hidden" role="alert"></p>
        </div>

        <div data-field="bio" class="sm:col-span-2">
            <label for="candidate-bio" class="hb-label">Short bio</label>
            <textarea
                id="candidate-bio"
                name="bio"
                rows="3"
                class="hb-input"
                maxlength="600"
                data-char-count-input
            >Product designer focused on complex SaaS workflows, research-informed decisions, and accessible design systems.</textarea>
            <div class="mt-1.5 flex justify-between gap-3 text-xs text-hb-muted">
                <span>Optional · Expand later on your profile.</span>
                <span data-char-count>0 / 600</span>
            </div>
        </div>
    </div>
</div>
