<?php

namespace App\Frontend\Company;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use App\Frontend\Auth\ApiSession;
use App\Models\CompanyAPI;

class CompanyController extends Controller
{
    private $api = null;

    public function __construct()
    {
        $this->api = new CompanyAPI();
    }

    function getProfile(Request $request)
    {
        $response = $this->api->getProfile();

        if ($response['statusCode'] === 200) {
            // Profile retrieval successful, you can perform additional actions here if needed
            return view('frontend.company.profile', ['profile' => $response['data']]);
        } else {
            // Profile retrieval failed, return the error message
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    function updateProfile(Request $request)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'name' => 'required|string|max:255',
                'website' => 'nullable|url',
                'industry' => 'nullable|string|max:255',
                'contact_email' => 'nullable|email',
                'contact_phone' => 'nullable|string|max:30',
                'address' => 'nullable|string',
            ]);

            $data = $request->only(['name', 'website', 'industry', 'contact_email', 'contact_phone', 'address']);

            $response = $this->api->updateProfile($data);

            if ($response['statusCode'] === 200) {
                // Profile update successful, you can perform additional actions here if needed
                return redirect()->back()->with('success_key', 'flash.company.profile_updated');
            } else {
                // Profile update failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.company.update-profile', get_defined_vars());
    }

    function getVacancies(Request $request)
    {
        $response = $this->api->getVacancies();

        if ($response['statusCode'] === 200) {
            // Vacancies retrieval successful, you can perform additional actions here if needed
            return view('frontend.company.vacancies', ['vacancies' => $response['data']]);
        } else {
            // Vacancies retrieval failed, return the error message
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    function createVacancy(Request $request)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'location' => 'required|string|max:255',
                'work_mode' => 'required|in:onsite,remote,hybrid',
                'employment_type' => 'required|string|max:50',
                'salary_min' => 'nullable|numeric|min:0',
                'salary_max' => 'nullable|numeric|gte:salary_min',
                'currency' => 'nullable|string|max:10',
                'salary_period' => 'nullable|string|max:20',
                'experience_years' => 'nullable|integer|min:0',
                'department' => 'nullable|string|max:255',
                'openings_count' => 'required|integer|min:1',
                'deadline' => 'nullable|date',
                'category_id' => 'required|integer',
                'visibility' => 'required|in:public,private',
                'private_apply_mode' => 'nullable|in:all_employees,nominated_only',
            ]);

            $data = $request->only(['title', 'description', 'location', 'work_mode', 'employment_type', 'salary_min', 'salary_max', 'currency', 'salary_period', 'experience_years', 'department', 'openings_count', 'deadline', 'category_id', 'visibility', 'private_apply_mode']);

            $response = $this->api->createVacancy($data);

            if ($response['statusCode'] === 200) {
                // Vacancy creation successful, you can perform additional actions here if needed
                return redirect()->back()->with('success_key', 'flash.company.vacancy_created');
            } else {
                // Vacancy creation failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.company.create-vacancy', get_defined_vars());
    }

    function updateVacancy(Request $request, $vacancyId)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'location' => 'required|string|max:255',
                'work_mode' => 'required|in:onsite,remote,hybrid',
                'employment_type' => 'required|string|max:50',
                'salary_min' => 'nullable|numeric|min:0',
                'salary_max' => 'nullable|numeric|gte:salary_min',
                'currency' => 'nullable|string|max:10',
                'salary_period' => 'nullable|string|max:20',
                'experience_years' => 'nullable|integer|min:0',
                'department' => 'nullable|string|max:255',
                'openings_count' => 'required|integer|min:1',
                'deadline' => 'nullable|date',
                'category_id' => 'required|integer',
                'visibility' => 'required|in:public,private',
                'private_apply_mode' => 'nullable|in:all_employees,nominated_only',
            ]);

            $data = $request->only(['title', 'description', 'location', 'work_mode', 'employment_type', 'salary_min', 'salary_max', 'currency', 'salary_period', 'experience_years', 'department', 'openings_count', 'deadline', 'category_id', 'visibility', 'private_apply_mode']);

            $response = $this->api->updateVacancy($vacancyId, $data);

            if ($response['statusCode'] === 200) {
                // Vacancy update successful, you can perform additional actions here if needed
                return redirect()->back()->with('success_key', 'flash.company.vacancy_updated');
            } else {
                // Vacancy update failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.company.update-vacancy', get_defined_vars());
    }

    function deleteVacancy(Request $request, $vacancyId)
    {
        $response = $this->api->deleteVacancy($vacancyId);

        if ($response['statusCode'] === 200) {
            // Vacancy deletion successful, you can perform additional actions here if needed
            return redirect()->back()->with('success_key', 'flash.company.vacancy_deleted');
        } else {
            // Vacancy deletion failed, return the error message
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    function getApplications(Request $request, $vacancyId)
    {
        $response = $this->api->getApplications($vacancyId);

        if ($response['statusCode'] === 200) {
            // Applications retrieval successful, you can perform additional actions here if needed
            return view('frontend.company.applications', ['applications' => $response['data']]);
        } else {
            // Applications retrieval failed, return the error message
            return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    function updateApplicationStatus(Request $request, $applicationId)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'status' => 'required|string',
                'reason' => 'nullable|string|max:1000',
            ]);

            $data = $request->only(['status', 'reason']);

            $response = $this->api->updateApplicationStatus($applicationId, $data);

            if ($response['statusCode'] === 200) {
                // Application status update successful, you can perform additional actions here if needed
                return redirect()->back()->with('success_key', 'flash.company.application_status_updated');
            } else {
                // Application status update failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.company.update-application-status', get_defined_vars());
    }

    function createExam(Request $request)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'title' => 'required|string|max:255',
                'time_limit_min' => 'required|integer|min:1',
                'attempt_limit' => 'required|integer|min:1|max:3',
                'passing_score' => 'required|numeric|min:0|max:100',
            ]);

            $data = $request->only(['title', 'time_limit_min', 'attempt_limit', 'passing_score']);

            $response = $this->api->createExam($data);

            if ($response['statusCode'] === 200) {
                // Exam creation successful, you can perform additional actions here if needed
                return redirect()->back()->with('success_key', 'flash.company.exam_created');
            } else {
                // Exam creation failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.company.create-exam', get_defined_vars());
    }

    function createOffer(Request $request)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'application_id' => 'required|integer',
                'salary_amount' => 'required|numeric|min:0',
                'currency' => 'required|string|max:10',
                'salary_period' => 'required|string',
                'start_date' => 'required|date',
                'expires_at' => 'required|date|after:today',
            ]);

            $data = $request->only(['application_id', 'salary_amount', 'currency', 'salary_period', 'start_date', 'expires_at']);

            $response = $this->api->createOffer($data);

            if ($response['statusCode'] === 200) {
                // Offer creation successful, you can perform additional actions here if needed
                return redirect()->back()->with('success_key', 'flash.company.offer_created');
            } else {
                // Offer creation failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        return view('frontend.company.create-offer', get_defined_vars());
    }
}
