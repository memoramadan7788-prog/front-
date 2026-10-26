@extends('frontend.layouts.app')

@section('title', __('Before you start'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Before you start') }}</h1>
@endsection

@section('content')
<x-card class="max-w-2xl">
    <x-slot:header>
        <h2 class="text-lg font-bold text-hb-navy">{{ __('Before you start') }}</h2>
        <p class="mt-1 text-sm text-hb-muted">{{ __('The exam is timed and your camera may be used to detect suspicious activity.') }}</p>
    </x-slot:header>

    
    <form method="POST" action="{{ route('frontend.candidate.exams.start.submit', ['examId' => $examId]) }}" class="space-y-5">
        @csrf
        <input type="hidden" name="application_id" value="{{ old('application_id', request('application_id')) }}">
        <x-form.checkbox name="camera_consent" :label="__('I agree to camera monitoring (proctoring) during this exam')" value="1" @checked(old('camera_consent', data_get([], 'camera_consent')))/>
    @error('camera_consent')<p class="hb-field-error" role="alert">{{ $message }}</p>@enderror
        <div class="flex flex-wrap items-center gap-3 pt-1">
            <x-button type="submit">{{ __('Start exam') }}</x-button>
            <x-button variant="light" :href="route('frontend.candidate.exams.index')">{{ __('Cancel') }}</x-button>
        </div>
    </form>
    
</x-card>
@endsection
