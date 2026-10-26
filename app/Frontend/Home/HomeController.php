<?php

namespace App\Frontend\Home;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return view('frontend.public.home');
    }

    public function about()
    {
        return view('frontend.public.about');
    }

    public function contact()
    {
        return view('frontend.public.contact');
    }

    /** Sends the logged-in user to the dashboard that matches their account type. */
    public function workspace(): RedirectResponse
    {
        return match (auth()->user()->account_type ?? null) {
            'candidate' => redirect()->route('frontend.candidate.dashboard'),
            'company' => redirect()->route('frontend.company.dashboard'),
            'admin' => redirect()->route('frontend.dashboard'),
            default => abort(403),
        };
    }
}
