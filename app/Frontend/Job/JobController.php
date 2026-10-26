<?php

namespace App\Frontend\Job;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use App\Models\JobAPI;

class JobController extends Controller
{
    private $api;

    public function __construct(JobAPI $api)
    {
        $this->api = $api;
    }

    function getJobs(Request $request)
    {
        $response = $this->api->getJobs($request->query());

        return $this->renderList($response);
    }

    function getJob(Request $request, $jobId)
    {
        $response = $this->api->getJob($jobId);

        if ($response['statusCode'] === 200) {
            return view('frontend.job.job-details', ['job' => $response['data']]);
        }

        // unknown / closed / expired job: a real 404 page (going "back" could loop on a public page)
        if ($response['statusCode'] === 404) {
            abort(404);
        }

        return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
    }

    function searchJobs(Request $request)
    {
        $response = $this->api->searchJobs((string) $request->query('q', ''));

        return $this->renderList($response);
    }

    function getSalaryBenchmark(Request $request)
    {
        $fields = $request->validate([
            'job_title' => 'required|string|max:255',
        ]);

        $response = $this->api->getSalaryBenchmark($fields['job_title']);

        if ($response['statusCode'] === 200) {
            // Salary benchmark retrieval successful, you can perform additional actions here if needed
            return response()->json(['benchmark' => $response['data']], 200);
        } else {
            // Salary benchmark retrieval failed, return the error message
            return response()->json(['error' => $response['message']], $response['statusCode']);
        }
    }

    /** Jobs page: the list + the paginator info (the view's pagination links need it). */
    private function renderList(array $response)
    {
        if ($response['statusCode'] === 200) {
            return view('frontend.job.jobs', [
                'jobs' => $response['data'],
                'meta' => $response['meta']['pagination'] ?? null,
            ]);
        }

        return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
    }
}
