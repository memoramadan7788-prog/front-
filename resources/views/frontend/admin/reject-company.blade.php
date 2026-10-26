@extends('frontend.layouts.app')

@section('title', __('Reject company'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Reject company') }}</h1>
@endsection

@section('content')
<x-card class="max-w-2xl">
    <x-slot:header>
        <h2 class="text-lg font-bold text-hb-navy">{{ __('Reject company') }}</h2>
    </x-slot:header>

    
    <form method="POST" action="{{ route('frontend.admin.companies.reject.submit', ['companyId' => $companyId]) }}" class="space-y-5">
        @csrf
        <x-form.textarea name="reason" :label="__('Reason')" rows="4" :required="true" :error="$errors->first('reason')">{{ old('reason', data_get([], 'reason', '')) }}</x-form.textarea>
        <div class="flex flex-wrap items-center gap-3 pt-1">
            <x-button type="submit">{{ __('Reject') }}</x-button>
            <x-button variant="light" :href="route('frontend.admin.companies.index')">{{ __('Cancel') }}</x-button>
        </div>
    </form>
    
</x-card>
@endsection
