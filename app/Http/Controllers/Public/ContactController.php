<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class ContactController extends Controller
{
    public function __invoke(): View
    {
        return view('public.contact', [
            'pageTitle' => 'Let’s talk about your next hire · Hirebridge',
            'frontendOnlyForm' => true,
        ]);
    }
}
