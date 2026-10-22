@extends('layouts.auth')

@section('title', $pageTitle ?? __('auth.reset.page_title'))
@section('eyebrow', __('auth.reset.eyebrow'))

@section('content')
    @php($email = $email ?? 'maya.chen@example.com')


    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('auth.reset.title') }}</h1>
    <p class="mt-2 text-sm text-hb-muted">{{ __('auth.reset.subtitle') }}</p>

    <div class="mt-8 space-y-5" data-auth-page="reset-password">
        <div class="rounded-lg border border-hb-border bg-hb-surface px-4 py-3 text-sm">
            <p class="font-semibold text-hb-navy">{{ __('auth.reset.preview_title') }}</p>
            <p class="mt-1 text-hb-muted">
                {{ __('auth.reset.preview_before') }} <span class="font-medium text-hb-text">{{ $email }}</span>.
                {{ __('auth.reset.preview_after') }}
            </p>
        </div>

        <form class="space-y-4" data-auth-form="reset-password" novalidate aria-label="{{ __('auth.reset.form_aria') }}">
            <div data-field="password">
                <label for="reset-password" class="hb-label">{{ __('auth.reset.password') }}</label>
                <div class="relative">
                    <input
                        id="reset-password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        class="hb-input pe-11"
                        required
                        minlength="12"
                        aria-describedby="reset-password-error reset-requirements"
                        data-password-input
                        data-password-rules
                        data-password-confirm-source
                    >
                    <button
                        type="button"
                        class="absolute end-2 top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-md text-hb-muted hover:bg-hb-page hover:text-hb-text"
                        data-password-toggle
                        aria-label="{{ __('common.show_password') }}"
                        aria-controls="reset-password"
                    >
                        <svg data-icon-show class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <svg data-icon-hide class="hidden h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                    </button>
                </div>
                <p id="reset-password-error" class="hb-field-error hidden" role="alert"></p>
            </div>

            <div data-field="password_confirmation">
                <label for="reset-password-confirmation" class="hb-label">{{ __('auth.reset.confirm') }}</label>
                <div class="relative">
                    <input
                        id="reset-password-confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        class="hb-input pe-11"
                        required
                        minlength="12"
                        aria-describedby="reset-password-confirmation-error reset-match"
                        data-password-input
                        data-password-confirm
                    >
                    <button
                        type="button"
                        class="absolute end-2 top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-md text-hb-muted hover:bg-hb-page hover:text-hb-text"
                        data-password-toggle
                        aria-label="{{ __('common.show_password') }}"
                        aria-controls="reset-password-confirmation"
                    >
                        <svg data-icon-show class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <svg data-icon-hide class="hidden h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                    </button>
                </div>
                <p id="reset-match" class="mt-1.5 text-xs font-medium text-hb-muted" data-password-match aria-live="polite"></p>
                <p id="reset-password-confirmation-error" class="hb-field-error hidden" role="alert"></p>
            </div>

            <div id="reset-requirements" class="rounded-lg border border-hb-border bg-hb-page px-4 py-3" data-password-requirements>
                <p class="text-sm font-semibold text-hb-navy">{{ __('auth.reset.requirements') }}</p>
                <ul class="mt-2 space-y-1.5 text-sm text-hb-muted">
                    <li data-req="length" class="flex items-center gap-2"><span data-req-icon aria-hidden="true">○</span> {{ __('auth.reset.req_length') }}</li>
                    <li data-req="number" class="flex items-center gap-2"><span data-req-icon aria-hidden="true">○</span> {{ __('auth.reset.req_number') }}</li>
                    <li data-req="symbol" class="flex items-center gap-2"><span data-req-icon aria-hidden="true">○</span> {{ __('auth.reset.req_symbol') }}</li>
                    <li class="flex items-center gap-2 text-hb-muted/80"><span aria-hidden="true">○</span> {{ __('auth.reset.req_different') }}</li>
                </ul>
            </div>

            <label class="flex items-start gap-2.5 text-sm text-hb-muted">
                <input name="logout_others" type="checkbox" class="hb-checkbox mt-0.5" checked disabled title="{{ __('auth.reset.logout_others_title') }}">
                <span>{{ __('auth.reset.sign_out_devices') }}</span>
            </label>

            <button type="submit" class="hb-btn-primary w-full" data-auth-submit>
                {{ __('auth.reset.submit') }}
            </button>

            <div class="hidden rounded-lg border border-hb-info-soft bg-hb-info-soft px-3 py-2.5 text-sm text-hb-navy" data-auth-frontend-note role="status" tabindex="-1">
                <p>{{ __('auth.reset.note_ready') }}</p>
                <p class="mt-2">
                    <a href="{{ route('login') }}" class="font-semibold text-hb-blue hover:underline">{{ __('auth.reset.continue_sign_in') }}</a>
                </p>
            </div>
        </form>

        <p class="text-xs leading-relaxed text-hb-muted">
            {{ __('auth.reset.after_reset') }}
        </p>

        <p class="text-center text-sm">
            <a href="{{ route('login') }}" class="font-semibold text-hb-blue hover:underline">{{ __('auth.reset.back_sign_in') }}</a>
        </p>
        <p class="text-center text-xs text-hb-muted">
            {{ __('auth.login.need_help') }} <a href="mailto:support@hirebridge.example" class="text-hb-blue hover:underline">support@hirebridge.example</a>
        </p>
    </div>
@endsection
