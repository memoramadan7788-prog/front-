@extends('layouts.app')

@section('title', $pageTitle ?? __('candidate.notifications.page_title'))
@section('breadcrumb', __('candidate.notifications.breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.notifications.title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.notifications.intro') }}</p>
@endsection

@section('page_actions')
    <button type="button" class="hb-btn-outline" data-notifications-mark-all>
        {{ __('candidate.notifications.mark_all') }}
    </button>
@endsection

@section('content')

    <div
        data-candidate-notifications
        data-storage-key="hb_candidate_notifications_v1"
        data-default-json='@json($notifications)'
    >
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <label class="sr-only" for="notif-filter">{{ __('candidate.notifications.filter_aria') }}</label>
            <select id="notif-filter" class="hb-input w-auto min-w-[10rem]" data-notifications-filter>
                <option value="all">{{ __('candidate.notifications.filter_all') }}</option>
                <option value="unread">{{ __('candidate.notifications.filter_unread') }}</option>
                <option value="interviews">{{ __('candidate.notifications.filter_interviews') }}</option>
                <option value="messages">{{ __('candidate.notifications.filter_messages') }}</option>
                <option value="offers">{{ __('candidate.notifications.filter_offers') }}</option>
                <option value="assessments">{{ __('candidate.notifications.filter_assessments') }}</option>
            </select>
            <p class="text-sm text-hb-muted">
                <span data-notifications-unread-count>0</span> <span data-notifications-unread-label>{{ __('candidate.notifications.unread_count', ['count' => 0]) }}</span>
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
                                        {{ __('candidate.notifications.open') }}
                                    </a>
                                @endif
                                <button
                                    type="button"
                                    class="text-sm font-semibold text-hb-muted hover:text-hb-blue"
                                    data-notification-toggle-read
                                >
                                    {{ ! empty($item['unread']) ? __('candidate.notifications.mark_read') : __('candidate.notifications.mark_unread') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="hb-surface mt-4 hidden px-5 py-10 text-center" data-notifications-empty role="status" hidden>
            <p class="font-semibold text-hb-navy">{{ __('candidate.notifications.empty') }}</p>
            <p class="mt-2 text-sm text-hb-muted">{{ __('candidate.notifications.empty_filter') }}</p>
        </div>
    </div>

    <p class="mt-8 text-xs text-hb-muted">{{ __('common.footer.workspace_note', ['date' => __('candidate.footer_date')]) }}</p>
@endsection
