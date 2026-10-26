@extends('frontend.layouts.app')

@section('title', __('Book interview'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Book interview') }}</h1>
@endsection

@section('content')
<x-card class="max-w-2xl">
    <x-slot:header>
        <h2 class="text-lg font-bold text-hb-navy">{{ __('Book interview') }}</h2>
    </x-slot:header>

    
    <form method="POST" action="{{ route('frontend.candidate.interviews.book.submit') }}" class="space-y-5">
        @csrf
        <x-form.input name="application_id" :label="__('Application ID')" type="number" :required="true" :error="$errors->first('application_id')" value="{{ old('application_id', request('application_id')) }}" />
        <x-form.input name="availability_id" :label="__('Slot ID')" type="number" :required="true" :error="$errors->first('availability_id')" value="{{ old('availability_id', data_get([], 'availability_id', request('availability_id'))) }}" />
        <div class="flex flex-wrap items-center gap-3 pt-1">
            <x-button type="submit">{{ __('Book') }}</x-button>
            <x-button variant="light" :href="route('frontend.candidate.interviews.index')">{{ __('Cancel') }}</x-button>
        </div>
    </form>
    <p class="mt-4 text-sm"><a class="font-semibold text-hb-blue hover:underline" href="{{ route('frontend.candidate.interviews.slots', array_filter(['application_id' => request('application_id')])) }}">{{ __('Browse available slots') }}</a></p>
</x-card>
@endsection
