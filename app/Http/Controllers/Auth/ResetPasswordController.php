<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Figma 10 — Set a new password (presentation only).
 */
class ResetPasswordController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.reset-password', [
            'pageTitle' => 'Set a new password · Hirebridge',
            'email' => $request->query('email', 'maya.chen@example.com'),
            'frontendOnly' => true,
        ]);
    }
}
