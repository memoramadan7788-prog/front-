@extends('layouts.app')

@php
    $messageUrl = ! empty($application['message_thread_id'])
        ? route('candidate.messages.index', ['thread' => $application['message_thread_id']])
        : route('candidate.messages.index');
    $interviewUrl = ! empty($application['interview_id'])
        ? route('candidate.interviews.show', $application['interview_id'])
        : route('candidate.interviews.index');
    $offerUrl = ! empty($application['offer_id'])
        ? route('candidate.offers.show', $application['offer_id'])
        : null;
@endphp

@section('title', $pageTitle ?? __('candidate.applications.show_page_title'))
@section('breadcrumb', __('candidate.workspace') . ' / ' . __('navigation.candidate.applications'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ $application['title'] }}</h1>
    <p class="mt-1 text-sm text-hb-muted">
        {{ $application['company'] }} · {{ $application['code'] }} · {{ __('candidate.applications.application_label', ['reference' => $application['reference']]) }}
    </p>
    <div class="mt-3 flex flex-wrap items-center gap-2">
        <x-badge
            :variant="$application['archived'] ? 'neutral' : 'info'"
            :dot="true"
        >{{ $application['stage'] }}</x-badge>
        <x-badge variant="neutral" :dot="true">{{ $application['source'] }}</x-badge>
        <span class="text-sm text-hb-muted">{{ __('candidate.applications.applied_long', ['date' => $application['applied_on_long']]) }}</span>
    </div>
@endsection

@section('page_actions')
    <a href="{{ $messageUrl }}" class="hb-btn-primary">
        {{ __('candidate.applications.message_team') }}
    </a>
@endsection

