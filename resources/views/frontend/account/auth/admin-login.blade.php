@extends('frontend.layouts.auth')

@section('title', __('auth.admin_login.title'))

@section('content')
    <h1 class="text-3xl font-black tracking-tight">{{ __('auth.admin_login.heading') }}</h1>

    <p class="mt-2 text-sm text-slate-500">
        {{ __('auth.admin_login.intro') }}
    </p>

    <form method="POST" action="{{ route('frontend.account.admin-login.submit') }}" class="mt-8 space-y-5" autocomplete="off" data-admin-login-form>
        @csrf

        <x-auth-field
            name="email"
            :label="__('Email')"
            type="email"
            icon="mail"
            placeholder="admin@example.com"
            autocomplete="username"
            required
            autofocus
        />

        <x-auth-field
            name="password"
            :label="__('Password')"
            type="password"
            icon="lock"
            placeholder="••••••••"
            autocomplete="current-password"
            required
        />

        <button
            type="submit"
            class="w-full rounded-full bg-sky-500 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-sky-500/25 transition hover:-translate-y-0.5 hover:bg-sky-600 focus:outline-none focus-visible:ring-4 focus-visible:ring-sky-500/30"
            data-admin-login-submit
        >
            {{ __('auth.admin_login.submit') }}
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-500">
        {{ __('auth.admin_login.not_admin') }}
        <a href="{{ route('frontend.account.login') }}" class="font-bold text-sky-600 hover:underline">{{ __('auth.admin_login.back_to_login') }}</a>
    </p>
@endsection
