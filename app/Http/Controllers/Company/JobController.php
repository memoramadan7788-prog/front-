<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockCompanyData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Company jobs & requisitions (Batch 5B) — Frames 26–28.
 * Frontend presentation only.
 */
class JobController extends Controller
{
    public function index(): View
    {
        return view('company.jobs.index', [
            'pageTitle' => 'Jobs & requisitions · Hirebridge',
            'uiRole' => 'company',
            'showMockBanner' => false,
            'jobsPage' => MockCompanyData::jobsIndex(),
            'frontendOnly' => true,
        ]);
    }

    public function create(Request $request): View|Response
    {
        $jobId = $request->query('job');
        $jobId = is_string($jobId) && $jobId !== '' ? $jobId : null;

        if ($jobId !== null && MockCompanyData::job($jobId) === null) {
            abort(404);
        }

        $wizard = MockCompanyData::jobWizard($jobId);

        return view('company.jobs.wizard.create', [
            'pageTitle' => 'Create a job · Hirebridge',
            'uiRole' => 'company',
            'showMockBanner' => false,
            'wizard' => $wizard,
            'frontendOnly' => true,
        ]);
    }

    public function show(string $job): View|Response
    {
        $detail = MockCompanyData::job($job);

        if ($detail === null) {
            abort(404);
        }

        return view('company.jobs.show', [
            'pageTitle' => ($detail['title'] ?? 'Job').' · Hirebridge',
            'uiRole' => 'company',
            'showMockBanner' => false,
            'job' => $detail,
            'frontendOnly' => true,
        ]);
    }
}
