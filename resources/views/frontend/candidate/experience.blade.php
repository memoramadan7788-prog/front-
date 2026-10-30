@extends('frontend.layouts.app')

@section('title', __('Add experience'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Add experience') }}</h1>
@endsection

@section('content')
<x-card class="max-w-2xl">
    <x-slot:header>
        <h2 class="text-lg font-bold text-hb-navy">{{ __('Add experience') }}</h2>
    </x-slot:header>

    
    <form method="POST" action="{{ route('frontend.candidate.experience.add.submit') }}" class="space-y-5">
        @csrf
        <x-form.input name="job_title" :label="__('Job title')" type="text" :required="true" :error="$errors->first('job_title')" value="{{ old('job_title', data_get(($prefill ?? []), 'job_title', '')) }}" />
        <x-form.input name="company_name" :label="__('Company')" type="text" :required="true" :error="$errors->first('company_name')" value="{{ old('company_name', data_get(($prefill ?? []), 'company_name', '')) }}" />
        <x-form.input name="place" :label="__('Place')" type="text" :error="$errors->first('place')" value="{{ old('place', data_get(($prefill ?? []), 'place', '')) }}" />
        <x-form.input name="start_date" :label="__('Start date')" type="date" :required="true" :error="$errors->first('start_date')" value="{{ old('start_date', data_get(($prefill ?? []), 'start_date', '')) }}" />
        <x-form.input name="end_date" :label="__('End date')" type="date" :error="$errors->first('end_date')" value="{{ old('end_date', data_get(($prefill ?? []), 'end_date', '')) }}" />
        <x-form.checkbox name="is_current" :label="__('I currently work here')" value="1" :checked="(bool) (old('is_current', data_get(($prefill ?? []), 'is_current')))"/>
    @error('is_current')<p class="hb-field-error" role="alert">{{ $message }}</p>@enderror
        <x-form.textarea name="description" :label="__('Description')" rows="4" :error="$errors->first('description')">{{ old('description', data_get(($prefill ?? []), 'description', '')) }}</x-form.textarea>
        <div class="flex flex-wrap items-center gap-3 pt-1">
            <x-button type="submit">{{ __('Save') }}</x-button>
            <x-button variant="light" :href="route('frontend.candidate.profile')">{{ __('Cancel') }}</x-button>
        </div>
    </form>
    
</x-card>
@endsection