@section('content')

    <div class="mb-6">
        <x-timeline :steps="$application['pipeline']" />
    </div>

    <div data-tabs>
        <div class="border-b border-hb-border" role="tablist" aria-label="{{ __('candidate.applications.detail_tabs_aria') }}">
            <div class="flex flex-wrap gap-1">
                <button type="button" id="app-detail-tab-overview" role="tab" aria-selected="true" aria-controls="app-detail-panel-overview" tabindex="0" class="border-b-2 border-hb-blue px-3 py-2.5 text-sm font-semibold text-hb-blue">
                    {{ __('candidate.applications.overview') }}
                </button>
                <button type="button" id="app-detail-tab-documents" role="tab" aria-selected="false" aria-controls="app-detail-panel-documents" tabindex="-1" class="border-b-2 border-transparent px-3 py-2.5 text-sm font-semibold text-hb-muted">
                    {{ __('candidate.applications.documents') }}
                </button>
                <button type="button" id="app-detail-tab-activity" role="tab" aria-selected="false" aria-controls="app-detail-panel-activity" tabindex="-1" class="border-b-2 border-transparent px-3 py-2.5 text-sm font-semibold text-hb-muted">
                    {{ __('candidate.applications.activity') }}
                </button>
            </div>
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
            <div class="min-w-0 space-y-6">
                <div id="app-detail-panel-overview" role="tabpanel" aria-labelledby="app-detail-tab-overview">
                    @if (! empty($application['interview']))
                        <section class="hb-surface p-5 sm:p-6" aria-labelledby="interview-heading">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h2 id="interview-heading" class="text-lg font-semibold text-hb-navy">
                                        {{ $application['interview']['title'] }}
                                    </h2>
                                    <p class="mt-1 text-sm text-hb-muted">
                                        {{ $application['company'] }} · {{ $application['title'] }} · {{ $application['code'] }}
                                    </p>
                                </div>
                                <x-badge variant="info" :dot="true">{{ $application['interview']['status'] }}</x-badge>
                            </div>

                            <div class="mt-5 space-y-2 text-sm text-hb-text">
                                <p class="text-base font-semibold text-hb-navy">{{ $application['interview']['when'] }}</p>
                                <p class="text-hb-muted">{{ $application['interview']['time'] }}</p>
                                <p class="pt-2">{{ $application['interview']['interviewer'] }}</p>
                                <p class="text-hb-muted">{{ $application['interview']['format'] }}</p>
                            </div>

                            <div class="mt-5 flex flex-wrap gap-2">
                                <a href="{{ $interviewUrl }}" class="hb-btn-primary">{{ __('candidate.applications.view_preparation') }}</a>
                                <a href="{{ $interviewUrl }}" class="hb-btn-outline">{{ __('candidate.interviews.reschedule') }}</a>
                            </div>
                        </section>
                    @elseif (! empty($application['archived']))
                        <section class="hb-surface p-5 sm:p-6">
                            <h2 class="text-lg font-semibold text-hb-navy">{{ __('candidate.applications.archived_heading') }}</h2>
                            <p class="mt-2 text-sm leading-relaxed text-hb-muted">{{ $application['archived_note'] }}</p>
                        </section>
                    @else
                        <section class="hb-surface p-5 sm:p-6">
                            <h2 class="text-lg font-semibold text-hb-navy">{{ __('candidate.applications.current_status') }}</h2>
                            <p class="mt-2 text-sm text-hb-muted">
                                {{ __('candidate.applications.stage_label', ['stage' => $application['stage']]) }}
                            </p>
                            <p class="mt-2 text-sm text-hb-text">
                                <span class="font-medium text-hb-navy">{{ __('candidate.applications.next_label') }}</span> {{ $application['next_step'] }}
                            </p>
                            <p class="mt-2 text-sm text-hb-muted">{{ __('candidate.applications.last_activity', ['date' => $application['last_activity']]) }}</p>
                            @if (($application['stage_key'] ?? '') === 'offer' && $offerUrl)
                                <a href="{{ $offerUrl }}" class="hb-btn-primary mt-4 inline-flex">
                                    {{ __('candidate.applications.review_offer') }}
                                </a>
                            @endif
                        </section>
                    @endif

                    @if (! empty($application['assessment_id']))
                        <section class="hb-surface mt-6 p-5 sm:p-6" aria-labelledby="linked-assessment-heading" data-linked-assessment>
                            <h2 id="linked-assessment-heading" class="text-lg font-semibold text-hb-navy">{{ __('candidate.assessments.linked_assessment_heading') }}</h2>
                            <p class="mt-2 text-sm text-hb-muted">{{ __('candidate.assessments.linked_assessment_intro') }}</p>
                            <dl class="mt-4 grid gap-2 text-sm sm:grid-cols-2">
                                <div>
                                    <dt class="text-hb-muted">{{ __('candidate.applications.current_status') }}</dt>
                                    <dd class="font-semibold text-hb-navy">{{ $application['assessment_status'] ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-hb-muted">{{ __('candidate.assessments.attempts_used') }}</dt>
                                    <dd class="font-semibold text-hb-navy" dir="ltr">
                                        {{ __('candidate.assessments.assessment_attempt_line', [
                                            'used' => $application['assessment_attempts_used'] ?? 0,
                                            'limit' => $application['assessment_attempt_limit'] ?? 1,
                                        ]) }}
                                    </dd>
                                </div>
                            </dl>
                            @if (($application['assessment_proctoring_review'] ?? '') === 'pending')
                                <p class="mt-3 text-sm text-hb-text">{{ __('candidate.assessments.assessment_proctoring_pending') }}</p>
                            @endif
                            <a href="{{ route('candidate.assessments.show', $application['assessment_id']) }}" class="hb-btn-outline mt-4 inline-flex">
                                {{ __('candidate.assessments.open_assessment') }}
                            </a>
                        </section>
                    @endif

                    <section class="hb-surface mt-6 p-5 sm:p-6" aria-labelledby="activity-heading-overview">
                        <h2 id="activity-heading-overview" class="text-lg font-semibold text-hb-navy">{{ __('candidate.applications.application_activity') }}</h2>
                        <ol class="mt-4 space-y-3 rounded-lg border border-hb-border px-4 py-4">
                            @foreach ($application['activity'] as $item)
                                <li class="text-sm">
                                    <p class="font-medium text-hb-navy">{{ $item['when'] }}</p>
                                    <p class="mt-0.5 text-hb-muted">{{ $item['text'] }}</p>
                                </li>
                            @endforeach
                        </ol>
                    </section>
                </div>

                <div id="app-detail-panel-documents" role="tabpanel" aria-labelledby="app-detail-tab-documents" class="hidden" hidden>
                    <section class="hb-surface p-5 sm:p-6">
                        <h2 class="text-lg font-semibold text-hb-navy">{{ __('candidate.applications.submitted_materials') }}</h2>
                        <ul class="mt-4 divide-y divide-hb-border rounded-lg border border-hb-border">
                            @foreach ($application['materials'] as $material)
                                <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                                    <span class="font-medium text-hb-navy">{{ $material['label'] }}</span>
                                    <span class="text-hb-muted">{{ $material['meta'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <p class="mt-4 text-sm text-hb-muted">{{ $application['screening_summary'] }}</p>
                        <button type="button" class="hb-btn-outline mt-4" disabled title="{{ __('candidate.applications.view_documents_soon') }}">
                            {{ __('candidate.applications.view_documents') }}
                        </button>
                    </section>
                </div>

                <div id="app-detail-panel-activity" role="tabpanel" aria-labelledby="app-detail-tab-activity" class="hidden" hidden>
                    <section class="hb-surface p-5 sm:p-6">
                        <h2 class="text-lg font-semibold text-hb-navy">{{ __('candidate.applications.full_activity') }}</h2>
                        <ol class="mt-4 space-y-4">
                            @foreach ($application['activity'] as $item)
                                <li class="border-s-2 border-hb-border ps-4 text-sm">
                                    <p class="font-medium text-hb-navy">{{ $item['when'] }}</p>
                                    <p class="mt-1 text-hb-muted">{{ $item['text'] }}</p>
                                </li>
                            @endforeach
                        </ol>
                    </section>
                </div>
            </div>

            <aside class="space-y-4">
                <section class="hb-surface p-5" aria-labelledby="contacts-heading">
                    <h2 id="contacts-heading" class="text-base font-semibold text-hb-navy">{{ __('candidate.applications.hiring_contacts') }}</h2>
                    <ul class="mt-3 space-y-3 text-sm">
                        @foreach ($application['contacts'] as $contact)
                            <li>
                                <p class="font-medium text-hb-navy">{{ $contact['name'] }}</p>
                                <p class="text-hb-muted">{{ $contact['role'] }}</p>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ $messageUrl }}" class="hb-btn-outline mt-4 w-full text-center">
                        {{ __('candidate.applications.send_message') }}
                    </a>
                </section>

                <section class="hb-surface p-5" aria-labelledby="materials-heading">
                    <h2 id="materials-heading" class="text-base font-semibold text-hb-navy">{{ __('candidate.applications.submitted_materials') }}</h2>
                    <ul class="mt-3 space-y-2 text-sm text-hb-text">
                        @foreach ($application['materials'] as $material)
                            <li>{{ $material['label'] }}@if (! empty($material['meta'])) · {{ $material['meta'] }}@endif</li>
                        @endforeach
                    </ul>
                    <button type="button" class="hb-btn-outline mt-4 w-full" disabled title="{{ __('candidate.applications.view_documents_soon') }}">
                        {{ __('candidate.applications.view_documents') }}
                    </button>
                </section>

                <section class="hb-surface p-5" aria-labelledby="controls-heading" data-application-controls>
                    <h2 id="controls-heading" class="text-base font-semibold text-hb-navy">{{ __('candidate.applications.application_controls') }}</h2>
                    <dl class="mt-3 space-y-2 text-sm text-hb-muted">
                        <div class="flex justify-between gap-2">
                            <dt>{{ __('candidate.applications.attempt_label') }}</dt>
                            <dd class="font-medium text-hb-navy">
                                {{ __('candidate.applications.attempt_value', [
                                    'current' => $application['attempt_no'] ?? 1,
                                    'limit' => $application['attempt_limit'] ?? 1,
                                ]) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-hb-text">{{ __('candidate.applications.reapply_heading') }}</dt>
                            <dd class="mt-1">
                                @if (! empty($application['can_reapply']))
                                    {{ __('candidate.applications.reapply_available') }}
                                @else
                                    {{ $application['reapply_reason'] ?? __('candidate.applications.reapply_blocked') }}
                                @endif
                            </dd>
                        </div>
                    </dl>
                    <p class="mt-3 text-sm leading-relaxed text-hb-muted">
                        @if (! empty($application['can_withdraw']))
                            {{ __('candidate.applications.withdraw_available_note') }}
                        @else
                            {{ $application['withdraw_reason'] ?? __('candidate.applications.withdraw_note') }}
                        @endif
                    </p>
                    <button
                        type="button"
                        class="mt-4 text-sm font-semibold text-hb-blue hover:underline disabled:cursor-not-allowed disabled:opacity-50"
                        data-withdraw-preview
                        @disabled(! empty($application['archived']) || empty($application['can_withdraw']))
                        title="{{ ! empty($application['can_withdraw']) ? __('candidate.applications.withdraw_preview_title') : ($application['withdraw_reason'] ?? __('candidate.applications.withdraw_note')) }}"
                    >
                        {{ ! empty($application['archived']) ? __('candidate.applications.withdrawn') : __('candidate.applications.withdraw') }}
                    </button>
                    <p class="mt-2 text-xs text-hb-muted">{{ __('candidate.applications.withdraw_preview_only') }}</p>
                    <p class="mt-2 hidden text-sm text-hb-muted" data-withdraw-status role="status"></p>
                </section>

                <a href="{{ route('candidate.applications.index') }}" class="inline-flex text-sm font-semibold text-hb-blue hover:underline">
                    {{ __('candidate.applications.back_to_index') }}
                </a>
            </aside>
        </div>
    </div>

    <p class="mt-8 text-xs text-hb-muted">{{ __('common.footer.workspace_note', ['date' => __('candidate.footer_date')]) }}</p>
@endsection
