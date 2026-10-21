@extends('layouts.auth')

@section('title', 'Layout preview · Auth · Hirebridge')
@section('eyebrow', 'Stage 1 / Auth layout preview')
@section('brand_headline', 'Good work starts with the right connection.')
@section('brand_copy', 'This is a frontend layout preview. Real authentication processing is Backend Team scope.')

@section('content')
    <div class="hb-mock-banner mb-6 rounded-lg px-3 py-2 text-start">
        Mock UI only — no credentials are processed.
    </div>

    <h1 class="text-2xl font-semibold text-hb-navy">Welcome back</h1>
    <p class="mt-2 text-sm text-hb-muted">Sign in to continue your hiring journey.</p>

    <div class="mt-8 space-y-4">
        <button type="button" class="hb-btn-outline w-full" disabled>Continue with Google</button>
        <button type="button" class="hb-btn-outline w-full" disabled>Continue with company SSO</button>

        <div class="relative py-2 text-center text-xs font-medium text-hb-muted">
            <span class="bg-hb-page px-2">or</span>
        </div>

        <form class="space-y-4" onsubmit="return false;" aria-label="Mock sign-in form">
            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-hb-text">Email address</label>
                <input
                    id="email"
                    type="email"
                    class="w-full rounded-lg border border-hb-border bg-hb-surface px-3 py-2.5 text-sm"
                    value="maya.chen@example.com"
                    readonly
                >
            </div>
            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-hb-text">Password</label>
                <input
                    id="password"
                    type="password"
                    class="w-full rounded-lg border border-hb-border bg-hb-surface px-3 py-2.5 text-sm"
                    value="password"
                    readonly
                >
            </div>
            <div class="flex items-center justify-between gap-3 text-sm">
                <label class="inline-flex items-center gap-2 text-hb-muted">
                    <input type="checkbox" class="rounded border-hb-border" disabled>
                    Keep me signed in
                </label>
                <span class="text-hb-blue">Forgot password?</span>
            </div>
            <button type="button" class="hb-btn-primary w-full" disabled>Sign in</button>
        </form>

        <p class="text-center text-sm text-hb-muted">
            New to Hirebridge?
            <span class="font-semibold text-hb-blue">Create an account</span>
        </p>
    </div>
@endsection
