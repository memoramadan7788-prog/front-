<?php

namespace App\Frontend\Auth;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use App\Frontend\Auth\ApiSession;
use App\Models\AccountAPI;

class AuthController extends Controller
{
    private $api = null;

    public function __construct()
    {
        $this->api = new AccountAPI();
    }

    public function register(Request $request)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'account_type' => 'required|in:candidate,company',
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'password' => 'required|min:8|confirmed',
                'company_name' => 'required_if:account_type,company|nullable|string|max:255',
                'industry' => 'nullable|string|max:100',
                'website' => 'nullable|url|max:2048',
            ]);

            $data = $request->only(['account_type', 'name', 'email', 'password', 'password_confirmation']);

            if ($data['account_type'] === 'company') {
                $data += $request->only(['company_name', 'industry', 'website']);
            }

            // optional fields that were left empty are not sent
            $data = array_filter($data, fn ($value) => $value !== null && $value !== '');

            $response = $this->api->register($data);

            if ($response['statusCode'] === 200) {
                // Registration successful, you can perform additional actions here if needed
                $user = (array) ($response['data']['user'] ?? []);
                // register answers {user, company, token}: the company is not inside user, so the session keeps it
                if (!empty($response['data']['company'])) {
                    $user['company'] = array_intersect_key((array) $response['data']['company'], array_flip(['id', 'name', 'status']));
                }
                ApiSession::store($response['data']['token'], $user);

                if (($user['account_type'] ?? 'candidate') === 'company') {
                    return redirect()->route('frontend.company.dashboard')
                        ->with('success_key', 'auth.flash.register_company_pending');
                }

                // Flash a translation key (not a pre-rendered string) so the banner
                // respects the locale at display time; Laravel flash is single-use.
                return redirect()->route('frontend.candidate.dashboard')
                    ->with('success_key', 'auth.flash.register_candidate');
            } else {
                // Registration failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.account.auth.register', get_defined_vars());
    }

    public function login(Request $request)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $data = $request->only(['email', 'password']);

            $response = $this->api->login($data);

            if ($response['statusCode'] === 200) {
                // Login successful, you can perform additional actions here if needed
                $user = (array) ($response['data']['user'] ?? []);
                ApiSession::store($response['data']['token'], $user);

                $type = $user['account_type'] ?? 'candidate';

                if ($type === 'company') {
                    $pending = data_get($user, 'company.status') === 'pending';

                    return redirect()->route('frontend.company.dashboard')
                        ->with('success_key', $pending ? 'auth.flash.login_company_pending' : 'auth.flash.login_success');
                } elseif ($type === 'admin') {
                    return redirect()->route('frontend.dashboard')
                        ->with('success_key', 'auth.flash.login_success');
                }

                return redirect()->route('frontend.candidate.dashboard')
                    ->with('success_key', 'auth.flash.login_success');
            } else {
                // Login failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput($request->only('email'));
            }
        }

        return view('frontend.account.auth.login', get_defined_vars());
    }

    public function logout(Request $request)
    {
        // tell the backend to delete the token, then forget it here whatever the answer was
        $this->api->logout();
        ApiSession::forget();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('frontend.account.login');
    }
}
