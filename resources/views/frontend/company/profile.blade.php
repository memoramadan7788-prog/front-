@extends('frontend.layouts.app')

@section('title', __('Company profile'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Company profile') }}</h1>
@endsection

@section('page_actions')
    <x-button variant="primary" size="md" :href="route('frontend.company.profile.update')">{{ __('Edit profile') }}</x-button>
    <x-button variant="light" size="md" :href="route('frontend.company.change-password')">{{ __('Change password') }}</x-button>
@endsection

@section('content')
@php
    $profile = is_array($profile ?? null) ? $profile : [];
    $documents = $profile['documents'] ?? [];
    unset($profile['documents']);
@endphp
<x-card>@include('frontend.partials.record', ['record' => $profile])</x-card>

<x-card class="mt-6">
    <x-slot:header>
        <h2 class="text-lg font-bold text-hb-navy">{{ __('Company documents') }}</h2>
    </x-slot:header>
    @include('frontend.partials.company-documents', [
        'documents' => $documents,
        'downloadUrl' => fn ($d) => route('frontend.company.documents.download', ['documentId' => data_get($d, 'id'), 'name' => data_get($d, 'original_filename')]),
    ])
    <div class="mt-4"><x-button variant="light" size="sm" :href="route('frontend.company.profile.update')">{{ __('Manage documents') }}</x-button></div>
</x-card>

<x-card class="mt-6">
    <h2 class="text-lg font-bold text-hb-navy">{{ __('Delete company account') }}</h2>
    <p class="mt-1 text-sm text-hb-muted">{{ __('This permanently removes the company and its data.') }}</p>
    <form method="POST" action="{{ route('frontend.company.delete-account') }}" class="mt-4" onsubmit="return confirm('{{ __('Delete your account permanently?') }}')">@csrf @method('DELETE')<x-button type="submit" variant="destructive">{{ __('Delete company account') }}</x-button></form>
</x-card>
@endsection
