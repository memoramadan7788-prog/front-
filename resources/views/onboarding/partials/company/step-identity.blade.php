<div class="space-y-5" data-onboarding-panel data-step="1">
    <div>
        <h2 class="text-base font-semibold text-hb-navy">Company identity</h2>
        <p class="mt-1 text-sm text-hb-muted">Confirm the legal organization you’re setting up. Verification is reviewed by the platform team after Backend submission.</p>
    </div>

    <div data-field="logo" class="flex flex-wrap items-center gap-4">
        <div
            class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-hb-navy text-sm font-semibold text-white"
            data-logo-preview
            aria-hidden="true"
        >
            NS
        </div>
        <div class="min-w-0 flex-1">
            <label for="company-logo" class="hb-label">Company logo</label>
            <input
                id="company-logo"
                name="logo"
                type="file"
                accept="image/png,image/jpeg,image/webp,image/svg+xml"
                class="block w-full text-sm text-hb-muted file:me-3 file:rounded-lg file:border-0 file:bg-hb-page file:px-3 file:py-2 file:text-sm file:font-semibold file:text-hb-navy hover:file:bg-hb-border/40"
                data-file-input
                data-file-preview="logo"
            >
            <p class="mt-1.5 text-xs text-hb-muted">Square mark preferred · Preview only — not uploaded.</p>
            <p class="mt-1 text-xs font-medium text-hb-navy hidden" data-file-name></p>
        </div>
    </div>

    <div data-field="legal_name">
        <label for="company-legal-name" class="hb-label">Legal company name <span class="text-hb-error">*</span></label>
        <input id="company-legal-name" name="legal_name" type="text" class="hb-input" value="Northstar Labs, Inc." required maxlength="160" aria-describedby="company-legal-name-error">
        <p id="company-legal-name-error" class="hb-field-error hidden" role="alert"></p>
    </div>

    <div data-field="document">
        <label for="company-document" class="hb-label">Verification document <span class="text-hb-error">*</span></label>
        <div class="rounded-xl border border-dashed border-hb-border bg-hb-page px-4 py-5">
            <input
                id="company-document"
                name="document"
                type="file"
                accept=".pdf,image/png,image/jpeg,application/pdf"
                class="block w-full text-sm text-hb-muted file:me-3 file:rounded-lg file:border-0 file:bg-hb-surface file:px-3 file:py-2 file:text-sm file:font-semibold file:text-hb-navy file:shadow-hb-sm"
                data-file-input
                data-file-preview="document"
                required
                aria-describedby="company-document-hint company-document-error"
            >
            <p id="company-document-hint" class="mt-2 text-xs text-hb-muted">Business registration or equivalent · Reviewed after Backend submission.</p>
            <div class="mt-3 hidden rounded-lg border border-hb-border bg-hb-surface px-3 py-2 text-sm" data-file-chip>
                <p class="font-medium text-hb-navy" data-file-name></p>
                <p class="text-xs text-hb-muted" data-file-meta></p>
            </div>
        </div>
        <p id="company-document-error" class="hb-field-error hidden" role="alert"></p>
    </div>
</div>
