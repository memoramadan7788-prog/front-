<?php

namespace Tests\Feature;

use App\Http\Middleware\SetFrontendLocale;
use App\Support\Frontend\MockInterviewerData;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class InterviewerEvaluationScreensTest extends TestCase
{
    public function test_evaluations_index_lists_assigned_and_overdue(): void
    {
        $this->get(route('interviewer.evaluations.index'))
            ->assertOk()
            ->assertSee('Scorecards', false)
            ->assertSee('Owen Clarke', false)
            ->assertSee('Maya Chen', false)
            ->assertSee('Luis Romero', false)
            ->assertSee('Aisha Patel', false)
            ->assertSee(route('interviewer.evaluations.create', 'int-ns204-portfolio'), false)
            ->assertSee(route('interviewer.evaluations.create', 'scorecard-owen-ns205'), false)
            ->assertDontSee('Frame 43', false)
            ->assertDontSee('Journey-state', false);
    }

    public function test_maya_scorecard_matches_frame_forty_three_structure(): void
    {
        $this->get(route('interviewer.evaluations.create', 'int-ns204-portfolio'))
            ->assertOk()
            ->assertSee('Submit scorecard · Maya Chen', false)
            ->assertSee('NS-204', false)
            ->assertSee('int-ns204-portfolio', false)
            ->assertSee('Competency ratings', false)
            ->assertSee('Problem framing', false)
            ->assertSee('Recommendation & evidence')
            ->assertSee('Save draft', false)
            ->assertSee('Submit scorecard', false)
            ->assertSee('data-interviewer-scorecard', false)
            ->assertSee(route('interviewer.interviews.live', 'int-ns204-portfolio'), false)
            ->assertDontSee('Frontend UI only', false)
            ->assertDontSee('43 /', false);
    }

    public function test_unknown_scorecard_returns_not_found(): void
    {
        $this->get(route('interviewer.evaluations.create', 'int-missing'))
            ->assertNotFound();
    }

    public function test_live_continue_links_to_scorecard(): void
    {
        $this->get(route('interviewer.interviews.live', 'int-ns204-portfolio'))
            ->assertOk()
            ->assertSee(route('interviewer.evaluations.create', 'int-ns204-portfolio'), false)
            ->assertDontSee(__('interviewer.live.scorecard_soon'), false);
    }

    public function test_dashboard_feedback_links_to_owen_scorecard(): void
    {
        $this->get(route('interviewer.dashboard'))
            ->assertOk()
            ->assertSee(route('interviewer.evaluations.create', 'scorecard-owen-ns205'), false)
            ->assertSee(route('interviewer.evaluations.index'), false)
            ->assertSee(__('interviewer.dashboard.checklist_heading'), false)
            ->assertSee(__('interviewer.dashboard.checklist_purpose'), false);
    }

    public function test_sidebar_scorecards_route_is_active(): void
    {
        $this->assertTrue(Route::has('interviewer.evaluations.index'));
        $this->assertTrue(Route::has('interviewer.evaluations.create'));

        $this->get(route('interviewer.evaluations.index'))
            ->assertOk()
            ->assertSee(route('interviewer.evaluations.index'), false)
            ->assertSee(__('navigation.interviewer.scorecards'), false);
    }

    public function test_arabic_scorecard_screens_render(): void
    {
        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('interviewer.evaluations.index'))
            ->assertOk()
            ->assertSee('بطاقات التقييم', false);

        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('interviewer.evaluations.create', 'int-ns204-portfolio'))
            ->assertOk()
            ->assertSee('إرسال بطاقة التقييم', false)
            ->assertSee('تقييمات الكفاءات', false);
    }

    public function test_scorecard_mock_aligns_with_interview_identity(): void
    {
        $maya = MockInterviewerData::scorecard('int-ns204-portfolio');
        $this->assertNotNull($maya);
        $this->assertSame('Maya Chen', $maya['interview']['applicant']);
        $this->assertSame('Alex Morgan', $maya['interview']['interviewer']);
        $this->assertSame('hb_iv_int-ns204-portfolio_scorecard_v1', $maya['storage_key']);

        $luis = MockInterviewerData::scorecard('int-ns204-luis-team');
        $this->assertNotNull($luis);
        $this->assertSame('Luis Romero', $luis['interview']['applicant']);
        $this->assertNotSame($maya['storage_key'], $luis['storage_key']);
    }

    public function test_maya_preparation_exposes_shared_session_checklist_key(): void
    {
        $prep = MockInterviewerData::preparation('int-ns204-portfolio');
        $this->assertNotNull($prep);
        $this->assertSame('hb_iv_int-ns204-portfolio_session_v1', $prep['session_checklist_key']);
        $this->assertNotEmpty($prep['session_checklist']);
        $this->assertNotEmpty($prep['role_checklist']);

        $luis = MockInterviewerData::preparation('int-ns204-luis-team');
        $this->assertNotNull($luis);
        $this->assertSame([], $luis['session_checklist']);
    }
}
