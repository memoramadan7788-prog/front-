@extends('frontend.layouts.app')

@section('title', __('Interview evaluation'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Interview evaluation') }}</h1>
@endsection

@section('content')
<x-card class="max-w-2xl">
    <x-slot:header>
        <h2 class="text-lg font-bold text-hb-navy">{{ __('Interview evaluation') }}</h2>
    </x-slot:header>

    
    <form method="POST" action="{{ route('frontend.company.interviews.evaluation.submit', ['interviewId' => $interviewId]) }}" class="space-y-5">
        @csrf
        <x-form.input name="numeric_score" :label="__('Score (0–100)')" type="number" :required="true" :error="$errors->first('numeric_score')" value="{{ old('numeric_score', data_get([], 'numeric_score', '')) }}" min="0" />
        <x-form.textarea name="internal_notes" :label="__('Internal notes')" rows="4" :error="$errors->first('internal_notes')">{{ old('internal_notes', data_get([], 'internal_notes', '')) }}</x-form.textarea>
        <x-form.textarea name="candidate_feedback" :label="__('Feedback shared with the candidate')" rows="4" :error="$errors->first('candidate_feedback')">{{ old('candidate_feedback', data_get([], 'candidate_feedback', '')) }}</x-form.textarea>
        <div class="flex flex-wrap items-center gap-3 pt-1">
            <x-button type="submit">{{ __('Save') }}</x-button>
            <x-button variant="light" :href="route('frontend.company.dashboard')">{{ __('Cancel') }}</x-button>
        </div>
    </form>
    
</x-card>
@endsection
