<div class="space-y-5 hidden" data-onboarding-panel data-step="2" hidden>
    <div>
        <h2 class="text-base font-semibold text-hb-navy">Workspace details</h2>
        <p class="mt-1 text-sm text-hb-muted">These details appear on your public company profile and inside your hiring workspace.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div data-field="display_name" class="sm:col-span-2">
            <label for="company-display-name" class="hb-label">Company display name <span class="text-hb-error">*</span></label>
            <input
                id="company-display-name"
                name="display_name"
                type="text"
                class="hb-input"
                value="Northstar Labs"
                required
                maxlength="120"
                data-preview-source="name"
                aria-describedby="company-display-name-error"
            >
            <p id="company-display-name-error" class="hb-field-error hidden" role="alert"></p>
        </div>

        <div data-field="workspace_slug" class="sm:col-span-2">
            <label for="company-workspace-slug" class="hb-label">Workspace URL <span class="text-hb-error">*</span></label>
            <div class="flex overflow-hidden rounded-lg border border-hb-border bg-hb-surface focus-within:border-hb-blue focus-within:ring-2 focus-within:ring-hb-blue/20">
                <span class="inline-flex items-center border-e border-hb-border bg-hb-page px-3 text-sm text-hb-muted">hirebridge.example/</span>
                <input
                    id="company-workspace-slug"
                    name="workspace_slug"
                    type="text"
                    class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5 text-sm text-hb-text focus:outline-none focus:ring-0"
                    value="northstar-labs"
                    required
                    pattern="[a-z0-9\-]+"
                    aria-describedby="company-workspace-slug-error"
                >
            </div>
            <p id="company-workspace-slug-error" class="hb-field-error hidden" role="alert"></p>
        </div>

        <div data-field="website">
            <label for="company-website" class="hb-label">Company website <span class="text-hb-error">*</span></label>
            <input id="company-website" name="website" type="url" class="hb-input" value="https://northstarlabs.example" required placeholder="https://" inputmode="url" aria-describedby="company-website-error">
            <p id="company-website-error" class="hb-field-error hidden" role="alert"></p>
        </div>

        <div data-field="industry">
            <label for="company-industry" class="hb-label">Industry <span class="text-hb-error">*</span></label>
            <select id="company-industry" name="industry" class="hb-input" required data-preview-source="industry" aria-describedby="company-industry-error">
                <option value="">Select industry</option>
                <option value="software" selected>Software &amp; collaboration</option>
                <option value="health">Health &amp; life sciences</option>
                <option value="finance">Financial services</option>
                <option value="agency">Design &amp; creative</option>
                <option value="other">Other</option>
            </select>
            <p id="company-industry-error" class="hb-field-error hidden" role="alert"></p>
        </div>

        <div data-field="company_size">
            <label for="company-size" class="hb-label">Company size</label>
            <select id="company-size" name="company_size" class="hb-input">
                <option value="1-10">1–10 employees</option>
                <option value="11-50">11–50 employees</option>
                <option value="51-200" selected>51–200 employees</option>
                <option value="201-1000">201–1,000 employees</option>
                <option value="1000+">1,000+ employees</option>
            </select>
        </div>

        <div data-field="headquarters">
            <label for="company-hq" class="hb-label">Headquarters <span class="text-hb-error">*</span></label>
            <input
                id="company-hq"
                name="headquarters"
                type="text"
                class="hb-input"
                value="Austin, Texas, United States"
                required
                data-preview-source="location"
                aria-describedby="company-hq-error"
            >
            <p id="company-hq-error" class="hb-field-error hidden" role="alert"></p>
        </div>

        <div data-field="timezone" class="sm:col-span-2">
            <label for="company-timezone" class="hb-label">Default timezone <span class="text-hb-error">*</span></label>
            <select id="company-timezone" name="timezone" class="hb-input" required aria-describedby="company-timezone-error">
                <option value="America/Chicago" selected>Central Time · America/Chicago</option>
                <option value="America/New_York">Eastern Time · America/New_York</option>
                <option value="America/Denver">Mountain Time · America/Denver</option>
                <option value="America/Los_Angeles">Pacific Time · America/Los_Angeles</option>
                <option value="UTC">UTC</option>
            </select>
            <p id="company-timezone-error" class="hb-field-error hidden" role="alert"></p>
        </div>

        <div data-field="description" class="sm:col-span-2">
            <label for="company-description" class="hb-label">Company description</label>
            <textarea
                id="company-description"
                name="description"
                rows="4"
                class="hb-input"
                maxlength="800"
                data-char-count-input
            >Northstar Labs builds collaborative software that helps operations teams simplify complex work.</textarea>
            <div class="mt-1.5 flex justify-between gap-3 text-xs text-hb-muted">
                <span>Shown on your public company profile.</span>
                <span data-char-count>0 / 800</span>
            </div>
        </div>
    </div>

    <label class="flex items-start gap-2.5 text-sm text-hb-text" data-field="authorized">
        <input id="company-authorized" name="authorized" type="checkbox" class="hb-checkbox mt-0.5" checked required aria-describedby="company-authorized-error">
        <span>I’m authorized to create and manage this company workspace</span>
    </label>
    <p id="company-authorized-error" class="hb-field-error hidden" role="alert"></p>
</div>
