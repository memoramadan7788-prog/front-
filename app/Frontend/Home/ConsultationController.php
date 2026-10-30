<?php

namespace App\Frontend\Home;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Mission 11 — Request Consultation (Task #52).
 *
 * Backend verification (2026-10-31):
 * - POST /api/contact exists (general ContactMessage: name, email, title, description).
 * - POST /api/contact/consultation does NOT exist.
 * - Consultation must not silently map into General Contact.
 *
 * CONSULTATION_API_AVAILABLE remains false until a verified consultation contract ships.
 */
class ConsultationController extends Controller
{
    /** Flip only after a verified Backend consultation endpoint is integrated. */
    public const CONSULTATION_API_AVAILABLE = false;

    /** Stable hiring-model keys (labels come from lang). */
    public const HIRING_MODELS = [
        'agency_direct',
        'direct_only',
        'agency_only',
    ];

    /** Stable expected-hires keys. */
    public const EXPECTED_HIRES = [
        '1_3',
        '4_10',
        '10_plus',
    ];

    /** Stable preferred-contact keys. */
    public const PREFERRED_CONTACTS = [
        'email',
        'phone',
    ];

    /** Stable recruitment-support keys. */
    public const SUPPORT_TYPES = [
        'contingent',
        'retained',
        'rpo',
        'advisory',
    ];

    public function show(): View
    {
        return view('frontend.public.consultation', [
            'consultationApiAvailable' => self::CONSULTATION_API_AVAILABLE,
            'hiringModels' => self::HIRING_MODELS,
            'expectedHires' => self::EXPECTED_HIRES,
            'preferredContacts' => self::PREFERRED_CONTACTS,
            'supportTypes' => self::SUPPORT_TYPES,
        ]);
    }

    public function submit(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules($request));

        // Never POST consultation payloads to /api/contact (different workflow).
        // Never invent success while CONSULTATION_API_AVAILABLE is false.
        if (! self::CONSULTATION_API_AVAILABLE) {
            $input = auth()->check()
                ? collect($validated)->except(['full_name', 'work_email'])->all()
                : $validated;

            return back()
                ->withInput($input)
                ->with('submission_unavailable', __('public.consultation.unavailable'))
                ->with('consultation_blocked', true);
        }

        // Placeholder for future verified API mapping — unreachable until flag is enabled.
        return back()->with('submission_unavailable', __('public.consultation.unavailable'));
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(Request $request): array
    {
        $rules = [
            'company' => ['required', 'string', 'max:255'],
            'role_title' => ['required', 'string', 'max:255'],
            'hiring_model' => ['required', 'string', Rule::in(self::HIRING_MODELS)],
            'expected_hires' => ['nullable', 'string', Rule::in(self::EXPECTED_HIRES)],
            'needs' => ['required', 'string', 'min:10', 'max:5000'],
            'preferred_contact' => ['nullable', 'string', Rule::in(self::PREFERRED_CONTACTS)],
            'privacy_consent' => ['accepted'],
            'insights_consent' => ['sometimes', 'boolean'],
            'support_type' => ['nullable', 'string', Rule::in(self::SUPPORT_TYPES)],
        ];

        if (! auth()->check()) {
            $rules['full_name'] = ['required', 'string', 'max:255'];
            $rules['work_email'] = ['required', 'email', 'max:255'];
        }

        return $rules;
    }
}
