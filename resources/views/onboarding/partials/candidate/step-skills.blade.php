<div class="space-y-5" data-onboarding-panel data-step="3">
    <div>
        <h2 class="text-base font-semibold text-hb-navy">What are you looking for?</h2>
        <p class="mt-1 text-sm text-hb-muted">Skills and preferences help match you with roles that fit — without oversharing.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div data-field="preferred_roles" class="sm:col-span-2">
            <label for="candidate-roles" class="hb-label">Preferred roles <span class="text-hb-error">*</span></label>
            <input
                id="candidate-roles"
                name="preferred_roles"
                type="text"
                class="hb-input"
                value="Senior Product Designer · Lead Product Designer"
                required
                aria-describedby="candidate-roles-error"
            >
            <p id="candidate-roles-error" class="hb-field-error hidden" role="alert"></p>
        </div>

        <div data-field="home_location">
            <label for="candidate-home-location" class="hb-label">Home location <span class="text-hb-error">*</span></label>
            <input
                id="candidate-home-location"
                name="home_location"
                type="text"
                class="hb-input"
                value="Austin, Texas, United States"
                required
                aria-describedby="candidate-home-location-error"
            >
            <p id="candidate-home-location-error" class="hb-field-error hidden" role="alert"></p>
        </div>

        <div data-field="work_model">
            <label for="candidate-work-model" class="hb-label">Work model <span class="text-hb-error">*</span></label>
            <select id="candidate-work-model" name="work_model" class="hb-input" required aria-describedby="candidate-work-model-error">
                <option value="hybrid_remote" selected>Hybrid + Remote</option>
                <option value="remote">Remote</option>
                <option value="hybrid">Hybrid</option>
                <option value="onsite">On-site</option>
            </select>
            <p id="candidate-work-model-error" class="hb-field-error hidden" role="alert"></p>
        </div>

        <div data-field="skills" class="sm:col-span-2">
            <label for="candidate-skills" class="hb-label">Skills <span class="text-hb-error">*</span></label>
            <input
                id="candidate-skills"
                name="skills"
                type="text"
                class="hb-input"
                value="Product design · Figma · Design systems · User research"
                required
                aria-describedby="candidate-skills-hint candidate-skills-error"
            >
            <p id="candidate-skills-hint" class="mt-1.5 text-xs text-hb-muted">Separate skills with a middle dot or comma.</p>
            <p id="candidate-skills-error" class="hb-field-error hidden" role="alert"></p>
        </div>

        <div data-field="salary">
            <label for="candidate-salary" class="hb-label">Minimum base salary · USD</label>
            <input
                id="candidate-salary"
                name="salary_min"
                type="text"
                class="hb-input"
                value="$140,000 / year"
                inputmode="text"
            >
        </div>

        <div data-field="availability">
            <label for="candidate-availability" class="hb-label">Availability</label>
            <select id="candidate-availability" name="availability" class="hb-input">
                <option value="immediate">Immediately</option>
                <option value="two_weeks">Two weeks after offer</option>
                <option value="four_weeks" selected>Four weeks after offer</option>
                <option value="flexible">Flexible</option>
            </select>
        </div>
    </div>

    <div class="space-y-3">
        <label class="flex items-start gap-2.5 text-sm text-hb-text">
            <input name="recruiter_supported" type="checkbox" class="hb-checkbox mt-0.5" checked>
            <span>I’m open to recruiter-supported opportunities</span>
        </label>
        <label class="flex items-start gap-2.5 text-sm text-hb-text">
            <input name="private_until_apply" type="checkbox" class="hb-checkbox mt-0.5" checked>
            <span>Only share my profile with employers when I apply or give permission</span>
        </label>
    </div>
</div>
