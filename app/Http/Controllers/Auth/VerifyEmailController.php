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
        return view('auth.verify-email', [
            'pageTitle' => 'Check your inbox · Hirebridge',
            'email' => $request->query('email', 'maya.chen@example.com'),
            'frontendOnly' => true,
        ]);
    }
}
