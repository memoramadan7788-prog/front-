<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockCompanyData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Company applicant pipeline & profile (Batch 5C) — Frames 30–31.
 * Frontend presentation only.
 */
class ApplicantController extends Controller
{
    public function index(Request $request): View
    {
        $jobId = $request->query('job');
        $jobId = is_string($jobId) && $jobId !== '' ? $jobId : 'ns-204';

        if (MockCompanyData::job($jobId) === null) {
            abort(404);
        }

        $pipeline = MockCompanyData::applicantsPipeline($jobId);

        return view('company.applicants.pipeline', [
            'pageTitle' => __('company.applicants.page_title'),
            'uiRole' => 'company',
            'showMockBanner' => false,
            'pipeline' => $pipeline,
            'frontendOnly' => true,
        ]);
    }

    public function show(string $applicant): View|Response
    {
        $detail = MockCompanyData::applicant($applicant);

        if ($detail === null) {
            abort(404);
        }

        return view('company.applicants.show', [
            'pageTitle' => ($detail['name'] ?? 'Applicant').' · Hirebridge',
            'uiRole' => 'company',
            'showMockBanner' => false,
            'applicant' => $detail,
            'frontendOnly' => true,
        ]);
    }
}
