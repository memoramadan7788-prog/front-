@extends('frontend.layouts.app')

@section('title', __('Send offer'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Send offer') }}</h1>
@endsection

@section('content')
<x-card class="max-w-2xl">
    <x-slot:header>
        <h2 class="text-lg font-bold text-hb-navy">{{ __('Send offer') }}</h2>
    </x-slot:header>

    
    <form method="POST" action="{{ route('frontend.company.offers.create.submit') }}" class="space-y-5">
        @csrf
        <x-form.input name="application_id" :label="__('Application ID')" type="number" :required="true" :error="$errors->first('application_id')" value="{{ old('application_id', data_get([], 'application_id', request('application_id'))) }}" />
        <x-form.input name="salary_amount" :label="__('Salary')" type="number" :required="true" :error="$errors->first('salary_amount')" value="{{ old('salary_amount', data_get([], 'salary_amount', '')) }}" min="0" />
        <x-form.select name="currency" :label="__('Currency')" :required="true" :error="$errors->first('currency')">
        <option value="">{{ __('Select…') }}</option>
        <option value="EGP" @selected((string) old('currency', data_get([], 'currency', '')) === 'EGP')>{{ __('EGP') }}</option>
        <option value="USD" @selected((string) old('currency', data_get([], 'currency', '')) === 'USD')>{{ __('USD') }}</option>
        <option value="EUR" @selected((string) old('currency', data_get([], 'currency', '')) === 'EUR')>{{ __('EUR') }}</option>
        <option value="SAR" @selected((string) old('currency', data_get([], 'currency', '')) === 'SAR')>{{ __('SAR') }}</option>
        <option value="AED" @selected((string) old('currency', data_get([], 'currency', '')) === 'AED')>{{ __('AED') }}</option>
    </x-form.select>
        <x-form.select name="salary_period" :label="__('Salary period')" :required="true" :error="$errors->first('salary_period')">
        <option value="">{{ __('Select…') }}</option>
        <option value="hour" @selected((string) old('salary_period', data_get([], 'salary_period', '')) === 'hour')>{{ __('Hourly') }}</option>
        <option value="month" @selected((string) old('salary_period', data_get([], 'salary_period', '')) === 'month')>{{ __('Monthly') }}</option>
        <option value="year" @selected((string) old('salary_period', data_get([], 'salary_period', '')) === 'year')>{{ __('Yearly') }}</option>
    </x-form.select>
        <x-form.input name="start_date" :label="__('Start date')" type="date" :required="true" :error="$errors->first('start_date')" value="{{ old('start_date', data_get([], 'start_date', '')) }}" />
        <x-form.input name="expires_at" :label="__('Offer expires on')" type="date" :required="true" :error="$errors->first('expires_at')" value="{{ old('expires_at', data_get([], 'expires_at', '')) }}" />
        <div class="flex flex-wrap items-center gap-3 pt-1">
            <x-button type="submit">{{ __('Save') }}</x-button>
            <x-button variant="light" :href="route('frontend.company.dashboard')">{{ __('Cancel') }}</x-button>
        </div>
    </form>
    
</x-card>
@endsection
