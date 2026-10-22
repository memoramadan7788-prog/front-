@extends('layouts.auth')

@section('title', $pageTitle ?? __('auth.login.page_title'))
@section('eyebrow', __('auth.login.eyebrow'))

@section('content')

    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('auth.login.title') }}</h1>
    <p class="mt-2 text-sm text-hb-muted">{{ __('auth.login.subtitle') }}</p>

    <div class="mt-8 space-y-4" data-auth-page="login">
        <button type="button" class="hb-btn-outline w-full gap-2" disabled title="{{ __('auth.login.sso_soon') }}">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.66l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
            {{ __('auth.login.sso_google') }}
        </button>
        <button type="button" class="hb-btn-outline w-full" disabled title="{{ __('auth.login.sso_soon') }}">
            {{ __('auth.login.sso_company') }}
        </button>

        <div class="relative py-1 text-center text-xs font-medium text-hb-muted" role="separator">
            <span class="absolute inset-x-0 top-1/2 h-px -translate-y-1/2 bg-hb-border" aria-hidden="true"></span>
            <span class="relative bg-hb-page px-3">{{ __('common.or') }}</span>
        </div>

        <form class="space-y-4" data-auth-form="login" novalidate aria-label="{{ __('auth.login.form_aria') }}">
            <div data-field="email">
                <label for="login-email" class="hb-label">{{ __('auth.login.email_address') }}</label>
                <input
                    id="login-email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    inputmode="email"
                    class="hb-input"
                    placeholder="{{ __('auth.login.email_placeholder') }}"
                    value="maya.chen@example.com"
                    required
                    aria-describedby="login-email-error"
                >
                <p id="login-email-error" class="hb-field-error hidden" role="alert"></p>
            </div>

            <div data-field="password">
                <label for="login-password" class="hb-label">{{ __('auth.login.password') }}</label>
                <div class="relative">
                    <input
                        id="login-password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        class="hb-input pe-11"
                        placeholder="{{ __('auth.login.password_placeholder') }}"
                        required
                        minlength="8"
                        aria-describedby="login-password-error"
                        data-password-input
                    >
                    <button
                        type="button"
                        class="absolute end-2 top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-md text-hb-muted hover:bg-hb-page hover:text-hb-text"
                        data-password-toggle
                        aria-label="{{ __('common.show_password') }}"
                        aria-controls="login-password"
                    >
                        <svg data-icon-show class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <svg data-icon-hide class="hidden h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                    </button>
                </div>
                <p id="login-password-error" class="hb-field-error hidden" role="alert"></p>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
                <label class="inline-flex items-center gap-2 text-hb-muted">
                    <input type="checkbox" name="remember" class="hb-checkbox" checked>
                    {{ __('auth.login.remember') }}
                </label>
                <a href="{{ route('password.request') }}" class="font-medium text-hb-blue hover:underline">{{ __('auth.login.forgot') }}</a>
            </div>

            <button type="submit" class="hb-btn-primary w-full" data-auth-submit>
                {{ __('auth.login.submit') }}
            </button>

            <div class="hidden rounded-lg border border-hb-info-soft bg-hb-info-soft px-3 py-2.5 text-sm text-hb-navy" data-auth-frontend-note role="status" tabindex="-1">
                <p>{{ __('auth.login.note_ready') }}</p>
                <p class="mt-2 space-y-1">
                    <a href="{{ route('candidate.dashboard') }}" class="block font-semibold text-hb-blue hover:underline">{{ __('auth.login.continue_candidate') }}</a>
                    <a href="{{ route('company.dashboard') }}" class="block font-semibold text-hb-blue hover:underline">{{ __('auth.login.continue_company') }}</a>
                </p>
            </div>
        </form>

        <div class="rounded-lg border border-hb-border bg-hb-surface px-3 py-2.5 text-xs text-hb-muted">
            <span class="inline-flex h-2 w-2 rounded-full bg-hb-blue align-middle" aria-hidden="true"></span>
            <span class="ms-1.5 align-middle">{{ __('auth.login.continue_workspace') }}</span>
            <p class="mt-1.5 leading-relaxed">{{ __('auth.login.continue_hint') }}</p>
        </div>

        <p class="text-center text-sm text-hb-muted">
            {{ __('auth.login.new_here') }}
            <a href="{{ route('register') }}" class="font-semibold text-hb-blue hover:underline">{{ __('auth.login.create_account') }}</a>
        </p>
        <p class="text-center text-xs text-hb-muted">
            {{ __('auth.login.need_help') }} <a href="mailto:support@hirebridge.example" class="text-hb-blue hover:underline">support@hirebridge.example</a>
        </p>
    </div>
@endsection
