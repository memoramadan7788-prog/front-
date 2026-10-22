<?php

namespace Tests\Feature;

use App\Http\Middleware\SetFrontendLocale;
use Tests\TestCase;

class CompanyInterviewsEvaluationsScreensTest extends TestCase
{
    public function test_company_interview_calendar_matches_frame_thirty_three(): void
    {
        $this->get(route('company.interviews.index'))
            ->assertOk()
            ->assertSee('Company workspace', false)
            ->assertSee('Interviews', false)
            ->assertDontSee('33 / Company workspace', false)
            ->assertSee('Team interview calendar', false)
            ->assertSee('Coordinate interviews without losing availability context', false)
            ->assertSee('Schedule interview', false)
            ->assertSee('Search candidate or interviewer', false)
            ->assertSee('October 19–23, 2026', false)
            ->assertSee('Maya Chen · Portfolio · NS-204', false)
            ->assertSee('Luis Romero', false)
            ->assertSee('Aisha Patel', false)
            ->assertSee('Interviewer availability', false)
            ->assertSee('Alex Morgan', false)
            ->assertSee('View interview details', false)
            ->assertSee('Reschedule', false)
            ->assertSee(route('company.interviews.schedule'), false)
            ->assertDontSee('Frontend UI only', false);
    }

    public function test_company_schedule_interview_screen_renders(): void
    {
        $this->get(route('company.interviews.schedule', ['applicant' => 'maya-chen']))
            ->assertOk()
            ->assertSee('Company workspace', false)
            ->assertSee('Interviews', false)
            ->assertDontSee('32 / Company workspace', false)
            ->assertSee('Schedule interview', false)
            ->assertSee('Maya Chen', false)
            ->assertSee('NS-204', false)
            ->assertSee('Interview type', false)
            ->assertSee('Interviewer', false)
            ->assertSee('Available time slots', false)
            ->assertSee('Scheduling summary', false)
            ->assertSee('Confirm schedule', false)
            ->assertSee('Central Time', false)
            ->assertDontSee('invitation was sent', false);
    }

    public function test_company_interview_show_and_404(): void
    {
        $this->get(route('company.interviews.show', 'int-ns204-portfolio'))
            ->assertOk()
            ->assertSee('Portfolio interview', false)
            ->assertSee('Maya Chen', false)
            ->assertSee('Alex Morgan', false);

        $this->get(route('company.interviews.show', 'missing-interview'))->assertNotFound();
    }

    public function test_schedule_unknown_applicant_returns_not_found(): void
    {
        $this->get(route('company.interviews.schedule', ['applicant' => 'unknown-person']))
            ->assertNotFound();
    }

    public function test_evaluation_review_matches_frame_thirty_four(): void
    {
        $this->get(route('company.evaluations.show', 'eval-ethan-cole'))
            ->assertOk()
            ->assertSee('Company workspace', false)
            ->assertSee('Applicants', false)
            ->assertDontSee('34 / Company workspace', false)
            ->assertSee('Evaluation review · Ethan Cole', false)
            ->assertSee('Final interviews completed October 16', false)
            ->assertSee('Offer stage', false)
            ->assertSee('Direct hire', false)
            ->assertSee('Daniel Brooks', false)
            ->assertSee('Competency evidence', false)
            ->assertSee('Problem framing', false)
            ->assertSee('Interviewer recommendations', false)
            ->assertSee('Strong hire', false)
            ->assertSee('Proceed to offer', false)
            ->assertSee('Open draft offer', false)
            ->assertSee('Request clarification', false)
            ->assertSee('Evidence, not an automatic score', false)
            ->assertDontSee('Frontend UI only', false);
    }

    public function test_evaluation_resolves_by_applicant_id_and_404(): void
    {
        $this->get(route('company.evaluations.show', 'ethan-cole'))
            ->assertOk()
            ->assertSee('Ethan Cole', false);

        $this->get(route('company.evaluations.show', 'unknown-eval'))->assertNotFound();
    }

    public function test_interview_routes_are_get_only(): void
    {
        $this->get(route('company.interviews.index'))->assertOk();
        $this->get(route('company.interviews.schedule'))->assertOk();
        $this->post('/company/interviews')->assertMethodNotAllowed();
        $this->post('/company/interviews/schedule')->assertMethodNotAllowed();
        $this->post('/company/evaluations/eval-ethan-cole')->assertMethodNotAllowed();
    }

    public function test_sidebar_and_cross_links_wire_interviews(): void
    {
        $this->get(route('company.interviews.index'))
            ->assertOk()
            ->assertSee(route('company.interviews.index'), false)
            ->assertSee('aria-current="page"', false);

        $this->get(route('company.dashboard'))
            ->assertOk()
            ->assertSee(route('company.interviews.index'), false)
            ->assertDontSee('Calendar · Soon', false);

        $this->get(route('company.applicants.show', 'maya-chen'))
            ->assertOk()
            ->assertSee(route('company.interviews.schedule', ['applicant' => 'maya-chen']), false)
            ->assertSee(route('company.interviews.show', 'int-ns204-portfolio'), false)
            ->assertDontSee(route('company.evaluations.show', 'eval-ethan-cole'), false);

        $this->get(route('company.applicants.show', 'ethan-cole'))
            ->assertOk()
            ->assertSee(route('company.evaluations.show', 'eval-ethan-cole'), false)
            ->assertDontSee(route('company.interviews.show', 'int-ns204-portfolio'), false);

        $this->get(route('company.interviews.index'))
            ->assertOk()
            ->assertSee(route('company.interviews.schedule', ['applicant' => 'maya-chen']), false)
            ->assertSee('October 19–23, 2026', false)
            ->assertSee('data-weeks=', false);
    }

    public function test_arabic_interview_and_evaluation_screens_render(): void
    {
        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('company.interviews.index'))
            ->assertOk()
            ->assertSee('تقويم مقابلات الفريق', false)
            ->assertSee('جدولة مقابلة', false);

        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('company.interviews.schedule'))
            ->assertOk()
            ->assertSee('تأكيد الجدولة', false)
            ->assertSee('ملخص الجدولة', false);

        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('company.evaluations.show', 'eval-ethan-cole'))
            ->assertOk()
            ->assertSee('مراجعة التقييم', false)
            ->assertSee('أدلة الكفاءات', false);
    }
}
