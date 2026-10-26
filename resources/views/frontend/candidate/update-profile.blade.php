@extends('frontend.layouts.app')

@section('title', __('Edit profile'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Edit profile') }}</h1>
@endsection

@section('content')
<x-card class="max-w-2xl">
    <x-slot:header>
        <h2 class="text-lg font-bold text-hb-navy">{{ __('Edit profile') }}</h2>
    </x-slot:header>

    
    <form method="POST" action="{{ route('frontend.candidate.profile.update.submit') }}" class="space-y-5">
        @csrf
        <x-form.input name="headline" :label="__('Headline')" type="text" :error="$errors->first('headline')" value="{{ old('headline', data_get(($prefill ?? []), 'headline', '')) }}" />
        <x-form.input name="phone" :label="__('Phone')" type="tel" :error="$errors->first('phone')" value="{{ old('phone', data_get(($prefill ?? []), 'phone', '')) }}" />
        <x-form.input name="location" :label="__('Location')" type="text" :error="$errors->first('location')" value="{{ old('location', data_get(($prefill ?? []), 'location', '')) }}" />
        <x-form.textarea name="bio" :label="__('About you')" rows="5" :error="$errors->first('bio')">{{ old('bio', data_get(($prefill ?? []), 'bio', '')) }}</x-form.textarea>
        <x-form.input name="years_experience" :label="__('Years of experience')" type="number" :error="$errors->first('years_experience')" value="{{ old('years_experience', data_get(($prefill ?? []), 'years_experience', '')) }}" min="0" />
        <x-form.checkbox name="has_no_experience" :label="__('I have no work experience yet')" value="1" @checked(old('has_no_experience', data_get(($prefill ?? []), 'has_no_experience')))/>
    @error('has_no_experience')<p class="hb-field-error" role="alert">{{ $message }}</p>@enderror
        <div class="flex flex-wrap items-center gap-3 pt-1">
            <x-button type="submit">{{ __('Save') }}</x-button>
            <x-button variant="light" :href="route('frontend.candidate.profile')">{{ __('Cancel') }}</x-button>
        </div>
    </form>
    
</x-card>
@endsection
