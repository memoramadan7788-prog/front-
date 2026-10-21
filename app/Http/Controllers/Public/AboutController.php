<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class AboutController extends Controller
{
    public function __invoke(): View
    {
        return view('public.about', [
            'pageTitle' => 'Specialist recruitment · Hirebridge',
        ]);
    }
}
