<?php

namespace App\Frontend\Home;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function submitContact(Request $request): RedirectResponse
    {
        $rules = [
            'message' => ['required', 'string', 'max:10000'],
        ];

        if (! auth()->check()) {
            $rules['name'] = ['required', 'string', 'max:255'];
            $rules['email'] = ['required', 'email', 'max:255'];
        }

        $request->validate($rules);

        $input = auth()->check()
            ? $request->except(['name', 'email', '_token'])
            : $request->input();

        // General Contact remains separate; still blocked from live Backend wiring in this FE batch.
        return back()->withInput($input)->with('submission_unavailable', __('public.contact.unavailable'));
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
