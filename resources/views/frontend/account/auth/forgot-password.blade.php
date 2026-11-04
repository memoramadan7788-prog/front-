@extends('frontend.layouts.auth')

@section('content')
<div class="h-screen w-full overflow-hidden flex items-center justify-center bg-white px-5">

    <div class="w-full max-w-2xl">

        {{-- Heading --}}
        <div class="text-center mb-8">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900">
                Forgot Password?
            </h2>

            <p class="mt-3 text-sm sm:text-base text-gray-600">
                Enter your email address and we will send you a password reset link.
            </p>
        </div>

        {{-- Success Messages --}}
        @if (session('success_key'))
            <div class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('success_key') }}
            </div>
        @endif

        @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- Error Message --}}
        @if (session('error'))
            <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- Form --}}
        <form
            action="{{ route('frontend.account.forgot-password.submit') }}"
            method="POST"
            class="space-y-6"
        >
            @csrf

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
                    value="{{ old('email') }}"
                    placeholder="name@example.com"
                    class="w-full px-4 py-4 text-base text-gray-900 bg-white border border-gray-300 rounded-xl outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 @error('email') border-red-500 @enderror"
                >

                @error('email')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full py-4 px-5 rounded-xl text-base font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                Send Password Reset Link
            </button>

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