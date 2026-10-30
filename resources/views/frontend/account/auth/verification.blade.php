@extends('frontend.layouts.auth')

@section('title', __('auth.register.verification_page_title'))

@section('content')
    <p class="text-xs font-bold uppercase tracking-[0.18em] text-sky-600">{{ __('auth.register.verification_eyebrow') }}</p>
    <h1 class="mt-2 text-3xl font-black tracking-tight">{{ __('auth.register.verification_title') }}</h1>

    <div class="mt-6 rounded-2xl border border-sky-200 bg-sky-50 p-5 text-sm leading-6 text-sky-950">
        <p>{{ __('auth.register.verification_notice', ['email' => $email]) }}</p>
        <p class="mt-3">{{ __('auth.register.verification_instructions') }}</p>
    </div>

    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
        <a
            href="{{ route('frontend.account.register') }}"
            class="inline-flex min-h-11 flex-1 items-center justify-center rounded-full border border-slate-300 px-5 py-3 text-center text-sm font-bold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-sky-500/30"
        >{{ __('auth.register.verification_return') }}</a>
        <a
            href="{{ route('frontend.contact') }}"
            class="inline-flex min-h-11 flex-1 items-center justify-center rounded-full bg-sky-500 px-5 py-3 text-center text-sm font-bold text-white transition hover:bg-sky-600 focus:outline-none focus-visible:ring-4 focus-visible:ring-sky-500/30"
        >{{ __('auth.register.verification_contact') }}</a>
    </div>
@endsection
