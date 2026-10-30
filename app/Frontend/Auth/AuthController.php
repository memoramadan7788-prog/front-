<?php

namespace App\Frontend\Auth;

use App\Models\AccountAPI;
use App\Models\AdminAPI;
use App\Support\Frontend\SafeApiMessage;
use App\Support\Frontend\SafeInternalRedirect;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

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

            // Backend may answer 201 Created; treat any 2xx as success only when a token is present.
            $status = (int) ($response['statusCode'] ?? 0);
            $ok = $status >= 200 && $status < 300 && ! empty($response['data']['token'] ?? $response['data']['access_token'] ?? null);

            if ($ok) {
                $user = (array) ($response['data']['user'] ?? []);
                // register answers {user, company, token}: the company is not inside user, so the session keeps it
                if (! empty($response['data']['company'])) {
                    $user['company'] = array_intersect_key((array) $response['data']['company'], array_flip(['id', 'name', 'status']));
                }
                ApiSession::store((string) ($response['data']['token'] ?? $response['data']['access_token'] ?? ''), $user, ApiSession::REALM_USER);

                if (($user['account_type'] ?? 'candidate') === 'company') {
                    return redirect()->route('frontend.company.dashboard')
                        ->with('success_key', 'auth.flash.register_company_pending');
                }

                return redirect()->route('frontend.candidate.dashboard')
                    ->with('success_key', 'auth.flash.register_candidate');
            }

            // Never flash SQLSTATE / schema dumps from Backend APP_DEBUG; do not claim success.
            return redirect()->back()
                ->withErrors($this->safeRegisterErrors($response))
                ->withInput();
        }

        return view('frontend.account.auth.register', get_defined_vars());
    }

    public function login(Request $request): View|RedirectResponse
    {
        if ($request->isMethod('post')) {

            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
                // redirect is optional and sanitized separately; never trust raw open redirects.
                'redirect' => 'nullable|string|max:2048',
            ]);

            // Backend LoginRequest accepts email + password only (no remember / OAuth).
            $data = $request->only(['email', 'password']);

            // Candidate/Company (and legacy User) only — do not fall back to /admin/login.
            $response = $this->api->login($data);
            unset($data);

            $status = (int) ($response['statusCode'] ?? 0);

            if ($status === 200) {
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

                $defaultPath = $type === 'company'
                    ? route('frontend.company.dashboard', [], false)
                    : route('frontend.candidate.dashboard', [], false);
                $successKey = ($type === 'company' && data_get($user, 'company.status') === 'pending')
                    ? 'auth.flash.login_company_pending'
                    : 'auth.flash.login_success';

                return redirect()->to($this->safePostLoginPath($request, $defaultPath))
                    ->with('success_key', $successKey);
            }

            return redirect()->back()
                ->withErrors($this->safeLoginErrors($response))
                ->withInput($request->only('email', 'redirect'));
        }

        $safeRedirect = $this->safeLoginContinuationPath($request);

        return view('frontend.account.auth.login', get_defined_vars());
    }

    /**
     * Same-app continuation target for Welcome Back messaging / post-login return.
     * Rejects external hosts, schemes, and auth pages themselves.
     */
    private function safeLoginContinuationPath(Request $request): ?string
    {
        $raw = $request->query('redirect');
        if (! is_string($raw) || trim($raw) === '') {
            return null;
        }

        $path = SafeInternalRedirect::sanitize($raw, '/__unsafe__');
        if ($path === '/__unsafe__' || $this->isAuthPagePath($path)) {
            return null;
        }

        return $path;
    }

    /**
     * Prefer a sanitized return path; otherwise the role dashboard default.
     */
    private function safePostLoginPath(Request $request, string $defaultPath): string
    {
        $raw = $request->input('redirect');
        if (! is_string($raw) || trim($raw) === '') {
            return $defaultPath;
        }

        $path = SafeInternalRedirect::sanitize($raw, $defaultPath);
        if ($this->isAuthPagePath($path)) {
            return $defaultPath;
        }

        return $path;
    }

    private function isAuthPagePath(string $path): bool
    {
        $onlyPath = parse_url($path, PHP_URL_PATH);
        $onlyPath = is_string($onlyPath) ? $onlyPath : $path;

        return preg_match('#^/account/(login|register|admin-login)(/|$)#', $onlyPath) === 1;
    }

    /**
     * Public login errors must never include SQLSTATE, table names, hosts, or stack traces.
     *
     * @param  array<string, mixed>  $response
     * @return array<string, string>
     */
    private function safeLoginErrors(array $response): array
    {
        $fallback = __('auth.login.unavailable');
        $rawMessage = is_string($response['message'] ?? null) ? $response['message'] : null;
        $errors = is_array($response['errors'] ?? null) ? $response['errors'] : [];

        $safe = [];
        foreach ($errors as $key => $value) {
            if (is_array($value)) {
                $value = reset($value);
            }
            if (! is_string($value) || trim($value) === '') {
                continue;
            }
            $field = is_string($key) ? $key : 'error';
            $safe[$field] = SafeApiMessage::forUser($value, $fallback, [
                'action' => 'auth.login',
                'field' => $field,
                'status' => $response['statusCode'] ?? null,
            ]);
        }

        if ($safe === []) {
            $safe['error'] = SafeApiMessage::forUser($rawMessage, $fallback, [
                'action' => 'auth.login',
                'status' => $response['statusCode'] ?? null,
            ]);
        }

        return $safe;
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
        return SafeApiMessage::forUser($message, $fallback, ['action' => 'auth.admin_login']);
    }

    /**
     * Public registration errors must never include SQLSTATE, table names, hosts, or stack traces.
     *
     * @param  array<string, mixed>  $response
     * @return array<string, string>
     */
    private function safeRegisterErrors(array $response): array
    {
        $fallback = __('auth.register.unavailable');
        $rawMessage = is_string($response['message'] ?? null) ? $response['message'] : null;
        $errors = is_array($response['errors'] ?? null) ? $response['errors'] : [];

        $safe = [];
        foreach ($errors as $key => $value) {
            if (is_array($value)) {
                $value = reset($value);
            }
            if (! is_string($value) || trim($value) === '') {
                continue;
            }
            $field = is_string($key) ? $key : 'error';
            $safe[$field] = SafeApiMessage::forUser($value, $fallback, [
                'action' => 'auth.register',
                'field' => $field,
                'status' => $response['statusCode'] ?? null,
            ]);
        }

        if ($safe === []) {
            $safe['error'] = SafeApiMessage::forUser($rawMessage, $fallback, [
                'action' => 'auth.register',
                'status' => $response['statusCode'] ?? null,
            ]);
        }

        // If Backend left a partial account but verification schema failed, keep failure UX (no success flash).
        if (SafeApiMessage::looksSensitive((string) ($rawMessage ?? '')) || SafeApiMessage::looksSensitive(implode(' ', $safe))) {
            Log::warning('Registration Backend error sanitized for user flash.', [
                'status' => $response['statusCode'] ?? null,
                'snippet' => mb_substr((string) ($rawMessage ?? reset($safe) ?: ''), 0, 180),
            ]);
        }

        return $safe;
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
