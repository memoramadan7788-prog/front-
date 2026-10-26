@php
    use App\Support\Frontend\Notifications\NotificationsGateway;

    $notificationsGateway = app(NotificationsGateway::class);
    $notificationsAvailable = $notificationsGateway->isAvailable();
    $notificationItems = [];
    $notificationsError = null;
    $unreadCount = null;
    $notificationsPanelId = 'notifications-menu-panel';

    if ($notificationsAvailable) {
        $list = $notificationsGateway->list(1);
        if ((int) ($list['statusCode'] ?? 500) === 401) {
            // Session clearing is handled elsewhere on API 401; show error in panel.
            $notificationsError = __('common.notifications.session_expired');
        } elseif ((int) ($list['statusCode'] ?? 500) === 200) {
            $notificationItems = is_array($list['data'] ?? null) ? $list['data'] : [];
        } else {
            $notificationsError = is_string($list['message'] ?? null) && trim((string) $list['message']) !== ''
                ? trim((string) $list['message'])
                : __('common.notifications.load_error');
        }

        $countRes = $notificationsGateway->unreadCount();
        if (($countRes['available'] ?? false) && (int) ($countRes['statusCode'] ?? 500) === 200) {
            $unreadCount = $countRes['unread'];
        }
    }
@endphp

<div class="relative">
    <button
        type="button"
        class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-hb-border text-hb-text hover:bg-hb-page focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40"
        data-dropdown-trigger
        aria-haspopup="true"
        aria-expanded="false"
        aria-controls="{{ $notificationsPanelId }}"
        id="{{ $notificationsPanelId }}-trigger"
        title="{{ __('common.notifications.label') }}"
    >
        <span class="sr-only">{{ __('common.notifications.label') }}</span>
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9" />
        </svg>
        @if ($notificationsAvailable && is_int($unreadCount) && $unreadCount > 0)
            <span class="absolute end-1 top-1 inline-flex min-w-[1.1rem] items-center justify-center rounded-full bg-hb-error px-1 text-[10px] font-bold leading-4 text-white" aria-hidden="true">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
            <span class="sr-only">{{ __('common.notifications.unread', ['count' => $unreadCount]) }}</span>
        @endif
    </button>

    <div
        id="{{ $notificationsPanelId }}"
        class="absolute end-0 z-50 mt-2 hidden w-[min(100vw-2rem,22rem)] overflow-hidden rounded-hb border border-hb-border bg-hb-surface shadow-hb-md"
        role="menu"
        aria-labelledby="{{ $notificationsPanelId }}-trigger"
        data-dropdown-panel
        data-notifications-panel
    >
        <div class="flex items-center justify-between border-b border-hb-border px-4 py-3">
            <p class="text-sm font-semibold text-hb-navy">{{ __('common.notifications.label') }}</p>
            @if ($notificationsAvailable && is_int($unreadCount))
                <span class="text-xs text-hb-muted">{{ __('common.notifications.unread', ['count' => $unreadCount]) }}</span>
            @endif
        </div>

        @if (! $notificationsAvailable)
            <div class="px-4 py-6 text-center" role="status" data-notifications-unavailable>
                <p class="text-sm font-semibold text-hb-navy">{{ __('common.notifications.unavailable_title') }}</p>
                <p class="mt-2 text-xs text-hb-muted">{{ __('common.notifications.unavailable_body') }}</p>
            </div>
        @elseif (filled($notificationsError))
            <div class="px-4 py-6" role="alert">
                <p class="text-sm font-semibold text-hb-navy">{{ __('common.notifications.load_error_title') }}</p>
                <p class="mt-2 text-xs text-hb-muted">{{ $notificationsError }}</p>
            </div>
        @elseif (empty($notificationItems))
            <div class="px-4 py-6 text-center" role="status">
                <p class="text-sm font-semibold text-hb-navy">{{ __('common.notifications.caught_up_title') }}</p>
                <p class="mt-2 text-xs text-hb-muted">{{ __('common.notifications.caught_up_body') }}</p>
            </div>
        @else
            <ul class="max-h-80 divide-y divide-hb-border overflow-y-auto" role="none">
                @foreach ($notificationItems as $item)
                    @php
                        $title = data_get($item, 'title');
                        $body = data_get($item, 'body', data_get($item, 'message'));
                        $created = data_get($item, 'created_at', data_get($item, 'timestamp'));
                        $readAt = data_get($item, 'read_at');
                        $isUnread = blank($readAt);
                        $href = data_get($item, 'action_url', data_get($item, 'url'));
                        $safeHref = is_string($href) && str_starts_with($href, '/') ? $href : null;
                    @endphp
                    <li role="none">
                        @if ($safeHref)
                            <a href="{{ $safeHref }}" class="block px-4 py-3 hover:bg-hb-page focus-visible:bg-hb-page focus-visible:outline-none" role="menuitem">
                        @else
                            <div class="block px-4 py-3" role="menuitem">
                        @endif
                            <div class="flex items-start gap-2">
                                @if ($isUnread)
                                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-hb-blue" aria-hidden="true"></span>
                                @else
                                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-transparent" aria-hidden="true"></span>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-hb-navy {{ $isUnread ? '' : 'text-hb-muted' }}">
                                        {{ filled($title) ? $title : __('common.notifications.untitled') }}
                                    </p>
                                    @if (filled($body))
                                        <p class="mt-0.5 line-clamp-2 text-xs text-hb-muted">{{ $body }}</p>
                                    @endif
                                    @if (filled($created))
                                        <p class="mt-1 text-[11px] text-hb-muted">{{ \App\Support\Rows::date($created) }}</p>
                                    @endif
                                </div>
                            </div>
                        @if ($safeHref)
                            </a>
                        @else
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
