@extends('frontend.layouts.auth')

@section('title', 'Log in')

@section('content')
    <h1 class="text-3xl font-black tracking-tight">Welcome back</h1>

    <p class="mt-2 text-sm text-slate-500">
        Log in to find jobs, take exams, book interviews or manage your company's hiring.
    </p>

    <form method="POST" action="{{ route('frontend.account.login.submit') }}" class="mt-8 space-y-5">
        @csrf

        <x-auth-field
            name="email"
            label="Email"
            type="email"
            icon="mail"
            placeholder="you@example.com"
            autocomplete="email"
            required
            autofocus
        />

        <x-auth-field
            name="password"
            label="Password"
            type="password"
            icon="lock"
            placeholder="Your password"
            autocomplete="current-password"
            required
        />

        <button
            type="submit"
            class="w-full rounded-full bg-sky-500 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-sky-500/25 transition hover:-translate-y-0.5 hover:bg-sky-600 focus:outline-none focus-visible:ring-4 focus-visible:ring-sky-500/30"
        >
            Log in
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-500">
        Don't have an account?
        <a href="{{ route('frontend.account.register') }}" class="font-bold text-sky-600 hover:underline">Sign up</a>
    </p>
@endsection
