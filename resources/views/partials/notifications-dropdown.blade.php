@php
    /**
     * MOCK notifications — frontend presentation only (F9E actor-scoped).
     * Not loaded from the database or notification service.
     */
    $notifActor = $uiRole ?? request()->query('role', 'candidate');
    if (! in_array($notifActor, ['candidate', 'company', 'interviewer', 'admin'], true)) {
        $notifActor = 'candidate';
    }
    $notifications = $mockNotifications ?? \App\Support\Frontend\MockNotificationData::forActor($notifActor);
    $unreadCount = collect($notifications)->where('unread', true)->count();
    $panelId = $panelId ?? 'notifications-panel';
    $storageKey = \App\Support\Frontend\MockNotificationData::storageKey($notifActor);
    $viewAllRoute = \App\Support\Frontend\MockNotificationData::viewAllRoute($notifActor);
@endphp

<div class="relative" data-notifications-dropdown data-storage-key="{{ $storageKey }}" data-notif-actor="{{ $notifActor }}">
    <button
        type="button"
        class="relative inline-flex h-10 items-center gap-2 rounded-lg px-2 text-sm font-medium text-hb-muted hover:bg-hb-page hover:text-hb-text"
        data-dropdown-trigger
        aria-haspopup="true"
        aria-expanded="false"
        aria-controls="{{ $panelId }}"
        id="{{ $panelId }}-trigger"
    >
        {{ __('common.notifications.label') }}
        <span
            class="absolute end-1 top-2 h-1.5 w-1.5 rounded-full bg-hb-blue {{ $unreadCount > 0 ? '' : 'hidden' }}"
            data-dropdown-unread-dot
            aria-hidden="true"
        ></span>
        <span class="sr-only" data-dropdown-unread-label>{{ __('common.notifications.unread', ['count' => $unreadCount]) }}</span>
    </button>

    <div
        id="{{ $panelId }}"
        class="absolute end-0 z-50 mt-2 hidden w-80 overflow-hidden rounded-hb border border-hb-border bg-hb-surface shadow-hb-md"
        role="menu"
        aria-labelledby="{{ $panelId }}-trigger"
        data-dropdown-panel
    >
        <div class="border-b border-hb-border px-4 py-3">
            <div class="flex items-center justify-between gap-2">
                <p class="text-sm font-semibold text-hb-navy">{{ __('common.notifications.label') }}</p>
                <span class="rounded-full bg-hb-pending-soft px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-hb-pending">
                    {{ __('common.status.new') }}
                </span>
            </div>
            <p class="mt-2 text-[11px] text-hb-muted">{{ __('notifications.preview_note') }}</p>
        </div>

        @if (count($notifications) === 0)
            <div class="px-4 py-8 text-center">
                <p class="text-sm font-medium text-hb-navy">{{ __('common.notifications.caught_up_title') }}</p>
                <p class="mt-1 text-xs text-hb-muted">{{ __('common.notifications.caught_up_body') }}</p>
            </div>
        @else
            <ul class="max-h-80 divide-y divide-hb-border overflow-y-auto" data-dropdown-notification-list>
                @foreach ($notifications as $item)
                    <li role="menuitem" data-dropdown-item data-notification-id="{{ $item['id'] }}" data-unread="{{ ! empty($item['unread']) ? 'true' : 'false' }}" data-notif-event="{{ $item['event'] ?? '' }}">
                        <a
                            href="{{ $item['href'] ?? '#' }}"
                            class="flex gap-3 px-4 py-3 hover:bg-hb-page {{ ! empty($item['unread']) ? 'bg-hb-info-soft/50' : 'bg-hb-surface' }}"
                            data-dropdown-item-link
                        >
                            <span
                                class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ ! empty($item['unread']) ? 'bg-hb-blue' : 'bg-transparent' }}"
                                data-unread-dot
                                aria-hidden="true"
                            ></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-hb-text">{{ $item['title'] }}</p>
                                <p class="mt-0.5 text-xs text-hb-muted">{{ $item['body'] }}</p>
                                <p class="mt-1 text-[11px] text-hb-muted">{{ $item['time'] }}</p>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($viewAllRoute)
            <div class="border-t border-hb-border px-4 py-2 text-center">
                <a href="{{ route($viewAllRoute) }}" class="text-xs font-semibold text-hb-blue hover:underline">
                    {{ __('common.notifications.view_all') }}
                </a>
            </div>
        @endif
    </div>
</div>
