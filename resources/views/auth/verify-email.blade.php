@extends('layouts.auth')

@section('title', $pageTitle ?? 'Check your inbox · Hirebridge')
@section('eyebrow', '09 / Account access')
@section('brand_headline', 'Good work starts with the right connection.')
@section('brand_copy', 'Find your next role, build your team, or partner with a recruiter. One clear workspace for every step.')

@section('content')
    @php($email = $email ?? 'maya.chen@example.com')

    <div class="hb-mock-banner mb-6 rounded-lg text-start">
        Frontend UI only — no verification email is sent from this screen. Email delivery requires Backend Team integration.
    </div>

    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">Check your inbox</h1>
    <p class="mt-2 text-sm text-hb-muted">
        Enter the six-digit code we would send to
        <span class="font-medium text-hb-text">{{ $email }}</span>
        after Backend Team email verification is connected.
    </p>

    <div class="mt-8 space-y-5" data-auth-page="verify-email">
        <div class="rounded-lg border border-hb-border bg-hb-surface px-4 py-3 text-sm">
            <p class="font-semibold text-hb-navy">Verification code entry</p>
            <p class="mt-1 text-hb-muted">In production, codes are valid for 10 minutes. Keep this window open while you check your email.</p>
        </div>

        <form class="space-y-5" data-auth-form="verify-email" novalidate aria-label="Email verification">
            <div data-field="code">
                <label id="verify-code-label" class="hb-label">Verification code</label>
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
                            aria-label="Digit {{ $i + 1 }} of 6"
                            @if ($i === 0) autofocus @endif
                        >
                    @endforeach
                </div>
                <input type="hidden" name="code" value="" data-otp-value>
                <p class="mt-2 text-xs text-hb-muted">Six digits · You can paste the full code.</p>
                <p id="verify-code-error" class="hb-field-error hidden" role="alert"></p>
            </div>

            <button type="submit" class="hb-btn-primary w-full" data-auth-submit>
                Verify email &amp; continue
            </button>

            <div class="hidden rounded-lg border border-hb-info-soft bg-hb-info-soft px-3 py-2.5 text-sm text-hb-navy" data-auth-frontend-note role="status">
                Code format validated on the client. Email verification is not completed — Backend Team integration is required.
            </div>
        </form>

        <div class="flex flex-wrap items-center gap-2 text-sm text-hb-muted">
            <span>Didn’t receive it?</span>
            <button
                type="button"
                class="font-semibold text-hb-blue hover:underline disabled:cursor-not-allowed disabled:opacity-50"
                data-resend-code
                title="Resend requires Backend Team email delivery"
            >
                Resend code
            </button>
            <span class="hidden text-xs text-hb-pending" data-resend-note role="status">
                Resend is not available until Backend Team email delivery is connected.
            </span>
        </div>

        <div class="rounded-lg border border-hb-border bg-hb-page px-4 py-3 text-sm text-hb-muted">
            <p class="font-semibold text-hb-navy">A couple of things to check</p>
            <p class="mt-1.5 leading-relaxed">Look in spam or promotions folders. Make sure your email address is spelled correctly. Requesting a new code makes the previous code expire.</p>
        </div>

        <p class="text-sm">
            <a href="{{ route('register') }}" class="font-semibold text-hb-blue hover:underline">Change email address</a>
        </p>
        <p class="text-xs text-hb-muted">Need to come back later? Return anytime via <a href="{{ route('login') }}" class="text-hb-blue hover:underline">sign in</a>.</p>
        <p class="text-center text-xs text-hb-muted">
            Need help? <a href="mailto:support@hirebridge.example" class="text-hb-blue hover:underline">support@hirebridge.example</a>
        </p>
    </div>
@endsection
