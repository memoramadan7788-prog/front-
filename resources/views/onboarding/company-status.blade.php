@extends('layouts.app')

@section('title', $pageTitle ?? __('onboarding.company_status.page_title'))
@section('breadcrumb', __('onboarding.company_status.breadcrumb'))

@section('page_header')
    @php
        $status = $reviewStatus ?? ($organizationContext['organization']['status'] ?? 'pending');
        $companyName = $companyName ?? ($organizationContext['organization']['name'] ?? 'Northstar Labs');
        $titleKey = match ($status) {
            'approved' => 'onboarding.company_status.title_approved',
            'rejected' => 'onboarding.company_status.title_rejected',
            'suspended' => 'onboarding.company_status.title_suspended',
            default => 'onboarding.company_status.title',
        };
        $introKey = match ($status) {
            'approved' => 'onboarding.company_status.intro_approved',
            'rejected' => 'onboarding.company_status.intro_rejected',
            'suspended' => 'onboarding.company_status.intro_suspended',
            default => 'onboarding.company_status.intro',
        };
    @endphp
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __($titleKey) }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ __($introKey) }}</p>
@endsection

@section('content')
    @php
        $status = $reviewStatus ?? ($organizationContext['organization']['status'] ?? 'pending');
        $companyName = $companyName ?? ($organizationContext['organization']['name'] ?? 'Northstar Labs');
        $organization = $organizationContext['organization'] ?? [];
        $capabilities = $organizationContext['capabilities'] ?? [];
        $statusLabel = match ($status) {
            'approved' => __('onboarding.company_status.approved'),
            'rejected' => __('onboarding.company_status.rejected'),
            'suspended' => __('onboarding.company_status.suspended'),
            default => __('onboarding.company_status.pending'),
        };
        $statusVariant = match ($status) {
            'approved' => 'success',
            'rejected', 'suspended' => 'error',
            default => 'warning',
        };
        $statusBody = match ($status) {
            'approved' => __('onboarding.company_status.approved_body', ['company' => $companyName]),
            'rejected' => __('onboarding.company_status.rejected_body', ['company' => $companyName]),
            'suspended' => __('onboarding.company_status.suspended_body', ['company' => $companyName]),
            default => __('onboarding.company_status.pending_body', ['company' => $companyName]),
        };
    @endphp

    <div class="grid max-w-3xl gap-4" data-company-review-status="{{ $status }}">
        <x-alert :variant="$statusVariant">
            <p class="text-sm font-semibold text-hb-navy">● {{ $statusLabel }}</p>
            <p class="mt-1 text-sm text-hb-muted">{{ $statusBody }}</p>
            @if (! empty($organization['rejection_reason']))
                <p class="mt-2 text-sm text-hb-muted">
                    <span class="font-medium text-hb-text">{{ __('onboarding.company_status.rejection_reason') }}:</span>
                    {{ $organization['rejection_reason'] }}
                </p>
            @endif
            @if (! empty($organization['reviewed_at']))
                <p class="mt-2 text-xs text-hb-muted">{{ __('onboarding.company_status.reviewed_at', ['date' => $organization['reviewed_at']]) }}</p>
            @endif
        </x-alert>

        <p class="rounded-lg border border-hb-border bg-hb-page px-4 py-3 text-xs text-hb-muted" role="note">
            {{ __('onboarding.company_status.preview_disclaimer') }}
        </p>

        <ul class="space-y-2 text-sm text-hb-muted">
            <li class="flex gap-2"><span class="text-hb-success" aria-hidden="true">☑</span> {{ __('onboarding.company_status.org_submitted') }}</li>
            <li class="flex gap-2"><span class="text-hb-success" aria-hidden="true">☑</span> {{ __('onboarding.company_status.doc_uploaded') }}</li>
            <li class="flex gap-2">
                <span @class(['text-hb-success' => $status !== 'pending', 'text-hb-muted' => $status === 'pending']) aria-hidden="true">
                    {{ $status === 'pending' ? '○' : '☑' }}
                </span>
                {{ __('onboarding.company_status.admin_review') }}
            </li>
            <li class="flex gap-2">
                <span @class(['text-hb-success' => $status === 'approved', 'text-hb-muted' => $status !== 'approved']) aria-hidden="true">
                    {{ $status === 'approved' ? '☑' : '○' }}
                </span>
                {{ __('onboarding.company_status.activation') }}
            </li>
        </ul>

        <section class="hb-surface p-4" aria-labelledby="status-capabilities-heading">
            <h2 id="status-capabilities-heading" class="text-sm font-semibold text-hb-navy">{{ __('onboarding.company_status.capabilities_heading') }}</h2>
            <p class="mt-1 text-xs text-hb-muted">{{ __('onboarding.company_status.capabilities_note') }}</p>
            <ul class="mt-3 list-inside list-disc text-sm text-hb-muted">
                <li>{{ __('company.organization.cap_create_jobs') }}:
                    {{ ! empty($capabilities['can_create_jobs']) ? __('company.organization.available') : __('company.organization.unavailable') }}
                </li>
                <li>{{ __('company.organization.cap_publish_jobs') }}:
                    {{ ! empty($capabilities['can_publish_jobs']) ? __('company.organization.available') : __('company.organization.unavailable') }}
                </li>
                <li>{{ __('company.organization.cap_manage_members') }}:
                    {{ ! empty($capabilities['can_manage_members']) ? __('company.organization.available') : __('company.organization.unavailable') }}
                </li>
            </ul>
        </section>

        <div class="flex flex-wrap gap-3 pt-2">
            <a href="{{ route('onboarding.company') }}" class="hb-btn-outline">{{ __('onboarding.company_status.back_setup') }}</a>
            @if ($status === 'approved')
                <a href="{{ route('company.dashboard') }}" class="hb-btn-primary">{{ __('onboarding.company_status.open_workspace') }}</a>
            @else
                <a href="{{ route('login') }}" class="hb-btn-primary">{{ __('onboarding.company_status.return_sign_in') }}</a>
            @endif
        </div>

        <p class="text-xs text-hb-muted">
            {{ __('onboarding.company_status.demo_variants') }}
            <a class="text-hb-blue hover:underline" href="{{ route('onboarding.company.status', ['status' => 'pending']) }}">pending</a>
            ·
            <a class="text-hb-blue hover:underline" href="{{ route('onboarding.company.status', ['status' => 'approved']) }}">approved</a>
            ·
            <a class="text-hb-blue hover:underline" href="{{ route('onboarding.company.status', ['status' => 'rejected']) }}">rejected</a>
            ·
            <a class="text-hb-blue hover:underline" href="{{ route('onboarding.company.status', ['status' => 'suspended']) }}">suspended</a>
        </p>
    </div>
@endsection
