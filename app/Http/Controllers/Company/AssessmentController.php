<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockCompanyData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

/**
 * Company assessment management UI (Batch 5G) — workflow/ERD-derived.
 * Frontend presentation only. No grading, assignment, or persistence.
 */
class AssessmentController extends Controller
{
    public function index(): View
    {
        $catalog = MockCompanyData::assessments();

        return view('company.assessments.index', [
            'pageTitle' => __('company.assessments.page_title'),
            'uiRole' => 'company',
            'showMockBanner' => false,
            'catalog' => $catalog,
            'frontendOnly' => true,
        ]);
    }

    public function create(): View
    {
        $catalog = MockCompanyData::assessments();

        return view('company.assessments.create', [
            'pageTitle' => __('company.assessments.create_page_title'),
            'uiRole' => 'company',
            'showMockBanner' => false,
            'catalog' => $catalog,
            'frontendOnly' => true,
        ]);
    }

    public function show(string $assessment): View|Response
    {
        $record = MockCompanyData::assessment($assessment);

        if ($record === null) {
            abort(404);
        }

        return view('company.assessments.show', [
            'pageTitle' => __('company.assessments.show_page_title', ['title' => $record['title']]),
            'uiRole' => 'company',
            'showMockBanner' => false,
            'assessment' => $record,
            'catalog' => MockCompanyData::assessments(),
            'frontendOnly' => true,
        ]);
    }

    public function results(string $assessment): View|Response
    {
        $record = MockCompanyData::assessment($assessment);

        if ($record === null) {
            abort(404);
        }

        return view('company.assessments.results', [
            'pageTitle' => __('company.assessments.results_page_title', ['title' => $record['title']]),
            'uiRole' => 'company',
            'showMockBanner' => false,
            'assessment' => $record,
            'frontendOnly' => true,
        ]);
    }
}
