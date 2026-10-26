@extends('frontend.layouts.app')

@section('title', __('Add question'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Add question') }}</h1>
@endsection

@section('content')
<x-card class="max-w-2xl">
    <x-slot:header>
        <h2 class="text-lg font-bold text-hb-navy">{{ __('Add question') }}</h2>
    </x-slot:header>

    
    <form method="POST" action="{{ route('frontend.company.exams.questions.add.submit', ['examId' => $examId]) }}" class="space-y-5">
        @csrf
        <x-form.select name="question_type" :label="__('Type')" :required="true" :error="$errors->first('question_type')">
        <option value="">{{ __('Select…') }}</option>
        <option value="mcq" @selected((string) old('question_type', data_get([], 'question_type', '')) === 'mcq')>{{ __('Multiple choice') }}</option>
        <option value="written" @selected((string) old('question_type', data_get([], 'question_type', '')) === 'written')>{{ __('Written') }}</option>
        <option value="coding" @selected((string) old('question_type', data_get([], 'question_type', '')) === 'coding')>{{ __('Coding') }}</option>
    </x-form.select>
        <x-form.textarea name="content" :label="__('Question')" rows="4" :required="true" :error="$errors->first('content')">{{ old('content', data_get([], 'content', '')) }}</x-form.textarea>
        <x-form.input name="points" :label="__('Points')" type="number" :required="true" :error="$errors->first('points')" value="{{ old('points', data_get([], 'points', '')) }}" min="0" />
        <div class="flex flex-wrap items-center gap-3 pt-1">
            <x-button type="submit">{{ __('Save') }}</x-button>
            <x-button variant="light" :href="route('frontend.company.dashboard')">{{ __('Cancel') }}</x-button>
        </div>
    </form>
    
</x-card>
@endsection
