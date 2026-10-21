@extends('layouts.public')

@section('title', $pageTitle ?? 'Contact · Hirebridge')

@section('content')
    {{-- Figma 06 · Contact (8:31634) --}}
    <section class="bg-hb-page">
        <div class="hb-container py-10 lg:py-14">
            <p class="text-xs font-semibold tracking-[0.12em] text-hb-muted uppercase">
                06 / Hirebridge / Let’s talk about your next hire.
            </p>

            <div class="mt-4 grid gap-10 lg:grid-cols-[minmax(0,1fr)_360px]">
                <div>
                    <h1 class="text-3xl font-semibold text-hb-navy sm:text-4xl">Let’s talk about your next hire.</h1>
                    <p class="mt-3 max-w-2xl text-sm text-hb-muted">
                        Tell us what your team needs. We’ll help you find the right hiring approach.
                    </p>

                    <form id="contact-form" class="mt-8 hb-surface space-y-5 p-6 lg:p-8" novalidate data-frontend-only-form>
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-lg font-semibold text-hb-navy">Request a consultation</h2>
                            <span class="rounded-full bg-hb-pending-soft px-2 py-0.5 text-[10px] font-semibold text-hb-pending uppercase">Frontend only</span>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="full_name" class="text-sm font-medium text-hb-text">Full name <span class="text-hb-error">*</span></label>
                                <input id="full_name" name="full_name" type="text" required autocomplete="name"
                                       class="mt-1.5 w-full rounded-lg border border-hb-border px-3 py-2.5 text-sm" aria-describedby="full_name-error">
                                <p id="full_name-error" class="mt-1 hidden text-xs text-hb-error" data-error-for="full_name"></p>
                            </div>
                            <div>
                                <label for="work_email" class="text-sm font-medium text-hb-text">Work email <span class="text-hb-error">*</span></label>
                                <input id="work_email" name="work_email" type="email" required autocomplete="email"
                                       class="mt-1.5 w-full rounded-lg border border-hb-border px-3 py-2.5 text-sm" aria-describedby="work_email-error">
                                <p id="work_email-error" class="mt-1 hidden text-xs text-hb-error" data-error-for="work_email"></p>
                            </div>
                            <div>
                                <label for="company" class="text-sm font-medium text-hb-text">Company <span class="text-hb-error">*</span></label>
                                <input id="company" name="company" type="text" required autocomplete="organization"
                                       class="mt-1.5 w-full rounded-lg border border-hb-border px-3 py-2.5 text-sm" aria-describedby="company-error">
                                <p id="company-error" class="mt-1 hidden text-xs text-hb-error" data-error-for="company"></p>
                            </div>
                            <div>
                                <label for="role_title" class="text-sm font-medium text-hb-text">Your role <span class="text-hb-error">*</span></label>
                                <input id="role_title" name="role_title" type="text" required
                                       class="mt-1.5 w-full rounded-lg border border-hb-border px-3 py-2.5 text-sm" aria-describedby="role_title-error">
                                <p id="role_title-error" class="mt-1 hidden text-xs text-hb-error" data-error-for="role_title"></p>
                            </div>
                            <div>
                                <label for="hiring_model" class="text-sm font-medium text-hb-text">Hiring model <span class="text-hb-error">*</span></label>
                                <select id="hiring_model" name="hiring_model" required class="mt-1.5 w-full rounded-lg border border-hb-border px-3 py-2.5 text-sm" aria-describedby="hiring_model-error">
                                    <option value="">Select…</option>
                                    <option>Agency support + direct hiring</option>
                                    <option>Direct hiring only</option>
                                    <option>Agency support only</option>
                                </select>
                                <p id="hiring_model-error" class="mt-1 hidden text-xs text-hb-error" data-error-for="hiring_model"></p>
                            </div>
                            <div>
                                <label for="expected_hires" class="text-sm font-medium text-hb-text">Expected hires</label>
                                <select id="expected_hires" name="expected_hires" class="mt-1.5 w-full rounded-lg border border-hb-border px-3 py-2.5 text-sm">
                                    <option>1–3 roles</option>
                                    <option>4–10 roles</option>
                                    <option>10+ roles</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="needs" class="text-sm font-medium text-hb-text">Tell us about your hiring needs <span class="text-hb-error">*</span></label>
                            <textarea id="needs" name="needs" rows="5" required
                                      class="mt-1.5 w-full rounded-lg border border-hb-border px-3 py-2.5 text-sm"
                                      aria-describedby="needs-error"
                                      placeholder="We’re hiring a Senior Product Designer in Austin…"></textarea>
                            <p id="needs-error" class="mt-1 hidden text-xs text-hb-error" data-error-for="needs"></p>
                        </div>

                        <div>
                            <label for="preferred_contact" class="text-sm font-medium text-hb-text">Preferred contact</label>
                            <select id="preferred_contact" name="preferred_contact" class="mt-1.5 w-full rounded-lg border border-hb-border px-3 py-2.5 text-sm sm:max-w-xs">
                                <option>Email first</option>
                                <option>Phone first</option>
                            </select>
                        </div>

                        <div class="space-y-3 text-sm">
                            <label class="flex items-start gap-2">
                                <input id="privacy_consent" name="privacy_consent" type="checkbox" required class="mt-1 rounded border-hb-border text-hb-blue" aria-describedby="privacy_consent-error">
                                <span>I agree that Hirebridge may contact me about this request and process my information as described in the Privacy Notice. <span class="text-hb-error">*</span></span>
                            </label>
                            <p id="privacy_consent-error" class="hidden text-xs text-hb-error" data-error-for="privacy_consent"></p>
                            <label class="flex items-start gap-2 text-hb-muted">
                                <input type="checkbox" name="insights" class="mt-1 rounded border-hb-border text-hb-blue">
                                <span>Send me occasional hiring insights. Optional; unsubscribe at any time.</span>
                            </label>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <x-button type="submit" variant="primary">Request consultation</x-button>
                            <p class="text-xs text-hb-muted">* Required fields</p>
                        </div>

                        <div class="hidden rounded-lg border border-hb-pending/40 bg-hb-pending-soft px-4 py-3 text-sm text-hb-pending" data-form-notice role="status">
                            Frontend-only form: your details were validated in the browser and were not submitted to a server.
                        </div>
                    </form>
                </div>

                <aside class="space-y-6">
                    <div class="hb-surface p-5">
                        <h2 class="font-semibold text-hb-navy">What happens next</h2>
                        <ol class="mt-4 space-y-4 text-sm text-hb-muted">
                            <li><span class="font-semibold text-hb-text">01 · We review your brief</span><br>A recruitment specialist reviews your role, timing, and preferred support.</li>
                            <li><span class="font-semibold text-hb-text">02 · A focused conversation</span><br>We’ll follow up by email to arrange a 30-minute discovery call, typically within two business days.</li>
                            <li><span class="font-semibold text-hb-text">03 · A clear recommendation</span><br>Receive an outline of scope, engagement options, and terms. You decide whether to proceed.</li>
                        </ol>
                        <p class="mt-4 text-xs text-hb-muted">No obligation. No candidate data is shared until the appropriate permissions are in place.</p>
                    </div>
                    <div class="text-sm text-hb-muted">
                        <p>Already using Hirebridge?
                            <a href="{{ route('login') }}" class="font-semibold text-hb-blue hover:underline">Sign in</a>
                            for workspace support.
                        </p>
                        <p class="mt-2">Looking for a role? <a href="{{ route('jobs.index') }}" class="font-semibold text-hb-blue hover:underline">Browse jobs</a>.</p>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('contact-form');
        if (!form) return;

        const notice = form.querySelector('[data-form-notice]');
        const fields = ['full_name', 'work_email', 'company', 'role_title', 'hiring_model', 'needs', 'privacy_consent'];

        const setError = (name, message) => {
            const input = form.querySelector(`[name="${name}"]`);
            const error = form.querySelector(`[data-error-for="${name}"]`);
            if (!input || !error) return false;
            const invalid = Boolean(message);
            error.textContent = message || '';
            error.classList.toggle('hidden', !invalid);
            input.classList.toggle('border-hb-error', invalid);
            input.setAttribute('aria-invalid', invalid ? 'true' : 'false');
            return !invalid;
        };

        const validate = () => {
            let ok = true;
            fields.forEach((name) => {
                const el = form.querySelector(`[name="${name}"]`);
                if (!el) return;
                let message = '';
                if (name === 'privacy_consent' && !el.checked) {
                    message = 'Consent is required to continue.';
                } else if (name === 'work_email' && el.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(el.value)) {
                    message = 'Enter a valid work email.';
                } else if (el.required && !String(el.value || '').trim() && name !== 'privacy_consent') {
                    message = 'This field is required.';
                }
                if (!setError(name, message)) ok = false;
            });
            return ok;
        };

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            if (!validate()) {
                const firstInvalid = form.querySelector('[aria-invalid="true"]');
                if (firstInvalid) firstInvalid.focus();
                return;
            }
            if (notice) notice.classList.remove('hidden');
        });

        form.addEventListener('input', (event) => {
            const name = event.target?.name;
            if (name && fields.includes(name)) setError(name, '');
        });
        form.addEventListener('change', (event) => {
            const name = event.target?.name;
            if (name && fields.includes(name)) setError(name, '');
        });
    });
</script>
@endpush
