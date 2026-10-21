<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockCompanyData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

/**
 * Company evaluation review (Batch 5D) — Frame 34.
 * Frontend presentation only.
 */
class EvaluationController extends Controller
{
    public function show(string $evaluation): View|Response
    {
        $detail = MockCompanyData::evaluation($evaluation);

        if ($detail === null) {
            abort(404);
        }

        return view('company.evaluations.show', [
            'pageTitle' => __('company.evaluations.page_title', ['name' => $detail['applicant'] ?? '']),
            'uiRole' => 'company',
            'showMockBanner' => false,
            'evaluation' => $detail,
            'frontendOnly' => true,
        ]);
    }
}
