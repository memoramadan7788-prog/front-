@extends('layouts.app')

@section('title', $pageTitle ?? __('onboarding.company_status.page_title'))
@section('breadcrumb', __('onboarding.company_status.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('onboarding.company_status.title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ __('onboarding.company_status.intro') }}</p>
@endsection

@section('content')
    @php($companyName = $companyName ?? 'Northstar Labs')


    <div class="grid max-w-3xl gap-4">
        <div class="rounded-xl border border-hb-pending/30 bg-hb-pending-soft px-4 py-4">
            <p class="text-sm font-semibold text-hb-pending">● {{ __('onboarding.company_status.pending') }}</p>
            <p class="mt-1 text-sm text-hb-muted">
                {{ __('onboarding.company_status.pending_body', ['company' => $companyName]) }}
            </p>
        </div>

        <ul class="space-y-2 text-sm text-hb-muted">
            <li class="flex gap-2"><span class="text-hb-success" aria-hidden="true">☑</span> {{ __('onboarding.company_status.org_submitted') }}</li>
            <li class="flex gap-2"><span class="text-hb-success" aria-hidden="true">☑</span> {{ __('onboarding.company_status.doc_uploaded') }}</li>
            <li class="flex gap-2"><span class="text-hb-muted" aria-hidden="true">○</span> {{ __('onboarding.company_status.admin_review') }}</li>
            <li class="flex gap-2"><span class="text-hb-muted" aria-hidden="true">○</span> {{ __('onboarding.company_status.activation') }}</li>
        </ul>

        <div class="flex flex-wrap gap-3 pt-2">
            <a href="{{ route('onboarding.company') }}" class="hb-btn-outline">{{ __('onboarding.company_status.back_setup') }}</a>
            <a href="{{ route('login') }}" class="hb-btn-primary">{{ __('onboarding.company_status.return_sign_in') }}</a>
        </div>
    </div>
@endsection
