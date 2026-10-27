<?php

namespace App\Frontend\Account;

use App\Frontend\Auth\ApiSession;
use App\Models\AccountAPI;
use App\Models\CandidateAPI;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Shared account pages (profile settings, change password).
 *
 * Profile: GET /auth/me (read-only). No authenticated profile-update API exists on Backend.
 * Password (Candidate): PUT /api/candidate/change-password — Company/Admin remain unavailable.
 */
class AccountController extends Controller
{
    public function __construct(
        private readonly AccountAPI $accounts,
        private readonly CandidateAPI $candidates,
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
                'passwordChangeAvailable' => $this->candidatePasswordChangeAvailable(),
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
}
