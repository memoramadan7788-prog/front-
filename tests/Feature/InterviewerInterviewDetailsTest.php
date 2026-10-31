<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Models\InterviewerAPI;
use Mockery;
use Tests\TestCase;

class InterviewerInterviewDetailsTest extends TestCase
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

    public function test_guest_is_redirected_from_interview_details(): void
    {
        $this->get(route('frontend.interviewer.interviews.show', ['interviewId' => 42]))
            ->assertRedirect(route('frontend.account.login'));
    }

    public function test_candidate_cannot_open_interviewer_details(): void
    {
        $this->actingAsCandidate();

        $this->get(route('frontend.interviewer.interviews.show', ['interviewId' => 42]))
            ->assertRedirect();
    }

    public function test_assigned_interviewer_sees_api_fields_and_all_detail_sections(): void
    {
        $this->actingAsInterviewer();
        $this->api->shouldReceive('getInterview')->once()->with(42)->andReturn([
            'statusCode' => 200,
            'message' => 'OK',
            'data' => $this->interviewPayload(),
        ]);

        $html = $this->get(route('frontend.interviewer.interviews.show', ['interviewId' => 42]))
            ->assertOk()
            ->getContent();

        foreach ([
            __('interviewer.details.prepare_heading'),
            __('interviewer.details.interviewer_heading'),
            __('interviewer.details.candidate_brief_heading'),
            __('interviewer.details.competencies_heading'),
            __('interviewer.details.prompts_heading'),
            __('interviewer.details.agenda_heading'),
            __('interviewer.details.scoped_heading'),
        ] as $heading) {
            $this->assertStringContainsString(e($heading), $html);
        }

        $this->assertStringContainsString('Amina Hassan', $html);
        $this->assertStringContainsString('Backend Engineer', $html);
        $this->assertStringContainsString('Dr. Hisham', $html);
        $this->assertStringContainsString('2026-11-05', $html);
        $this->assertStringContainsString(__('interviewer.details.competencies_unavailable'), $html);
        $this->assertStringContainsString(__('interviewer.details.agenda_unavailable'), $html);
        $this->assertStringNotContainsString('amina@example.com', $html);
        $this->assertStringNotContainsString('+20100', $html);
    }

    public function test_missing_interview_data_shows_unavailable_states_without_fabrication(): void
    {
        $this->actingAsInterviewer();
        $this->api->shouldReceive('getInterview')->once()->with(42)->andReturn([
            'statusCode' => 200,
            'message' => 'OK',
            'data' => ['id' => 42, 'status' => 'scheduled', 'bookings' => []],
        ]);

        $html = $this->get(route('frontend.interviewer.interviews.show', ['interviewId' => 42]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('interviewer.details.candidate_brief_empty'), $html);
        $this->assertStringContainsString(__('interviewer.details.no_assigned_interviewers'), $html);
        $this->assertStringContainsString(__('interviewer.details.no_instructions'), $html);
        $this->assertStringNotContainsString('45 minutes', $html);
    }

    public function test_cancelled_booking_is_not_shown_as_an_assigned_interviewer(): void
    {
        $this->actingAsInterviewer();
        $payload = $this->interviewPayload();
        $payload['bookings'][0]['booking_status'] = 'cancelled';
        $this->api->shouldReceive('getInterview')->once()->with(42)->andReturn([
            'statusCode' => 200,
            'message' => 'OK',
            'data' => $payload,
        ]);

        $this->get(route('frontend.interviewer.interviews.show', ['interviewId' => 42]))
            ->assertOk()
            ->assertSee(__('interviewer.details.no_assigned_interviewers'))
            ->assertDontSee('2026-11-05');
    }

    public function test_forbidden_response_displays_access_state(): void
    {
        $this->actingAsInterviewer();
        $this->api->shouldReceive('getInterview')->once()->with(99)->andReturn([
            'statusCode' => 403,
            'message' => 'Forbidden',
            'data' => null,
        ]);

        $this->get(route('frontend.interviewer.interviews.show', ['interviewId' => 99]))
            ->assertOk()
            ->assertSee(__('interviewer.details.forbidden'))
            ->assertSee('data-state="forbidden"', false);
    }

    public function test_not_found_error_session_expiry_and_network_error_are_handled(): void
    {
        $this->actingAsInterviewer();
        $this->api->shouldReceive('getInterview')->once()->with(404)->andReturn([
            'statusCode' => 404,
            'message' => 'Not found',
            'data' => null,
        ]);
        $this->get(route('frontend.interviewer.interviews.show', ['interviewId' => 404]))->assertNotFound();

        $this->api->shouldReceive('getInterview')->once()->with(43)->andReturn([
            'statusCode' => 500,
            'message' => 'SQLSTATE secrets',
            'data' => null,
        ]);
        $this->get(route('frontend.interviewer.interviews.show', ['interviewId' => 43]))
            ->assertOk()
            ->assertSee(__('interviewer.details.load_error'))
            ->assertDontSee('SQLSTATE');

        $this->api->shouldReceive('getInterview')->once()->with(44)->andReturn([
            'statusCode' => 401,
            'message' => 'Unauthenticated',
            'data' => null,
        ]);
        $this->get(route('frontend.interviewer.interviews.show', ['interviewId' => 44]))
            ->assertRedirect(route('frontend.account.login'));
        $this->assertNull(ApiSession::token());
    }

    public function test_arabic_labels_render(): void
    {
        app()->setLocale('ar');
        $this->actingAsInterviewer();
        $this->api->shouldReceive('getInterview')->once()->with(42)->andReturn([
            'statusCode' => 200,
            'message' => 'OK',
            'data' => $this->interviewPayload(),
        ]);

        $this->get(route('frontend.interviewer.interviews.show', ['interviewId' => 42]))
            ->assertOk()
            ->assertSee(__('interviewer.details.prepare_heading'))
            ->assertSee(__('interviewer.details.scoped_heading'));
    }

    private function actingAsInterviewer(): void
    {
        ApiSession::store('interviewer-token', [
            'id' => 77,
            'email' => 'interviewer@example.test',
            'name' => 'Dr. Hisham',
            'account_type' => 'company',
            'roles' => ['interviewer'],
            'company' => ['id' => 5],
        ]);
    }

    private function actingAsCandidate(): void
    {
        ApiSession::store('candidate-token', [
            'id' => 11,
            'email' => 'candidate@example.test',
            'name' => 'Candidate User',
            'account_type' => 'candidate',
            'roles' => ['candidate'],
        ]);
    }

    /** @return array<string, mixed> */
    private function interviewPayload(): array
    {
        return [
            'id' => 42,
            'application_id' => 9,
            'round_no' => 1,
            'interview_type' => 'online',
            'status' => 'scheduled',
            'location' => 'Remote',
            'meeting_link' => 'https://meet.example.test/room/42',
            'instructions' => 'Review the returned role and candidate details.',
            'candidate' => [
                'name' => 'Amina Hassan',
                'email' => 'amina@example.com',
                'phone' => '+20100',
                'headline' => 'Backend Engineer',
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
        ];
    }
}
