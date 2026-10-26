@extends('layouts.hall')
@section('title', __('hall.exterior.page_title'))

@section('content')
    <x-hall-scene :scene="config('hall.exterior')">

        {{-- soft shadow so the white text is readable on any photo --}}
        <div class="pointer-events-none absolute inset-x-0 top-0 h-1/2 bg-linear-to-b from-black/60 to-transparent"></div>

        {{-- top bar: logo + login / sign up --}}
        <header class="absolute inset-x-0 top-0 z-30 flex items-center justify-between px-[3%] py-[1.5%]">

            <div class="flex items-center gap-2">

                @auth
                    <x-button variant="cta" href="{{ route('frontend.hall.lobby') }}">{{ __('hall.exterior.enter') }}</x-button>
                @endauth
            </div>
        </header>

        {{-- welcome card (kept small so it never covers the entrance) --}}
        <div class="pointer-events-none absolute inset-x-0 top-[14%] z-10 flex justify-center">
            <div class="w-[60%] rounded-2xl bg-slate-900/55 text-center text-white backdrop-blur-sm" style="padding: 1.8cqw">
                <p class="font-semibold uppercase tracking-[0.25em] text-sky-300" style="font-size: 0.95cqw">{{ __('hall.exterior.eyebrow') }}</p>
                <h1 class="mt-[0.6cqw] font-extrabold leading-tight" style="font-size: 3cqw">{{ __('hall.exterior.headline') }}</h1>
                <p class="mt-[0.6cqw] text-white/90" style="font-size: 1.4cqw">{{ __('hall.exterior.body') }}</p>
                <p class="mt-[1cqw] font-semibold text-amber-300" style="font-size: 1.2cqw">
                    @auth {{ __('hall.exterior.click_entrance') }} @endauth
                </p>
            </div>
        </div>

    </x-hall-scene>
@endsection
