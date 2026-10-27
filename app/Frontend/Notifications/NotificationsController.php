<?php

namespace App\Frontend\Notifications;

use App\Frontend\Auth\ApiSession;
use App\Support\Frontend\Notifications\NotificationsGateway;
use App\Support\Frontend\SafeInternalRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Candidate-only Frontend proxy for notification read mutations.
 * Uses Backend PATCH contracts; tests must mock the gateway.
 */
class NotificationsController extends Controller
{
    public function __construct(
        private readonly NotificationsGateway $gateway,
    ) {}

    public function markRead(Request $request, int $id): RedirectResponse
    {
        $back = SafeInternalRedirect::fromRequest($request, route('frontend.candidate.dashboard', [], false));

        if (ApiSession::accountType() !== 'candidate' || ! $this->gateway->isAvailable()) {
            return redirect($back)->withErrors([
                'error' => __('common.notifications.forbidden'),
            ]);
        }

        $result = $this->gateway->markAsRead($id);
        $status = (int) ($result['statusCode'] ?? 500);

        if ($status === 401) {
            ApiSession::forget();

            return redirect()->route('frontend.account.login')
                ->withErrors(['error' => __('common.notifications.session_expired')]);
        }

        if ($status === 200) {
            return redirect($back)->with('success_key', 'common.notifications.marked_read');
        }

        if ($status === 403) {
            return redirect($back)->withErrors(['error' => __('common.notifications.forbidden')]);
        }

        if ($status === 404) {
            return redirect($back)->withErrors(['error' => __('common.notifications.not_found')]);
        }

        $message = is_string($result['message'] ?? null) && trim((string) $result['message']) !== ''
            ? trim((string) $result['message'])
            : __('common.notifications.mark_read_error');

        return redirect($back)->withErrors(['error' => $message]);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $back = SafeInternalRedirect::fromRequest($request, route('frontend.candidate.dashboard', [], false));

        if (ApiSession::accountType() !== 'candidate' || ! $this->gateway->isAvailable()) {
            return redirect($back)->withErrors([
                'error' => __('common.notifications.forbidden'),
            ]);
        }

        $result = $this->gateway->markAllAsRead();
        $status = (int) ($result['statusCode'] ?? 500);

        if ($status === 401) {
            ApiSession::forget();

            return redirect()->route('frontend.account.login')
                ->withErrors(['error' => __('common.notifications.session_expired')]);
        }

        if ($status === 200) {
            return redirect($back)->with('success_key', 'common.notifications.marked_all_read');
        }

        $message = is_string($result['message'] ?? null) && trim((string) $result['message']) !== ''
            ? trim((string) $result['message'])
            : __('common.notifications.mark_read_error');

        return redirect($back)->withErrors(['error' => $message]);
    }
}
