<?php

namespace App\Models;

/**
 * Backend: POST /auth/register/candidate | /auth/register/company, POST /auth/login,
 *          POST /auth/logout, GET /auth/me
 */
class AccountAPI extends ApiBase
{
    /** $data['account_type'] is 'candidate' or 'company' and picks the endpoint */
    public function register($data)
    {
        $type = $data['account_type'] === 'company' ? 'company' : 'candidate';
        unset($data['account_type']);

        return $this->call('POST', "/auth/register/{$type}", ['json' => $data, 'auth' => false]);
    }

    public function login($data)
    {
        return $this->call('POST', '/auth/login', ['json' => $data, 'auth' => false]);
    }

    public function logout()
    {
        return $this->call('POST', '/auth/logout');
    }

    public function getProfile()
    {
        return $this->call('GET', '/auth/me');
    }
}
