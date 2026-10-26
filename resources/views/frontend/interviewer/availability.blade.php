@extends('frontend.layouts.app')

@section('title', __('Add availability'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Add availability') }}</h1>
@endsection

@section('content')
<x-card class="max-w-2xl">
    <x-slot:header>
        <h2 class="text-lg font-bold text-hb-navy">{{ __('Add availability') }}</h2>
    </x-slot:header>

    <form method="POST" action="{{ route('frontend.interviewer.availability.add.submit') }}" class="space-y-5">
        @csrf
        <x-form.input name="start_at" :label="__('Start at')" type="datetime-local" :required="true" :error="$errors->first('start_at')" value="{{ old('start_at') }}" />
        <x-form.input name="end_at" :label="__('End at')" type="datetime-local" :required="true" :error="$errors->first('end_at')" value="{{ old('end_at') }}" />
        <div class="flex flex-wrap items-center gap-3 pt-1">
            <x-button type="submit">{{ __('Save') }}</x-button>
            <x-button variant="light" :href="route('frontend.interviewer.interviews.index')">{{ __('Cancel') }}</x-button>
        </div>
    </form>
</x-card>
@endsection
