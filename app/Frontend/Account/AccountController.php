<?php

namespace App\Frontend\Account;

use App\Frontend\Auth\ApiSession;
use App\Models\AccountAPI;
use App\Models\AdminAPI;
use App\Models\CandidateAPI;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Shared account pages (profile settings, change password).
 *
 * Profile:
 * - User realm → GET /auth/me
 * - Platform Admin realm → GET /admin/me (never /auth/me with Admin tokens)
 * Password (Candidate): PUT /api/candidate/change-password — Company/Admin remain unavailable.
 */
class AccountController extends Controller
{
    public function __construct(
        private readonly AccountAPI $accounts,
        private readonly CandidateAPI $candidates,
        private readonly AdminAPI $admins,
    ) {}

    public function profile(Request $request): View|RedirectResponse
    {
        $loginRoute = ApiSession::isPlatformAdmin()
            ? 'frontend.account.admin-login'
            : 'frontend.account.login';

        $response = ApiSession::isPlatformAdmin()
            ? $this->admins->me()
            : $this->accounts->getProfile();
        $status = (int) ($response['statusCode'] ?? $response['httpStatus'] ?? 500);

        if ($status === 401) {
            ApiSession::forget();

            return redirect()->route($loginRoute)
                ->withErrors(['error' => __('Your session has expired. Please sign in again.')]);
        }

        if ($status === 403) {
            return redirect()->route('frontend.workspace')
                ->withErrors(['error' => __('You are not authorized to view this profile.')]);
        }

        if ($status !== 200) {
            return view('frontend.account.profile', [
                'user' => ApiSession::user() ?? [],
                'loadError' => trim((string) ($response['message'] ?? __('Could not load profile.'))),
                'passwordChangeAvailable' => $this->candidatePasswordChangeAvailable(),
                'profileEditAvailable' => false,
            ]);
        }

        $user = ApiSession::isPlatformAdmin()
            ? ($this->extractPlatformAdmin($response['data'] ?? null) ?? (ApiSession::user() ?? []))
            : ($this->extractUser($response['data'] ?? null) ?? (ApiSession::user() ?? []));

        if ($user !== []) {
            ApiSession::refreshUser($user);
        }

        return view('frontend.account.profile', [
            'user' => $user,
            'loadError' => null,
            'passwordChangeAvailable' => $this->candidatePasswordChangeAvailable(),
            'profileEditAvailable' => false,
        ]);
    }

    public function changePasswordForm(): View
    {
        $available = $this->candidatePasswordChangeAvailable();

        return view(match (ApiSession::accountType() ?? auth()->user()->account_type ?? null) {
            'candidate' => 'frontend.candidate.change-password',
            'company' => 'frontend.company.change-password',
            default => 'frontend.account.auth.change-password',
        }, [
            'passwordChangeAvailable' => $available,
        ]);
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed|different:current_password',
        ]);

        // Candidate-only Backend contract. Never call /candidate/change-password for other roles.
        if (! $this->candidatePasswordChangeAvailable()) {
            return redirect()->back()->withErrors([
                'error' => __('common.password_change.unavailable_role'),
            ]);
        }

        $payload = [
            'current_password' => (string) $request->input('current_password'),
            'new_password' => (string) $request->input('new_password'),
            'new_password_confirmation' => (string) $request->input('new_password_confirmation'),
        ];

        $response = $this->candidates->changePassword($payload);
        // Never retain plaintext passwords on the request/session (do not withInput).
        unset($payload);

        $status = (int) ($response['statusCode'] ?? 500);

        if ($status === 401) {
            ApiSession::forget();

            return redirect()->route('frontend.account.login')
                ->withErrors(['error' => __('common.password_change.session_expired')]);
        }

        if ($status === 403) {
            return redirect()->route('frontend.workspace')
                ->withErrors(['error' => __('common.password_change.forbidden')]);
        }

        if ($status === 422) {
            $errors = $response['errors'] ?? null;

            return redirect()->back()->withErrors(
                (is_array($errors) && $errors !== [])
                    ? $errors
                    : ['error' => $response['message'] ?? __('common.password_change.failed')]
            );
        }

        if ($status === 200) {
            // Backend keeps the Sanctum token valid (no revoke) — stay signed in.
            return redirect()->back()->with('success_key', 'flash.candidate.password_changed');
        }

        $message = is_string($response['message'] ?? null) ? trim((string) $response['message']) : '';

        return redirect()->back()->withErrors([
            'error' => $message !== '' ? $message : __('common.password_change.failed'),
        ]);
    }

    public function deleteAccount(): RedirectResponse
    {
        return redirect()->back()->withErrors([
            'error' => __('This feature is not available yet.'),
        ]);
    }

    private function candidatePasswordChangeAvailable(): bool
    {
        return ApiSession::accountType() === 'candidate';
    }

    /** @param  mixed  $data */
    private function extractUser(mixed $data): ?array
    {
        if (! is_array($data)) {
            return null;
        }

        if (isset($data['account_type']) || isset($data['email']) || isset($data['name'])) {
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

    /** @param  mixed  $data */
    private function extractPlatformAdmin(mixed $data): ?array
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

        $roles = is_array($data['roles'] ?? null) ? array_values($data['roles']) : [];
        $permissions = is_array($data['permissions'] ?? null) ? array_values($data['permissions']) : [];

        return [
            'id' => $data['id'] ?? $data['email'],
            'name' => $data['name'] ?? '',
            'email' => $data['email'],
            'account_type' => 'admin',
            'identity' => ApiSession::REALM_PLATFORM_ADMIN,
            'roles' => $roles,
            'permissions' => $permissions,
            'account_status_id' => $data['account_status_id'] ?? null,
        ];
    }
}
