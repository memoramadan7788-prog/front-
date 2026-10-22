<?php

namespace Tests\Feature;

use App\Http\Middleware\SetFrontendLocale;
use App\Support\Frontend\MockCompanyData;
use App\Support\Frontend\MockInterviewerData;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class InterviewerScreensTest extends TestCase
{
    public function test_interviewer_dashboard_renders_frame_thirty_nine_structure(): void
    {
        $this->get(route('interviewer.dashboard'))
            ->assertOk()
            ->assertSee('Good morning, Alex', false)
            ->assertSee('Your upcoming conversations', false)
            ->assertSee('Upcoming this week', false)
            ->assertSee('Preparation tasks', false)
            ->assertSee('Feedback overdue', false)
            ->assertSee('Maya Chen', false)
            ->assertSee('Portfolio interview', false)
            ->assertSee('NS-204', false)
            ->assertSee('View schedule', false)
            ->assertSee(route('interviewer.schedule.index'), false)
            ->assertSee('This week', false)
            ->assertSee('Aisha Patel', false)
            ->assertSee('Owen Clarke', false)
            ->assertSee('NS-205', false)
            ->assertSee('Session readiness', false)
            ->assertSee('Your availability', false)
            ->assertSee('Interviewer workspace', false)
            ->assertDontSee('39 /', false)
            ->assertDontSee('Frontend UI only', false)
            ->assertDontSee('Frame 39', false);
    }

    public function test_interviewer_schedule_renders_frame_forty_structure(): void
    {
        $this->get(route('interviewer.schedule.index'))
            ->assertOk()
            ->assertSee('My interview schedule', false)
            ->assertSee('Alex Morgan', false)
            ->assertSee('Assigned interviews only', false)
            ->assertSee('Week', false)
            ->assertSee('Upcoming list', false)
            ->assertSee('Past interviews', false)
            ->assertSee('October 19–23, 2026', false)
            ->assertSee('Maya Chen', false)
            ->assertSee('Aisha Patel', false)
            ->assertSee('Meeting access', false)
            ->assertSee('data-interviewer-schedule', false)
            ->assertSee('data-week-nav', false)
            ->assertDontSee('40 /', false)
            ->assertDontSee('Frontend UI only', false);
    }

    public function test_interviewer_sidebar_activates_implemented_routes_only(): void
    {
        $dashboard = $this->get(route('interviewer.dashboard'));
        $dashboard->assertOk();
        $dashboard->assertSee(route('interviewer.dashboard'), false);
        $dashboard->assertSee(route('interviewer.schedule.index'), false);
        $dashboard->assertSee(route('interviewer.interviews.prepare', 'int-ns204-portfolio'), false);
        $dashboard->assertSee(__('navigation.interviewer.preparation'), false);
        $dashboard->assertSee(__('common.soon'), false);

        $this->assertTrue(Route::has('interviewer.dashboard'));
        $this->assertTrue(Route::has('interviewer.schedule.index'));
        $this->assertTrue(Route::has('interviewer.interviews.prepare'));
        $this->assertTrue(Route::has('interviewer.interviews.live'));
        $this->assertTrue(Route::has('interviewer.evaluations.index'));
        $this->assertFalse(Route::has('interviewer.scorecards.index'));
    }

    public function test_assigned_interviews_align_with_company_records(): void
    {
        $maya = MockCompanyData::interview('int-ns204-portfolio');
        $aisha = MockCompanyData::interview('int-ns204-aisha-portfolio');
        $luis = MockCompanyData::interview('int-ns204-luis-team');

        $this->assertNotNull($maya);
        $this->assertSame('Alex Morgan', $maya['interviewer']);
        $this->assertSame('2026-10-22', $maya['day_key']);
        $this->assertSame('10:00–10:45 AM Central', $maya['time']);

        $this->assertNotNull($aisha);
        $this->assertSame('Alex Morgan', $aisha['interviewer']);
        $this->assertSame('2026-10-23', $aisha['day_key']);

        $this->assertNotNull($luis);
        $this->assertSame('Alex Morgan', $luis['interviewer']);
        $this->assertSame('2026-10-21', $luis['day_key']);

        $assignedIds = array_column(MockInterviewerData::assignedInterviews(), 'id');
        $this->assertContains('int-ns204-portfolio', $assignedIds);
        $this->assertContains('int-ns204-aisha-portfolio', $assignedIds);
        $this->assertContains('int-ns204-luis-team', $assignedIds);

        $interviewerMaya = MockInterviewerData::interview('int-ns204-portfolio');
        $this->assertSame($maya['applicant'], $interviewerMaya['applicant']);
        $this->assertSame($maya['job_code'], $interviewerMaya['job_code']);
        $this->assertSame($maya['day_key'], $interviewerMaya['day_key']);
        $this->assertSame($maya['timezone'], $interviewerMaya['timezone']);
    }

    public function test_arabic_interviewer_screens_render(): void
    {
        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('interviewer.dashboard'))
            ->assertOk()
            ->assertSee('صباح الخير، أليكس', false)
            ->assertSee('القادمة هذا الأسبوع', false)
            ->assertSee('عرض الجدول', false)
            ->assertDontSee('39 /', false);

        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('interviewer.schedule.index'))
            ->assertOk()
            ->assertSee('جدول مقابلاتي', false)
            ->assertSee('القائمة القادمة', false)
            ->assertSee('المقابلات السابقة', false);
    }

    public function test_future_interviewer_actions_are_honestly_disabled(): void
    {
        $this->get(route('interviewer.dashboard'))
            ->assertOk()
            ->assertSee('Prepare for interview', false)
            ->assertSee(route('interviewer.interviews.prepare', 'int-ns204-portfolio'), false)
            ->assertSee(route('interviewer.evaluations.create', 'scorecard-owen-ns205'), false);

        $this->get(route('interviewer.schedule.index'))
            ->assertOk()
            ->assertSee(route('interviewer.interviews.prepare', 'int-ns204-portfolio'), false)
            ->assertSee(__('interviewer.schedule.meeting_soon'), false);
    }

    public function test_existing_company_and_candidate_routes_remain(): void
    {
        $this->get(route('company.interviews.index'))->assertOk();
        $this->get(route('company.applicants.show', 'maya-chen'))->assertOk();
        $this->get(route('candidate.interviews.index'))->assertOk();
        $this->get(route('candidate.dashboard'))->assertOk();
        $this->get(route('home'))->assertOk();
    }
}
