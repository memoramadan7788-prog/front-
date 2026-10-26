@extends('frontend.layouts.auth')

@section('title', 'Create account')

@php
    $selected = old('account_type', request('type', 'candidate'));
    $types = [
        'candidate' => [
            'title' => "I'm a candidate",
            'text' => 'Find jobs and apply',
            'icon' => '<circle cx="10" cy="8" r="4"/><path d="M3 21v-2a7 7 0 0 1 14 0v2"/><circle cx="18" cy="9" r="2.5"/><path d="M19 15a5 5 0 0 1 3 4v2"/>',
        ],
        'company' => [
            'title' => 'I represent a company',
            'text' => 'Post jobs and hire',
            'icon' => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 7h2m2 0h2M9 11h2m2 0h2M9 15h2m2 0h2"/>',
        ],
    ];
@endphp

@section('content')
    <h1 class="text-3xl font-black tracking-tight">Create your account</h1>

    <form method="POST" action="{{ route('frontend.account.register.submit') }}" class="mt-8 space-y-5">
        @csrf

        <fieldset>
            <legend class="mb-1.5 block text-sm font-semibold text-slate-700">Account type</legend>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($types as $value => $type)
                    <label class="cursor-pointer">
                        <input type="radio" name="account_type" value="{{ $value }}" class="peer sr-only" @checked($selected === $value)>
                        <span class="flex h-full items-start gap-3 rounded-xl border border-slate-300 bg-white p-3.5 transition peer-checked:border-sky-500 peer-checked:bg-sky-50 peer-checked:ring-4 peer-checked:ring-sky-500/15 peer-focus-visible:ring-4 peer-focus-visible:ring-sky-500/30">
                            <svg class="mt-0.5 h-6 w-6 shrink-0 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $type['icon'] !!}</svg>
                            <span>
                                <span class="block text-sm font-bold text-slate-800">{{ $type['title'] }}</span>
                                <span class="block text-xs text-slate-500">{{ $type['text'] }}</span>
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('account_type')
                <p class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </fieldset>

        <x-auth-field name="name" label="Full name" icon="user" placeholder="Your full name" autocomplete="name" required />
        <x-auth-field name="email" label="Email" type="email" icon="mail" placeholder="you@example.com" autocomplete="email" required />

        <div class="grid gap-5 sm:grid-cols-2">
            <x-auth-field name="password" label="Password" type="password" icon="lock" placeholder="Min. 6 characters" autocomplete="new-password" required />
            <x-auth-field name="password_confirmation" label="Confirm password" type="password" icon="lock" placeholder="Repeat password" autocomplete="new-password" required />
        </div>

        <button type="submit"
            class="w-full rounded-full bg-sky-500 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-sky-500/25 transition hover:-translate-y-0.5 hover:bg-sky-600 focus:outline-none focus-visible:ring-4 focus-visible:ring-sky-500/30">
            Create account
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-500">
        Already have an account?
        <a href="{{ route('frontend.account.login') }}" class="font-bold text-sky-600 hover:underline">Log in</a>
    </p>
@endsection
