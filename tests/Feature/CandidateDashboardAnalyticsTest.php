<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Support\Frontend\CandidateDashboard\CandidateDashboardKpiGateway;
use Carbon\Carbon;
use Tests\Support\FakeCandidateDashboardKpiGateway;
use Tests\TestCase;

class CandidateDashboardAnalyticsTest extends TestCase
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

    public function test_empty_dashboard_shows_analytics_empty_states_not_fake_charts(): void
    {
        $this->actingAsCandidate();

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString(__('candidate.analytics.section_title'), $html);
        $this->assertStringContainsString('data-candidate-analytics', $html);
        $this->assertStringContainsString('data-activity-period="7"', $html);
        $this->assertStringContainsString('data-activity-period="30"', $html);
        $this->assertStringContainsString('data-activity-period="90"', $html);
        $this->assertStringContainsString(__('candidate.analytics.activity_empty'), $html);
        $this->assertStringContainsString(__('candidate.analytics.pipeline_empty'), $html);
        // Interview empty lives on the sole upcoming-interview card (timeline panel removed).
        $this->assertStringContainsString('data-upcoming-interview-empty', $html);
        $this->assertStringContainsString(__('candidate.dashboard.upcoming_empty'), $html);
        $this->assertStringNotContainsString('data-analytics-panel="timeline"', $html);
        $this->assertStringContainsString(__('candidate.kpi.quick_actions'), $html);
        $this->assertStringContainsString(__('Discover jobs'), $html);
        $this->assertStringNotContainsString('999 applications invented', $html);
    }

    public function test_populated_dashboard_renders_pipeline_legend_and_upcoming_card(): void
    {
        $this->actingAsCandidate();
        $this->gateway->applicationPages = [
            $this->gateway->okPage([
                ['id' => 1, 'status' => 'submitted', 'applied_at' => '2026-10-20T10:00:00+00:00'],
                ['id' => 2, 'status' => 'mystery_stage', 'applied_at' => '2026-10-21T10:00:00+00:00'],
            ], ['total' => 2, 'last_page' => 1, 'current_page' => 1, 'per_page' => 100]),
        ];
        $this->gateway->interviewPages = [
            $this->gateway->okPage([
                [
                    'id' => 9,
                    'status' => 'scheduled',
                    'start_at' => '2026-11-02T15:00:00+00:00',
                    'is_online' => true,
                    'job_title' => 'Platform Engineer',
                    'company_name' => 'Acme',
                ],
            ], ['total' => 1, 'last_page' => 1, 'current_page' => 1, 'per_page' => 100]),
        ];
        $this->gateway->profileResponse = $this->gateway->okPage([
            'phone' => '1', 'location' => '2', 'headline' => '3', 'bio' => '4',
            'has_no_experience' => true, 'experiences' => [], 'educations' => [['id' => 1]], 'cvs' => [['id' => 1]],
        ]);

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString(__('candidate.analytics.status.submitted'), $html);
        $this->assertStringContainsString(__('candidate.analytics.status.unknown'), $html);
        $this->assertStringContainsString('mystery_stage', $html);
        $this->assertStringContainsString('data-upcoming-interview-item', $html);
        $this->assertStringContainsString('Platform Engineer', $html);
        $this->assertStringContainsString('Acme', $html);
        $this->assertStringContainsString('data-profile-ring', $html);
        $this->assertStringContainsString('data-percent="100"', $html);
        $this->assertStringContainsString(__('candidate.analytics.profile_ring_title'), $html);
        $this->assertStringContainsString(__('candidate.kpi.quick_actions'), $html);
        $this->assertStringNotContainsString('data-next-action="review_interview"', $html);
    }

    public function test_historical_unavailable_when_applied_at_missing(): void
    {
        $this->actingAsCandidate();
        $this->gateway->applicationPages = [
            $this->gateway->okPage([
                ['id' => 1, 'status' => 'submitted', 'applied_at' => null],
            ], ['total' => 1, 'last_page' => 1, 'current_page' => 1, 'per_page' => 100]),
        ];

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString(__('candidate.analytics.historical_unavailable'), $html);
        $this->assertStringContainsString(__('candidate.analytics.historical_missing_dates'), $html);
    }

    public function test_api_failure_shows_unavailable_visualizations_and_keeps_quick_actions(): void
    {
        $this->actingAsCandidate();
        $this->gateway->applicationPages = [$this->gateway->error(503, 'Service unavailable')];
        $this->gateway->interviewPages = [$this->gateway->error(503, 'Service unavailable')];

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString(__('candidate.kpi.partial_error'), $html);
        $this->assertStringContainsString(__('candidate.analytics.historical_unavailable'), $html);
        $this->assertStringContainsString(__('candidate.analytics.pipeline_unavailable'), $html);
        $this->assertStringContainsString('data-upcoming-interview-error', $html);
        $this->assertStringContainsString(__('candidate.kpi.quick_actions'), $html);
    }

    public function test_arabic_locale_localizes_analytics_chrome(): void
    {
        $this->actingAsCandidate();
        app()->setLocale('ar');

        $html = $this->withSession(['locale' => 'ar'])
            ->get(route('frontend.candidate.dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('candidate.analytics.section_title', [], 'ar'), $html);
        $this->assertStringContainsString(__('candidate.analytics.period_30', [], 'ar'), $html);
        $this->assertStringContainsString(__('navigation.candidate.overview', [], 'ar'), $html);
        $this->assertStringContainsString('data-sidebar-nav-toggle', $html);
    }

    public function test_responsive_analytics_panels_and_kpi_regression(): void
    {
        $this->actingAsCandidate();

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('data-kpi="total_applications"', $html);
        $this->assertStringContainsString('data-analytics-panel="activity"', $html);
        $this->assertStringContainsString('data-analytics-panel="pipeline"', $html);
        $this->assertStringContainsString('data-analytics-panel="profile"', $html);
        $this->assertStringContainsString('data-upcoming-interview-card', $html);
        $this->assertStringNotContainsString('data-analytics-panel="timeline"', $html);
        $this->assertStringContainsString('lg:grid-cols-3', $html);
        $this->assertStringContainsString('sm:grid-cols-2', $html);
    }

    private function actingAsCandidate(): void
    {
        ApiSession::store('cand-token', [
            'id' => 31,
            'email' => 'analytics@example.com',
            'name' => 'Analytics Candidate',
            'account_type' => 'candidate',
            'roles' => ['candidate'],
        ]);
    }
}
