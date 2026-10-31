@extends('frontend.layouts.app')

@section('title', __('company.talent_pool.page_title'))

@section('page_header')
    <div>
        <p class="text-sm font-semibold text-hb-muted">{{ __('company.talent_pool.breadcrumb') }}</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-hb-navy">{{ __('company.talent_pool.title') }}</h1>
    </div>
@endsection

@section('content')
<div class="mx-auto max-w-3xl">
    <x-card>
        <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('company.talent_pool.unavailable_title') }}</h2></x-slot:header>
        <p class="text-sm leading-6 text-hb-muted">{{ __('company.talent_pool.unavailable_body') }}</p>
        <ul class="mt-4 space-y-2 text-sm text-hb-muted">
            <li class="rounded-lg bg-hb-page px-3 py-2">
                {{ __('company.talent_pool.missing_prospect_api') }}
            </li>
            <li class="rounded-lg bg-hb-page px-3 py-2">{{ __('company.talent_pool.missing_tags_api') }}</li>
            <li class="rounded-lg bg-hb-page px-3 py-2">{{ __('company.talent_pool.missing_consent_fields') }}</li>
        </ul>
    </x-card>
</div>
@endsection
