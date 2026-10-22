<?php

namespace App\Http\Controllers\Interviewer;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockInterviewerData;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Figma Frames 41–42 — Interview preparation and live workspace.
 * Frontend presentation only.
 */
class InterviewController extends Controller
{
    public function prepare(string $interview): View
    {
        $preparation = MockInterviewerData::preparation($interview);
        if ($preparation === null) {
            throw new NotFoundHttpException;
        }

        return view('interviewer.interviews.preparation', [
            'pageTitle' => __('interviewer.prepare.page_title', ['name' => $preparation['interview']['applicant']]),
            'uiRole' => 'interviewer',
            'showMockBanner' => false,
            'preparation' => $preparation,
            'frontendOnly' => true,
        ]);
    }

    public function live(string $interview): View
    {
        $live = MockInterviewerData::live($interview);
        if ($live === null) {
            throw new NotFoundHttpException;
        }

        return view('interviewer.interviews.live', [
            'pageTitle' => __('interviewer.live.page_title', ['name' => $live['interview']['applicant']]),
            'uiRole' => 'interviewer',
            'showMockBanner' => false,
            'live' => $live,
            'frontendOnly' => true,
        ]);
    }
}
