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
                'terms_accepted' => 'required|accepted',
                'marketing_opt_in' => 'sometimes|boolean',
                'company_name' => 'required_if:account_type,company|nullable|string|max:255',
                'industry' => 'nullable|string|max:100',
                'website' => 'nullable|url|max:2048',
            ], [
                'terms_accepted.required' => __('auth.register.terms_required'),
                'terms_accepted.accepted' => __('auth.register.terms_required'),
            ]);

            $data = $request->only([
                'account_type',
                'name',
                'email',
                'password',
                'password_confirmation',
                'terms_accepted',
            ]);

            $data['terms_accepted'] = true;
            $data['marketing_opt_in'] = $request->boolean('marketing_opt_in');

            if ($data['account_type'] === 'company') {
                $data += $request->only(['company_name', 'industry', 'website']);
            }

            $data = array_filter(
                $data,
                fn ($value) => $value !== null && $value !== ''
            );

            $response = $this->api->register($data);

            $status = (int) ($response['statusCode'] ?? 0);

            $ok = $status >= 200
                && $status < 300
                && ! empty(
                    $response['data']['token']
                    ?? $response['data']['access_token']
                    ?? null
                );

            if ($ok) {
                if (data_get($response, 'data.verification_required') === true) {
                    ApiSession::forget();

                    $request->session()->put(
                        'pending_verification_email',
                        $request->input('email')
                    );

                    return redirect()->route('frontend.account.verification');
                }

                $request->session()->forget('pending_verification_email');

                $user = (array) ($response['data']['user'] ?? []);

                if (! empty($response['data']['company'])) {
                    $user['company'] = array_intersect_key(
                        (array) $response['data']['company'],
                        array_flip(['id', 'name', 'status'])
                    );
                }

                ApiSession::store(
                    (string) (
                        $response['data']['token']
                        ?? $response['data']['access_token']
                        ?? ''
                    ),
                    $user,
                    ApiSession::REALM_USER
                );

                if (($user['account_type'] ?? 'candidate') === 'company') {
                    return redirect()->route('frontend.company.dashboard')
                        ->with('success_key', 'auth.flash.register_company_pending');
                }

                return redirect()->route('frontend.candidate.dashboard')
                    ->with('success_key', 'auth.flash.register_candidate');
            }

            return redirect()->back()
                ->withErrors($this->safeRegisterErrors($response))
                ->withInput($request->except([
                    'password',
                    'password_confirmation',
                ]));
        }

        return view('frontend.account.auth.register', get_defined_vars());
    }

    public function verification(Request $request): View|RedirectResponse
    {
        $email = $request->session()->get('pending_verification_email');

        if (! is_string($email) || $email === '') {
            return redirect()->route('frontend.account.register');
        }

        return view('frontend.account.auth.verification', compact('email'));
    }

    /**
     * Forgot Password
     *
     * GET  /account/forgot-password
     * POST /account/forgot-password
     */
    public function forgotPassword(Request $request): View|RedirectResponse
    {
        if ($request->isMethod('post')) {

            $request->validate([
                'email' => 'required|email|max:255',
            ]);

            $response = $this->api->forgotPassword([
                'email' => $request->input('email'),
            ]);

            $status = (int) ($response['statusCode'] ?? 500);

            if ($status === 200) {
                return redirect()->back()
                    ->with('success_key', 'auth.flash.password_reset_link_sent');
            }

            $message = $status === 429
                ? 'Please wait before requesting another reset link.'
                : 'Unable to send the password reset link. Please try again later.';

            return redirect()->back()
                ->withErrors([
                    'email' => SafeApiMessage::forUser(
                        $response['message'] ?? null,
                        $message,
                        [
                            'action' => 'auth.forgot_password',
                            'status' => $status,
                        ]
                    ),
                ])
                ->withInput($request->only('email'));
        }

        return view('frontend.account.auth.forgot-password', get_defined_vars());
    }

    /**
     * Reset Password
     *
     * GET  /account/reset-password?token=...&email=...
     * POST /account/reset-password
     */
    public function resetPassword(Request $request): View|RedirectResponse
    {
        if ($request->isMethod('post')) {

            $request->validate([
                'email' => 'required|email|max:255',
                'token' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $response = $this->api->resetPassword([
                'email' => $request->input('email'),
                'token' => $request->input('token'),
                'password' => $request->input('password'),
                'password_confirmation' => $request->input('password_confirmation'),
            ]);

            $status = (int) ($response['statusCode'] ?? 500);

            if ($status === 200) {
                return redirect()->route('frontend.account.login')
                    ->with('success_key', 'auth.flash.password_reset_success');
            }

            return redirect()->back()
                ->withErrors([
                    'error' => SafeApiMessage::forUser(
                        $response['message'] ?? null,
                        'The password reset link is invalid or expired. Please request a new one.',
                        [
                            'action' => 'auth.reset_password',
                            'status' => $status,
                        ]
                    ),
                ])
                ->withInput($request->only('email', 'token'));
        }

        $email = $request->query('email');
        $token = $request->query('token');

        return view(
            'frontend.account.auth.reset-password',
            compact('email', 'token')
        );
    }

    public function login(Request $request): View|RedirectResponse
    {
        if ($request->isMethod('post')) {

            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
                'redirect' => 'nullable|string|max:2048',
            ]);

            $data = $request->only(['email', 'password']);

            $response = $this->api->login($data);
            unset($data);

            $status = (int) ($response['statusCode'] ?? 0);

            if ($status === 200) {
                $user = (array) ($response['data']['user'] ?? []);
                $type = $user['account_type'] ?? 'candidate';

                if ($type === 'admin') {
                    return redirect()->route('frontend.account.admin-login')
                        ->withErrors([
                            'error' => __('auth.admin_login.platform_required'),
                        ])
                        ->withInput($request->only('email'));
                }

                ApiSession::store(
                    (string) ($response['data']['token'] ?? ''),
                    $user,
                    ApiSession::REALM_USER
                );

                $defaultPath = $type === 'company'
                    ? route('frontend.company.dashboard', [], false)
                    : route('frontend.candidate.dashboard', [], false);

                $successKey = (
                    $type === 'company'
                    && data_get($user, 'company.status') === 'pending'
                )
                    ? 'auth.flash.login_company_pending'
                    : 'auth.flash.login_success';

                return redirect()->to(
                    $this->safePostLoginPath($request, $defaultPath)
                )->with('success_key', $successKey);
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

        return preg_match(
            '#^/account/(login|register|admin-login)(/|$)#',
            $onlyPath
        ) === 1;
    }

    /**
     * Public login errors must never include SQLSTATE, table names, hosts, or stack traces.
     *
     * @param array<string, mixed> $response
     * @return array<string, string>
     */
    private function safeLoginErrors(array $response): array
    {
        $fallback = __('auth.login.unavailable');

        $rawMessage = is_string($response['message'] ?? null)
            ? $response['message']
            : null;

        $errors = is_array($response['errors'] ?? null)
            ? $response['errors']
            : [];

        $safe = [];

        foreach ($errors as $key => $value) {
            if (is_array($value)) {
                $value = reset($value);
            }

            if (! is_string($value) || trim($value) === '') {
                continue;
            }

            $field = is_string($key) ? $key : 'error';

            $safe[$field] = SafeApiMessage::forUser(
                $value,
                $fallback,
                [
                    'action' => 'auth.login',
                    'field' => $field,
                    'status' => $response['statusCode'] ?? null,
                ]
            );
        }

        if ($safe === []) {
            $safe['error'] = SafeApiMessage::forUser(
                $rawMessage,
                $fallback,
                [
                    'action' => 'auth.login',
                    'status' => $response['statusCode'] ?? null,
                ]
            );
        }

        return $safe;
    }

    /**
     * Platform Super Admin portal login.
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
                $payload = is_array($response['data'] ?? null)
                    ? $response['data']
                    : [];

                $token = (string) (
                    $payload['token']
                    ?? $payload['access_token']
                    ?? ''
                );

                $admin = is_array($payload['admin'] ?? null)
                    ? $payload['admin']
                    : [];

                if ($token === '' || blank($admin['email'] ?? null)) {
                    return redirect()->back()
                        ->withErrors([
                            'error' => __('auth.admin_login.invalid_response'),
                        ])
                        ->withInput($request->only('email'));
                }

                ApiSession::store($token, [
                    'id' => $admin['id'] ?? $admin['email'],
                    'name' => $admin['name'] ?? '',
                    'email' => $admin['email'],
                    'account_type' => 'admin',
                    'identity' => ApiSession::REALM_PLATFORM_ADMIN,
                ], ApiSession::REALM_PLATFORM_ADMIN);

                $me = $this->admins->me();

                if ((int) ($me['statusCode'] ?? 500) === 200) {
                    $hydrated = $this->normalizePlatformAdminIdentity(
                        $me['data'] ?? null
                    );

                    if ($hydrated !== null) {
                        ApiSession::refreshUser($hydrated);
                    }
                }

                return redirect()->route('frontend.dashboard')
                    ->with('success_key', 'auth.flash.login_success');
            }

            if ($status === 403) {
                return redirect()->back()
                    ->withErrors([
                        'error' => $this->safeAdminLoginMessage(
                            $response['message'] ?? null,
                            __('auth.admin_login.inactive')
                        ),
                    ])
                    ->withInput($request->only('email'));
            }

            if ($status === 422) {
                return redirect()->back()
                    ->withErrors(
                        ($response['errors'] ?? [])
                        ?: [
                            'error' => $this->safeAdminLoginMessage(
                                $response['message'] ?? null,
                                __('auth.admin_login.failed')
                            ),
                        ]
                    )
                    ->withInput($request->only('email'));
            }

            if ($status === 401) {
                return redirect()->back()
                    ->withErrors([
                        'error' => $this->safeAdminLoginMessage(
                            $response['message'] ?? null,
                            __('auth.admin_login.failed')
                        ),
                    ])
                    ->withInput($request->only('email'));
            }

            return redirect()->back()
                ->withErrors([
                    'error' => __('auth.admin_login.unavailable'),
                ])
                ->withInput($request->only('email'));
        }

        return view('frontend.account.auth.admin-login', get_defined_vars());
    }

    public function logout(Request $request): RedirectResponse
    {
        $wasPlatformAdmin = ApiSession::isPlatformAdmin();

        if ($wasPlatformAdmin) {
            $this->admins->logout();
        } else {
            $this->api->logout();
        }

        ApiSession::forget();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route(
            $wasPlatformAdmin
                ? 'frontend.account.admin-login'
                : 'frontend.account.login'
        );
    }

    /**
     * Drop Backend debug payloads from Admin login flash errors.
     */
    private function safeAdminLoginMessage(
        ?string $message,
        string $fallback
    ): string {
        return SafeApiMessage::forUser(
            $message,
            $fallback,
            ['action' => 'auth.admin_login']
        );
    }

    /**
     * Public registration errors must never include SQLSTATE, table names, hosts, or stack traces.
     *
     * @param array<string, mixed> $response
     * @return array<string, string>
     */
    private function safeRegisterErrors(array $response): array
    {
        $fallback = __('auth.register.unavailable');

        $rawMessage = is_string($response['message'] ?? null)
            ? $response['message']
            : null;

        $errors = is_array($response['errors'] ?? null)
            ? $response['errors']
            : [];

        $safe = [];

        foreach ($errors as $key => $value) {
            if (is_array($value)) {
                $value = reset($value);
            }

            if (! is_string($value) || trim($value) === '') {
                continue;
            }

            $field = is_string($key) ? $key : 'error';

            $safe[$field] = SafeApiMessage::forUser(
                $value,
                $fallback,
                [
                    'action' => 'auth.register',
                    'field' => $field,
                    'status' => $response['statusCode'] ?? null,
                ]
            );
        }

        if ($safe === []) {
            $safe['error'] = SafeApiMessage::forUser(
                $rawMessage,
                $fallback,
                [
                    'action' => 'auth.register',
                    'status' => $response['statusCode'] ?? null,
                ]
            );
        }

        if (
            SafeApiMessage::looksSensitive((string) ($rawMessage ?? ''))
            || SafeApiMessage::looksSensitive(implode(' ', $safe))
        ) {
            Log::warning(
                'Registration Backend error sanitized for user flash.',
                [
                    'status' => $response['statusCode'] ?? null,
                    'snippet' => mb_substr(
                        (string) ($rawMessage ?? reset($safe) ?: ''),
                        0,
                        180
                    ),
                ]
            );
        }

        return $safe;
    }

    private function normalizePlatformAdminIdentity(mixed $data): ?array
    {
        if (! is_array($data)) {
            return null;
        }

        if (
            isset($data['data'])
            && is_array($data['data'])
            && ! isset($data['email'])
        ) {
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
