@extends('layouts.auth')

@section('title', $pageTitle ?? 'Reset your password · Hirebridge')
@section('eyebrow', 'Account access')
@section('brand_headline', 'Good work starts with the right connection.')
@section('brand_copy', 'Find your next role, build your team, or partner with a recruiter. One clear workspace for every step.')

@section('content')
    <div class="hb-mock-banner mb-6 rounded-lg text-start">
        Supporting recovery entry UI (not Figma Frames 07–10). No reset email is sent — Backend Team integration required.
    </div>

    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">Forgot your password?</h1>
    <p class="mt-2 text-sm text-hb-muted">Enter the email associated with your Hirebridge account. We’ll show the next steps for Backend Team password recovery.</p>

    <div class="mt-8 space-y-5" data-auth-page="forgot-password">
        <form class="space-y-4" data-auth-form="forgot-password" novalidate aria-label="Request password reset">
            <div data-field="email">
                <label for="forgot-email" class="hb-label">Email address</label>
                <input
                    id="forgot-email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    class="hb-input"
                    placeholder="you@company.com"
                    value="maya.chen@example.com"
                    required
                    aria-describedby="forgot-email-error"
                >
                <p id="forgot-email-error" class="hb-field-error hidden" role="alert"></p>
            </div>

            <button type="submit" class="hb-btn-primary w-full" data-auth-submit>
                Continue
            </button>

            <div class="hidden rounded-lg border border-hb-info-soft bg-hb-info-soft px-3 py-2.5 text-sm text-hb-navy" data-auth-frontend-note role="status">
                <p>Email format validated. No reset message was sent — Backend Team email delivery is required.</p>
                <p class="mt-2">
                    <a href="{{ route('password.reset', ['email' => 'maya.chen@example.com']) }}" class="font-semibold text-hb-blue hover:underline">
                        Preview “Set a new password” screen →
                    </a>
                </p>
            </div>
        </form>

        <p class="text-center text-sm text-hb-muted">
            Remembered it?
            <a href="{{ route('login') }}" class="font-semibold text-hb-blue hover:underline">Back to sign in</a>
        </p>
        <p class="text-center text-xs text-hb-muted">
            Need help? <a href="mailto:support@hirebridge.example" class="text-hb-blue hover:underline">support@hirebridge.example</a>
        </p>
    </div>
@endsection
