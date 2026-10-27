@php
    use App\Support\Frontend\Notifications\NotificationDestination;
    use App\Support\Frontend\Notifications\NotificationsGateway;
    use App\Support\Frontend\TopbarAccount;

    $account = TopbarAccount::fromSession();
    $isCandidate = ($account['account_type'] ?? null) === 'candidate';

    $notificationsGateway = app(NotificationsGateway::class);
    $notificationsAvailable = $isCandidate && $notificationsGateway->isAvailable();
    $notificationItems = [];
    $notificationsError = null;
    $unreadCount = null;
    $notificationsMeta = null;
    $notificationsPanelId = 'notifications-menu-panel';
    $hasUnread = false;

    if ($notificationsAvailable) {
        $list = $notificationsGateway->list(1, 15);
        $status = (int) ($list['statusCode'] ?? 500);

        if ($status === 401) {
            $notificationsError = __('common.notifications.session_expired');
        } elseif ($status === 403) {
            $notificationsError = __('common.notifications.forbidden');
        } elseif ($status === 200) {
            $notificationItems = is_array($list['data'] ?? null) ? $list['data'] : [];
            $notificationsMeta = is_array($list['meta'] ?? null) ? $list['meta'] : null;
            $metaUnread = data_get($notificationsMeta, 'unread_count');
            $unreadCount = is_numeric($metaUnread) ? max(0, (int) $metaUnread) : null;
        } else {
            $notificationsError = is_string($list['message'] ?? null) && trim((string) $list['message']) !== ''
                ? trim((string) $list['message'])
                : __('common.notifications.load_error');
        }

        foreach ($notificationItems as $row) {
            if (is_array($row) && blank(data_get($row, 'read_at'))) {
                $hasUnread = true;
                break;
            }
        }
    }
@endphp

<div class="relative" data-notifications-root data-notifications-role="{{ $account['account_type'] ?? 'guest' }}">
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
            <span
                class="absolute end-1 top-1 inline-flex min-w-[1.1rem] items-center justify-center rounded-full bg-hb-error px-1 text-[10px] font-bold leading-4 text-white"
                aria-hidden="true"
                data-notifications-unread-badge
            >
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
        data-notifications-available="{{ $notificationsAvailable ? '1' : '0' }}"
    >
        <div class="flex items-center justify-between gap-2 border-b border-hb-border px-4 py-3">
            <p class="text-sm font-semibold text-hb-navy">{{ __('common.notifications.label') }}</p>
            @if ($notificationsAvailable && is_int($unreadCount))
                <span class="text-xs text-hb-muted" data-notifications-unread-label>{{ __('common.notifications.unread', ['count' => $unreadCount]) }}</span>
            @endif
        </div>

        @if (! $notificationsAvailable)
            <div class="px-4 py-6 text-center" role="status" data-notifications-unavailable>
                <p class="text-sm font-semibold text-hb-navy">{{ __('common.notifications.unavailable_title') }}</p>
                <p class="mt-2 text-xs text-hb-muted">{{ __('common.notifications.unavailable_body') }}</p>
            </div>
        @elseif (filled($notificationsError))
            <div class="px-4 py-6" role="alert" data-notifications-error>
                <p class="text-sm font-semibold text-hb-navy">{{ __('common.notifications.load_error_title') }}</p>
                <p class="mt-2 text-xs text-hb-muted">{{ $notificationsError }}</p>
            </div>
        @elseif (empty($notificationItems))
            <div class="px-4 py-6 text-center" role="status" data-notifications-empty>
                <p class="text-sm font-semibold text-hb-navy">{{ __('common.notifications.caught_up_title') }}</p>
                <p class="mt-2 text-xs text-hb-muted">{{ __('common.notifications.caught_up_body') }}</p>
            </div>
        @else
            @if ($hasUnread)
                <div class="border-b border-hb-border px-4 py-2">
                    <form method="POST" action="{{ route('frontend.candidate.notifications.read-all') }}" class="flex justify-end">
                        @csrf
                        <input type="hidden" name="redirect" value="{{ request()->getRequestUri() }}">
                        <button type="submit" class="text-xs font-semibold text-hb-blue hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40" data-notifications-mark-all>
                            {{ __('common.notifications.mark_all_read') }}
                        </button>
                    </form>
                </div>
            @endif
            <ul class="max-h-80 divide-y divide-hb-border overflow-y-auto" role="none" data-notifications-list>
                @foreach ($notificationItems as $item)
                    @php
                        $id = (int) data_get($item, 'id', 0);
                        $title = data_get($item, 'title');
                        $body = data_get($item, 'body', data_get($item, 'message'));
                        $created = data_get($item, 'created_at', data_get($item, 'timestamp'));
                        $readAt = data_get($item, 'read_at');
                        $isUnread = blank($readAt);
                        $safeHref = NotificationDestination::href(is_array($item) ? $item : []);
                    @endphp
                    <li role="none" data-notification-id="{{ $id }}" data-notification-unread="{{ $isUnread ? '1' : '0' }}">
                        <div class="px-4 py-3 {{ $isUnread ? 'bg-hb-info-soft/40' : '' }}">
                            <div class="flex items-start gap-2">
                                @if ($isUnread)
                                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-hb-blue" aria-hidden="true"></span>
                                @else
                                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-transparent" aria-hidden="true"></span>
                                @endif
                                <div class="min-w-0 flex-1">
                                    @if ($safeHref)
                                        <a href="{{ $safeHref }}" class="text-sm font-medium text-hb-navy hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40" role="menuitem">
                                            {{ filled($title) ? $title : __('common.notifications.untitled') }}
                                        </a>
                                    @else
                                        <p class="text-sm font-medium {{ $isUnread ? 'text-hb-navy' : 'text-hb-muted' }}" role="menuitem">
                                            {{ filled($title) ? $title : __('common.notifications.untitled') }}
                                        </p>
                                    @endif
                                    @if (filled($body))
                                        <p class="mt-0.5 line-clamp-2 text-xs text-hb-muted">{{ $body }}</p>
                                    @endif
                                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1">
                                        @if (filled($created))
                                            <p class="text-[11px] text-hb-muted">{{ \App\Support\Rows::date($created) }}</p>
                                        @endif
                                        @if ($isUnread && $id > 0)
                                            <form method="POST" action="{{ route('frontend.candidate.notifications.read', ['id' => $id]) }}">
                                                @csrf
                                                <input type="hidden" name="redirect" value="{{ request()->getRequestUri() }}">
                                                <button type="submit" class="text-[11px] font-semibold text-hb-blue hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40" data-notification-mark-read>
                                                    {{ __('common.notifications.mark_read') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
            @if (is_array($notificationsMeta) && (int) data_get($notificationsMeta, 'last_page', 1) > 1)
                <p class="border-t border-hb-border px-4 py-2 text-[11px] text-hb-muted" data-notifications-pagination>
                    {{ __('common.notifications.page_of', [
                        'page' => (int) data_get($notificationsMeta, 'current_page', 1),
                        'pages' => (int) data_get($notificationsMeta, 'last_page', 1),
                        'total' => (int) data_get($notificationsMeta, 'total', count($notificationItems)),
                    ]) }}
                </p>
            @endif
        @endif
    </div>
</div>
