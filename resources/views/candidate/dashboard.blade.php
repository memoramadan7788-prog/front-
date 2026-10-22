@extends('layouts.app')

@section('title', $pageTitle ?? __('candidate.dashboard.page_title'))
@section('breadcrumb', __('candidate.dashboard.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ $dashboard['greeting'] }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ $dashboard['intro'] }}</p>
@endsection

@section('page_actions')
    <a href="{{ route('candidate.jobs.index') }}" class="hb-btn-primary">{{ __('candidate.dashboard.discover_jobs') }}</a>
@endsection

@section('content')
    <div class="sr-only" aria-live="polite" data-save-live></div>

    {{-- Stats --}}
    <div class="grid gap-4 sm:grid-cols-3">
        @foreach ($dashboard['stats'] as $stat)
            @php
                $href = ! empty($stat['href']) && \Illuminate\Support\Facades\Route::has($stat['href'])
                    ? route($stat['href'])
                    : null;
            @endphp
            @if ($href)
                <a href="{{ $href }}" class="hb-surface block p-5 transition hover:border-hb-blue/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40">
                    <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ $stat['label'] }}</p>
                    <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">
                        @if (! empty($stat['count_attr']) && $stat['count_attr'] === 'data-saved-count')
                            <span data-saved-count>{{ $stat['value'] }}</span>
                        @else
                            {{ $stat['value'] }}
                        @endif
                    </p>
                    <p class="mt-2 text-sm text-hb-muted">{{ $stat['meta'] }}</p>
                </a>
            @else
                <div class="hb-surface p-5">
                    <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ $stat['label'] }}</p>
                    <p class="mt-2 text-3xl font-semibold tracking-tight text-hb-navy">{{ $stat['value'] }}</p>
                    <p class="mt-2 text-sm text-hb-muted">{{ $stat['meta'] }}</p>
                </div>
            @endif
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div class="space-y-6">
            {{-- Upcoming interview --}}
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="upcoming-interview-heading">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 id="upcoming-interview-heading" class="text-lg font-semibold text-hb-navy">
                            {{ $dashboard['interview']['title'] }}
                        </h2>
                        <p class="mt-1 text-sm text-hb-muted">
                            {{ $dashboard['interview']['company'] }} · {{ $dashboard['interview']['role'] }} · {{ $dashboard['interview']['code'] }}
                        </p>
                    </div>
                    <x-badge variant="success" :dot="true">{{ $dashboard['interview']['status'] }}</x-badge>
                </div>

                <div class="mt-5 space-y-2 text-sm text-hb-text">
                    <p class="font-semibold text-hb-navy">{{ $dashboard['interview']['when'] }}</p>
                    <p class="text-hb-muted">{{ $dashboard['interview']['time'] }}</p>
                    <p class="pt-2">{{ $dashboard['interview']['interviewer'] }}</p>
                    <p class="text-hb-muted">{{ $dashboard['interview']['format'] }}</p>
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    <a
                        href="{{ route('candidate.interviews.show', $dashboard['interview']['id'] ?? 'int-ns204-portfolio') }}"
                        class="hb-btn-primary"
                    >{{ __('candidate.dashboard.view_preparation') }}</a>
                    <a href="{{ route('candidate.messages.index', ['thread' => 'msg-olivia-ns204']) }}" class="hb-btn-outline">{{ __('candidate.dashboard.open_messages') }}</a>
                </div>
            </section>

            {{-- Application snapshot --}}
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="application-heading">
                <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('candidate.dashboard.northstar_application') }}</p>
                <div class="mt-2 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 id="application-heading" class="text-lg font-semibold text-hb-navy">
                            {{ $dashboard['application']['title'] }} · {{ $dashboard['application']['code'] }}
                        </h2>
                    </div>
                    <x-badge variant="info" :dot="true">{{ $dashboard['application']['stage'] }}</x-badge>
                </div>

                <div class="mt-5">
                    <x-timeline :steps="$dashboard['application']['pipeline']" />
                </div>

                <p class="mt-5 text-sm leading-relaxed text-hb-muted">{{ $dashboard['application']['note'] }}</p>

                <div class="mt-5">
                    <a href="{{ route('candidate.applications.show', 'hb-ns204-mc-1012') }}" class="hb-btn-outline">{{ __('candidate.dashboard.view_application') }}</a>
                </div>
            </section>

            {{-- Recommended --}}
            <section class="hb-surface p-5 sm:p-6" aria-labelledby="recommended-heading" data-candidate-dashboard-recommended>
                <h2 id="recommended-heading" class="text-lg font-semibold text-hb-navy">{{ __('candidate.dashboard.recommended') }}</h2>
                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <p class="font-semibold text-hb-navy">{{ $dashboard['recommended']['title'] }}</p>
                        <p class="mt-1 text-sm text-hb-muted">
                            {{ $dashboard['recommended']['company'] }} · {{ $dashboard['recommended']['code'] }}
                        </p>
                        <p class="mt-1 text-sm text-hb-muted">{{ $dashboard['recommended']['meta'] }}</p>
                        <div class="mt-3">
                            <x-badge variant="pending" :dot="true">{{ $dashboard['recommended']['source'] }}</x-badge>
                        </div>
                        <p class="mt-3 text-sm text-hb-muted">{{ $dashboard['recommended']['reason'] }}</p>
                    </div>
                    <div class="flex shrink-0 flex-wrap gap-2">
                        <button
                            type="button"
                            class="hb-btn-ghost"
                            data-save-job="{{ $dashboard['recommended']['id'] }}"
                            aria-pressed="false"
                        >
                            <span data-save-label>{{ __('candidate.dashboard.save') }}</span>
                        </button>
                        <a href="{{ route('jobs.show', $dashboard['recommended']['id']) }}" class="hb-btn-outline">
                            {{ __('candidate.dashboard.view_role') }}
                        </a>
                        <a href="{{ route('candidate.applications.apply', $dashboard['recommended']['id']) }}" class="hb-btn-primary">
                            {{ __('candidate.jobs.review_apply_arrow') }}
                        </a>
                    </div>
                </div>
            </section>
        </div>

        <aside class="space-y-4" aria-label="{{ __('candidate.dashboard.progress_aside') }}">
            <div class="hb-surface p-5">
                <p class="text-xs font-semibold tracking-wide text-hb-muted uppercase">{{ __('candidate.dashboard.profile_eyebrow') }}</p>
                <h2 class="mt-2 text-base font-semibold text-hb-navy">{{ __('candidate.dashboard.complete_profile') }}</h2>
                <p class="mt-2 text-sm font-medium text-hb-blue">{{ __('candidate.dashboard.percent_complete', ['percent' => $dashboard['profile_progress']['percent']]) }}</p>
                <ul class="mt-4 space-y-2 text-sm text-hb-muted">
                    @foreach ($dashboard['profile_progress']['items'] as $item)
                        <li class="flex gap-2">
                            <span @class(['text-hb-success' => $item['done'], 'text-hb-muted' => ! $item['done']]) aria-hidden="true">
                                {{ $item['done'] ? '☑' : '□' }}
                            </span>
                            {{ $item['label'] }}
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('candidate.profile') }}" class="hb-btn-primary mt-5 w-full">{{ __('candidate.dashboard.finish_profile') }}</a>
            </div>

            <div class="hb-surface p-5">
                <h2 class="text-base font-semibold text-hb-navy">{{ __('candidate.dashboard.next_actions') }}</h2>
                <ul class="mt-4 space-y-4">
                    @foreach ($dashboard['next_actions'] as $action)
                        <li>
                            @if (str_contains($action['title'], 'Meridian offer'))
                                <a href="{{ route('candidate.offers.show', 'offer-ms108') }}" class="text-sm font-semibold text-hb-blue hover:underline">
                                    {{ $action['title'] }}
                                </a>
                            @elseif (str_contains($action['title'], 'message'))
                                <a href="{{ route('candidate.messages.index', ['thread' => 'msg-olivia-ns204']) }}" class="text-sm font-semibold text-hb-blue hover:underline">
                                    {{ $action['title'] }}
                                </a>
                            @elseif (str_contains($action['title'], 'portfolio'))
                                <a href="{{ route('candidate.interviews.show', 'int-ns204-portfolio') }}" class="text-sm font-semibold text-hb-blue hover:underline">
                                    {{ $action['title'] }}
                                </a>
                            @else
                                <p class="text-sm font-semibold text-hb-navy">{{ $action['title'] }}</p>
                            @endif
                            <p class="mt-1 text-xs text-hb-muted">{{ $action['meta'] }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>
    </div>

    <p class="mt-8 text-xs text-hb-muted">{{ $dashboard['footer'] }}</p>
@endsection
