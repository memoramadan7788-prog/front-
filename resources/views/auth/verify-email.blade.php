@extends('layouts.auth')

@section('title', $pageTitle ?? __('auth.verify.page_title'))
@section('eyebrow', __('auth.verify.eyebrow'))

@section('content')
    @php($email = $email ?? 'maya.chen@example.com')


    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('auth.verify.title') }}</h1>
    <p class="mt-2 text-sm text-hb-muted">
        {{ __('auth.verify.intro_before') }}
        <span class="font-medium text-hb-text">{{ $email }}</span>
        {{ __('auth.verify.intro_after') }}
    </p>

    <div class="mt-8 space-y-5" data-auth-page="verify-email">
        <div class="rounded-lg border border-hb-border bg-hb-surface px-4 py-3 text-sm">
            <p class="font-semibold text-hb-navy">{{ __('auth.verify.entry_title') }}</p>
            <p class="mt-1 text-hb-muted">{{ __('auth.verify.entry_note') }}</p>
        </div>

        <form class="space-y-5" data-auth-form="verify-email" novalidate aria-label="{{ __('auth.verify.form_aria') }}">
            <div data-field="code">
                <label id="verify-code-label" class="hb-label">{{ __('auth.verify.code') }}</label>
                <div
                    class="mt-2 flex justify-between gap-2"
                    data-otp-group
                    role="group"
                    aria-labelledby="verify-code-label"
                >
                    @foreach (range(0, 5) as $i)
                        <input
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="1"
                            class="hb-input h-12 w-full max-w-12 px-0 text-center text-lg font-semibold tracking-wide"
                            data-otp-digit
                            aria-label="{{ __('auth.verify.digit_aria', ['current' => $i + 1, 'total' => 6]) }}"
                            @if ($i === 0) autofocus @endif
                        >
                    @endforeach
                </div>
                <input type="hidden" name="code" value="" data-otp-value>
                <p class="mt-2 text-xs text-hb-muted">{{ __('auth.verify.otp_hint') }}</p>
                <p id="verify-code-error" class="hb-field-error hidden" role="alert"></p>
            </div>

            <button type="submit" class="hb-btn-primary w-full" data-auth-submit>
                {{ __('auth.verify.submit') }}
            </button>

            <div class="hidden rounded-lg border border-hb-info-soft bg-hb-info-soft px-3 py-2.5 text-sm text-hb-navy" data-auth-frontend-note role="status" tabindex="-1">
                <p>{{ __('auth.verify.note_ready') }}</p>
                <p class="mt-2">
                    <a href="{{ route('onboarding.candidate') }}" class="font-semibold text-hb-blue hover:underline">{{ __('auth.verify.continue_onboarding') }}</a>
                </p>
            </div>
        </form>

        <div class="flex flex-wrap items-center gap-2 text-sm text-hb-muted">
            <span>{{ __('auth.verify.didnt_receive') }}</span>
            <button
                type="button"
                class="font-semibold text-hb-blue hover:underline disabled:cursor-not-allowed disabled:opacity-50"
                data-resend-code
                title="{{ __('auth.verify.resend_soon_title') }}"
            >
                {{ __('auth.verify.resend') }}
            </button>
            <span class="hidden text-xs text-hb-pending" data-resend-note role="status">
                {{ __('auth.verify.resend_soon') }}
            </span>
        </div>

        <div class="rounded-lg border border-hb-border bg-hb-page px-4 py-3 text-sm text-hb-muted">
            <p class="font-semibold text-hb-navy">{{ __('auth.verify.tips_title') }}</p>
            <p class="mt-1.5 leading-relaxed">{{ __('auth.verify.tips_body') }}</p>
        </div>

        <p class="text-sm">
            <a href="{{ route('register') }}" class="font-semibold text-hb-blue hover:underline">{{ __('auth.verify.change_email') }}</a>
        </p>
        <p class="text-xs text-hb-muted">{{ __('auth.verify.return_later') }} <a href="{{ route('login') }}" class="text-hb-blue hover:underline">{{ __('auth.verify.sign_in_link') }}</a>.</p>
        <p class="text-center text-xs text-hb-muted">
            {{ __('auth.login.need_help') }} <a href="mailto:support@hirebridge.example" class="text-hb-blue hover:underline">support@hirebridge.example</a>
        </p>
    </div>
@endsection
