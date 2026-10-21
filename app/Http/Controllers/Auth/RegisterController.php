<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'pageTitle' => 'Create your account · Hirebridge',
            'frontendOnly' => true,
        ]);
    }
}
