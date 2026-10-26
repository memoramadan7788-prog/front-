<?php

namespace App\Frontend\Account;

use App\Frontend\Auth\ApiSession;
use App\Models\AccountAPI;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Shared account pages (profile settings, change password).
 *
 * Profile: GET /auth/me (read-only). No authenticated profile-update API exists on Backend.
 * Password: no authenticated change-password API exists yet (only admin forgot/reset-token flow).
 * Forms validate locally; submit does not call invent endpoints or mutate credentials.
 */
class AccountController extends Controller
{
    public function __construct(
        private readonly AccountAPI $accounts
    ) {}

    public function profile(Request $request): View|RedirectResponse
    {
        $response = $this->accounts->getProfile();
        $status = (int) ($response['statusCode'] ?? $response['httpStatus'] ?? 500);

        if ($status === 401) {
            ApiSession::forget();

            return redirect()->route('frontend.account.login')
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
                'passwordChangeAvailable' => false,
                'profileEditAvailable' => false,
            ]);
        }

        $user = $this->extractUser($response['data'] ?? null) ?? (ApiSession::user() ?? []);

        if ($user !== []) {
            ApiSession::refreshUser($user);
        }

        return view('frontend.account.profile', [
            'user' => $user,
            'loadError' => null,
            'passwordChangeAvailable' => false,
            'profileEditAvailable' => false,
        ]);
    }

    public function changePasswordForm(): View
    {
        return view(match (auth()->user()->account_type ?? null) {
            'candidate' => 'frontend.candidate.change-password',
            'company' => 'frontend.company.change-password',
            default => 'frontend.account.auth.change-password',
        }, [
            // Backend has no authenticated POST change-password contract yet.
            'passwordChangeAvailable' => false,
        ]);
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        // Do not invent Backend calls. Passwords are never logged.
        return redirect()->back()->withErrors([
            'error' => __('Changing your password is not available yet. The Backend has no authenticated change-password API.'),
        ]);
    }

    public function deleteAccount(): RedirectResponse
    {
        return redirect()->back()->withErrors([
            'error' => __('This feature is not available yet.'),
        ]);
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
}
