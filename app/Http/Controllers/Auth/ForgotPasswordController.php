<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * Supporting frontend screen for “Forgot password?” navigation.
 * Not a numbered Figma auth frame in 07–10; styled to match auth layout.
 */
class ForgotPasswordController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password', [
            'pageTitle' => 'Reset your password · Hirebridge',
            'frontendOnly' => true,
        ]);
    }
}
