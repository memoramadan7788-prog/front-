@extends('layouts.app')

@section('title', 'Layout preview · App · Hirebridge')

@section('breadcrumb', 'Stage 1 / Workspace / Layout preview')

@section('page_header')
    <h1 class="text-2xl font-semibold text-hb-navy">Application workspace</h1>
    <p class="mt-1 text-sm text-hb-muted">
        App shell verification for role “{{ $uiRole }}”. Shared partials and components are Stage 2-ready.
    </p>
@endsection

@section('page_actions')
    <x-button variant="ghost" size="sm" :href="route('frontend.layouts.app', ['role' => 'candidate'])">Candidate</x-button>
    <x-button variant="ghost" size="sm" :href="route('frontend.layouts.app', ['role' => 'company'])">Company</x-button>
    <x-button variant="ghost" size="sm" :href="route('frontend.layouts.app', ['role' => 'interviewer'])">Interviewer</x-button>
    <x-button variant="ghost" size="sm" :href="route('frontend.layouts.app', ['role' => 'admin'])">Admin</x-button>
    <x-button variant="primary" size="sm" :href="route('frontend.components', ['role' => $uiRole])">Component showcase</x-button>
@endsection

@section('content')
    <div class="grid gap-4 lg:grid-cols-3">
        <x-card class="lg:col-span-2">
            <x-slot:header>
                <h2 class="font-semibold text-hb-navy">Content canvas</h2>
            </x-slot:header>
            <p class="text-sm text-hb-muted">
                Desktop frames in Figma are 1440px with a 224px navy rail and a light content pane.
                Use the topbar notification and profile menus to verify Stage 2 interactions.
            </p>
            <div class="mt-4 flex flex-wrap gap-2">
                <x-badge variant="success" :dot="true">Success</x-badge>
                <x-badge variant="pending" :dot="true">Pending</x-badge>
                <x-badge variant="error" :dot="true">Error</x-badge>
                <x-badge variant="info" :dot="true">Information</x-badge>
            </div>
        </x-card>

        <x-card>
            <x-slot:header>
                <h2 class="font-semibold text-hb-navy">Modal chrome</h2>
            </x-slot:header>
            <p class="text-sm text-hb-muted">Opens the shared modal structure include.</p>
            <x-button class="mt-4" variant="primary" data-open-modal="layout-modal">Open modal</x-button>
        </x-card>
    </div>

    @include('layouts.modal', [
        'modalId' => 'layout-modal',
        'modalTitle' => 'Shared modal structure',
        'modalSize' => 'md',
        'modalOpen' => false,
        'modalBody' => '<p class="text-hb-muted">Stage 1 modal shell with Stage 2 open/close behavior via app.js.</p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>Close</button>',
    ])
@endsection
