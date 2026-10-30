@extends('frontend.layouts.app')

@section('title', __('company.jobs.preview_page_title'))

@section('page_header')
    <div>
        <p class="text-xs font-medium uppercase tracking-wide text-hb-muted">{{ __('company.jobs.create_breadcrumb') }}</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-hb-navy">{{ __('company.jobs.preview') }}</h1>
        <p class="mt-1 text-sm text-hb-muted">{{ __('company.jobs.preview_no_mutation') }}</p>
    </div>
@endsection

@section('content')
@php $fields = is_array($fields ?? null) ? $fields : []; @endphp
<div class="mx-auto max-w-3xl space-y-5" data-job-preview>
    <article class="hb-surface rounded-2xl p-5 sm:p-6">
        <p class="text-xs uppercase tracking-wide text-hb-muted">{{ $categoryName ?: __('company.jobs.category_unset') }}</p>
        <h2 class="mt-1 text-2xl font-semibold text-hb-navy">{{ $fields['title'] ?? '—' }}</h2>
        <p class="mt-2 text-sm text-hb-muted">
            {{ $fields['location'] ?? '—' }}
            · {{ isset($fields['work_mode']) ? __('company.jobs.work.'.$fields['work_mode']) : '—' }}
            · {{ isset($fields['employment_type']) ? __('company.jobs.employment.'.$fields['employment_type']) : '—' }}
        </p>
        <div class="prose prose-sm mt-6 max-w-none text-hb-navy">
            {{-- Plain text only — never {!! !!} for XSS safety --}}
            <pre class="whitespace-pre-wrap font-sans">{{ $composedDescription !== '' ? $composedDescription : '—' }}</pre>
        </div>
    </article>
    <div class="flex flex-wrap gap-2">
        <x-button variant="light" :href="route('frontend.company.vacancies.create', ['step' => 4])">{{ __('company.jobs.back_to_review') }}</x-button>
    </div>
</div>
@endsection
