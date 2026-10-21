@extends('layouts.app')

@section('title', $pageTitle ?? (($interview['title'] ?? 'Interview').' · Hirebridge'))
@section('breadcrumb', __('company.interviews.show_breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">
        {{ __('company.interviews.show_title', ['title' => $interview['title']]) }}
    </h1>
    <p class="mt-1 text-sm text-hb-muted">
        {{ __('company.interviews.show_subtitle', [
            'applicant' => $interview['applicant'],
            'code' => $interview['job_code'],
            'job' => $interview['job_title'],
        ]) }}
    </p>
    <div class="mt-3 flex flex-wrap items-center gap-2">
        <x-badge variant="info" :dot="true">{{ $interview['status'] }}</x-badge>
        <x-badge variant="neutral" :dot="true">{{ $interview['type'] }}</x-badge>
        <span class="text-sm text-hb-muted">{{ $interview['duration'] }} · {{ $interview['format'] }}</span>
    </div>
@endsection

@section('page_actions')
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('company.interviews.index') }}" class="hb-btn-outline">{{ __('company.interviews.back_to_calendar') }}</a>
        <a href="{{ route('company.applicants.show', $interview['applicant_id']) }}" class="hb-btn-outline">
            {{ __('company.interviews.back_to_applicant') }}
        </a>
        <a href="{{ route('company.interviews.schedule', ['applicant' => $interview['applicant_id']]) }}" class="hb-btn-primary">
            {{ __('company.interviews.reschedule') }}
        </a>
    </div>
@endsection

@section('content')
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
        <section class="hb-surface p-5 sm:p-6">
            <dl class="grid gap-4 sm:grid-cols-2 text-sm">
                <div>
                    <dt class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('company.interviews.when_label') }}</dt>
                    <dd class="mt-1 font-semibold text-hb-navy">{{ $interview['when'] }}</dd>
                    <dd class="text-hb-muted">{{ $interview['time'] }}</dd>
                    <dd class="text-hb-muted">{{ $interview['timezone'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('company.interviews.interviewer_label') }}</dt>
                    <dd class="mt-1 font-semibold text-hb-navy">{{ $interview['interviewer'] }}</dd>
                    <dd class="text-hb-muted">{{ __('company.interviews.organizer_label') }}: {{ $interview['organizer'] }}</dd>
                </div>
            </dl>
            <ul class="mt-6 space-y-2 text-sm">
                @foreach ($interview['checklist'] ?? [] as $item)
                    <li class="flex items-start gap-2 text-hb-muted">
                        <span aria-hidden="true">{{ ! empty($item['done']) ? '✓' : '○' }}</span>
                        <span>{{ $item['label'] }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        <aside class="space-y-4">
            <section class="hb-surface p-5">
                <h2 class="text-base font-semibold text-hb-navy">{{ $interview['applicant'] }}</h2>
                <p class="mt-1 text-sm text-hb-muted">{{ $interview['job_title'] }} · {{ $interview['job_code'] }}</p>
                <a href="{{ route('company.applicants.show', $interview['applicant_id']) }}" class="hb-btn-outline mt-4 inline-flex">
                    {{ __('company.interviews.back_to_applicant') }}
                </a>
            </section>
        </aside>
    </div>
@endsection
