@extends('frontend.layouts.auth')

@section('title', __('auth.login.page_title'))

@php
    $safeRedirect = $safeRedirect ?? null;
    $continuation = is_string($safeRedirect) && $safeRedirect !== ''
        && ! preg_match('#^/account/(login|register|admin-login)#', parse_url($safeRedirect, PHP_URL_PATH) ?: $safeRedirect);
@endphp

@section('branding')
  <div>
    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-100/80">{{ __('auth.brand_tagline') }}</p>
    <p class="mt-6 text-3xl font-black leading-tight tracking-tight xl:text-4xl">TalentBridge</p>
    <h2 class="mt-3 text-xl font-bold leading-snug text-white/95 xl:text-2xl">{{ __('auth.brand_headline') }}</h2>
    <p class="mt-4 max-w-md text-sm leading-7 text-sky-50/90">{{ __('auth.brand_copy') }}</p>
  </div>
  <div>
    <p class="text-sm font-semibold text-white/90">{{ __('auth.brand_designed') }}</p>
    <ul class="mt-4 space-y-3 text-sm text-sky-50/90">
      @foreach (__('auth.brand_points') as $point)
        <li class="flex items-start gap-2.5">
          <span class="mt-0.5 grid h-6 w-6 shrink-0 place-items-center rounded-full bg-white/15" aria-hidden="true">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m5 12 4 4L19 6"/></svg>
          </span>
          <span>{{ $point }}</span>
        </li>
      @endforeach
    </ul>
    <p class="mt-8 text-xs text-white/55">{{ __('auth.brand_footer') }}</p>
  </div>
@endsection

@section('branding_mobile')
  <p class="text-lg font-black tracking-tight">TalentBridge</p>
  <p class="text-sm text-sky-50/90">{{ __('auth.brand_headline') }}</p>
  <p class="mx-auto max-w-md text-xs leading-6 text-white/70">{{ __('auth.login.continue_hint') }}</p>
@endsection

