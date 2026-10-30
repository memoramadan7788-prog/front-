<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Models\JobAPI;
use App\Support\Frontend\CandidateDashboard\CandidateDashboardKpiGateway;
use Mockery;
use Tests\Support\FakeCandidateDashboardKpiGateway;
use Tests\TestCase;

class CandidateApplicationSuccessTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_application_success_page_uses_real_job_and_recommendation_responses(): void
    {
        $this->actingAsCandidate();
        $jobs = Mockery::mock(JobAPI::class);
        $jobs->shouldReceive('getJob')->times(3)->with(45)->andReturn(['statusCode' => 200, 'data' => ['id' => 45, 'title' => 'Product Designer']]);
        $this->app->instance(JobAPI::class, $jobs);
        $gateway = new FakeCandidateDashboardKpiGateway;
        $gateway->recommendedJobsResponse['data'] = [['id' => 92, 'title' => 'UX Researcher']];
        $this->app->instance(CandidateDashboardKpiGateway::class, $gateway);

        $this->get(route('frontend.candidate.applications.success', ['vacancyId' => 45]))
            ->assertRedirect(route('frontend.candidate.applications.index'));

        $this->withSession(['application_submitted_vacancy' => 45])
            ->get(route('frontend.candidate.applications.success', ['vacancyId' => 45]))
            ->assertOk()->assertSee('Product Designer')->assertSee('UX Researcher')->assertSee(__('success.candidate.track'));

        $gateway->recommendedJobsResponse['data'] = [];
        $this->withSession(['application_submitted_vacancy' => 45])
            ->get(route('frontend.candidate.applications.success', ['vacancyId' => 45]))
            ->assertOk()->assertSee(__('success.candidate.recommendations_empty'));

        $gateway->recommendedJobsResponse['statusCode'] = 503;
        $this->withUnencryptedCookie('hb_locale', 'ar')->withSession(['application_submitted_vacancy' => 45])
            ->get(route('frontend.candidate.applications.success', ['vacancyId' => 45]))
            ->assertOk()->assertSee(__('success.candidate.recommendations_unavailable', [], 'ar'));
    }

    private function actingAsCandidate(): void
    {
        ApiSession::store('candidate-token', ['id' => 14, 'email' => 'candidate@example.test', 'name' => 'Candidate', 'account_type' => 'candidate', 'roles' => ['candidate']]);
    }
}
