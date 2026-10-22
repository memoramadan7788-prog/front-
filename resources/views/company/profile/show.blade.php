@extends('layouts.app')

@section('title', $pageTitle ?? __('company.profile.page_title'))
@section('breadcrumb', __('company.profile.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('company.profile.title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">
        {{ __('company.profile.workspace_intro', ['name' => $profile['identity']['display_name']]) }}
    </p>
@endsection

@section('page_actions')
    <a
        href="{{ route('companies.show', $profile['public_slug']) }}"
        class="hb-btn-outline"
        target="_blank"
        rel="noopener noreferrer"
    >
        {{ __('company.profile.view_public_external') }}
    </a>
@endsection

@section('content')

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
        <div class="space-y-6">
            {{-- Identity --}}
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="identity-heading">
                <h2 id="identity-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.profile.identity_heading') }}</h2>
                <p class="mt-1 text-sm text-hb-muted">{{ __('company.profile.identity_intro') }}</p>

                <form class="mt-5 space-y-4" data-company-profile-form="identity" novalidate aria-label="{{ __('company.profile.identity_form_aria') }}">
                    <div class="flex flex-wrap items-center gap-4">
                        <div
                            class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-hb-navy text-sm font-semibold text-white"
                            aria-hidden="true"
                            data-logo-preview
                        >
                            {{ $profile['identity']['logo_initials'] }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <label for="company-logo" class="hb-label">{{ __('company.profile.company_logo') }}</label>
                            <input
                                id="company-logo"
                                name="logo"
                                type="file"
                                accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                class="block w-full text-sm text-hb-muted file:me-3 file:rounded-lg file:border-0 file:bg-hb-page file:px-3 file:py-2 file:text-sm file:font-semibold file:text-hb-navy hover:file:bg-hb-border/40"
                            >
                            <p class="mt-1.5 text-xs text-hb-muted">{{ __('company.profile.logo_hint') }}</p>
                        </div>
                    </div>

                    <div>
                        <label for="company-legal-name" class="hb-label">{{ __('company.profile.legal_name') }}</label>
                        <input
                            id="company-legal-name"
                            name="legal_name"
                            type="text"
                            class="hb-input"
                            value="{{ $profile['identity']['legal_name'] }}"
                            maxlength="160"
                            autocomplete="organization"
                        >
                    </div>

                    <div>
                        <label for="company-tagline" class="hb-label">{{ __('company.profile.tagline') }}</label>
                        <input
                            id="company-tagline"
                            name="tagline"
                            type="text"
                            class="hb-input"
                            value="{{ $profile['identity']['tagline'] }}"
                            maxlength="160"
                        >
                    </div>

                    <div class="pt-1">
                        <button type="button" class="hb-btn-primary" data-company-profile-save="identity">
                            {{ __('company.profile.save_identity') }}
                        </button>
                    </div>

                    <div
                        class="hidden rounded-lg border border-hb-info-soft bg-hb-info-soft px-3 py-2.5 text-sm text-hb-navy"
                        data-company-profile-note="identity"
                        role="status"
                    >
                        {{ __('company.profile.identity_saved_note') }}
                    </div>
                </form>
            </section>

            {{-- Workspace details --}}
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="workspace-heading">
                <h2 id="workspace-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.profile.workspace') }}</h2>
                <p class="mt-1 text-sm text-hb-muted">{{ __('company.profile.workspace_intro_long') }}</p>

                <form class="mt-5 space-y-4" data-company-profile-form="workspace" novalidate aria-label="{{ __('company.profile.workspace_form_aria') }}">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="company-display-name" class="hb-label">{{ __('company.profile.display_name') }}</label>
                            <input
                                id="company-display-name"
                                name="display_name"
                                type="text"
                                class="hb-input"
                                value="{{ $profile['identity']['display_name'] }}"
                                maxlength="120"
                                required
                                data-preview-source="name"
                            >
                        </div>

                        <div class="sm:col-span-2">
                            <label for="company-workspace-slug" class="hb-label">{{ __('company.profile.workspace_url') }}</label>
                            <div class="flex overflow-hidden rounded-lg border border-hb-border bg-hb-surface focus-within:border-hb-blue focus-within:ring-2 focus-within:ring-hb-blue/20">
                                <span class="inline-flex items-center border-e border-hb-border bg-hb-page px-3 text-sm text-hb-muted">hirebridge.example/</span>
                                <input
                                    id="company-workspace-slug"
                                    name="workspace_slug"
                                    type="text"
                                    class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5 text-sm text-hb-text focus:outline-none focus:ring-0"
                                    value="{{ $profile['workspace']['slug'] }}"
                                    pattern="[a-z0-9\-]+"
                                    required
                                >
                            </div>
                        </div>

                        <div>
                            <label for="company-website" class="hb-label">{{ __('company.profile.company_website') }}</label>
                            <input
                                id="company-website"
                                name="website"
                                type="url"
                                class="hb-input"
                                value="{{ $profile['workspace']['website'] }}"
                                inputmode="url"
                            >
                        </div>

                        <div>
                            <label for="company-industry" class="hb-label">{{ __('company.profile.industry') }}</label>
                            <select id="company-industry" name="industry" class="hb-input" data-preview-source="industry">
                                @foreach ($profile['industries'] as $value => $label)
                                    <option value="{{ $value }}" @selected($value === $profile['workspace']['industry_value'])>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="company-size" class="hb-label">{{ __('company.profile.company_size') }}</label>
                            <select id="company-size" name="company_size" class="hb-input">
                                @foreach ($profile['sizes'] as $value => $label)
                                    <option value="{{ $value }}" @selected($value === $profile['workspace']['company_size_value'])>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="company-hq" class="hb-label">{{ __('company.profile.headquarters') }}</label>
                            <input
                                id="company-hq"
                                name="headquarters"
                                type="text"
                                class="hb-input"
                                value="{{ $profile['workspace']['headquarters'] }}"
                                data-preview-source="location"
                            >
                        </div>

                        <div class="sm:col-span-2">
                            <label for="company-timezone" class="hb-label">{{ __('company.profile.default_timezone') }}</label>
                            <select id="company-timezone" name="timezone" class="hb-input">
                                @foreach ($profile['timezones'] as $value => $label)
                                    <option value="{{ $value }}" @selected($value === $profile['workspace']['timezone'])>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="company-description" class="hb-label">{{ __('company.profile.company_description') }}</label>
                            <textarea
                                id="company-description"
                                name="description"
                                rows="4"
                                class="hb-input"
                                maxlength="800"
                            >{{ $profile['workspace']['description'] }}</textarea>
                            <p class="mt-1.5 text-xs text-hb-muted">{{ __('company.profile.description_public_hint') }}</p>
                        </div>
                    </div>

                    <div class="pt-1">
                        <button type="button" class="hb-btn-primary" data-company-profile-save="workspace">
                            {{ __('company.profile.save_workspace') }}
                        </button>
                    </div>

                    <div
                        class="hidden rounded-lg border border-hb-info-soft bg-hb-info-soft px-3 py-2.5 text-sm text-hb-navy"
                        data-company-profile-note="workspace"
                        role="status"
                    >
                        {{ __('company.profile.workspace_saved_note') }}
                    </div>
                </form>
            </section>

            {{-- Contact --}}
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="contact-heading">
                <h2 id="contact-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.profile.hiring_contact') }}</h2>
                <p class="mt-1 text-sm text-hb-muted">{{ __('company.profile.contact_intro') }}</p>

                <form class="mt-5 space-y-4" data-company-profile-form="contact" novalidate aria-label="{{ __('company.profile.contact_form_aria') }}">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="company-hr-name" class="hb-label">{{ __('company.profile.primary_hr_contact') }}</label>
                            <input
                                id="company-hr-name"
                                name="primary_hr"
                                type="text"
                                class="hb-input"
                                value="{{ $profile['contact']['primary_hr'] }}"
                                autocomplete="name"
                            >
                        </div>
                        <div>
                            <label for="company-hr-email" class="hb-label">{{ __('company.profile.work_email') }}</label>
                            <input
                                id="company-hr-email"
                                name="primary_email"
                                type="email"
                                class="hb-input"
                                value="{{ $profile['contact']['primary_email'] }}"
                                autocomplete="email"
                            >
                        </div>
                        <div class="sm:col-span-2">
                            <label for="company-hr-phone" class="hb-label">{{ __('company.profile.hiring_phone') }}</label>
                            <input
                                id="company-hr-phone"
                                name="hiring_phone"
                                type="tel"
                                class="hb-input"
                                value="{{ $profile['contact']['hiring_phone'] }}"
                                autocomplete="tel"
                            >
                        </div>
                    </div>

                    <div class="pt-1">
                        <button type="button" class="hb-btn-primary" data-company-profile-save="contact">
                            {{ __('company.profile.save_contact') }}
                        </button>
                    </div>

                    <div
                        class="hidden rounded-lg border border-hb-info-soft bg-hb-info-soft px-3 py-2.5 text-sm text-hb-navy"
                        data-company-profile-note="contact"
                        role="status"
                    >
                        {{ __('company.profile.contact_saved_note') }}
                    </div>
                </form>
            </section>
        </div>

        <aside class="space-y-6">
            {{-- Verification --}}
            <section class="hb-surface p-5" aria-labelledby="verification-heading">
                <h2 id="verification-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.profile.verification') }}</h2>
                <div class="mt-4">
                    <x-badge variant="success" :dot="true">{{ $profile['verification']['status'] }}</x-badge>
                    <p class="mt-3 text-sm text-hb-muted">{{ $profile['verification']['detail'] }}</p>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div>
                            <dt class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('company.profile.document') }}</dt>
                            <dd class="mt-0.5 text-hb-navy">{{ $profile['verification']['document'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('company.profile.reviewed') }}</dt>
                            <dd class="mt-0.5 text-hb-navy">{{ $profile['verification']['reviewed_at'] }}</dd>
                        </div>
                    </dl>
                </div>
            </section>

            {{-- Live preview --}}
            <section class="hb-surface p-5" aria-labelledby="preview-heading">
                <h2 id="preview-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.profile.public_preview') }}</h2>
                <p class="mt-1 text-xs text-hb-muted">{{ __('company.profile.preview_hint') }}</p>
                <div class="mt-4 rounded-lg border border-hb-border bg-hb-page p-4">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-hb-navy text-xs font-semibold text-white" aria-hidden="true">
                            {{ $profile['identity']['logo_initials'] }}
                        </span>
                        <div class="min-w-0">
                            <p class="font-semibold text-hb-navy" data-company-preview-name>{{ $profile['identity']['display_name'] }}</p>
                            <p class="text-sm text-hb-muted">
                                <span data-company-preview-industry>{{ $profile['workspace']['industry'] }}</span>
                                ·
                                <span data-company-preview-location>{{ explode(',', $profile['workspace']['headquarters'])[0] }}</span>
                            </p>
                        </div>
                    </div>
                </div>
                <a href="{{ route('company.dashboard') }}" class="mt-4 inline-block text-sm font-semibold text-hb-blue hover:underline">
                    {{ __('company.profile.back_overview') }}
                </a>
            </section>

        </aside>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const storageKey = 'hb_company_profile_draft_v1';
            try {
                const saved = JSON.parse(localStorage.getItem(storageKey) || 'null');
                if (saved && typeof saved === 'object') {
                    Object.entries(saved).forEach(([formKey, values]) => {
                        const form = document.querySelector(`[data-company-profile-form="${formKey}"]`);
                        if (!form || !values) return;
                        Object.entries(values).forEach(([name, value]) => {
                            const field = form.elements.namedItem(name);
                            if (!field || !('value' in field) || field.type === 'file') return;
                            if (field.type === 'checkbox') {
                                field.checked = value === '1' || value === true;
                            } else {
                                field.value = value;
                            }
                        });
                    });
                }
            } catch (e) {
                // Ignore private-mode failures.
            }

            document.querySelectorAll('[data-company-profile-save]').forEach((button) => {
                button.addEventListener('click', () => {
                    const key = button.getAttribute('data-company-profile-save');
                    const form = document.querySelector(`[data-company-profile-form="${key}"]`);
                    const note = document.querySelector(`[data-company-profile-note="${key}"]`);
                    if (form && typeof form.reportValidity === 'function' && !form.reportValidity()) {
                        return;
                    }
                    if (form) {
                        const values = {};
                        [...form.elements].forEach((field) => {
                            if (!field.name || field.type === 'file') return;
                            values[field.name] = field.type === 'checkbox' ? (field.checked ? '1' : '0') : field.value;
                        });
                        try {
                            const existing = JSON.parse(localStorage.getItem(storageKey) || '{}') || {};
                            existing[key] = values;
                            localStorage.setItem(storageKey, JSON.stringify(existing));
                        } catch (e) {
                            // Ignore.
                        }
                    }
                    if (note) {
                        note.classList.remove('hidden');
                        note.focus?.();
                    }
                });
            });

            const nameInput = document.getElementById('company-display-name');
            const industrySelect = document.getElementById('company-industry');
            const hqInput = document.getElementById('company-hq');
            const nameEl = document.querySelector('[data-company-preview-name]');
            const industryEl = document.querySelector('[data-company-preview-industry]');
            const locationEl = document.querySelector('[data-company-preview-location]');

            const syncPreview = () => {
                if (nameEl && nameInput) nameEl.textContent = nameInput.value || 'Northstar Labs';
                if (industryEl && industrySelect) {
                    industryEl.textContent = industrySelect.selectedOptions?.[0]?.textContent?.trim() || 'Software & collaboration';
                }
                if (locationEl && hqInput) {
                    locationEl.textContent = (hqInput.value || 'Austin').split(',')[0]?.trim() || 'Austin';
                }
            };

            [nameInput, industrySelect, hqInput].forEach((el) => {
                el?.addEventListener('input', syncPreview);
                el?.addEventListener('change', syncPreview);
            });
        })();
    </script>
@endpush
