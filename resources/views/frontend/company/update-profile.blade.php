@extends('frontend.layouts.app')

@section('title', __('Edit company profile'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Edit company profile') }}</h1>
@endsection

@section('content')
<x-card class="max-w-2xl">
    <x-slot:header>
        <h2 class="text-lg font-bold text-hb-navy">{{ __('Edit company profile') }}</h2>
    </x-slot:header>

    
    <form method="POST" action="{{ route('frontend.company.profile.update.submit') }}" class="space-y-5">
        @csrf
        <x-form.input name="name" :label="__('Company name')" type="text" :required="true" :error="$errors->first('name')" value="{{ old('name', data_get(($prefill ?? []), 'name', '')) }}" />
        <x-form.input name="website" :label="__('Website')" type="url" :error="$errors->first('website')" value="{{ old('website', data_get(($prefill ?? []), 'website', '')) }}" />
        <x-form.input name="industry" :label="__('Industry')" type="text" :error="$errors->first('industry')" value="{{ old('industry', data_get(($prefill ?? []), 'industry', '')) }}" />
        <x-form.input name="contact_email" :label="__('Contact email')" type="email" :error="$errors->first('contact_email')" value="{{ old('contact_email', data_get(($prefill ?? []), 'contact_email', '')) }}" />
        <x-form.input name="contact_phone" :label="__('Contact phone')" type="tel" :error="$errors->first('contact_phone')" value="{{ old('contact_phone', data_get(($prefill ?? []), 'contact_phone', '')) }}" />
        <x-form.textarea name="address" :label="__('Address')" rows="4" :error="$errors->first('address')">{{ old('address', data_get(($prefill ?? []), 'address', '')) }}</x-form.textarea>
        <div class="flex flex-wrap items-center gap-3 pt-1">
            <x-button type="submit">{{ __('Save') }}</x-button>
            <x-button variant="light" :href="route('frontend.company.profile')">{{ __('Cancel') }}</x-button>
        </div>
    </form>
    
</x-card>
@endsection
