<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Http\Middleware\SetFrontendLocale;
use App\Support\Frontend\CandidateDashboard\CandidateDashboardKpiGateway;
use Carbon\Carbon;
use Tests\Support\FakeCandidateDashboardKpiGateway;
use Tests\TestCase;

class CandidateDashboardTask12Test extends TestCase
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

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get(route('frontend.candidate.dashboard'))
            ->assertRedirect(route('frontend.account.login'));
    }

    public function test_company_role_cannot_open_candidate_dashboard(): void
    {
        ApiSession::store('co-token', [
            'id' => 9,
            'email' => 'hr@example.com',
            'name' => 'HR',
            'account_type' => 'company',
            'roles' => ['company_admin'],
        ]);

        $this->get(route('frontend.candidate.dashboard'))
            ->assertRedirect();
    }

    public function test_upcoming_interview_card_selects_nearest_future_by_timestamp(): void
    {
        $this->actingAsCandidate();
        $this->gateway->interviewPages = [
            $this->gateway->okPage([
                [
                    'id' => 2,
                    'status' => 'scheduled',
                    'start_at' => '2026-11-05T15:00:00+00:00',
                    'job_title' => 'Later Role',
                    'company_name' => 'Later Co',
                    'interview_type' => 'technical',
                    'is_online' => true,
                ],
                [
                    'id' => 1,
                    'status' => 'scheduled',
                    'start_at' => '2026-10-28T09:30:00+02:00',
                    'job_title' => 'Nearest Role',
                    'company_name' => 'Near Co',
                    'interview_type' => 'screening',
                    'is_online' => false,
                ],
                [
                    'id' => 3,
                    'status' => 'cancelled',
                    'start_at' => '2026-10-27T18:00:00+00:00',
                    'job_title' => 'Cancelled Role',
                ],
                [
                    'id' => 4,
                    'status' => 'scheduled',
                    'start_at' => '2026-10-20T10:00:00+00:00',
                    'job_title' => 'Past Role',
                ],
            ], ['total' => 4, 'last_page' => 1, 'current_page' => 1, 'per_page' => 100]),
        ];

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/data-upcoming-interview-item[\s\S]*?Nearest Role[\s\S]*?Near Co[\s\S]*?<\/article>/',
            $html
        );
        $this->assertDoesNotMatchRegularExpression(
            '/data-upcoming-interview-item[\s\S]*?(Cancelled Role|Past Role|Later Role)[\s\S]*?<\/article>/',
            $html
        );
        // Single interview presentation: later interviews are not listed on the dashboard.
        $this->assertStringNotContainsString('Later Role', $html);
        $this->assertStringNotContainsString('data-analytics-panel="timeline"', $html);
        $this->assertStringNotContainsString('data-next-action="review_interview"', $html);
    }

    public function test_upcoming_interview_empty_state(): void
    {
        $this->actingAsCandidate();

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('data-upcoming-interview-empty', $html);
        $this->assertStringContainsString(__('candidate.dashboard.upcoming_empty'), $html);
    }

    public function test_application_tracker_shows_recent_status_snapshot(): void
    {
        $this->actingAsCandidate();
        $this->gateway->applicationPages = [
            $this->gateway->okPage([
                [
                    'id' => 10,
                    'status' => 'screening',
                    'job_title' => 'Backend Engineer',
                    'company_name' => 'Acme',
                    'applied_at' => '2026-10-26T10:00:00+00:00',
                ],
                [
                    'id' => 9,
                    'status' => 'submitted',
                    'job_title' => 'Older Role',
                    'company_name' => 'Beta',
                    'applied_at' => '2026-10-01T10:00:00+00:00',
                ],
            ], ['total' => 2, 'last_page' => 1, 'current_page' => 1, 'per_page' => 100]),
        ];

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('data-application-tracker', $html);
        $this->assertStringContainsString('data-tracker-snapshot-note', $html);
        $this->assertStringContainsString('Backend Engineer', $html);
        $this->assertStringContainsString(__('candidate.analytics.status.screening'), $html);
        $this->assertStringContainsString(route('frontend.candidate.applications.show', ['applicationId' => 10], false), $html);
    }

    public function test_application_tracker_marks_partial_catalog(): void
    {
        $this->actingAsCandidate();
        $rows = [];
        for ($i = 1; $i <= 100; $i++) {
            $rows[] = [
                'id' => $i,
                'status' => 'submitted',
                'job_title' => "Role {$i}",
                'applied_at' => '2026-10-01T10:00:00+00:00',
            ];
        }
        // Only one page returned while meta says many pages remain → incomplete catalog.
        $this->gateway->applicationPages = [
            $this->gateway->okPage($rows, ['total' => 2500, 'last_page' => 25, 'current_page' => 1, 'per_page' => 100]),
        ];

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('data-tracker-partial', $html);
        $this->assertStringContainsString(__('candidate.dashboard.tracker_partial_note'), $html);
    }

    public function test_recommendations_success_empty_and_failure(): void
    {
        $this->actingAsCandidate();

        $empty = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();
        $this->assertStringContainsString('data-recommended-empty', $empty);

        $this->gateway->recommendedJobsResponse = $this->gateway->okPage([
            [
                'id' => 55,
                'title' => 'Platform Engineer',
                'work_mode' => 'remote',
                'location' => 'Cairo',
                'company' => ['name' => 'TalentBridge Labs'],
                'why_it_fits' => ['Matches 2 of your skills'],
                'applied_by_me' => false,
            ],
        ], ['total' => 1, 'last_page' => 1, 'current_page' => 1, 'per_page' => 6]);

        $ok = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();
        $this->assertStringContainsString('data-recommended-list', $ok);
        $this->assertStringContainsString('Platform Engineer', $ok);
        $this->assertStringContainsString('TalentBridge Labs', $ok);
        $this->assertStringContainsString('Matches 2 of your skills', $ok);
        $this->assertStringContainsString(route('frontend.candidate.vacancies.show', ['vacancyId' => 55], false), $ok);
        $this->assertStringNotContainsString('98%', $ok);

        $this->gateway->recommendedJobsResponse = $this->gateway->error(503, 'SQLSTATE[HY000] boom');
        $fail = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();
        $this->assertStringContainsString('data-recommended-error', $fail);
        $this->assertStringNotContainsString('SQLSTATE', $fail);
    }

    public function test_profile_checklist_links_incomplete_fields(): void
    {
        $this->actingAsCandidate();
        $this->gateway->profileResponse = $this->gateway->okPage([
            'phone' => '123',
            'location' => null,
            'headline' => null,
            'bio' => null,
            'has_no_experience' => false,
            'experiences' => [],
            'educations' => [],
            'cvs' => [],
        ]);

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('data-profile-checklist', $html);
        $this->assertStringContainsString('data-profile-checklist-link="location"', $html);
        $this->assertStringContainsString(route('frontend.candidate.profile.update', [], false), $html);
        $this->assertStringContainsString(route('frontend.candidate.education.add', [], false), $html);
        $this->assertMatchesRegularExpression('/data-kpi="profile_completion"[^>]*data-kpi-value="14%"/', $html);
    }

    public function test_offer_deadline_next_action_uses_expires_at(): void
    {
        $this->actingAsCandidate();
        $this->gateway->offersResponse = $this->gateway->okPage([
            [
                'id' => 77,
                'status' => 'sent',
                'job_title' => 'Staff Engineer',
                'expires_at' => '2026-10-30T18:00:00+00:00',
            ],
            [
                'id' => 78,
                'status' => 'sent',
                'job_title' => 'Expired Offer',
                'expires_at' => '2026-10-20T18:00:00+00:00',
            ],
            [
                'id' => 79,
                'status' => 'sent',
                'job_title' => 'No Deadline',
                'expires_at' => null,
            ],
        ]);

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('data-next-action="offer_deadline"', $html);
        $this->assertStringContainsString('data-next-action="offer_deadline_overdue"', $html);
        $this->assertStringContainsString(route('frontend.candidate.offers.show', ['offerId' => 77], false), $html);
        $this->assertStringContainsString('Staff Engineer', $html);
        $this->assertStringNotContainsString('No Deadline', $html);
    }

    public function test_en_ar_rtl_and_overview_sections_render(): void
    {
        $this->actingAsCandidate();

        $en = $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'en')
            ->get(route('frontend.candidate.dashboard'))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString(__('candidate.dashboard.upcoming_card_title', [], 'en'), $en);
        $this->assertStringContainsString(__('candidate.dashboard.tracker_title', [], 'en'), $en);
        $this->assertStringContainsString(__('candidate.dashboard.recommended', [], 'en'), $en);
        $this->assertStringContainsString('data-candidate-analytics', $en);
        $this->assertStringContainsString(__('candidate.kpi.section_title', [], 'en'), $en);

        app()->setLocale('ar');
        $ar = $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('frontend.candidate.dashboard'))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString(__('candidate.dashboard.upcoming_card_title', [], 'ar'), $ar);
        $this->assertMatchesRegularExpression('/<html[^>]*\sdir="rtl"/', $ar);
    }

    public function test_kpi_analytics_still_present_with_task12_sections(): void
    {
        $this->actingAsCandidate();

        $html = $this->get(route('frontend.candidate.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('data-kpi="total_applications"', $html);
        $this->assertStringContainsString('data-dashboard-kpi-strip', $html);
        $this->assertStringContainsString('data-activity-period="7"', $html);
        $this->assertStringContainsString('data-analytics-panel="pipeline"', $html);
        $this->assertStringContainsString(__('candidate.kpi.quick_actions'), $html);
        $this->assertStringContainsString('data-dashboard-overview', $html);
        // KPI-first: analytics markup appears before Task 12 overview.
        $this->assertLessThan(
            strpos($html, 'data-dashboard-overview'),
            strpos($html, 'data-candidate-analytics')
        );
    }

    private function actingAsCandidate(): void
    {
        ApiSession::store('cand-token', [
            'id' => 21,
            'email' => 'task12@example.com',
            'name' => 'Task Twelve',
            'account_type' => 'candidate',
            'roles' => ['candidate'],
        ]);
    }
}
