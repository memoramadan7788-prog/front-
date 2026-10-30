<?php

namespace App\Frontend\Home;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

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

    public function consultation()
    {
        return view('frontend.public.consultation');
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $rules = [
            'message' => ['required', 'string'],
        ];

        if (! auth()->check()) {
            $rules['name'] = ['required', 'string'];
            $rules['email'] = ['required', 'email'];
        }

        $request->validate($rules);

        $input = auth()->check()
            ? $request->except(['name', 'email', '_token'])
            : $request->input();

        return back()->withInput($input)->with('submission_unavailable', __('public.contact.unavailable'));
    }

    public function submitConsultation(Request $request): RedirectResponse
    {
        $rules = [
            'company' => ['required', 'string'],
            'role_title' => ['required', 'string'],
            'hiring_model' => ['required', 'string'],
            'needs' => ['required', 'string'],
            'privacy_consent' => ['accepted'],
            'expected_hires' => ['nullable', 'string', Rule::in(__('public.contact.hire_counts'))],
            'preferred_contact' => ['nullable', 'string', Rule::in(__('public.contact.contact_options'))],
        ];

        if (! auth()->check()) {
            $rules['full_name'] = ['required', 'string'];
            $rules['work_email'] = ['required', 'email'];
        }

        $request->validate($rules);

        $input = auth()->check()
            ? $request->except(['full_name', 'work_email', '_token'])
            : $request->input();

        return back()->withInput($input)->with('submission_unavailable', __('public.consultation.unavailable'));
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
