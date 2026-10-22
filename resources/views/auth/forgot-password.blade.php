@extends('layouts.auth')

@section('title', $pageTitle ?? __('auth.forgot.page_title'))
@section('eyebrow', __('auth.forgot.eyebrow'))

@section('content')

    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('auth.forgot.title') }}</h1>
    <p class="mt-2 text-sm text-hb-muted">{{ __('auth.forgot.intro_hirebridge') }}</p>

    <div class="mt-8 space-y-5" data-auth-page="forgot-password">
        <form class="space-y-4" data-auth-form="forgot-password" novalidate aria-label="{{ __('auth.forgot.form_aria') }}">
            <div data-field="email">
                <label for="forgot-email" class="hb-label">{{ __('auth.forgot.email_address') }}</label>
                <input
                    id="forgot-email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    class="hb-input"
                    placeholder="{{ __('auth.forgot.email_placeholder') }}"
                    value="maya.chen@example.com"
                    required
                    aria-describedby="forgot-email-error"
                >
                <p id="forgot-email-error" class="hb-field-error hidden" role="alert"></p>
            </div>

            <button type="submit" class="hb-btn-primary w-full" data-auth-submit>
                {{ __('auth.forgot.submit') }}
            </button>

            <div class="hidden rounded-lg border border-hb-info-soft bg-hb-info-soft px-3 py-2.5 text-sm text-hb-navy" data-auth-frontend-note role="status" tabindex="-1">
                <p>{{ __('auth.forgot.note') }}</p>
                <p class="mt-2">
                    <a href="{{ route('password.reset', ['email' => 'maya.chen@example.com']) }}" class="font-semibold text-hb-blue hover:underline" data-forgot-continue>
                        {{ __('auth.forgot.continue_reset') }}
                    </a>
                </p>
            </div>
        </form>

        <p class="text-center text-sm text-hb-muted">
            {{ __('auth.forgot.remembered') }}
            <a href="{{ route('login') }}" class="font-semibold text-hb-blue hover:underline">{{ __('auth.forgot.back_sign_in') }}</a>
        </p>
        <p class="text-center text-xs text-hb-muted">
            {{ __('auth.login.need_help') }} <a href="mailto:support@hirebridge.example" class="text-hb-blue hover:underline">support@hirebridge.example</a>
        </p>
    </div>
@endsection
