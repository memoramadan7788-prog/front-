<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Support\Frontend\CandidateDashboard\CandidateDashboardKpiGateway;
use Carbon\Carbon;
use Tests\Support\FakeCandidateDashboardKpiGateway;
use Tests\TestCase;

class CandidateDashboardKpiTest extends TestCase
{
    private FakeCandidateDashboardKpiGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = new FakeCandidateDashboardKpiGateway;
        $this->app->instance(CandidateDashboardKpiGateway::class, $this->gateway);
        Carbon::setTestNow(Carbon::parse('2026-10-27T12:00:00+00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_dashboard_shows_zero_kpis_and_quick_actions(): void
    {
        $this->actingAsCandidate();

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString(__('candidate.kpi.section_title'), $html);
        $this->assertStringContainsString(__('candidate.kpi.total_applications'), $html);
        $this->assertStringContainsString(__('candidate.kpi.quick_actions'), $html);
        $this->assertStringContainsString(__('Discover jobs'), $html);
        $this->assertStringContainsString(__('My applications'), $html);
        $this->assertStringContainsString(__('candidate.kpi.assessments_unavailable'), $html);
        $this->assertStringContainsString('data-kpi="total_applications"', $html);
        $this->assertStringContainsString('data-kpi-value="0"', $html);
        $this->assertStringContainsString('data-kpi-available="0"', $html); // assessments
        $this->assertStringContainsString(route('frontend.candidate.applications.index', [], false), $html);
    }

    public function test_dashboard_shows_populated_kpi_values(): void
    {
        $this->actingAsCandidate();
        $this->gateway->applicationPages = [
            $this->gateway->okPage([
                ['id' => 1, 'status' => 'submitted'],
                ['id' => 2, 'status' => 'hired'],
            ], ['total' => 2, 'last_page' => 1, 'current_page' => 1, 'per_page' => 100]),
        ];
        $this->gateway->interviewPages = [
            $this->gateway->okPage([
                ['id' => 1, 'status' => 'scheduled', 'start_at' => '2026-11-01T10:00:00+00:00'],
            ], ['total' => 1, 'last_page' => 1, 'current_page' => 1, 'per_page' => 100]),
        ];
        $this->gateway->offersResponse = $this->gateway->okPage([
            ['id' => 7, 'status' => 'sent'],
        ]);
        $this->gateway->profileResponse = $this->gateway->okPage([
            'phone' => '1',
            'location' => '2',
            'headline' => '3',
            'bio' => '4',
            'has_no_experience' => true,
            'experiences' => [],
            'educations' => [['id' => 1]],
            'cvs' => [['id' => 1]],
        ]);

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/data-kpi="total_applications"[^>]*data-kpi-value="2"/', $html);
        $this->assertMatchesRegularExpression('/data-kpi="applications_in_progress"[^>]*data-kpi-value="1"/', $html);
        $this->assertMatchesRegularExpression('/data-kpi="upcoming_interviews"[^>]*data-kpi-value="1"/', $html);
        $this->assertMatchesRegularExpression('/data-kpi="offers_received"[^>]*data-kpi-value="1"/', $html);
        $this->assertMatchesRegularExpression('/data-kpi="profile_completion"[^>]*data-kpi-value="100%"/', $html);
        $this->assertStringNotContainsString(__('candidate.kpi.partial_error'), $html);
    }

    public function test_dashboard_api_error_shows_unavailable_not_fake_zero(): void
    {
        $this->actingAsCandidate();
        $this->gateway->applicationPages = [$this->gateway->error(503, 'Service unavailable')];
        $this->gateway->interviewPages = [$this->gateway->error(503, 'Service unavailable')];
        $this->gateway->offersResponse = $this->gateway->error(503, 'Service unavailable');
        $this->gateway->profileResponse = $this->gateway->error(503, 'Service unavailable');

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString(__('candidate.kpi.partial_error'), $html);
        $this->assertStringContainsString(__('candidate.kpi.unavailable'), $html);
        $this->assertMatchesRegularExpression('/data-kpi="total_applications"[^>]*data-kpi-available="0"[^>]*data-kpi-value="—"/u', $html);
        $this->assertStringContainsString(__('candidate.kpi.quick_actions'), $html);
    }

    public function test_dashboard_partial_error_banner_is_not_duplicated(): void
    {
        $this->actingAsCandidate();
        $leak = 'SQLSTATE[HY000]: General error while loading applications';
        $this->gateway->applicationPages = [$this->gateway->error(500, $leak)];
        $this->gateway->interviewPages = [$this->gateway->error(500, $leak)];
        $this->gateway->offersResponse = $this->gateway->error(500, $leak);
        $this->gateway->recommendedJobsResponse = $this->gateway->error(500, $leak);

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $title = __('candidate.kpi.partial_error');
        $this->assertSame(1, substr_count($html, $title));
        $this->assertStringContainsString('data-dashboard-partial-error', $html);
        $this->assertStringNotContainsString($title.' '.$title, $html);
        $this->assertStringNotContainsString('SQLSTATE', $html);
        // Sanitized fallback "Unavailable" must not be appended under the banner title.
        $this->assertStringNotContainsString($title.'</p>'."\n".'            <p class="mt-1 opacity-90">'.__('candidate.kpi.unavailable'), $html);
    }

    public function test_recommended_jobs_schema_failure_does_not_raise_global_banner(): void
    {
        $this->actingAsCandidate();
        $this->gateway->recommendedJobsResponse = $this->gateway->error(
            500,
            "SQLSTATE[42S02]: Base table or view not found: 1146 Table 'recruitment_audit.candidate_hidden_jobs' doesn't exist"
        );

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('data-recommended-error', $html);
        $this->assertStringContainsString('data-recommended-state="error"', $html);
        $this->assertStringContainsString('data-recommended-error-kind="schema"', $html);
        $this->assertStringContainsString(__('candidate.dashboard.recommended_backend_unavailable'), $html);
        $this->assertStringNotContainsString('data-recommended-empty', $html);
        $this->assertStringNotContainsString('data-dashboard-partial-error', $html);
        $this->assertMatchesRegularExpression('/data-kpi="total_applications"[^>]*data-kpi-available="1"/', $html);
    }

    public function test_dashboard_unauthorized_redirects_to_login(): void
    {
        $this->actingAsCandidate();
        $this->gateway->applicationPages = [$this->gateway->error(401, 'Unauthenticated')];

        $this->get(route('frontend.candidate.dashboard'))
            ->assertRedirect(route('frontend.account.login'));
        $this->assertFalse(ApiSession::check());
    }

    public function test_dashboard_preserves_locale_and_sidebar(): void
    {
        $this->actingAsCandidate();
        app()->setLocale('ar');

        $html = $this->withSession(['locale' => 'ar'])
            ->get(route('frontend.candidate.dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('candidate.kpi.section_title', [], 'ar'), $html);
        $this->assertStringContainsString(__('navigation.candidate.overview', [], 'ar'), $html);
        $this->assertStringContainsString('data-sidebar-nav-toggle', $html);
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get(route('frontend.candidate.dashboard'))
            ->assertRedirect(route('frontend.account.login'));
    }

    private function actingAsCandidate(): void
    {
        ApiSession::store('cand-token', [
            'id' => 21,
            'email' => 'kpi@example.com',
            'name' => 'KPI Candidate',
            'account_type' => 'candidate',
            'roles' => ['candidate'],
        ]);
    }
}
