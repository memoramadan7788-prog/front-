<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Models\CandidateAPI;
use App\Models\ExamAPI;
use App\Models\InterviewAPI;
use App\Models\OfferAPI;
use Mockery;
use Tests\TestCase;

class CandidatePipelinePagesTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_guest_is_redirected_from_pipeline_pages(): void
    {
        $this->get(route('frontend.candidate.applications.index'))
            ->assertRedirect(route('frontend.account.login'));
        $this->get(route('frontend.candidate.interviews.index'))
            ->assertRedirect(route('frontend.account.login'));
        $this->get(route('frontend.candidate.exams.index'))
            ->assertRedirect(route('frontend.account.login'));
        $this->get(route('frontend.candidate.offers.index'))
            ->assertRedirect(route('frontend.account.login'));
    }

    public function test_applications_empty_success_shows_contextual_empty_state(): void
    {
        $this->actingAsCandidate();
        $this->bind(CandidateAPI::class, 'getApplications', [
            'statusCode' => 200, 'data' => [], 'message' => 'OK', 'errors' => [],
        ]);

        $html = $this->get(route('frontend.candidate.applications.index'))->assertOk()->getContent();

        $this->assertStringContainsString(__('candidate.pipeline.applications_empty_title'), $html);
        $this->assertStringContainsString(__('candidate.pipeline.applications_empty_body'), $html);
        $this->assertStringContainsString(route('frontend.candidate.vacancies.index', [], false), $html);
        $this->assertStringNotContainsString(__('Nothing to show yet.'), $html);
        $this->assertStringNotContainsString(__('candidate.pipeline.load_error_title'), $html);
    }

    public function test_applications_with_records_preserve_actions(): void
    {
        $this->actingAsCandidate();
        $this->bind(CandidateAPI::class, 'getApplications', [
            'statusCode' => 200,
            'message' => 'OK',
            'errors' => [],
            'data' => [[
                'id' => 9,
                'status' => 'applied',
                'applied_at' => '2026-10-01T10:00:00+00:00',
                'job' => ['title' => 'Backend Engineer'],
            ]],
        ]);

        $html = $this->get(route('frontend.candidate.applications.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Backend Engineer', $html);
        $this->assertStringContainsString(route('frontend.candidate.applications.show', ['applicationId' => 9], false), $html);
        $this->assertStringContainsString(__('Withdraw'), $html);
        $this->assertStringNotContainsString(__('candidate.pipeline.applications_empty_title'), $html);
    }

    public function test_applications_api_failure_shows_error_not_empty_state(): void
    {
        $this->actingAsCandidate();
        $this->bind(CandidateAPI::class, 'getApplications', [
            'statusCode' => 503,
            'message' => 'Service unavailable',
            'errors' => [],
            'data' => null,
        ]);

        $html = $this->get(route('frontend.candidate.applications.index'))->assertOk()->getContent();

        $this->assertStringContainsString(__('candidate.pipeline.load_error_title'), $html);
        $this->assertStringContainsString('Service unavailable', $html);
        $this->assertStringNotContainsString(__('candidate.pipeline.applications_empty_title'), $html);
    }

    public function test_applications_unauthorized_clears_session_and_redirects_to_login(): void
    {
        $this->actingAsCandidate();
        $this->bind(CandidateAPI::class, 'getApplications', [
            'statusCode' => 401,
            'message' => 'Unauthenticated',
            'errors' => [],
            'data' => null,
        ]);

        $this->get(route('frontend.candidate.applications.index'))
            ->assertRedirect(route('frontend.account.login'));

        $this->assertFalse(ApiSession::check());
    }

    public function test_interviews_empty_success_shows_contextual_empty_state(): void
    {
        $this->actingAsCandidate();
        $this->bind(InterviewAPI::class, 'getInterviews', [
            'statusCode' => 200, 'data' => [], 'message' => 'OK', 'errors' => [],
        ]);

        $html = $this->get(route('frontend.candidate.interviews.index'))->assertOk()->getContent();

        $this->assertStringContainsString(__('candidate.pipeline.interviews_empty_title'), $html);
        $this->assertStringContainsString(route('frontend.candidate.applications.index', [], false), $html);
    }

    public function test_interviews_with_records_preserve_actions(): void
    {
        $this->actingAsCandidate();
        $this->bind(InterviewAPI::class, 'getInterviews', [
            'statusCode' => 200,
            'message' => 'OK',
            'errors' => [],
            'data' => [[
                'id' => 3,
                'application_id' => 9,
                'round_no' => 1,
                'interview_type' => 'video',
                'status' => 'scheduled',
                'scheduled_at' => '2026-10-20T15:00:00+00:00',
                'application' => ['job' => ['title' => 'Designer']],
            ]],
        ]);

        $html = $this->get(route('frontend.candidate.interviews.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Designer', $html);
        $this->assertStringContainsString(__('Reschedule'), $html);
        $this->assertStringNotContainsString(__('candidate.pipeline.interviews_empty_title'), $html);
    }

    public function test_interviews_forbidden_shows_error_state(): void
    {
        $this->actingAsCandidate();
        $this->bind(InterviewAPI::class, 'getInterviews', [
            'statusCode' => 403, 'message' => '', 'errors' => [], 'data' => null,
        ]);

        $html = $this->get(route('frontend.candidate.interviews.index'))->assertOk()->getContent();

        $this->assertStringContainsString(__('candidate.pipeline.forbidden'), $html);
        $this->assertStringNotContainsString(__('candidate.pipeline.interviews_empty_title'), $html);
    }

    public function test_assessments_stub_empty_is_not_treated_as_failure(): void
    {
        $this->actingAsCandidate();
        $this->bind(ExamAPI::class, 'getCandidateExams', [
            'statusCode' => 200, 'data' => [], 'message' => '', 'errors' => [],
        ]);

        $html = $this->get(route('frontend.candidate.exams.index'))->assertOk()->getContent();

        $this->assertStringContainsString(__('candidate.pipeline.assessments_empty_title'), $html);
        $this->assertStringContainsString(__('candidate.pipeline.assessments_unavailable_note'), $html);
        $this->assertStringNotContainsString(__('candidate.pipeline.load_error_title'), $html);
    }

    public function test_assessments_with_records_preserve_actions(): void
    {
        $this->actingAsCandidate();
        $this->bind(ExamAPI::class, 'getCandidateExams', [
            'statusCode' => 200,
            'message' => 'OK',
            'errors' => [],
            'data' => [[
                'id' => 1,
                'exam_id' => 4,
                'application_id' => 9,
                'status' => 'assigned',
                'title' => 'Logic Screen',
                'time_limit_min' => 30,
                'passing_score' => 70,
            ]],
        ]);

        $html = $this->get(route('frontend.candidate.exams.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Logic Screen', $html);
        $this->assertStringContainsString(__('Start'), $html);
    }

    public function test_assessments_server_error_shows_error_state(): void
    {
        $this->actingAsCandidate();
        $this->bind(ExamAPI::class, 'getCandidateExams', [
            'statusCode' => 500, 'message' => 'Boom', 'errors' => [], 'data' => null,
        ]);

        $html = $this->get(route('frontend.candidate.exams.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Boom', $html);
        $this->assertStringNotContainsString(__('candidate.pipeline.assessments_empty_title'), $html);
    }

    public function test_offers_empty_success_shows_contextual_empty_state(): void
    {
        $this->actingAsCandidate();
        $this->bind(OfferAPI::class, 'getOffers', [
            'statusCode' => 200, 'data' => [], 'message' => 'OK', 'errors' => [],
        ]);

        $html = $this->get(route('frontend.candidate.offers.index'))->assertOk()->getContent();

        $this->assertStringContainsString(__('candidate.pipeline.offers_empty_title'), $html);
        $this->assertStringContainsString(route('frontend.candidate.vacancies.index', [], false), $html);
        $this->assertStringContainsString(route('frontend.candidate.applications.index', [], false), $html);
    }

    public function test_offers_with_records_preserve_view_action(): void
    {
        $this->actingAsCandidate();
        $this->bind(OfferAPI::class, 'getOffers', [
            'statusCode' => 200,
            'message' => 'OK',
            'errors' => [],
            'data' => [[
                'id' => 5,
                'status' => 'pending',
                'salary_amount' => 100000,
                'currency' => 'USD',
                'salary_period' => 'year',
                'expires_at' => '2026-11-01',
                'application' => ['job' => ['title' => 'Lead Designer']],
            ]],
        ]);

        $html = $this->get(route('frontend.candidate.offers.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Lead Designer', $html);
        $this->assertStringContainsString(route('frontend.candidate.offers.show', ['offerId' => 5], false), $html);
    }

    public function test_offers_unauthorized_clears_session_and_redirects_to_login(): void
    {
        $this->actingAsCandidate();
        $this->bind(OfferAPI::class, 'getOffers', [
            'statusCode' => 401, 'message' => 'Unauthenticated', 'errors' => [], 'data' => null,
        ]);

        $this->get(route('frontend.candidate.offers.index'))
            ->assertRedirect(route('frontend.account.login'));
        $this->assertFalse(ApiSession::check());
    }

    public function test_pipeline_pages_keep_candidate_navigation(): void
    {
        $this->actingAsCandidate();
        $this->bind(CandidateAPI::class, 'getApplications', [
            'statusCode' => 200, 'data' => [], 'message' => 'OK', 'errors' => [],
        ]);

        $this->get(route('frontend.candidate.applications.index'))
            ->assertOk()
            ->assertSee('data-sidebar-nav-toggle', false)
            ->assertSee(__('navigation.candidate.applications'));
    }

    private function actingAsCandidate(): void
    {
        ApiSession::store('cand-token', [
            'id' => 11,
            'email' => 'candidate@example.com',
            'name' => 'Candidate',
            'account_type' => 'candidate',
            'roles' => ['candidate'],
        ]);
    }

    /**
     * @param  class-string  $class
     * @param  array<string, mixed>  $response
     */
    private function bind(string $class, string $method, array $response): void
    {
        $api = Mockery::mock($class);
        $api->shouldReceive($method)->once()->andReturn($response);
        $this->app->instance($class, $api);
    }
}
