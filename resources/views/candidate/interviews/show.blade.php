@extends('layouts.app')

@section('title', $pageTitle ?? __('candidate.interviews.page_title'))
@section('breadcrumb', __('candidate.workspace') . ' / ' . __('navigation.candidate.interviews'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ $interview['title'] }}</h1>
    <p class="mt-1 text-sm text-hb-muted">
        {{ $interview['company'] }} · {{ $interview['role'] }} · {{ $interview['code'] }}
    </p>
@endsection

@section('page_actions')
    <a href="{{ route('candidate.interviews.index') }}" class="hb-btn-outline">{{ __('candidate.interviews.all_interviews') }}</a>
@endsection

@section('content')

    <div class="mx-auto max-w-2xl" data-candidate-interviews>
        @include('candidate.interviews._detail', ['interview' => $interview])
    </div>

    @include('layouts.modal', [
        'modalId' => 'interview-prep-modal',
        'modalTitle' => __('candidate.interviews.prep_modal_title'),
        'modalSize' => 'lg',
        'modalBody' => view('candidate.interviews._prep-modal-body', ['interview' => $interview])->render(),
    ])

    @include('layouts.modal', [
        'modalId' => 'interview-reschedule-modal',
        'modalTitle' => __('candidate.interviews.reschedule'),
        'modalSize' => 'md',
        'modalBody' => view('candidate.interviews._reschedule-modal-body')->render(),
        'modalFooter' => view('candidate.interviews._reschedule-modal-footer')->render(),
    ])

    <p class="mt-8 text-xs text-hb-muted">{{ $interviews['footer'] ?? '' }}</p>
@endsection
