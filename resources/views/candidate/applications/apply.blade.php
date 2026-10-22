@extends('layouts.app')

@section('title', $pageTitle ?? __('candidate.applications.apply_page_title'))
@section('breadcrumb', __('candidate.workspace') . ' / ' . __('navigation.candidate.applications'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.applications.apply_title', ['company' => $flow['job']['company']]) }}</h1>
    <p class="mt-1 text-sm text-hb-muted">
        {{ $flow['job']['title'] }} · {{ $flow['job']['code'] }} · {{ __('candidate.applications.apply_review_line') }}
    </p>
@endsection

@section('content')
    @php
        $role = $flow['role_summary'];
    @endphp


    <div class="mb-6 rounded-lg border border-hb-blue/20 bg-hb-info-soft px-4 py-3 text-sm text-hb-blue" role="status">
        {{ $flow['journey_banner'] }}
    </div>

    <div class="mb-6" data-apply-stepper>
        <x-stepper :steps="$flow['steps']" :current="$flow['current_step']" variant="underline" />
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]" data-candidate-apply-page>
        <div>
            @include('candidate.applications._apply-form', [
                'flow' => $flow,
                'formId' => 'candidate-apply-page-form',
            ])
        </div>

        <aside class="space-y-4">
            <section class="hb-surface p-5" aria-labelledby="role-summary-heading">
                <div class="flex items-start justify-between gap-2">
                    <h2 id="role-summary-heading" class="text-base font-semibold text-hb-navy">{{ __('candidate.applications.role_summary') }}</h2>
                    <x-badge variant="info" :dot="true">{{ $role['source'] }}</x-badge>
                </div>
                <p class="mt-3 text-base font-semibold text-hb-navy">{{ $role['title'] }}</p>
                <ul class="mt-3 space-y-1.5 text-sm text-hb-muted">
                    <li>{{ $role['company'] }} · {{ $role['code'] }}</li>
                    <li>{{ $role['meta'] }}</li>
                    <li>{{ $role['salary'] }}</li>
                    <li>{{ __('candidate.applications.hiring_contact', ['name' => $role['hiring_contact']]) }}</li>
                    <li>{{ __('candidate.applications.team', ['name' => $role['team']]) }}</li>
                </ul>
            </section>

            <section class="hb-surface p-5" aria-labelledby="before-submit-heading">
                <h2 id="before-submit-heading" class="text-base font-semibold text-hb-navy">{{ __('candidate.applications.before_submit') }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-hb-muted">{{ $flow['before_submit'] }}</p>
            </section>

            <p class="text-xs text-hb-muted">
                {{ __('candidate.applications.compact_dialog') }}
                <button type="button" class="font-semibold text-hb-blue hover:underline" data-open-modal="candidate-apply-modal">
                    {{ __('candidate.applications.open_apply_modal') }}
                </button>
            </p>
        </aside>
    </div>

    <p class="mt-8 text-xs text-hb-muted">{{ $flow['footer'] }}</p>

    @include('candidate.applications.apply-modal', ['flow' => $flow])
@endsection
