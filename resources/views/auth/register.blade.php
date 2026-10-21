@extends('layouts.auth')

@section('title', $pageTitle ?? __('auth.register.page_title'))
@section('eyebrow', __('auth.register.eyebrow'))

@section('content')

    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('auth.register.title') }}</h1>
    <p class="mt-2 text-sm text-hb-muted">{{ __('auth.register.subtitle') }}</p>

    <div class="mt-8 space-y-5" data-auth-page="register">
        <div
            class="grid grid-cols-2 gap-2 rounded-xl border border-hb-border bg-hb-surface p-1"
            role="radiogroup"
            aria-label="{{ __('auth.register.account_type_aria') }}"
            data-account-type-group
        >
            <button
                type="button"
                role="radio"
                aria-checked="true"
                data-account-type="candidate"
                class="rounded-lg px-3 py-2.5 text-sm font-semibold text-hb-navy transition-colors data-[active=true]:bg-hb-info-soft data-[active=true]:text-hb-blue"
                data-active="true"
            >
                {{ __('auth.register.looking_job') }}
            </button>
            <button
                type="button"
                role="radio"
                aria-checked="false"
                data-account-type="company"
                class="rounded-lg px-3 py-2.5 text-sm font-semibold text-hb-muted transition-colors data-[active=true]:bg-hb-info-soft data-[active=true]:text-hb-blue"
                data-active="false"
            >
                {{ __('auth.register.hiring') }}
            </button>
        </div>

        <form class="space-y-4" data-auth-form="register" novalidate aria-label="{{ __('auth.register.form_aria') }}">
            <input type="hidden" name="account_type" value="candidate" data-account-type-input>

            <div class="grid gap-4 sm:grid-cols-2">
                <div data-field="first_name">
                    <label for="register-first-name" class="hb-label">{{ __('auth.register.first_name') }} <span class="text-hb-error">*</span></label>
                    <input id="register-first-name" name="first_name" type="text" autocomplete="given-name" class="hb-input" value="Maya" required aria-describedby="register-first-name-error">
                    <p id="register-first-name-error" class="hb-field-error hidden" role="alert"></p>
                </div>
                <div data-field="last_name">
                    <label for="register-last-name" class="hb-label">{{ __('auth.register.last_name') }} <span class="text-hb-error">*</span></label>
                    <input id="register-last-name" name="last_name" type="text" autocomplete="family-name" class="hb-input" value="Chen" required aria-describedby="register-last-name-error">
                    <p id="register-last-name-error" class="hb-field-error hidden" role="alert"></p>
                </div>
            </div>

            <div data-field="email">
                <label for="register-email" class="hb-label">{{ __('auth.register.email_address') }} <span class="text-hb-error">*</span></label>
                <input
                    id="register-email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    class="hb-input"
                    value="maya.chen@example.com"
                    required
                    aria-describedby="register-email-error"
                >
                <p id="register-email-error" class="hb-field-error hidden" role="alert"></p>
            </div>

            <div data-field="password">
                <label for="register-password" class="hb-label">{{ __('auth.register.create_password') }} <span class="text-hb-error">*</span></label>
                <div class="relative">
                    <input
                        id="register-password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        class="hb-input pe-11"
                        required
                        minlength="12"
                        aria-describedby="register-password-hint register-password-error register-password-strength"
                        data-password-input
                        data-password-rules
                    >
                    <button
                        type="button"
                        class="absolute end-2 top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-md text-hb-muted hover:bg-hb-page hover:text-hb-text"
                        data-password-toggle
                        aria-label="{{ __('common.show_password') }}"
                        aria-controls="register-password"
                    >
                        <svg data-icon-show class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <svg data-icon-hide class="hidden h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                    </button>
                </div>
                <p id="register-password-hint" class="mt-1.5 text-xs text-hb-muted">{{ __('auth.register.password_hint') }}</p>
                <p id="register-password-strength" class="mt-1 text-xs font-medium text-hb-muted" data-password-strength aria-live="polite"></p>
                <p id="register-password-error" class="hb-field-error hidden" role="alert"></p>
            </div>

            <label class="flex items-start gap-2.5 text-sm text-hb-text" data-field="terms">
                <input id="register-terms" name="terms" type="checkbox" class="hb-checkbox mt-0.5" required aria-describedby="register-terms-error">
                <span>{{ __('auth.register.terms_prefix') }} <a href="{{ route('about') }}" class="font-medium text-hb-blue hover:underline">{{ __('auth.register.terms_of_service') }}</a> {{ __('auth.register.terms_and') }} {{ __('auth.register.privacy_notice') }}</span>
            </label>
            <p id="register-terms-error" class="hb-field-error hidden" role="alert"></p>

            <label class="flex items-start gap-2.5 text-sm text-hb-muted">
                <input name="marketing" type="checkbox" class="hb-checkbox mt-0.5">
                <span>{{ __('auth.register.marketing') }}</span>
            </label>

            <button type="submit" class="hb-btn-primary w-full" data-auth-submit data-submit-label-candidate="{{ __('auth.register.submit_candidate') }}" data-submit-label-company="{{ __('auth.register.submit_company') }}">
                {{ __('auth.register.submit_candidate') }}
            </button>

            <div class="hidden rounded-lg border border-hb-info-soft bg-hb-info-soft px-3 py-2.5 text-sm text-hb-navy" data-auth-frontend-note role="status">
                <p>{{ __('auth.register.note_ready') }}</p>
                <p class="mt-2 space-y-1">
                    <a href="{{ route('verification.notice', ['email' => 'maya.chen@example.com']) }}" class="block font-semibold text-hb-blue hover:underline">
                        {{ __('auth.register.continue_verify') }}
                    </a>
                    <a href="{{ route('onboarding.candidate') }}" class="block font-semibold text-hb-blue hover:underline" data-onboarding-preview-candidate>
                        {{ __('auth.register.continue_candidate') }}
                    </a>
                    <a href="{{ route('onboarding.company') }}" class="block font-semibold text-hb-blue hover:underline" data-onboarding-preview-company>
                        {{ __('auth.register.continue_company') }}
                    </a>
                </p>
            </div>
        </form>

        <p class="text-xs leading-relaxed text-hb-muted">
            {{ __('auth.register.verify_hint') }}
        </p>

        <p class="text-center text-sm text-hb-muted">
            {{ __('auth.register.have_account') }}
            <a href="{{ route('login') }}" class="font-semibold text-hb-blue hover:underline">{{ __('auth.register.sign_in_arrow') }}</a>
        </p>
        <p class="text-center text-xs text-hb-muted">
            {{ __('auth.login.need_help') }} <a href="mailto:support@hirebridge.example" class="text-hb-blue hover:underline">support@hirebridge.example</a>
        </p>
    </div>
@endsection
