@extends('frontend.layouts.app')

@section('title', __('Application'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Application') }}</h1>
@endsection

@section('page_actions')
    <x-button variant="light" size="md" :href="route('frontend.candidate.applications.index')">{{ __('Back') }}</x-button>
@endsection

@section('content')

<x-card>
    @include('frontend.partials.record', ['record' => $application ?? []])
</x-card>

@endsection
