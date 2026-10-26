@extends('frontend.layouts.app')

@section('title', __('Reject request'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Reject request') }}</h1>
@endsection

@section('content')
<x-card class="max-w-2xl">
    <x-slot:header>
        <h2 class="text-lg font-bold text-hb-navy">{{ __('Reject request') }}</h2>
    </x-slot:header>

    
    <form method="POST" action="{{ route('frontend.company.employees.reject.submit', ['employeeId' => $employeeId]) }}" class="space-y-5">
        @csrf
        <x-form.textarea name="rejection_reason" :label="__('Reason')" rows="4" :required="true" :error="$errors->first('rejection_reason')">{{ old('rejection_reason', data_get([], 'rejection_reason', '')) }}</x-form.textarea>
        <div class="flex flex-wrap items-center gap-3 pt-1">
            <x-button type="submit">{{ __('Reject') }}</x-button>
            <x-button variant="light" :href="route('frontend.company.employees.index')">{{ __('Cancel') }}</x-button>
        </div>
    </form>
    
</x-card>
@endsection
