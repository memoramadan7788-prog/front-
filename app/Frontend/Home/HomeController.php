<?php

namespace App\Frontend\Home;

use App\Frontend\Auth\ApiSession;
use App\Models\ContactAPI;
use App\Support\Frontend\SafeApiMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class HomeController extends Controller
{
    public function __construct(private readonly ?ContactAPI $contactApi = null) {}

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
            'subject' => ['required', 'string'],
            'description' => ['required', 'string'],
        ];

        if (! auth()->check()) {
            $rules['name'] = ['required', 'string', 'max:255'];
            $rules['email'] = ['required', 'email', 'max:255'];
        }

        $validated = $request->validate($rules);
        $authenticated = auth()->check();
        $identity = ApiSession::user() ?? [];

        $name = $authenticated ? trim((string) ($identity['name'] ?? '')) : trim((string) ($validated['name'] ?? ''));
        $email = $authenticated ? trim((string) ($identity['email'] ?? '')) : trim((string) ($validated['email'] ?? ''));

        if ($name === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return back()
                ->withInput($validated)
                ->withErrors(['error' => __('public.contact.identity_unavailable')]);
        }

        $payload = [
            'name' => $name,
            'email' => $email,
            'title' => $validated['subject'],
            'description' => $validated['description'],
        ];
        $response = ($this->contactApi ?? new ContactAPI)->submitContactMessage($payload, $authenticated);

        if ((int) ($response['statusCode'] ?? 500) === 200) {
            return back()->with('contact_submitted', true);
        }

        $errors = $this->contactFieldErrors($response['errors'] ?? [], $authenticated);
        if ($errors !== []) {
            return back()->withInput($validated)->withErrors($errors);
        }

        $message = SafeApiMessage::forUser(
            isset($response['message']) ? (string) $response['message'] : null,
            __('public.contact.unavailable'),
            ['action' => 'contact.submit']
        );

        return back()->withInput($validated)->withErrors(['error' => $message]);
    }

    /**
     * @return array<string, string>
     */
    private function contactFieldErrors(mixed $errors, bool $authenticated): array
    {
        if (! is_array($errors)) {
            return [];
        }

        $fieldMap = ['title' => 'subject', 'description' => 'description', 'name' => 'name', 'email' => 'email'];
        $mapped = [];

        foreach ($fieldMap as $apiField => $formField) {
            if ($authenticated && in_array($formField, ['name', 'email'], true)) {
                continue;
            }

            $messages = $errors[$apiField] ?? null;
            if (is_array($messages) && isset($messages[0]) && is_string($messages[0])) {
                $mapped[$formField] = $messages[0];
            } elseif (is_string($messages) && $messages !== '') {
                $mapped[$formField] = $messages;
            }
        }

        return $mapped;
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
