@extends('layouts.app')

@section('title', $pageTitle ?? 'Interview · Hirebridge')
@section('breadcrumb', '20 / My workspace / Interviews')

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ $interview['title'] }}</h1>
    <p class="mt-1 text-sm text-hb-muted">
        {{ $interview['company'] }} · {{ $interview['role'] }} · {{ $interview['code'] }}
    </p>
@endsection

@section('page_actions')
    <a href="{{ route('candidate.interviews.index') }}" class="hb-btn-outline">All interviews</a>
@endsection

@section('content')
    <div class="hb-mock-banner mb-6 rounded-lg text-start">
        Frontend UI only — interview detail is mock presentation data.
    </div>

    <div class="mx-auto max-w-2xl" data-candidate-interviews>
        @include('candidate.interviews._detail', ['interview' => $interview])
    </div>

    @include('layouts.modal', [
        'modalId' => 'interview-prep-modal',
        'modalTitle' => 'Interview preparation',
        'modalSize' => 'lg',
        'modalBody' => view('candidate.interviews._prep-modal-body', ['interview' => $interview])->render(),
    ])

    @include('layouts.modal', [
        'modalId' => 'interview-reschedule-modal',
        'modalTitle' => 'Request reschedule',
        'modalSize' => 'md',
        'modalBody' => '<p class="text-sm text-hb-muted">Frontend preview only. Choose a preferred window — nothing is sent to the hiring team.</p><label class="hb-label mt-4" for="reschedule-note">Preferred times</label><textarea id="reschedule-note" class="hb-input" rows="3" placeholder="e.g. Thursday afternoon or Friday morning Central" data-reschedule-note></textarea><p class="mt-3 hidden text-sm text-hb-success" data-reschedule-status role="status"></p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>Cancel</button><button type="button" class="hb-btn-primary" data-reschedule-submit>Save preview request</button>',
    ])

    <p class="mt-8 text-xs text-hb-muted">{{ $interviews['footer'] ?? '' }}</p>
@endsection
