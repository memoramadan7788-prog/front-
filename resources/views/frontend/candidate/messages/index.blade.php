@extends('frontend.layouts.app')

@section('title', __('candidate.messages.title') ?: 'Messages')

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.messages.title') ?: 'Messages' }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.messages.intro') ?: 'Track your communications and messages with recruiters and companies.' }}</p>
@endsection

@section('content')
<x-card>
    <div class="py-12 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 mb-4 rounded-full bg-indigo-50 text-indigo-600 text-2xl">
            💬
        </div>
        <h3 class="text-lg font-bold text-hb-navy mb-1">No messages yet</h3>
        <p class="text-sm text-hb-muted max-w-sm mx-auto mb-6">
            When companies reach out to you or you receive a response to your job applications, conversations will appear here.
        </p>
        <x-button variant="primary" size="md" :href="route('frontend.candidate.vacancies.index')">
            Explore Available Vacancies
        </x-button>
    </div>
</x-card>
@endsection