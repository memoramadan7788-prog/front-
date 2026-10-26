@extends('frontend.layouts.app')

@section('title', __('New exam'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('New exam') }}</h1>
@endsection

@section('content')
<x-card class="max-w-2xl">
    <x-slot:header>
        <h2 class="text-lg font-bold text-hb-navy">{{ __('New exam') }}</h2>
    </x-slot:header>

    
    <form method="POST" action="{{ route('frontend.company.exams.create.submit') }}" class="space-y-5">
        @csrf
        <x-form.input name="title" :label="__('Title')" type="text" :required="true" :error="$errors->first('title')" value="{{ old('title', data_get([], 'title', '')) }}" />
        <x-form.input name="time_limit_min" :label="__('Time limit (minutes)')" type="number" :required="true" :error="$errors->first('time_limit_min')" value="{{ old('time_limit_min', data_get([], 'time_limit_min', '')) }}" min="1" />
        <x-form.input name="attempt_limit" :label="__('Attempts allowed (1–3)')" type="number" :required="true" :error="$errors->first('attempt_limit')" value="{{ old('attempt_limit', data_get([], 'attempt_limit', '')) }}" min="1" />
        <x-form.input name="passing_score" :label="__('Passing score (%)')" type="number" :required="true" :error="$errors->first('passing_score')" value="{{ old('passing_score', data_get([], 'passing_score', '')) }}" min="0" />
        <div class="flex flex-wrap items-center gap-3 pt-1">
            <x-button type="submit">{{ __('Save') }}</x-button>
            <x-button variant="light" :href="route('frontend.company.dashboard')">{{ __('Cancel') }}</x-button>
        </div>
    </form>
    
</x-card>
@endsection
