@extends('frontend.layouts.app')

@section('title', __('Add skill'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Add skill') }}</h1>
@endsection

@section('content')
<x-card class="max-w-2xl">
    <x-slot:header>
        <h2 class="text-lg font-bold text-hb-navy">{{ __('Add skill') }}</h2>
    </x-slot:header>

    
    <form method="POST" action="{{ route('frontend.candidate.skills.add.submit') }}" class="space-y-5">
        @csrf
        <x-form.select name="skill_id" :label="__('Skill')" :required="true" :error="$errors->first('skill_id')">
        <option value="">{{ __('Select…') }}</option>
        @foreach (\App\Support\Rows::from($skills ?? []) as $opt)
            <option value="{{ data_get($opt, 'id') }}" @selected((string) old('skill_id', data_get(($prefill ?? []), 'skill_id', '')) === (string) data_get($opt, 'id'))>{{ data_get($opt, 'name') }}</option>
        @endforeach
    </x-form.select>
        <x-form.select name="level" :label="__('Level')" :error="$errors->first('level')">
        <option value="">{{ __('Select…') }}</option>
        <option value="beginner" @selected((string) old('level', data_get(($prefill ?? []), 'level', '')) === 'beginner')>{{ __('Beginner') }}</option>
        <option value="intermediate" @selected((string) old('level', data_get(($prefill ?? []), 'level', '')) === 'intermediate')>{{ __('Intermediate') }}</option>
        <option value="advanced" @selected((string) old('level', data_get(($prefill ?? []), 'level', '')) === 'advanced')>{{ __('Advanced') }}</option>
        <option value="expert" @selected((string) old('level', data_get(($prefill ?? []), 'level', '')) === 'expert')>{{ __('Expert') }}</option>
    </x-form.select>
        <div class="flex flex-wrap items-center gap-3 pt-1">
            <x-button type="submit">{{ __('Save') }}</x-button>
            <x-button variant="light" :href="route('frontend.candidate.profile')">{{ __('Cancel') }}</x-button>
        </div>
    </form>
    
</x-card>
@endsection
