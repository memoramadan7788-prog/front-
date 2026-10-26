@extends('frontend.layouts.app')

@section('title', __('Update application status'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Update application status') }}</h1>
@endsection

@section('content')
<x-card class="max-w-2xl">
    <x-slot:header>
        <h2 class="text-lg font-bold text-hb-navy">{{ __('Update application status') }}</h2>
    </x-slot:header>

    
    <form method="POST" action="{{ route('frontend.company.applications.status.submit', ['applicationId' => $applicationId]) }}" class="space-y-5">
        @csrf
        <x-form.select name="status" :label="__('Status')" :required="true" :error="$errors->first('status')">
        <option value="">{{ __('Select…') }}</option>
        <option value="screening" @selected((string) old('status', data_get([], 'status', '')) === 'screening')>{{ __('Screening') }}</option>
        <option value="shortlisted" @selected((string) old('status', data_get([], 'status', '')) === 'shortlisted')>{{ __('Shortlisted') }}</option>
        <option value="interviewing" @selected((string) old('status', data_get([], 'status', '')) === 'interviewing')>{{ __('Interview') }}</option>
        <option value="offered" @selected((string) old('status', data_get([], 'status', '')) === 'offered')>{{ __('Offer') }}</option>
        <option value="hired" @selected((string) old('status', data_get([], 'status', '')) === 'hired')>{{ __('Hired') }}</option>
        <option value="rejected" @selected((string) old('status', data_get([], 'status', '')) === 'rejected')>{{ __('Rejected') }}</option>
    </x-form.select>
        <x-form.textarea name="reason" :label="__('Reason (optional)')" rows="4" :error="$errors->first('reason')">{{ old('reason', data_get([], 'reason', '')) }}</x-form.textarea>
        <div class="flex flex-wrap items-center gap-3 pt-1">
            <x-button type="submit">{{ __('Save') }}</x-button>
            <x-button variant="light" :href="route('frontend.company.vacancies.index')">{{ __('Cancel') }}</x-button>
        </div>
    </form>
    
</x-card>
@endsection
