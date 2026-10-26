@extends('frontend.layouts.app')

@section('title', __('Add education'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Add education') }}</h1>
@endsection

@section('content')
<x-card class="max-w-2xl">
    <x-slot:header>
        <h2 class="text-lg font-bold text-hb-navy">{{ __('Add education') }}</h2>
    </x-slot:header>

    
    <form method="POST" action="{{ route('frontend.candidate.education.add.submit') }}" class="space-y-5">
        @csrf
        <x-form.input name="institution" :label="__('Institution')" type="text" :required="true" :error="$errors->first('institution')" value="{{ old('institution', data_get(($prefill ?? []), 'institution', '')) }}" />
        <x-form.input name="degree" :label="__('Degree')" type="text" :required="true" :error="$errors->first('degree')" value="{{ old('degree', data_get(($prefill ?? []), 'degree', '')) }}" />
        <x-form.input name="field_of_study" :label="__('Field of study')" type="text" :required="true" :error="$errors->first('field_of_study')" value="{{ old('field_of_study', data_get(($prefill ?? []), 'field_of_study', '')) }}" />
        <x-form.input name="graduation_year" :label="__('Graduation year')" type="number" :required="true" :error="$errors->first('graduation_year')" value="{{ old('graduation_year', data_get(($prefill ?? []), 'graduation_year', '')) }}" min="1950" />
        <x-form.input name="place" :label="__('Place')" type="text" :error="$errors->first('place')" value="{{ old('place', data_get(($prefill ?? []), 'place', '')) }}" />
        <div class="flex flex-wrap items-center gap-3 pt-1">
            <x-button type="submit">{{ __('Save') }}</x-button>
            <x-button variant="light" :href="route('frontend.candidate.profile')">{{ __('Cancel') }}</x-button>
        </div>
    </form>
    
</x-card>
@endsection
