@extends('layouts.app')

@section('title', $pageTitle ?? 'Your interviews · Hirebridge')
@section('breadcrumb', '20 / My workspace / Interviews')

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">Your interviews</h1>
    <p class="mt-1 text-sm text-hb-muted">A clear plan for every conversation. All times shown in Central Time.</p>
@endsection

@section('content')
    @php
        $upcoming = $interviews['upcoming'] ?? [];
        $past = $interviews['past'] ?? [];
        $selected = $selected ?? ($upcoming[0] ?? null);
    @endphp

    <div class="hb-mock-banner mb-6 rounded-lg text-start">
        Frontend UI only — interview times, preparation, and reschedule actions are mock presentation data.
    </div>

    <div data-candidate-interviews data-tabs>
        <div class="border-b border-hb-border" role="tablist" aria-label="Interview lists">
            <div class="flex flex-wrap gap-1">
                <button
                    type="button"
                    id="interviews-tab-upcoming"
                    role="tab"
                    aria-selected="true"
                    aria-controls="interviews-panel-upcoming"
                    tabindex="0"
                    class="border-b-2 border-hb-blue px-3 py-2.5 text-sm font-semibold text-hb-blue"
                >
                    Upcoming ({{ count($upcoming) }})
                </button>
                <button
                    type="button"
                    id="interviews-tab-past"
                    role="tab"
                    aria-selected="false"
                    aria-controls="interviews-panel-past"
                    tabindex="-1"
                    class="border-b-2 border-transparent px-3 py-2.5 text-sm font-semibold text-hb-muted"
                >
                    Past ({{ count($past) }})
                </button>
            </div>
        </div>

        <div
            id="interviews-panel-upcoming"
            role="tabpanel"
            aria-labelledby="interviews-tab-upcoming"
            class="mt-6"
        >
            @if (count($upcoming) === 0)
                <div class="hb-surface px-5 py-10 text-center" role="status">
                    <p class="font-semibold text-hb-navy">No upcoming interviews</p>
                    <p class="mt-2 text-sm text-hb-muted">When a hiring team schedules a conversation, it will appear here.</p>
                    <a href="{{ route('candidate.applications.index') }}" class="hb-btn-primary mt-4 inline-flex">My applications</a>
                </div>
            @else
                <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
                    <div class="space-y-4">
                        <section class="hb-surface p-5 sm:p-6" aria-labelledby="week-heading">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <h2 id="week-heading" class="text-base font-semibold text-hb-navy">{{ $interviews['week_label'] }}</h2>
                                    <p class="mt-1 text-xs text-hb-muted">{{ $interviews['timezone'] }}</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="button" class="hb-btn-outline px-3 py-2" data-week-nav="prev" aria-label="Previous week">‹</button>
                                    <button type="button" class="hb-btn-outline px-3 py-2" data-week-today>Today</button>
                                    <button type="button" class="hb-btn-outline px-3 py-2" data-week-nav="next" aria-label="Next week">›</button>
                                </div>
                            </div>
                            <p class="mt-2 hidden text-xs text-hb-muted" data-week-nav-status role="status"></p>

                            <div class="mt-5 grid gap-2 sm:grid-cols-5" data-interview-calendar>
                                @foreach ($interviews['days'] as $day)
                                    <div
                                        @class([
                                            'rounded-lg border px-2 py-3 text-sm',
                                            'border-hb-blue bg-hb-info-soft' => ! empty($day['highlight']),
                                            'border-hb-border bg-hb-page' => empty($day['highlight']),
                                        ])
                                        data-calendar-day="{{ $day['key'] }}"
                                    >
                                        <p class="text-xs font-semibold text-hb-navy">{{ $day['label'] }}</p>
                                        <ul class="mt-2 space-y-1.5">
                                            @foreach ($day['slots'] as $slot)
                                                <li>
                                                    @if (! empty($slot['interview_id']))
                                                        <button
                                                            type="button"
                                                            class="w-full rounded-md bg-hb-blue px-1.5 py-1 text-start text-[11px] font-semibold leading-snug text-white"
                                                            data-select-interview="{{ $slot['interview_id'] }}"
                                                        >
                                                            <span class="block">{{ $slot['time'] }}</span>
                                                            <span class="block font-medium opacity-90">{{ $slot['label'] }}</span>
                                                        </button>
                                                    @else
                                                        <p class="text-[11px] text-hb-muted">{{ $slot['time'] }} · {{ $slot['label'] }}</p>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        </section>

                        @foreach ($past as $item)
                            <section class="hb-surface p-5" aria-labelledby="prev-convo-heading">
                                <h2 id="prev-convo-heading" class="text-base font-semibold text-hb-navy">Previous conversation</h2>
                                <p class="mt-2 text-sm font-medium text-hb-text">{{ $item['title'] }} · {{ $item['company'] }}</p>
                                <p class="mt-1 text-sm text-hb-muted">{{ $item['when'] }} · {{ $item['time'] }}</p>
                                <p class="mt-2 text-sm text-hb-muted">{{ $item['note'] ?? '' }}</p>
                                <a href="{{ route('candidate.interviews.show', $item['id']) }}" class="mt-3 inline-flex text-sm font-semibold text-hb-blue hover:underline">
                                    View details
                                </a>
                            </section>
                        @endforeach
                    </div>

                    <aside class="space-y-4" data-interview-detail-panel>
                        @if ($selected)
                            @include('candidate.interviews._detail', ['interview' => $selected, 'compact' => true])
                        @endif
                    </aside>
                </div>
            @endif
        </div>

        <div
            id="interviews-panel-past"
            role="tabpanel"
            aria-labelledby="interviews-tab-past"
            class="mt-6 hidden"
            hidden
        >
            <div class="space-y-4">
                @forelse ($past as $item)
                    <article class="hb-surface p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h2 class="text-base font-semibold text-hb-navy">{{ $item['title'] }} · {{ $item['company'] }}</h2>
                                <p class="mt-1 text-sm text-hb-muted">{{ $item['role'] }} · {{ $item['code'] }}</p>
                                <p class="mt-2 text-sm text-hb-text">{{ $item['when'] }} · {{ $item['time'] }}</p>
                                <p class="mt-2 text-sm text-hb-muted">{{ $item['note'] ?? '' }}</p>
                            </div>
                            <x-badge variant="success" :dot="true">{{ $item['status'] }}</x-badge>
                        </div>
                        <a href="{{ route('candidate.interviews.show', $item['id']) }}" class="hb-btn-outline mt-4 inline-flex">View details</a>
                    </article>
                @empty
                    <div class="hb-surface px-5 py-10 text-center" role="status">
                        <p class="font-semibold text-hb-navy">No past interviews yet</p>
                        <p class="mt-2 text-sm text-hb-muted">Completed conversations will move here.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @include('layouts.modal', [
        'modalId' => 'interview-prep-modal',
        'modalTitle' => 'Interview preparation',
        'modalSize' => 'lg',
        'modalBody' => view('candidate.interviews._prep-modal-body', ['interview' => $selected])->render(),
    ])

    @include('layouts.modal', [
        'modalId' => 'interview-reschedule-modal',
        'modalTitle' => 'Request reschedule',
        'modalSize' => 'md',
        'modalBody' => '<p class="text-sm text-hb-muted">Frontend preview only. Choose a preferred window — nothing is sent to the hiring team.</p><label class="hb-label mt-4" for="reschedule-note">Preferred times</label><textarea id="reschedule-note" class="hb-input" rows="3" placeholder="e.g. Thursday afternoon or Friday morning Central" data-reschedule-note></textarea><p class="mt-3 hidden text-sm text-hb-success" data-reschedule-status role="status"></p>',
        'modalFooter' => '<button type="button" class="hb-btn-outline" data-hb-modal-close>Cancel</button><button type="button" class="hb-btn-primary" data-reschedule-submit>Save preview request</button>',
    ])

    <p class="mt-8 text-xs text-hb-muted">{{ $interviews['footer'] }}</p>
@endsection
