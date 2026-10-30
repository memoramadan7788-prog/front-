@extends('frontend.layouts.app')

@section('title', data_get($company, 'name', __('common.role_labels.company')))

@section('page_header')
    <div class="flex flex-wrap items-center gap-3">
        <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ data_get($company, 'name', '—') }}</h1>
        @php $st = data_get($company, 'status'); @endphp
        <x-badge :variant="\App\Support\Rows::badgeVariant($st)" :dot="true">{{ $st ?? '—' }}</x-badge>
    </div>
@endsection

@section('page_actions')
    @php $cid = data_get($company, 'id'); @endphp
    <x-button variant="light" size="md" :href="route('frontend.admin.companies.index')">{{ __('Back to companies') }}</x-button>
    @if (in_array($st, ['pending', 'suspended'], true))
        <form method="POST" action="{{ route('frontend.admin.companies.approve', ['companyId' => $cid]) }}" class="inline">@csrf <x-button type="submit" variant="primary" size="md">{{ $st === 'suspended' ? __('Reactivate') : __('Approve') }}</x-button></form>
    @endif
    @if ($st === 'pending')
        <x-button variant="destructive" size="md" :href="route('frontend.admin.companies.reject', ['companyId' => $cid])">{{ __('Reject') }}</x-button>
    @endif
    @if ($st === 'approved')
        <form method="POST" action="{{ route('frontend.admin.companies.suspend', ['companyId' => $cid]) }}" class="inline" onsubmit="return confirm('{{ __('Suspend this company?') }}')">@csrf <x-button type="submit" variant="light" size="md">{{ __('Suspend') }}</x-button></form>
    @endif
@endsection

@section('content')
@if ($errors->has('error'))
    <x-alert variant="error" class="mb-4" role="alert">{{ $errors->first('error') }}</x-alert>
@endif

<div class="grid gap-6 lg:grid-cols-3">
    <x-card class="lg:col-span-1">
        <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('Overview') }}</h2></x-slot:header>
        <dl class="space-y-3 text-sm">
            <div><dt class="text-hb-muted">{{ __('Owner') }}</dt><dd class="font-semibold text-hb-navy">{{ data_get($company, 'owner.name', '—') }}</dd><dd class="text-xs text-hb-muted">{{ data_get($company, 'owner.email') }}</dd></div>
            <div><dt class="text-hb-muted">{{ __('common.labels.jobs') }}</dt><dd class="font-semibold text-hb-navy">{{ data_get($company, 'jobs_count', 0) }}</dd></div>
            <div><dt class="text-hb-muted">{{ __('Employees') }}</dt><dd class="font-semibold text-hb-navy">{{ data_get($company, 'employees_count', 0) }}</dd></div>
            <div><dt class="text-hb-muted">{{ __('Registered') }}</dt><dd class="font-semibold text-hb-navy">{{ \App\Support\Rows::date(data_get($company, 'created_at'), 'Y-m-d') }}</dd></div>
            @if (data_get($company, 'reviewed_by'))
                <div><dt class="text-hb-muted">{{ __('Reviewed by') }}</dt><dd class="font-semibold text-hb-navy">{{ data_get($company, 'reviewed_by') }}</dd><dd class="text-xs text-hb-muted">{{ \App\Support\Rows::date(data_get($company, 'reviewed_at'), 'Y-m-d H:i') }}</dd></div>
            @endif
            @if ($st === 'rejected' && data_get($company, 'rejection_reason'))
                <div><dt class="text-hb-muted">{{ __('Rejection reason') }}</dt><dd class="text-hb-text">{{ data_get($company, 'rejection_reason') }}</dd></div>
            @endif
        </dl>
    </x-card>

    <x-card class="lg:col-span-2">
        <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('Company details') }}</h2></x-slot:header>
        <form method="POST" action="{{ route('frontend.admin.companies.update', ['companyId' => $cid]) }}" class="space-y-5">
            @csrf
            <x-form.input name="name" :label="__('Company name')" type="text" :required="true" :error="$errors->first('name')" value="{{ old('name', data_get($company, 'name', '')) }}" />
            <div class="grid gap-5 sm:grid-cols-2">
                <x-form.input name="website" :label="__('Website')" type="url" :error="$errors->first('website')" value="{{ old('website', data_get($company, 'website', '')) }}" />
                <x-form.input name="industry" :label="__('Industry')" type="text" :error="$errors->first('industry')" value="{{ old('industry', data_get($company, 'industry', '')) }}" />
                <x-form.input name="contact_email" :label="__('Contact email')" type="email" :error="$errors->first('contact_email')" value="{{ old('contact_email', data_get($company, 'contact_email', '')) }}" />
                <x-form.input name="contact_phone" :label="__('Contact phone')" type="tel" :error="$errors->first('contact_phone')" value="{{ old('contact_phone', data_get($company, 'contact_phone', '')) }}" />
            </div>
            <x-form.textarea name="address" :label="__('Address')" rows="3" :error="$errors->first('address')">{{ old('address', data_get($company, 'address', '')) }}</x-form.textarea>
            <x-button type="submit">{{ __('Save changes') }}</x-button>
        </form>
    </x-card>
</div>

<x-card class="mt-6">
    <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('Company documents') }}</h2></x-slot:header>
    @include('frontend.partials.company-documents', [
        'documents' => data_get($company, 'documents', []),
        'uploadUrl' => route('frontend.admin.companies.documents.upload', ['companyId' => $cid]),
        'downloadUrl' => fn ($d) => route('frontend.admin.companies.documents.download', ['companyId' => $cid, 'documentId' => data_get($d, 'id'), 'name' => data_get($d, 'original_filename')]),
        'deleteUrl' => fn ($d) => route('frontend.admin.companies.documents.delete', ['companyId' => $cid, 'documentId' => data_get($d, 'id')]),
    ])
</x-card>
@endsection
