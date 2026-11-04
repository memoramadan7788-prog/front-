
@extends('frontend.layouts.auth')

@section('content')
<div
    class="h-screen w-full overflow-hidden flex items-center justify-center bg-white px-5"
    x-data="{ password: '', confirmPassword: '' }"
>
    <div class="w-full max-w-2xl">

        {{-- Heading --}}
        <div class="text-center mb-8">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">
                Reset Your Password
            </h2>

            <p class="mt-3 text-sm sm:text-base text-gray-600">
                Enter your new password and confirm it below.
            </p>
        </div>

        {{-- Error Messages --}}
        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- Form --}}
        <form
            action="{{ route('frontend.account.reset-password.submit') }}"
            method="POST"
            class="space-y-5"
        >
            @csrf

            <input
                type="hidden"
                name="token"
                value="{{ $token ?? request()->route('token') ?? request()->query('token') }}"
            >

            {{-- Email --}}
            <div>
                <label for="email" class="block text-base font-semibold text-gray-800 mb-2">
                    Email Address
                </label>

                <input
                    id="email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    required
                    value="{{ $email ?? old('email', request()->query('email')) }}"
                    placeholder="name@example.com"
                    class="w-full px-4 py-4 text-base text-gray-900 bg-white border border-gray-300 rounded-xl outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                >
            </div>

            {{-- New Password --}}
            <div>
                <label for="password" class="block text-base font-semibold text-gray-800 mb-2">
                    New Password
                </label>

                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                    required
                    x-model="password"
                    placeholder="Enter your new password"
                    class="w-full px-4 py-4 text-base text-gray-900 bg-white border border-gray-300 rounded-xl outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                >

                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div>
                <label for="password_confirmation" class="block text-base font-semibold text-gray-800 mb-2">
                    Confirm New Password
                </label>

                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    required
                    x-model="confirmPassword"
                    placeholder="Confirm your new password"
                    class="w-full px-4 py-4 text-base text-gray-900 bg-white border border-gray-300 rounded-xl outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                >
            </div>

            {{-- Password Requirements --}}
            <div class="bg-gray-50 p-4 rounded-xl text-sm space-y-2 text-gray-600">
                <p class="font-semibold text-gray-800">
                    Password Requirements:
                </p>

                <p :class="password.length >= 8 ? 'text-green-600 font-medium' : 'text-gray-500'">
                    ✓ At least 8 characters
                </p>

                <p :class="/[A-Z]/.test(password) ? 'text-green-600 font-medium' : 'text-gray-500'">
                    ✓ At least one uppercase letter
                </p>

                <p :class="/[0-9]/.test(password) ? 'text-green-600 font-medium' : 'text-gray-500'">
                    ✓ At least one number
                </p>

                <p :class="password && password === confirmPassword ? 'text-green-600 font-medium' : 'text-gray-500'">
                    ✓ Passwords must match
                </p>
            </div>

            {{-- Submit --}}
            <button
                type="submit"
                class="w-full py-4 px-5 rounded-xl text-base font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                Reset Password
            </button>

            {{-- Back to Login --}}
            <div class="text-center pt-2">
                <a
                    href="{{ route('frontend.account.login') }}"
                    class="text-base font-semibold text-indigo-600 hover:text-indigo-800 transition"
                >
                    Back to Login
                </a>
            </div>

        </form>
    </div>
</div>
@endsection
