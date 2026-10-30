<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Models\AdminAPI;
use App\Models\CandidateAPI;
use App\Models\CompanyAPI;
use App\Models\InterviewerAPI;
use App\Models\OfferAPI;
use Mockery;
use Tests\TestCase;

class HiringWorkspaceModulesTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_interviewer_scorecard_loads_only_after_scoped_interview_lookup(): void
    {
        $this->actingAsInterviewer();

        $api = Mockery::mock(InterviewerAPI::class);
        $api->shouldReceive('getInterview')->once()->with('42')->andReturn([
            'message' => 'OK',
            'data' => ['id' => 42, 'job' => ['title' => 'Product Designer'], 'candidate' => ['name' => 'Riley Candidate']],
            'statusCode' => 200,
        ]);
        $api->shouldNotReceive('submitEvaluation');
        $this->app->instance(InterviewerAPI::class, $api);

        $this->get(route('frontend.interviewer.interviews.evaluation', ['interviewId' => 42]))
            ->assertOk()
            ->assertSee('Product Designer')
            ->assertSee('Riley Candidate')
            ->assertSee(__('Save draft on this device'))
            ->assertSee(__('Submit scorecard'));
    }

    public function test_interviewer_cannot_submit_for_an_interview_outside_their_scope(): void
    {
        $this->actingAsInterviewer();

        $api = Mockery::mock(InterviewerAPI::class);
        $api->shouldReceive('getInterview')->once()->with('404')->andReturn([
            'message' => 'Not found',
            'data' => null,
            'statusCode' => 404,
        ]);
        $api->shouldNotReceive('submitEvaluation');
        $this->app->instance(InterviewerAPI::class, $api);

        $this->post(route('frontend.interviewer.interviews.evaluation.submit', ['interviewId' => 404]), [
            'ratings' => [
                'technical' => '4',
                'communication' => '4',
                'problem_solving' => '4',
                'collaboration' => '4',
                'role_fit' => '4',
            ],
            'strengths' => 'Clear technical reasoning.',
            'concerns' => 'No significant concerns.',
            'recommendation' => 'yes',
        ])->assertNotFound();
    }

    public function test_interviewer_scorecard_validates_ratings_before_submission(): void
    {
        $this->actingAsInterviewer();

        $api = Mockery::mock(InterviewerAPI::class);
        $api->shouldReceive('getInterview')->once()->with('42')->andReturn([
            'message' => 'OK',
            'data' => ['id' => 42],
            'statusCode' => 200,
        ]);
        $api->shouldNotReceive('submitEvaluation');
        $this->app->instance(InterviewerAPI::class, $api);

        $this->from(route('frontend.interviewer.interviews.evaluation', ['interviewId' => 42]))
            ->post(route('frontend.interviewer.interviews.evaluation.submit', ['interviewId' => 42]), [
                'ratings' => [
                    'technical' => 'no',
                    'communication' => 'no',
                    'problem_solving' => 'no',
                    'collaboration' => 'no',
                    'role_fit' => 'no',
                ],
                'strengths' => 'Clear technical reasoning.',
                'concerns' => 'No significant concerns.',
                'recommendation' => 'yes',
            ])
            ->assertRedirect(route('frontend.interviewer.interviews.evaluation', ['interviewId' => 42]))
            ->assertSessionHasErrors('ratings');
    }

    public function test_interviewer_scorecard_maps_rubric_to_the_supported_api_payload(): void
    {
        $this->actingAsInterviewer();

        $api = Mockery::mock(InterviewerAPI::class);
        $api->shouldReceive('getInterview')->once()->with('42')->andReturn([
            'message' => 'OK',
            'data' => ['id' => 42],
            'statusCode' => 200,
        ]);
        $api->shouldReceive('submitEvaluation')->once()->with('42', Mockery::on(function (array $payload): bool {
            return $payload['numeric_score'] === 80
                && str_contains($payload['internal_notes'], 'Technical: 4')
                && str_contains($payload['internal_notes'], 'Strengths: Clear technical reasoning.')
                && str_contains($payload['internal_notes'], 'Overall recommendation: Yes')
                && $payload['candidate_feedback'] === 'Thank you for your time.';
        }))->andReturn(['message' => 'Submitted', 'statusCode' => 200]);
        $this->app->instance(InterviewerAPI::class, $api);

        $this->post(route('frontend.interviewer.interviews.evaluation.submit', ['interviewId' => 42]), [
            'ratings' => [
                'technical' => '4',
                'communication' => '3',
                'problem_solving' => '5',
                'collaboration' => 'no',
                'role_fit' => '4',
            ],
            'strengths' => 'Clear technical reasoning.',
            'concerns' => 'No significant concerns.',
            'recommendation' => 'yes',
            'candidate_feedback' => 'Thank you for your time.',
        ])->assertRedirect(route('frontend.interviewer.interviews.evaluation', ['interviewId' => 42]));
    }

    public function test_company_analytics_shows_verified_job_data_and_unavailable_metrics(): void
    {
        $this->actingAsCompany();

        $api = Mockery::mock(CompanyAPI::class);
        $api->shouldReceive('getVacancies')->once()->andReturn([
            'message' => 'OK',
            'data' => [
                ['id' => 1, 'title' => 'Engineer', 'status' => 'published', 'location' => 'Remote', 'created_at' => '2026-10-12'],
                ['id' => 2, 'title' => 'Designer', 'status' => 'draft', 'location' => 'Cairo', 'created_at' => '2026-10-14'],
            ],
            'statusCode' => 200,
        ]);
        $this->app->instance(CompanyAPI::class, $api);

        $this->get(route('frontend.company.analytics'))
            ->assertOk()
            ->assertSee(__('Hiring analytics'))
            ->assertSee('Engineer')
            ->assertSee('Applicant stage distribution')
            ->assertSee('Source performance data is not available');
    }

    public function test_company_analytics_filters_jobs_and_validates_reversed_date_ranges(): void
    {
        $this->actingAsCompany();

        $api = Mockery::mock(CompanyAPI::class);
        $api->shouldReceive('getVacancies')->once()->andReturn([
            'message' => 'OK',
            'data' => [
                ['id' => 1, 'title' => 'Earlier Role', 'status' => 'published', 'created_at' => '2026-10-10'],
                ['id' => 2, 'title' => 'In Range Role', 'status' => 'draft', 'created_at' => '2026-10-15'],
            ],
            'statusCode' => 200,
        ]);
        $this->app->instance(CompanyAPI::class, $api);

        $this->get(route('frontend.company.analytics', ['from' => '2026-10-12', 'to' => '2026-10-20']))
            ->assertOk()
            ->assertSee('In Range Role')
            ->assertDontSee('Earlier Role');

        $this->get(route('frontend.company.analytics', ['from' => '2026-10-20', 'to' => '2026-10-12']))
            ->assertRedirect()
            ->assertSessionHasErrors('to');
    }

    public function test_company_job_overview_uses_live_job_and_application_rows(): void
    {
        $this->actingAsCompany();

        $api = Mockery::mock(CompanyAPI::class);
        $api->shouldReceive('getVacancies')->once()->andReturn([
            'message' => 'OK',
            'data' => [['id' => 15, 'title' => 'Platform Engineer', 'status' => 'published', 'description' => 'Own our platform.']],
            'statusCode' => 200,
        ]);
        $api->shouldReceive('getApplications')->once()->with('15')->andReturn([
            'message' => 'OK',
            'data' => [
                ['id' => 31, 'status' => 'screening', 'candidate_name' => 'Jordan', 'source' => 'Referral'],
                ['id' => 32, 'status' => 'submitted', 'source' => ['name' => 'Web']],
            ],
            'statusCode' => 200,
        ]);
        $this->app->instance(CompanyAPI::class, $api);

        $this->get(route('frontend.company.vacancies.overview', ['vacancyId' => 15]))
            ->assertOk()
            ->assertSee('Platform Engineer')
            ->assertSee('Applicant pipeline')
            ->assertSee('Jordan')
            ->assertSee('Referral')
            ->assertSee(__('company.applicants.col_name'))
            ->assertSee('data-job-tab="publication"', false);
    }

    public function test_company_interview_calendar_is_explicitly_a_browser_local_planning_view(): void
    {
        $this->actingAsCompany();

        $api = Mockery::mock(CompanyAPI::class);
        $api->shouldNotReceive('getVacancies');
        $api->shouldNotReceive('getApplications');
        $this->app->instance(CompanyAPI::class, $api);

        $this->get(route('frontend.company.interview-calendar'))
            ->assertOk()
            ->assertSee(__('Local planning view'))
            ->assertSee(__('Not synced'))
            ->assertSee(__('Add slot to this device'))
            ->assertSee(__('company.interviews.schedule_interview'))
            ->assertSee(route('frontend.company.interviews.schedule', absolute: false), false);
    }

    public function test_candidate_application_detail_exposes_tabs_and_existing_withdraw_action(): void
    {
        $this->actingAsCandidate();

        $api = Mockery::mock(CandidateAPI::class);
        $api->shouldReceive('getApplication')->once()->with('31')->andReturn([
            'message' => 'OK',
            'data' => [
                'id' => 31,
                'status' => 'interviewing',
                'job' => ['title' => 'Platform Engineer', 'company' => ['name' => 'Northstar']],
                'submitted_materials' => [['name' => 'Resume.pdf', 'url' => 'https://files.example.test/resume.pdf']],
                'activity' => [['action' => 'Application submitted', 'created_at' => '2026-10-10']],
            ],
            'statusCode' => 200,
        ]);
        $this->app->instance(CandidateAPI::class, $api);

        $this->get(route('frontend.candidate.applications.show', ['applicationId' => 31]))
            ->assertOk()
            ->assertSee('Platform Engineer')
            ->assertSee(__('Overview'))
            ->assertSee(__('Documents'))
            ->assertSee(__('Activity'))
            ->assertSee(route('frontend.candidate.applications.withdraw', ['applicationId' => 31], false))
            ->assertSee('Resume.pdf');
    }

    public function test_candidate_offer_requires_review_before_accept_api_call(): void
    {
        $this->actingAsCandidate();

        $api = Mockery::mock(OfferAPI::class);
        $api->shouldNotReceive('acceptOffer');
        $this->app->instance(OfferAPI::class, $api);

        $this->from(route('frontend.candidate.offers.show', ['offerId' => 8]))
            ->post(route('frontend.candidate.offers.accept', ['offerId' => 8]), [])
            ->assertRedirect(route('frontend.candidate.offers.show', ['offerId' => 8]))
            ->assertSessionHasErrors('offer_reviewed');
    }

    public function test_candidate_offer_details_render_review_docs_contact_and_after_accept_steps(): void
    {
        $this->actingAsCandidate();

        $api = Mockery::mock(OfferAPI::class);
        $api->shouldReceive('getOffer')->once()->with('8')->andReturn([
            'message' => 'OK',
            'data' => [
                'id' => 8,
                'status' => 'pending',
                'job' => ['title' => 'Platform Engineer'],
                'documents' => [['name' => 'Offer.pdf', 'url' => 'https://files.example.test/offer.pdf']],
                'recruiter' => ['name' => 'Casey Recruiter', 'email' => 'casey@example.test'],
            ],
            'statusCode' => 200,
        ]);
        $this->app->instance(OfferAPI::class, $api);

        $this->get(route('frontend.candidate.offers.show', ['offerId' => 8]))
            ->assertOk()
            ->assertSee('Platform Engineer')
            ->assertSee('Offer.pdf')
            ->assertSee('Casey Recruiter')
            ->assertSee(__('Review before accepting'))
            ->assertSee(__('Ask a question'))
            ->assertSee(__('After you accept'));
    }

    public function test_admin_job_moderation_route_is_protected_and_explicitly_local_only(): void
    {
        $this->actingAsPlatformAdmin();

        $api = Mockery::mock(AdminAPI::class);
        $api->shouldReceive('me')->once()->andReturn([
            'message' => 'OK',
            'data' => [
                'id' => 7,
                'email' => 'super@example.com',
                'name' => 'Super Admin',
                'roles' => ['super_admin'],
                'permissions' => [],
            ],
            'statusCode' => 200,
        ]);
        $this->app->instance(AdminAPI::class, $api);

        $this->get(route('frontend.admin.jobs.index'))
            ->assertOk()
            ->assertSee(__('Preview only — no moderation API is available.'))
            ->assertSee('data-moderation-action="changes"', false)
            ->assertSee('data-moderation-action="reject"', false)
            ->assertSee('super-admin-job-moderation-preview-v1');
    }

    private function actingAsInterviewer(): void
    {
        ApiSession::store('interviewer-token', [
            'id' => 20,
            'email' => 'interviewer@example.com',
            'name' => 'Interviewer',
            'account_type' => 'company',
            'roles' => ['interviewer'],
        ]);
    }

    private function actingAsCandidate(): void
    {
        ApiSession::store('candidate-token', [
            'id' => 11,
            'email' => 'candidate@example.com',
            'name' => 'Candidate',
            'account_type' => 'candidate',
            'roles' => ['candidate'],
        ]);
    }

    private function actingAsCompany(): void
    {
        ApiSession::store('company-token', [
            'id' => 21,
            'email' => 'hiring@example.com',
            'name' => 'Hiring Team',
            'account_type' => 'company',
            'roles' => ['company_admin'],
        ]);
    }

    private function actingAsPlatformAdmin(): void
    {
        ApiSession::store('platform-token', [
            'id' => 7,
            'email' => 'super@example.com',
            'name' => 'Super Admin',
            'account_type' => 'admin',
            'roles' => ['super_admin'],
        ], ApiSession::REALM_PLATFORM_ADMIN);
    }
}
