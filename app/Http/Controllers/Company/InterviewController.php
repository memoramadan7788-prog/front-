<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Support\Frontend\MockCompanyData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Company interviews calendar & scheduling (Batch 5D) — Frames 32–33.
 * Frontend presentation only.
 */
class InterviewController extends Controller
{
    public function index(): View
    {
        return view('company.interviews.index', [
            'pageTitle' => __('company.interviews.page_title'),
            'uiRole' => 'company',
            'showMockBanner' => false,
            'calendar' => MockCompanyData::interviewCalendar(),
            'frontendOnly' => true,
        ]);
    }

    public function schedule(Request $request): View|Response
    {
        $applicantId = $request->query('applicant');
        $applicantId = is_string($applicantId) && $applicantId !== '' ? $applicantId : 'maya-chen';

        if (MockCompanyData::applicant($applicantId) === null) {
            abort(404);
        }

        $schedule = MockCompanyData::interviewSchedule($applicantId);

        return view('company.interviews.schedule', [
            'pageTitle' => __('company.interviews.schedule_page_title'),
            'uiRole' => 'company',
            'showMockBanner' => false,
            'schedule' => $schedule,
            'frontendOnly' => true,
        ]);
    }

    public function show(string $interview): View|Response
    {
        $detail = MockCompanyData::interview($interview);

        if ($detail === null) {
            abort(404);
        }

        return view('company.interviews.show', [
            'pageTitle' => ($detail['title'] ?? 'Interview').' · Hirebridge',
            'uiRole' => 'company',
            'showMockBanner' => false,
            'interview' => $detail,
            'frontendOnly' => true,
        ]);
    }
}
