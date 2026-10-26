<?php

namespace App\Frontend\Application;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use App\Models\ApplicationAPI;

class ApplicationController extends Controller
{
    private $api = null;

    public function __construct()
    {
        $this->api = new ApplicationAPI();
    }

    function getApplications(Request $request)
    {
        $response = $this->api->getApplications();

        if ($response['statusCode'] === 200) {
            // Applications retrieval successful, you can perform additional actions here if needed
            return view('frontend.application.applications', ['applications' => $response['data']]);
        } else {
            // Applications retrieval failed, return the error message
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    function getApplication(Request $request, $applicationId)
    {
        $response = $this->api->getApplication($applicationId);

        if ($response['statusCode'] === 200) {
            // Application details retrieval successful, you can perform additional actions here if needed
            return view('frontend.application.application-details', ['application' => $response['data']]);
        } else {
            // Application details retrieval failed, return the error message
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    function withdrawApplication(Request $request, $applicationId)
    {
        $response = $this->api->withdrawApplication($applicationId);

        if ($response['statusCode'] === 200) {
            // Application withdrawal successful, you can perform additional actions here if needed
            return redirect()->back()->with('success_key', 'flash.application.withdrawn');
        } else {
            // Application withdrawal failed, return the error message
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    function updateStatus(Request $request, $applicationId)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'status' => 'required|string',
                'reason' => 'nullable|string|max:1000',
            ]);

            $data = $request->only(['status', 'reason']);

            $response = $this->api->updateStatus($applicationId, $data);

            if ($response['statusCode'] === 200) {
                // Application status update successful, you can perform additional actions here if needed
                return redirect()->back()->with('success_key', 'flash.application.status_updated');
            } else {
                // Application status update failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.application.update-status', get_defined_vars());
    }
}
