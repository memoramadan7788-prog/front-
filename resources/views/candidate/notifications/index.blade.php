@extends('layouts.app')

@section('title', $pageTitle ?? 'Notifications · Hirebridge')
@section('breadcrumb', 'Notifications / My workspace / Notifications')

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">Notifications</h1>
    <p class="mt-1 text-sm text-hb-muted">Hiring updates in one place. Frontend preview — not a live notification feed.</p>
@endsection

@section('page_actions')
    <button type="button" class="hb-btn-outline" data-notifications-mark-all>
        Mark all as read
    </button>
@endsection

@section('content')
    <div class="hb-mock-banner mb-6 rounded-lg text-start">
        Frontend UI only — read/unread state is stored in this browser and does not sync to the topbar across devices.
    </div>

    <div
        data-candidate-notifications
        data-storage-key="hb_candidate_notifications_v1"
        data-default-json='@json($notifications)'
    >
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <label class="sr-only" for="notif-filter">Filter notifications</label>
            <select id="notif-filter" class="hb-input w-auto min-w-[10rem]" data-notifications-filter>
                <option value="all">All</option>
                <option value="unread">Unread</option>
                <option value="interviews">Interviews</option>
                <option value="messages">Messages</option>
                <option value="offers">Offers</option>
                <option value="assessments">Assessments</option>
            </select>
            <p class="text-sm text-hb-muted">
                <span data-notifications-unread-count>0</span> unread
            </p>
        </div>

        <div class="hb-surface overflow-hidden" data-notifications-list>
            @foreach ($notifications as $item)
                <article
                    class="flex gap-3 border-b border-hb-border px-5 py-4 last:border-b-0 {{ ! empty($item['unread']) ? 'bg-hb-info-soft/40' : '' }}"
                    data-notification-item
                    data-notification-id="{{ $item['id'] }}"
                    data-category="{{ $item['category'] ?? 'all' }}"
                    data-unread="{{ ! empty($item['unread']) ? 'true' : 'false' }}"
                >
                    <span
                        class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ ! empty($item['unread']) ? 'bg-hb-blue' : 'bg-transparent' }}"
                        data-unread-dot
                        aria-hidden="true"
                    ></span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <p class="text-sm font-semibold text-hb-navy">{{ $item['title'] }}</p>
                                <p class="mt-0.5 text-sm text-hb-muted">{{ $item['body'] }}</p>
                                <p class="mt-1 text-xs text-hb-muted">{{ $item['time'] }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @if (! empty($item['href']))
                                    <a href="{{ $item['href'] }}" class="text-sm font-semibold text-hb-blue hover:underline" data-notification-link>
                                        Open
                                    </a>
                                @endif
                                <button
                                    type="button"
                                    class="text-sm font-semibold text-hb-muted hover:text-hb-blue"
                                    data-notification-toggle-read
                                >
                                    {{ ! empty($item['unread']) ? 'Mark read' : 'Mark unread' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="hb-surface mt-4 hidden px-5 py-10 text-center" data-notifications-empty role="status" hidden>
            <p class="font-semibold text-hb-navy">You’re all caught up</p>
            <p class="mt-2 text-sm text-hb-muted">No notifications match this filter.</p>
        </div>
    </div>

    <p class="mt-8 text-xs text-hb-muted">Hirebridge · Private workspace · All times Central unless noted · October 20, 2026</p>
@endsection
