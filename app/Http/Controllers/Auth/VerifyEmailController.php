<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Figma 09 — Check your inbox (email verification code UI).
 */
class VerifyEmailController extends Controller
{
    public function notice(Request $request): View
    {
        $state = $request->query('verify_state');
        $state = is_string($state) && in_array($state, ['default', 'failed', 'resent'], true)
            ? $state
            : 'default';

        return view('auth.verify-email', [
            'pageTitle' => 'Check your inbox · Hirebridge',
            'email' => $request->query('email', 'maya.chen@example.com'),
            'verifyState' => $state,
            'frontendOnly' => true,
        ]);
    }
}
