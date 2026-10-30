<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Models\InterviewerAPI;
use Mockery;
use Tests\TestCase;

class InterviewerLiveWorkspaceTest extends TestCase
{
    private $api;

    protected function setUp(): void
    {
        parent::setUp();

        $this->api = Mockery::mock(InterviewerAPI::class);
        $this->app->instance(InterviewerAPI::class, $this->api);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_guest_is_redirected_from_live_workspace(): void
    {
        $this->get(route('frontend.interviewer.interviews.live', ['interviewId' => 42]))
            ->assertRedirect(route('frontend.account.login'));
    }

    public function test_candidate_cannot_open_live_workspace(): void
    {
        $this->actingAsCandidate();

        $this->get(route('frontend.interviewer.interviews.live', ['interviewId' => 42]))
            ->assertRedirect();
    }

    public function test_company_employee_without_assignment_sees_forbidden_state(): void
    {
        $this->actingAsCompanyAdmin();
        $this->api->shouldReceive('getInterview')->once()->with(99)->andReturn([
            'statusCode' => 403,
            'message' => 'You are not assigned to this interview panel.',
            'data' => null,
        ]);

        $html = $this->get(route('frontend.interviewer.interviews.live', ['interviewId' => 99]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-unauthorized="1"', $html);
        $this->assertStringContainsString(__('interviewer.live.forbidden'), $html);
        $this->assertStringNotContainsString('data-live-timer', $html);
    }

    public function test_assigned_interviewer_sees_workspace_context(): void
    {
        $this->actingAsInterviewer();
        $this->api->shouldReceive('getInterview')->once()->with(42)->andReturn([
            'statusCode' => 200,
            'message' => 'OK',
            'data' => $this->interviewPayload(),
        ]);

        $html = $this->get(route('frontend.interviewer.interviews.live', ['interviewId' => 42]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-live-workspace', $html);
        $this->assertStringContainsString('data-mode="workspace"', $html);
        $this->assertStringContainsString('Amina Hassan', $html);
        $this->assertStringContainsString('online', $html);
        $this->assertStringContainsString('Dr. Hisham', $html);
        $this->assertStringContainsString('2026-11-05', $html);
        $this->assertStringContainsString(__('interviewer.live.job_unavailable'), $html);
        $this->assertStringNotContainsString('amina@example.com', $html);
        $this->assertStringNotContainsString('+20100', $html);
    }

    public function test_cross_company_or_foreign_id_returns_not_found_or_forbidden(): void
    {
        $this->actingAsInterviewer();
        $this->api->shouldReceive('getInterview')->once()->with(777)->andReturn([
            'statusCode' => 404,
            'message' => 'Not found',
            'data' => null,
        ]);

        $html = $this->get(route('frontend.interviewer.interviews.live', ['interviewId' => 777]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-mode="not_found"', $html);
        $this->assertStringContainsString(__('interviewer.live.not_found'), $html);
    }

    public function test_timer_is_labeled_local_only(): void
    {
        $this->actingAsInterviewer();
        $this->mockHappyInterview();

        $html = $this->get(route('frontend.interviewer.interviews.live', ['interviewId' => 42]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-timer-mode="local"', $html);
        $this->assertStringContainsString(__('interviewer.live.timer_footnote'), $html);
        $this->assertStringContainsString('data-timer-start', $html);
        $this->assertStringContainsString('data-timer-pause', $html);
    }

    public function test_agenda_is_local_generic_guide(): void
    {
        $this->actingAsInterviewer();
        $this->mockHappyInterview();

        $html = $this->get(route('frontend.interviewer.interviews.live', ['interviewId' => 42]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-agenda-mode="local"', $html);
        $this->assertStringContainsString(__('interviewer.live.agenda_local_note'), $html);
        $this->assertStringContainsString(__('interviewer.live.agenda_intro'), $html);
        $this->assertStringContainsString(__('interviewer.live.agenda_technical'), $html);
    }

    public function test_question_bank_unavailable_shows_local_prompts_only(): void
    {
        $this->actingAsInterviewer();
        $this->mockHappyInterview();

        $html = $this->get(route('frontend.interviewer.interviews.live', ['interviewId' => 42]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-questions-mode="local"', $html);
        $this->assertStringContainsString(__('interviewer.live.questions_unavailable_api'), $html);
        $this->assertStringContainsString(__('interviewer.live.questions_not_persisted'), $html);
        $this->assertStringContainsString(__('interviewer.live.q_problem'), $html);
    }

    public function test_notes_draft_save_is_blocked_and_not_server_claimed(): void
    {
        $this->actingAsInterviewer();
        $this->mockHappyInterview();
        $this->api->shouldReceive('submitEvaluation')->never();

        $html = $this->get(route('frontend.interviewer.interviews.live', ['interviewId' => 42]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-draft-blocked', $html);
        $this->assertStringContainsString('data-save-draft-blocked', $html);
        $this->assertStringContainsString(__('interviewer.live.save_draft_blocked'), $html);
        $this->assertStringContainsString(__('interviewer.live.notes_no_draft_api'), $html);
        $this->assertStringNotContainsString('interviewer-notes-draft', $html);
        $this->assertStringNotContainsString('localStorage.setItem', $html);
        $this->assertStringContainsString(__('interviewer.live.notes_footnote'), $html);
    }

    public function test_scorecard_is_separate_from_live_notes(): void
    {
        $this->actingAsInterviewer();
        $this->mockHappyInterview();

        $html = $this->get(route('frontend.interviewer.interviews.live', ['interviewId' => 42]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(
            route('frontend.interviewer.interviews.evaluation', ['interviewId' => 42], absolute: false),
            $html
        );
        $this->assertStringContainsString(__('interviewer.live.scorecard_separate'), $html);
    }

    public function test_api_error_shows_safe_message(): void
    {
        $this->actingAsInterviewer();
        $this->api->shouldReceive('getInterview')->once()->with(42)->andReturn([
            'statusCode' => 500,
            'message' => 'SQLSTATE boom secrets',
            'data' => null,
        ]);

        $html = $this->get(route('frontend.interviewer.interviews.live', ['interviewId' => 42]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-mode="error"', $html);
        $this->assertStringNotContainsString('SQLSTATE', $html);
    }

    public function test_empty_context_still_renders_workspace_shell_when_payload_minimal(): void
    {
        $this->actingAsInterviewer();
        $this->api->shouldReceive('getInterview')->once()->with(42)->andReturn([
            'statusCode' => 200,
            'message' => 'OK',
            'data' => ['id' => 42, 'status' => 'scheduled', 'interview_type' => 'exam', 'bookings' => []],
        ]);

        $html = $this->get(route('frontend.interviewer.interviews.live', ['interviewId' => 42]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-mode="workspace"', $html);
        $this->assertStringContainsString(__('interviewer.live.job_unavailable'), $html);
    }

    public function test_arabic_labels_render(): void
    {
        app()->setLocale('ar');
        $this->actingAsInterviewer();
        $this->mockHappyInterview();

        $html = $this->get(route('frontend.interviewer.interviews.live', ['interviewId' => 42]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('interviewer.live.title'), $html);
        $this->assertStringContainsString(__('interviewer.live.local_only_badge'), $html);
    }

    public function test_responsive_sections_present_for_mobile_layout(): void
    {
        $this->actingAsInterviewer();
        $this->mockHappyInterview();

        $html = $this->get(route('frontend.interviewer.interviews.live', ['interviewId' => 42]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('lg:grid-cols-2', $html);
        $this->assertStringContainsString('data-live-notes', $html);
        $this->assertStringContainsString('data-live-questions', $html);
    }

    public function test_assigned_list_links_to_live_workspace(): void
    {
        $this->actingAsInterviewer();
        $this->api->shouldReceive('getInterviews')->once()->andReturn([
            'statusCode' => 200,
            'message' => 'OK',
            'data' => [['id' => 42, 'status' => 'scheduled', 'candidate' => ['name' => 'Amina Hassan']]],
        ]);

        $html = $this->get(route('frontend.interviewer.interviews.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(
            route('frontend.interviewer.interviews.live', ['interviewId' => 42], absolute: false),
            $html
        );
    }

    public function test_no_live_mutations_endpoint_on_workspace_get(): void
    {
        $this->actingAsInterviewer();
        $this->mockHappyInterview();
        $this->api->shouldReceive('submitEvaluation')->never();
        $this->api->shouldReceive('addAvailability')->never();

        $this->get(route('frontend.interviewer.interviews.live', ['interviewId' => 42]))
            ->assertOk();
    }

    private function actingAsInterviewer(): void
    {
        ApiSession::store('interviewer-token', [
            'id' => 77,
            'email' => 'interviewer@example.com',
            'name' => 'Dr. Hisham',
            'account_type' => 'company',
            'roles' => ['interviewer'],
            'company' => ['id' => 5, 'name' => 'Acme'],
        ]);
    }

    private function actingAsCompanyAdmin(): void
    {
        ApiSession::store('co-token', [
            'id' => 20,
            'email' => 'hr@example.com',
            'name' => 'HR User',
            'account_type' => 'company',
            'roles' => ['company_admin'],
            'company' => ['id' => 5, 'name' => 'Acme'],
        ]);
    }

    private function actingAsCandidate(): void
    {
        ApiSession::store('cand-token', [
            'id' => 11,
            'email' => 'candidate@example.com',
            'name' => 'Candidate User',
            'account_type' => 'candidate',
            'roles' => ['candidate'],
        ]);
    }

    private function mockHappyInterview(): void
    {
        $this->api->shouldReceive('getInterview')->byDefault()->with(42)->andReturn([
            'statusCode' => 200,
            'message' => 'OK',
            'data' => $this->interviewPayload(),
        ]);
    }

    /**
     * Mirrors InterviewerInterviewResource shape (no job title field).
     *
     * @return array<string, mixed>
     */
    private function interviewPayload(): array
    {
        return [
            'id' => 42,
            'application_id' => 9,
            'round_no' => 1,
            'interview_type' => 'online',
            'status' => 'scheduled',
            'location' => null,
            'meeting_link' => null,
            'instructions' => null,
            'candidate' => [
                'name' => 'Amina Hassan',
                'email' => 'amina@example.com',
                'phone' => '+20100',
                'headline' => 'Backend Engineer',
                'cv' => null,
            ],
            'bookings' => [[
                'id' => 1,
                'interviewer_id' => 77,
                'availability_id' => 501,
                'start_at' => '2026-11-05T10:00:00+00:00',
                'end_at' => '2026-11-05T10:45:00+00:00',
                'booking_status' => 'booked',
                'interviewer' => [
                    'id' => 77,
                    'name' => 'Dr. Hisham',
                    'email' => 'interviewer@example.com',
                ],
            ]],
            'evaluations' => [],
        ];
    }
}
