<?php

namespace App\Frontend\Auth;

use App\Models\AccountAPI;
use App\Models\AdminAPI;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    public function __construct(
        private readonly AccountAPI $api,
        private readonly AdminAPI $admins,
    ) {}

    public function register(Request $request): View|RedirectResponse
    {
        if ($request->isMethod('post')) {

            $request->validate([
                'account_type' => 'required|in:candidate,company',
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'password' => 'required|min:8|confirmed',
                'company_name' => 'required_if:account_type,company|nullable|string|max:255',
                'industry' => 'nullable|string|max:100',
                'website' => 'nullable|url|max:2048',
            ]);

            $data = $request->only(['account_type', 'name', 'email', 'password', 'password_confirmation']);

            if ($data['account_type'] === 'company') {
                $data += $request->only(['company_name', 'industry', 'website']);
            }

            // optional fields that were left empty are not sent
            $data = array_filter($data, fn ($value) => $value !== null && $value !== '');

            $response = $this->api->register($data);

            if ($response['statusCode'] === 200) {
                $user = (array) ($response['data']['user'] ?? []);
                // register answers {user, company, token}: the company is not inside user, so the session keeps it
                if (! empty($response['data']['company'])) {
                    $user['company'] = array_intersect_key((array) $response['data']['company'], array_flip(['id', 'name', 'status']));
                }
                ApiSession::store((string) ($response['data']['token'] ?? ''), $user, ApiSession::REALM_USER);

                if (($user['account_type'] ?? 'candidate') === 'company') {
                    return redirect()->route('frontend.company.dashboard')
                        ->with('success_key', 'auth.flash.register_company_pending');
                }

                return redirect()->route('frontend.candidate.dashboard')
                    ->with('success_key', 'auth.flash.register_candidate');
            }

            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
        }

        return view('frontend.account.auth.register', get_defined_vars());
    }

    public function login(Request $request): View|RedirectResponse
    {
        if ($request->isMethod('post')) {

            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $data = $request->only(['email', 'password']);

            // Candidate/Company (and legacy User) only — do not fall back to /admin/login.
            $response = $this->api->login($data);
            unset($data);

            if ($response['statusCode'] === 200) {
                $user = (array) ($response['data']['user'] ?? []);
                $type = $user['account_type'] ?? 'candidate';

                // Legacy User account_type=admin cannot use platform Admin APIs (EnsureSuperAdmin).
                // Do not persist that token into a Frontend Admin session — send them to the Admin portal.
                if ($type === 'admin') {
                    return redirect()->route('frontend.account.admin-login')
                        ->withErrors(['error' => __('auth.admin_login.platform_required')])
                        ->withInput($request->only('email'));
                }

                ApiSession::store((string) ($response['data']['token'] ?? ''), $user, ApiSession::REALM_USER);

                if ($type === 'company') {
                    $pending = data_get($user, 'company.status') === 'pending';

                    return redirect()->route('frontend.company.dashboard')
                        ->with('success_key', $pending ? 'auth.flash.login_company_pending' : 'auth.flash.login_success');
                }

                return redirect()->route('frontend.candidate.dashboard')
                    ->with('success_key', 'auth.flash.login_success');
            }

            return redirect()->back()
                ->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])
                ->withInput($request->only('email'));
        }

        return view('frontend.account.auth.login', get_defined_vars());
    }

    /**
     * Platform Super Admin portal login → POST /api/admin/login (Admin model).
     * Does not use POST /api/auth/login and never mixes realms.
     */
    public function adminLogin(Request $request): View|RedirectResponse
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $data = $request->only(['email', 'password']);
            $response = $this->admins->login($data);
            unset($data);

            $status = (int) ($response['statusCode'] ?? 500);

            if ($status === 200) {
                $payload = is_array($response['data'] ?? null) ? $response['data'] : [];
                $token = (string) ($payload['token'] ?? $payload['access_token'] ?? '');
                $admin = is_array($payload['admin'] ?? null) ? $payload['admin'] : [];

                if ($token === '' || blank($admin['email'] ?? null)) {
                    return redirect()->back()
                        ->withErrors(['error' => __('auth.admin_login.invalid_response')])
                        ->withInput($request->only('email'));
                }

                ApiSession::store($token, [
                    'id' => $admin['id'] ?? $admin['email'],
                    'name' => $admin['name'] ?? '',
                    'email' => $admin['email'],
                    'account_type' => 'admin',
                    'identity' => ApiSession::REALM_PLATFORM_ADMIN,
                ], ApiSession::REALM_PLATFORM_ADMIN);

                // Hydrate roles/permissions from Backend identity endpoint (not client claims).
                $me = $this->admins->me();
                if ((int) ($me['statusCode'] ?? 500) === 200) {
                    $hydrated = $this->normalizePlatformAdminIdentity($me['data'] ?? null);
                    if ($hydrated !== null) {
                        ApiSession::refreshUser($hydrated);
                    }
                }

                return redirect()->route('frontend.dashboard')
                    ->with('success_key', 'auth.flash.login_success');
            }

            if ($status === 403) {
                return redirect()->back()
                    ->withErrors(['error' => $this->safeAdminLoginMessage($response['message'] ?? null, __('auth.admin_login.inactive'))])
                    ->withInput($request->only('email'));
            }

            if ($status === 422) {
                return redirect()->back()
                    ->withErrors(($response['errors'] ?? []) ?: ['error' => $this->safeAdminLoginMessage($response['message'] ?? null, __('auth.admin_login.failed'))])
                    ->withInput($request->only('email'));
            }

            if ($status === 401) {
                return redirect()->back()
                    ->withErrors(['error' => $this->safeAdminLoginMessage($response['message'] ?? null, __('auth.admin_login.failed'))])
                    ->withInput($request->only('email'));
            }

            // Never surface SQLSTATE / stack traces from Backend 5xx (APP_DEBUG).
            return redirect()->back()
                ->withErrors(['error' => __('auth.admin_login.unavailable')])
                ->withInput($request->only('email'));
        }

        return view('frontend.account.auth.admin-login', get_defined_vars());
    }

    public function logout(Request $request): RedirectResponse
    {
        $wasPlatformAdmin = ApiSession::isPlatformAdmin();

        // Revoke the correct Backend token; ignore transport failures and always clear local session.
        if ($wasPlatformAdmin) {
            $this->admins->logout();
        } else {
            $this->api->logout();
        }

        ApiSession::forget();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route(
            $wasPlatformAdmin ? 'frontend.account.admin-login' : 'frontend.account.login'
        );
    }

    /**
     * Drop Backend debug payloads (SQLSTATE, stack traces) from Admin login flash errors.
     */
    private function safeAdminLoginMessage(?string $message, string $fallback): string
    {
        $message = trim((string) $message);
        if ($message === '') {
            return $fallback;
        }

        if (preg_match('/SQLSTATE|Base table or view not found|Stack trace|Illuminate\\\\|Connection: mysql/i', $message) === 1) {
            return __('auth.admin_login.unavailable');
        }

        // Keep short API messages; reject oversized debug dumps.
        if (mb_strlen($message) > 240) {
            return $fallback;
        }

        return $message;
    }

    /** @param  mixed  $data */
    private function normalizePlatformAdminIdentity(mixed $data): ?array
    {
        if (! is_array($data)) {
            return null;
        }

        if (isset($data['data']) && is_array($data['data']) && ! isset($data['email'])) {
            $data = $data['data'];
        }

        if (! isset($data['email'])) {
            return null;
        }

        $roles = $data['roles'] ?? [];
        if (! is_array($roles)) {
            $roles = [];
        }
        $permissions = $data['permissions'] ?? [];
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
}
