<?php

namespace Tests\Feature;

use App\Http\Middleware\SetFrontendLocale;
use App\Support\Frontend\MockCompanyData;
use App\Support\Frontend\MockInterviewerData;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class InterviewerInterviewScreensTest extends TestCase
{
    public function test_preparation_renders_maya_frame_forty_one_structure(): void
    {
        $this->get(route('interviewer.interviews.prepare', 'int-ns204-portfolio'))
            ->assertOk()
            ->assertSee('Prepare to meet Maya Chen', false)
            ->assertSee('NS-204', false)
            ->assertSee('int-ns204-portfolio', false)
            ->assertSee('Open interview workspace', false)
            ->assertSee(route('interviewer.interviews.live', 'int-ns204-portfolio'), false)
            ->assertSee('Candidate brief', false)
            ->assertSee('Competency focus', false)
            ->assertSee('Role checklist', false)
            ->assertSee('Preparation notes', false)
            ->assertSee('data-interviewer-prepare', false)
            ->assertSee('data-prep-checklist-item', false)
            ->assertDontSee('Frame 41', false)
            ->assertDontSee('Frontend UI only', false)
            ->assertDontSee('Journey-state', false);
    }

    public function test_live_renders_maya_frame_forty_two_structure(): void
    {
        $this->get(route('interviewer.interviews.live', 'int-ns204-portfolio'))
            ->assertOk()
            ->assertSee('Portfolio interview · Maya Chen', false)
            ->assertSee('NS-204', false)
            ->assertSee('int-ns204-portfolio', false)
            ->assertSee('Alex Morgan', false)
            ->assertSee('Agenda progress', false)
            ->assertSee('Question bank', false)
            ->assertSee('Structured notes', false)
            ->assertSee('Save draft', false)
            ->assertSee('Continue to scorecard', false)
            ->assertSee('data-interviewer-live', false)
            ->assertSee('data-live-timer', false)
            ->assertSee(route('interviewer.interviews.prepare', 'int-ns204-portfolio'), false)
            ->assertDontSee('Frame 42', false)
            ->assertDontSee('Frontend UI only', false);
    }

    public function test_luis_and_aisha_preparation_use_distinct_identity(): void
    {
        $luis = $this->get(route('interviewer.interviews.prepare', 'int-ns204-luis-team'));
        $luis->assertOk()
            ->assertSee('Luis Romero', false)
            ->assertSee('int-ns204-luis-team', false)
            ->assertSee('NS-204', false)
            ->assertSee('data-interview-id="int-ns204-luis-team"', false)
            ->assertDontSee('data-interview-id="int-ns204-portfolio"', false);

        $aisha = $this->get(route('interviewer.interviews.prepare', 'int-ns204-aisha-portfolio'));
        $aisha->assertOk()
            ->assertSee('Aisha Patel', false)
            ->assertSee('int-ns204-aisha-portfolio', false)
            ->assertSee('data-interview-id="int-ns204-aisha-portfolio"', false)
            ->assertDontSee('data-interview-id="int-ns204-portfolio"', false);
    }

    public function test_unknown_interview_returns_not_found(): void
    {
        $this->get(route('interviewer.interviews.prepare', 'int-does-not-exist'))
            ->assertNotFound();
        $this->get(route('interviewer.interviews.live', 'int-does-not-exist'))
            ->assertNotFound();
    }

    public function test_named_routes_and_sidebar_preparation_are_active(): void
    {
        $this->assertTrue(Route::has('interviewer.interviews.prepare'));
        $this->assertTrue(Route::has('interviewer.interviews.live'));

        $dashboard = $this->get(route('interviewer.dashboard'));
        $dashboard->assertOk();
        $dashboard->assertSee(route('interviewer.interviews.prepare', 'int-ns204-portfolio'), false);
        $dashboard->assertDontSee(__('interviewer.dashboard.prepare_soon'), false);

        $sidebar = $this->get(route('interviewer.interviews.prepare', 'int-ns204-portfolio'));
        $sidebar->assertOk();
        $sidebar->assertSee(route('interviewer.interviews.prepare', 'int-ns204-portfolio'), false);
        $sidebar->assertSee(__('navigation.interviewer.scorecards'), false);
        $sidebar->assertSee(route('interviewer.evaluations.index'), false);
    }

    public function test_arabic_preparation_and_live_render(): void
    {
        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('interviewer.interviews.prepare', 'int-ns204-portfolio'))
            ->assertOk()
            ->assertSee('التحضير للقاء', false)
            ->assertSee('ملخص المرشح', false)
            ->assertSee('قائمة الدور', false);

        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('interviewer.interviews.live', 'int-ns204-portfolio'))
            ->assertOk()
            ->assertSee('ملاحظات منظّمة', false)
            ->assertSee('بنك الأسئلة', false);
    }

    public function test_mock_preparation_aligns_with_company_interview_records(): void
    {
        $mayaCompany = MockCompanyData::interview('int-ns204-portfolio');
        $mayaPrep = MockInterviewerData::preparation('int-ns204-portfolio');

        $this->assertNotNull($mayaCompany);
        $this->assertNotNull($mayaPrep);
        $this->assertSame('Maya Chen', $mayaPrep['interview']['applicant']);
        $this->assertSame('Alex Morgan', $mayaPrep['interview']['interviewer']);
        $this->assertSame($mayaCompany['day_key'], $mayaPrep['interview']['day_key']);
        $this->assertSame('maya-chen', $mayaPrep['interview']['applicant_id']);

        $aisha = MockInterviewerData::preparation('int-ns204-aisha-portfolio');
        $this->assertNotNull($aisha);
        $this->assertSame('Alex Morgan', $aisha['interview']['interviewer']);
    }

    public function test_scorecard_cta_navigates_from_live(): void
    {
        $this->get(route('interviewer.interviews.live', 'int-ns204-portfolio'))
            ->assertOk()
            ->assertSee('Continue to scorecard', false)
            ->assertSee(route('interviewer.evaluations.create', 'int-ns204-portfolio'), false);

        $this->assertTrue(Route::has('interviewer.evaluations.create'));
        $this->assertFalse(Route::has('interviewer.scorecards.index'));
    }
}
