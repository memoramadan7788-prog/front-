<?php

namespace App\Models;

/**
 * Backend:
 * POST /auth/register/candidate
 * POST /auth/register/company
 * POST /auth/login
 * POST /auth/logout
 * GET  /auth/me
 * POST /auth/forgot-password
 * POST /auth/reset-password
 */
class AccountAPI extends ApiBase
{
    /**
     * Register Candidate or Company.
     */
    public function register($data)
    {
        $type = ($data['account_type'] ?? 'candidate') === 'company'
            ? 'company'
            : 'candidate';

        unset($data['account_type']);

        return $this->call(
            'POST',
            "/auth/register/{$type}",
            ['json' => $data, 'auth' => false]
        );
    }

    /**
     * Login Candidate / Company.
     */
    public function login($data)
    {
        return $this->call(
            'POST',
            '/auth/login',
            ['json' => $data, 'auth' => false]
        );
    }

    /**
     * Logout current user.
     */
    public function logout()
    {
        return $this->call('POST', '/auth/logout');
    }

    /**
     * Get authenticated user profile.
     */
    public function getProfile()
    {
        return $this->call('GET', '/auth/me');
    }

    /**
     * Request password reset link.
     */
    public function forgotPassword($data)
    {
        return $this->call(
            'POST',
            '/auth/forgot-password',
            [
                'json' => $data,
                'auth' => false,
            ]
        );
    }

    /**
     * Reset password using email and token.
     */
    public function resetPassword($data)
    {
        return $this->call(
            'POST',
            '/auth/reset-password',
            [
                'json' => $data,
                'auth' => false,
            ]
        );
    }
}
