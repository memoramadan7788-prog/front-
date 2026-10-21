@extends('layouts.app')

@section('title', $pageTitle ?? 'Application confirmation preview · Hirebridge')
@section('breadcrumb', '17 / My workspace / My applications')

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">Your application is on its way</h1>
    <p class="mt-1 text-sm text-hb-muted">
        Submitted to {{ $preview['company'] }} · {{ $preview['submitted_at'] }}
    </p>
@endsection

@section('content')
    <div class="hb-mock-banner mb-4 rounded-lg text-start">
        {{ $preview['preview_notice'] }}
    </div>

    <div class="mb-4 rounded-lg border border-hb-blue/20 bg-hb-info-soft px-4 py-3 text-sm text-hb-blue" role="status">
        {{ $preview['journey_banner'] }}
    </div>

    <div class="mb-6 rounded-lg border border-hb-success/30 bg-hb-success-soft px-4 py-3 text-sm text-hb-success" role="status">
        Application confirmation preview. We’ve prepared a mock confirmation for {{ $preview['email'] }}.
        Application reference: {{ $preview['reference'] }}.
        <span class="mt-1 block font-medium">No real application was received by Hirebridge or the employer.</span>
    </div>

    <section class="hb-surface p-5 sm:p-6" aria-labelledby="submitted-summary-heading">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 id="submitted-summary-heading" class="text-lg font-semibold text-hb-navy">
                    {{ $preview['title'] }} · {{ $preview['code'] }}
                </h2>
                <p class="mt-1 text-sm text-hb-muted">{{ $preview['company'] }} · {{ $preview['discipline'] }}</p>
            </div>
            <x-badge variant="info" :dot="true">Applied</x-badge>
        </div>

        <div class="mt-5">
            <x-timeline :steps="$preview['pipeline']" />
        </div>

        <dl class="mt-5 space-y-3 rounded-lg border border-hb-border bg-hb-page px-4 py-4 text-sm">
            <div class="flex flex-wrap justify-between gap-2">
                <dt class="font-medium text-hb-muted">Profile</dt>
                <dd class="text-end text-hb-navy">{{ $preview['summary']['profile'] }}</dd>
            </div>
            <div class="flex flex-wrap justify-between gap-2">
                <dt class="font-medium text-hb-muted">Resume</dt>
                <dd class="text-end text-hb-navy">{{ $preview['summary']['resume'] }}</dd>
            </div>
            <div class="flex flex-wrap justify-between gap-2">
                <dt class="font-medium text-hb-muted">Portfolio</dt>
                <dd class="text-end text-hb-navy">{{ $preview['summary']['portfolio'] }}</dd>
            </div>
            <div class="flex flex-wrap justify-between gap-2">
                <dt class="font-medium text-hb-muted">Screening answers</dt>
                <dd class="text-end text-hb-navy">{{ $preview['summary']['screening'] }}</dd>
            </div>
            <div class="flex flex-wrap justify-between gap-2">
                <dt class="font-medium text-hb-muted">Consent</dt>
                <dd class="text-end text-hb-navy">{{ $preview['summary']['consent'] }}</dd>
            </div>
        </dl>
    </section>

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        <section class="hb-surface p-5 sm:p-6" aria-labelledby="next-heading">
            <h2 id="next-heading" class="text-base font-semibold text-hb-navy">What happens next</h2>
            <ol class="mt-4 space-y-4">
                @foreach ($preview['next_steps'] as $step)
                    <li class="flex gap-3">
                        <span class="text-sm font-semibold text-hb-blue">{{ $step['number'] }}</span>
                        <div>
                            <p class="text-sm font-semibold text-hb-navy">{{ $step['title'] }}</p>
                            <p class="mt-1 text-sm text-hb-muted">{{ $step['body'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
            @if (! empty($preview['application_id']))
                <a href="{{ route('candidate.applications.show', $preview['application_id']) }}" class="hb-btn-primary mt-5 inline-flex">
                    Track this application
                </a>
            @else
                <a href="{{ route('candidate.applications.index') }}" class="hb-btn-primary mt-5 inline-flex">
                    Go to My applications
                </a>
            @endif
        </section>

        <section class="hb-surface p-5 sm:p-6" aria-labelledby="momentum-heading">
            <h2 id="momentum-heading" class="text-base font-semibold text-hb-navy">Keep your momentum</h2>
            <p class="mt-2 text-sm leading-relaxed text-hb-muted">
                Explore other roles while you wait. Your search stays private to your workspace.
            </p>
            <a href="{{ route('candidate.jobs.index') }}" class="hb-btn-outline mt-5 inline-flex text-hb-blue">
                Browse recommended jobs
            </a>
            <p class="mt-4 text-xs text-hb-muted">
                Need to correct a material? Open the application and message your hiring contact. Do not submit a duplicate.
            </p>
        </section>
    </div>

    <p class="mt-8 text-xs text-hb-muted">{{ $preview['footer'] }}</p>
@endsection
