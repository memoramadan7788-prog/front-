<?php

namespace App\Http\Middleware;

use App\Frontend\Auth\ApiSession;
use App\Models\AccountAPI;
use Closure;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Revalidates Super Admin page access against Backend GET /auth/me.
 *
 * Must run after `auth` and `account:admin` (session gate). Session account_type alone
 * is not trusted for continued Admin access — Backend identity wins.
 *
 * 401 → clear session, redirect to login (expired / revoked authentication).
 * 403 → keep session, redirect to workspace (insufficient permission / forbidden).
 * 200 with non-admin account_type → refresh session from Backend, redirect to workspace.
 */
class EnsureAdminAccess
{
    public function __construct(
        private readonly AccountAPI $accounts
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! ApiSession::check()) {
            return redirect()->route('frontend.account.login');
        }

        $response = $this->accounts->getProfile();
        $status = (int) ($response['statusCode'] ?? $response['httpStatus'] ?? 500);

        if ($status === 401) {
            // Expired/revoked token. CurlClient may already have forgotten the session;
            // clear again so behavior is consistent when AccountAPI is mocked in tests.
            ApiSession::forget();

            return redirect()->route('frontend.account.login')
                ->withErrors(['error' => __('Your session has expired. Please sign in again.')]);
        }

        if ($status === 403) {
            // Insufficient permission / forbidden — do not erase the session.
            return redirect()->route('frontend.workspace')
                ->withErrors(['error' => __('You are not authorized to access the admin workspace.')]);
        }

        if ($status !== 200) {
            return redirect()->route('frontend.workspace')
                ->withErrors(['error' => __('Could not verify admin access. Please try again.')]);
        }

        $backendUser = $this->extractUser($response['data'] ?? null);
        if ($backendUser === null) {
            return redirect()->route('frontend.workspace')
                ->withErrors(['error' => __('Could not verify admin access. Please try again.')]);
        }

        ApiSession::refreshUser($backendUser);
        $this->rebindAuthUser($request);

        if (ApiSession::accountType() !== 'admin') {
            return redirect()->route('frontend.workspace')
                ->withErrors(['error' => __('You are not authorized to access the admin workspace.')]);
        }

        return $next($request);
    }

    /** @param  mixed  $data */
    private function extractUser(mixed $data): ?array
    {
        if (! is_array($data)) {
            return null;
        }

        // Some envelopes nest the user again under data/user.
        if (isset($data['account_type']) || isset($data['email'])) {
            return $data;
        }

        if (isset($data['user']) && is_array($data['user'])) {
            return $data['user'];
        }

        if (isset($data['data']) && is_array($data['data'])) {
            return $this->extractUser($data['data']);
        }

        return null;
    }

    private function rebindAuthUser(Request $request): void
    {
        $user = ApiSession::user();
        if ($user === null) {
            return;
        }

        $generic = new GenericUser($user);
        Auth::setUser($generic);
        $request->setUserResolver(static fn () => $generic);
    }
}
