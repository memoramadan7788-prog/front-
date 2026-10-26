@extends('frontend.layouts.app')

@section('title', __('Reschedule interview'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Reschedule interview') }}</h1>
@endsection

@section('content')
<x-card class="max-w-2xl">
    <x-slot:header>
        <h2 class="text-lg font-bold text-hb-navy">{{ __('Reschedule interview') }}</h2>
    </x-slot:header>

    
    <form method="POST" action="{{ route('frontend.candidate.interviews.reschedule.submit', ['interviewId' => $interviewId]) }}" class="space-y-5">
        @csrf
        <x-form.input name="availability_id" :label="__('New slot ID')" type="number" :required="true" :error="$errors->first('availability_id')" value="{{ old('availability_id', data_get([], 'availability_id', '')) }}" />
        <div class="flex flex-wrap items-center gap-3 pt-1">
            <x-button type="submit">{{ __('Reschedule') }}</x-button>
            <x-button variant="light" :href="route('frontend.candidate.interviews.index')">{{ __('Cancel') }}</x-button>
        </div>
    </form>
    <p class="mt-4 text-sm"><a class="font-semibold text-hb-blue hover:underline" href="{{ route('frontend.candidate.interviews.slots', ['interview_id' => $interviewId, 'reschedule' => 1]) }}">{{ __('Browse available slots') }}</a></p>
</x-card>
@endsection