@section('content')
  <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 dark:text-[var(--hb-muted)]">{{ __('auth.login.eyebrow') }}</p>
  <h1 class="mt-2 text-3xl font-black tracking-tight text-[var(--hb-text)]">{{ __('auth.login.title') }}</h1>
  <p class="mt-2 text-sm text-slate-500 dark:text-[var(--hb-muted)]">{{ __('auth.login.subtitle') }}</p>

  @if ($continuation)
    <div class="mt-5 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900 dark:border-sky-800 dark:bg-sky-950/40 dark:text-sky-100" role="status" data-login-continuation>
      <p class="font-semibold">{{ __('auth.login.continue_workspace') }}</p>
      <p class="mt-1 text-sky-800/90 dark:text-sky-200/90">{{ __('auth.login.continue_hint') }}</p>
      <p class="mt-2 truncate text-xs text-sky-700/80 dark:text-sky-300/80" title="{{ $safeRedirect }}">{{ __('auth.login.return_to') }}: {{ $safeRedirect }}</p>
    </div>
  @else
    <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 dark:border-[var(--hb-border)] dark:bg-slate-900/40 dark:text-[var(--hb-muted)]" role="note" data-login-continuation-default>
      <p class="font-semibold text-slate-800 dark:text-[var(--hb-text)]">{{ __('auth.login.continue_workspace') }}</p>
      <p class="mt-1">{{ __('auth.login.continue_hint') }}</p>
    </div>
  @endif

  {{-- Unsupported federated sign-in: Backend has no Google OAuth or Company SSO routes. --}}
  <div class="mt-6 space-y-2" data-login-sso-unavailable>
    <p class="sr-only">{{ __('auth.login.sso_unavailable_sr') }}</p>
    <button type="button" disabled aria-disabled="true" title="{{ __('auth.login.sso_unavailable') }}"
            class="flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-400 opacity-80 dark:border-[var(--hb-border)] dark:bg-slate-900/50 dark:text-slate-500">
      <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 11v2h5.5c-.2 1.2-.9 2.2-1.9 2.9l3 2.3c1.8-1.6 2.9-4 2.9-7.2 0-.7-.1-1.3-.2-1.9H12z"/><path fill="currentColor" d="M12 22c2.6 0 4.8-.9 6.4-2.4l-3-2.3c-.9.6-2 1-3.4 1-2.6 0-4.8-1.8-5.6-4.1H3.3v2.4C4.9 19.8 8.2 22 12 22z"/><path fill="currentColor" d="M6.4 14.2A5.9 5.9 0 0 1 6 12c0-.8.1-1.5.4-2.2V7.4H3.3A9.96 9.96 0 0 0 2 12c0 1.6.4 3.1 1.3 4.4l3.1-2.2z"/><path fill="currentColor" d="M12 5.9c1.4 0 2.7.5 3.7 1.5l2.8-2.8A9.6 9.6 0 0 0 12 2C8.2 2 4.9 4.2 3.3 7.4l3.1 2.4C7.2 7.6 9.4 5.9 12 5.9z"/></svg>
      {{ __('auth.login.sso_google') }}
    </button>
    <button type="button" disabled aria-disabled="true" title="{{ __('auth.login.sso_unavailable') }}"
            class="flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-400 opacity-80 dark:border-[var(--hb-border)] dark:bg-slate-900/50 dark:text-slate-500">
      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a4 4 0 0 1 8 0v2"/></svg>
      {{ __('auth.login.sso_company') }}
    </button>
    <p class="text-center text-xs text-slate-500 dark:text-[var(--hb-muted)]" data-login-sso-note>{{ __('auth.login.sso_unavailable') }}</p>
  </div>

  <div class="my-6 flex items-center gap-3 text-xs font-semibold uppercase tracking-wide text-slate-400" aria-hidden="true">
    <span class="h-px flex-1 bg-slate-200 dark:bg-[var(--hb-border)]"></span>
    <span>{{ __('auth.login.or_email') }}</span>
    <span class="h-px flex-1 bg-slate-200 dark:bg-[var(--hb-border)]"></span>
  </div>

  <form method="POST"
        action="{{ route('frontend.account.login.submit') }}"
        class="space-y-5"
        data-login-form
        aria-label="{{ __('auth.login.form_aria') }}">
    @csrf
    @if ($continuation)
      <input type="hidden" name="redirect" value="{{ $safeRedirect }}">
    @endif

    <x-auth-field
      name="email"
      :label="__('auth.login.email_address')"
      type="email"
      icon="mail"
      :placeholder="__('auth.login.email_placeholder')"
      autocomplete="email"
      required
      autofocus
    />

    <x-auth-field
      name="password"
      :label="__('auth.login.password')"
      type="password"
      icon="lock"
      :placeholder="__('auth.login.password_placeholder')"
      autocomplete="current-password"
      required
    />

    {{-- Remember Me is not accepted by Backend LoginRequest; keep control disabled and honest. --}}
    <div class="flex items-start gap-3" data-login-remember-unavailable>
      <input id="remember_preview"
             type="checkbox"
             disabled
             aria-disabled="true"
             class="mt-1 h-4 w-4 cursor-not-allowed rounded border-slate-300 text-sky-600 opacity-60"
             title="{{ __('auth.login.remember_unavailable') }}">
      <div>
        <label for="remember_preview" class="text-sm font-medium text-slate-500 dark:text-[var(--hb-muted)]">{{ __('auth.login.remember') }}</label>
        <p class="mt-0.5 text-xs text-slate-400 dark:text-slate-500">{{ __('auth.login.remember_unavailable') }}</p>
      </div>
    </div>

    <button
      type="submit"
      data-login-submit
      class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-sky-500 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-sky-500/25 transition hover:-translate-y-0.5 hover:bg-sky-600 focus:outline-none focus-visible:ring-4 focus-visible:ring-sky-500/30 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
    >
      <span data-login-idle>{{ __('auth.login.submit') }}</span>
      <span data-login-loading class="hidden items-center gap-2" aria-hidden="true">
        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z"/></svg>
        {{ __('auth.login.submitting') }}
      </span>
    </button>
  </form>

  <div class="mt-8 space-y-3 text-center text-sm text-slate-500 dark:text-[var(--hb-muted)]">
    <p>
      {{ __('auth.login.new_here') }}
      <a href="{{ route('frontend.account.register', ['type' => 'candidate']) }}" class="font-bold text-sky-600 hover:underline dark:text-sky-400">{{ __('auth.login.register_candidate') }}</a>
      <span class="text-slate-300 dark:text-slate-600" aria-hidden="true">·</span>
      <a href="{{ route('frontend.account.register', ['type' => 'company']) }}" class="font-bold text-sky-600 hover:underline dark:text-sky-400">{{ __('auth.login.register_company') }}</a>
    </p>
    <p>
      {{ __('auth.admin_login.portal_hint') }}
      <a href="{{ route('frontend.account.admin-login') }}" class="font-bold text-sky-600 hover:underline dark:text-sky-400">{{ __('auth.admin_login.portal_link') }}</a>
    </p>
  </div>
@endsection
