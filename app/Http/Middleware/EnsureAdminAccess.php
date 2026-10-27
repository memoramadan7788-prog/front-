<?php

namespace App\Http\Middleware;

use App\Frontend\Auth\ApiSession;
use App\Models\AdminAPI;
use Closure;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Revalidates Super Admin page access against the platform Admin identity endpoint.
 *
 * Platform Admin realm (POST /api/admin/login) → GET /api/admin/me only.
 *
 * Legacy User account_type=admin tokens (POST /api/auth/login) are not admitted to
 * Admin workspace pages: Backend EnsureSuperAdmin rejects them on dashboard/analytics
 * with "Forbidden. Super Admin access only." — Frontend must not render that shell.
 *
 * Must run after `auth` and `account:admin` (session gate). Session account_type alone
 * is not trusted for continued Admin access — Backend identity wins.
 *
 * 401 → clear session, redirect to admin-login (expired / revoked authentication).
 * 403 → keep session, redirect to workspace (insufficient permission / forbidden).
 * 200 with non-admin identity → refresh session from Backend, redirect to workspace.
 */
class EnsureAdminAccess
{
    public function __construct(
        private readonly AdminAPI $admins,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! ApiSession::check()) {
            return redirect()->route('frontend.account.admin-login');
        }

        if (! ApiSession::isPlatformAdmin()) {
            return $this->rejectNonPlatformAdminSession();
        }

        return $this->handlePlatformAdmin($request, $next);
    }

    private function handlePlatformAdmin(Request $request, Closure $next): Response
    {
        $response = $this->admins->me();
        $status = (int) ($response['statusCode'] ?? $response['httpStatus'] ?? 500);

        if ($status === 401) {
            ApiSession::forget();

            return redirect()->route('frontend.account.admin-login')
                ->withErrors(['error' => __('Your session has expired. Please sign in again.')]);
        }

        if ($status === 403) {
            return redirect()->route('frontend.workspace')
                ->withErrors(['error' => __('You are not authorized to access the admin workspace.')]);
        }

        if ($status !== 200) {
            return redirect()->route('frontend.workspace')
                ->withErrors(['error' => __('Could not verify admin access. Please try again.')]);
        }

        $backendAdmin = $this->extractPlatformAdmin($response['data'] ?? null);
        if ($backendAdmin === null) {
            return redirect()->route('frontend.workspace')
                ->withErrors(['error' => __('Could not verify admin access. Please try again.')]);
        }

        // Backend /admin/me succeeded — never grant from session claim alone.
        ApiSession::refreshUser($backendAdmin);
        $this->rebindAuthUser($request);

        if (ApiSession::accountType() !== 'admin' || ! ApiSession::isPlatformAdmin()) {
            return redirect()->route('frontend.workspace')
                ->withErrors(['error' => __('You are not authorized to access the admin workspace.')]);
        }

        return $next($request);
    }

    /**
     * Legacy User admins (and any non-platform realm) cannot use platform Admin pages.
     * Clear the incompatible session and send them to the Admin portal login.
     */
    private function rejectNonPlatformAdminSession(): Response
    {
        ApiSession::forget();

        return redirect()->route('frontend.account.admin-login')
            ->withErrors(['error' => __('auth.admin_login.platform_required')]);
    }

    /** @param  mixed  $data */
    private function extractPlatformAdmin(mixed $data): ?array
    {
        if (! is_array($data)) {
            return null;
        }

        // AdminResource / unwrapped me payload.
        if (isset($data['email']) && (isset($data['id']) || array_key_exists('roles', $data))) {
            $roles = $data['roles'] ?? [];
            if ($roles instanceof \Illuminate\Support\Collection) {
                $roles = $roles->values()->all();
            }
            if (! is_array($roles)) {
                $roles = [];
            }

            $permissions = $data['permissions'] ?? [];
            if ($permissions instanceof \Illuminate\Support\Collection) {
                $permissions = $permissions->values()->all();
            }
            if (! is_array($permissions)) {
                $permissions = [];
            }

            return [
                'id' => $data['id'] ?? $data['email'],
                'name' => $data['name'] ?? '',
                'email' => $data['email'],
                'account_type' => 'admin',
                'identity' => ApiSession::REALM_PLATFORM_ADMIN,
                'roles' => array_values($roles),
                'permissions' => array_values($permissions),
                'account_status_id' => $data['account_status_id'] ?? null,
            ];
        }

        if (isset($data['data']) && is_array($data['data'])) {
            return $this->extractPlatformAdmin($data['data']);
        }

        if (isset($data['admin']) && is_array($data['admin'])) {
            return $this->extractPlatformAdmin($data['admin'] + ['roles' => $data['roles'] ?? []]);
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
