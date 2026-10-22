<?php

namespace App\Http\Controllers\Interviewer;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockInterviewerData;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Figma Frame 43 — Interview scorecards (frontend presentation only).
 */
class EvaluationController extends Controller
{
    public function index(): View
    {
        $index = MockInterviewerData::evaluationsIndex();

        return view('interviewer.evaluations.index', [
            'pageTitle' => __('interviewer.evaluations.index_page_title'),
            'uiRole' => 'interviewer',
            'showMockBanner' => false,
            'index' => $index,
            'frontendOnly' => true,
        ]);
    }

    public function create(string $interview): View
    {
        $scorecard = MockInterviewerData::scorecard($interview);
        if ($scorecard === null) {
            throw new NotFoundHttpException;
        }

        return view('interviewer.evaluations.create', [
            'pageTitle' => __('interviewer.evaluations.page_title', ['name' => $scorecard['interview']['applicant']]),
            'uiRole' => 'interviewer',
            'showMockBanner' => false,
            'scorecard' => $scorecard,
            'frontendOnly' => true,
        ]);
    }
}
