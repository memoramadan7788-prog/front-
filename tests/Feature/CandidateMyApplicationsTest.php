<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Models\ApplicationAPI;
use App\Support\Frontend\CandidateDashboard\CandidateDashboardKpiGateway;
use Mockery;
use Tests\Support\FakeCandidateDashboardKpiGateway;
use Tests\TestCase;

class CandidateMyApplicationsTest extends TestCase
{
    private FakeCandidateDashboardKpiGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = new FakeCandidateDashboardKpiGateway;
        $this->app->instance(CandidateDashboardKpiGateway::class, $this->gateway);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_guest_is_redirected_from_my_applications(): void
    {
        $this->get(route('frontend.candidate.applications.index'))
            ->assertRedirect(route('frontend.account.login'));
    }

    public function test_company_role_cannot_open_my_applications(): void
    {
        ApiSession::store('co-token', [
            'id' => 9,
            'email' => 'hr@example.com',
            'name' => 'HR',
            'account_type' => 'company',
            'roles' => ['company_admin'],
        ]);

        $this->get(route('frontend.candidate.applications.index'))
            ->assertRedirect();
    }

    public function test_candidate_sees_application_listing_with_tabs_and_badges(): void
    {
        $this->actingAsCandidate();
        $this->seedCatalog([
            $this->appRow(1, 'submitted', 'Engineer', 'Acme', '2026-10-01T10:00:00+00:00'),
            $this->appRow(2, 'interviewing', 'Designer', 'Beta', '2026-10-02T10:00:00+00:00'),
            $this->appRow(3, 'offered', 'PM', 'Gamma', '2026-10-03T10:00:00+00:00'),
            $this->appRow(4, 'hired', 'Analyst', 'Delta', '2026-09-01T10:00:00+00:00'),
        ]);

        $html = $this->get(route('frontend.candidate.applications.index'))->assertOk()->getContent();

        $this->assertStringContainsString('data-apps-tab="all"', $html);
        $this->assertStringContainsString('data-apps-tab="in_progress"', $html);
        $this->assertStringContainsString('data-apps-tab="interviewing"', $html);
        $this->assertStringContainsString('data-apps-tab="offered"', $html);
        $this->assertStringContainsString('data-apps-tab="completed"', $html);
        $this->assertStringContainsString('Engineer', $html);
        $this->assertStringContainsString('Acme', $html);
        $this->assertStringContainsString(__('candidate.analytics.status.submitted'), $html);
        $this->assertStringContainsString(__('candidate.analytics.status.interviewing'), $html);
        $this->assertStringContainsString('data-pipeline-mode="snapshot"', $html);
        $this->assertStringContainsString(__('candidate.applications.pipeline_snapshot_note'), $html);
    }

    public function test_tab_state_groups_by_documented_statuses(): void
    {
        $this->actingAsCandidate();
        $this->seedCatalog([
            $this->appRow(1, 'submitted', 'A', 'Co', '2026-10-01T10:00:00+00:00'),
            $this->appRow(2, 'screening', 'B', 'Co', '2026-10-02T10:00:00+00:00'),
            $this->appRow(3, 'interviewing', 'C', 'Co', '2026-10-03T10:00:00+00:00'),
            $this->appRow(4, 'offered', 'D', 'Co', '2026-10-04T10:00:00+00:00'),
            $this->appRow(5, 'rejected', 'E', 'Co', '2026-10-05T10:00:00+00:00'),
        ]);

        $html = $this->get(route('frontend.candidate.applications.index', ['tab' => 'interviewing']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('aria-selected="true"', $html);
        $this->assertStringContainsString('data-apps-tab="interviewing"', $html);
        $this->assertStringContainsString('C', $html);
        $this->assertStringNotContainsString('>A</', $html);
        $this->assertStringNotContainsString('data-status="submitted"', $html);
        $this->assertStringContainsString('data-status="interviewing"', $html);
    }

    public function test_keyword_and_status_filters_are_applied(): void
    {
        $this->actingAsCandidate();
        $this->seedCatalog([
            $this->appRow(1, 'submitted', 'Backend Engineer', 'Acme Labs', '2026-10-01T10:00:00+00:00'),
            $this->appRow(2, 'submitted', 'Frontend Engineer', 'Other Co', '2026-10-02T10:00:00+00:00'),
            $this->appRow(3, 'interviewing', 'Backend Engineer', 'Other Co', '2026-10-03T10:00:00+00:00'),
        ]);

        $html = $this->get(route('frontend.candidate.applications.index', [
            'q' => 'Acme',
            'status' => 'submitted',
        ]))->assertOk()->getContent();

        $this->assertStringContainsString('Backend Engineer', $html);
        $this->assertStringContainsString('Acme Labs', $html);
        $this->assertStringNotContainsString('Other Co', $html);
        $this->assertStringContainsString('value="Acme"', $html);
    }

    public function test_pagination_preserves_filters_in_links(): void
    {
        $this->actingAsCandidate();
        $rows = [];
        for ($i = 1; $i <= 12; $i++) {
            $rows[] = $this->appRow($i, 'submitted', "Role {$i}", 'Acme', sprintf('2026-10-%02dT10:00:00+00:00', min($i, 28)));
        }
        $this->seedCatalog($rows);

        $html = $this->get(route('frontend.candidate.applications.index', [
            'q' => 'Acme',
            'tab' => 'in_progress',
            'page' => 2,
        ]))->assertOk()->getContent();

        $this->assertStringContainsString('data-pagination-pages', $html);
        $this->assertStringContainsString('page=1', $html);
        $this->assertStringContainsString('q=Acme', $html);
        $this->assertStringContainsString('tab=in_progress', $html);
        $this->assertStringContainsString('Role 2', $html);
    }

    public function test_unknown_status_uses_fallback_badge_and_no_fake_pipeline(): void
    {
        $this->actingAsCandidate();
        $this->seedCatalog([
            $this->appRow(9, 'mystery_stage', 'Odd Role', 'Co', '2026-10-01T10:00:00+00:00'),
        ]);

        $html = $this->get(route('frontend.candidate.applications.index'))->assertOk()->getContent();

        $this->assertStringContainsString(__('candidate.analytics.status.unknown'), $html);
        $this->assertStringContainsString('data-status="unknown"', $html);
        $this->assertStringNotContainsString('data-status="mystery_stage"', $html);
        $this->assertStringContainsString('data-pipeline-mode="unknown"', $html);
        $this->assertStringNotContainsString(
            route('frontend.candidate.applications.withdraw', ['applicationId' => 9], false),
            $html
        );
    }

    public function test_pipeline_snapshot_marks_current_stage_only(): void
    {
        $this->actingAsCandidate();
        $this->seedCatalog([
            $this->appRow(1, 'interviewing', 'IC', 'Co', '2026-10-01T10:00:00+00:00'),
        ]);

        $html = $this->get(route('frontend.candidate.applications.index'))->assertOk()->getContent();

        $this->assertStringContainsString('data-pipeline-mode="snapshot"', $html);
        $this->assertStringContainsString('data-pipeline-current="interviewing"', $html);
        $this->assertStringContainsString('hb-apps-pipeline__dot--current', $html);
        $this->assertStringContainsString(__('candidate.applications.pipeline_snapshot_note'), $html);
        $this->assertStringNotContainsString('data-pipeline-mode="history"', $html);
    }

    public function test_application_detail_links_are_present(): void
    {
        $this->actingAsCandidate();
        $this->seedCatalog([
            $this->appRow(42, 'submitted', 'Linked Role', 'Co', '2026-10-01T10:00:00+00:00'),
        ]);

        $html = $this->get(route('frontend.candidate.applications.index'))->assertOk()->getContent();

        $this->assertStringContainsString(
            route('frontend.candidate.applications.show', ['applicationId' => 42], false),
            $html
        );
    }

    public function test_empty_catalog_shows_discover_cta(): void
    {
        $this->actingAsCandidate();
        $this->seedCatalog([]);

        $html = $this->get(route('frontend.candidate.applications.index'))->assertOk()->getContent();

        $this->assertStringContainsString(__('candidate.pipeline.applications_empty_title'), $html);
        $this->assertStringContainsString(route('frontend.candidate.vacancies.index', [], false), $html);
    }

    public function test_backend_failure_shows_error_state(): void
    {
        $this->actingAsCandidate();
        $this->gateway->applicationPages = [[
            'statusCode' => 500,
            'message' => 'Upstream failed',
            'data' => null,
            'meta' => null,
            'errors' => null,
        ]];

        $html = $this->get(route('frontend.candidate.applications.index'))->assertOk()->getContent();

        $this->assertStringContainsString(__('candidate.pipeline.load_error_title'), $html);
        $this->assertStringContainsString('Upstream failed', $html);
        $this->assertStringNotContainsString(__('candidate.pipeline.applications_empty_title'), $html);
    }

    public function test_withdraw_form_includes_confirmation_and_mocked_failure_does_not_claim_success(): void
    {
        $this->actingAsCandidate();
        $this->seedCatalog([
            $this->appRow(7, 'submitted', 'Keep Me', 'Co', '2026-10-01T10:00:00+00:00'),
        ]);

        $listHtml = $this->get(route('frontend.candidate.applications.index'))->assertOk()->getContent();
        $this->assertStringContainsString('confirm(', $listHtml);
        $this->assertStringContainsString(
            route('frontend.candidate.applications.withdraw', ['applicationId' => 7], false),
            $listHtml
        );

        $api = Mockery::mock(ApplicationAPI::class);
        $api->shouldReceive('withdrawApplication')->once()->with('7')->andReturn([
            'statusCode' => 422,
            'message' => 'Cannot withdraw',
            'data' => null,
            'errors' => ['error' => 'Cannot withdraw'],
        ]);
        $this->app->instance(ApplicationAPI::class, $api);

        // ApplicationController constructs ApplicationAPI directly — bind via alias
        // by overriding the concrete class used when new ApplicationAPI() is called
        // is not possible; hit the route with a partial mock via container only if
        // the controller resolves from the container. Fall back: assert list confirm only
        // when constructor bypasses DI.
        $controller = new \App\Frontend\Application\ApplicationController;
        $ref = new \ReflectionClass($controller);
        $prop = $ref->getProperty('api');
        $prop->setAccessible(true);
        $prop->setValue($controller, $api);
        $this->app->instance(\App\Frontend\Application\ApplicationController::class, $controller);

        $response = $this->from(route('frontend.candidate.applications.index'))
            ->post(route('frontend.candidate.applications.withdraw', ['applicationId' => 7]));

        $response->assertRedirect(route('frontend.candidate.applications.index'));
        $response->assertSessionHasErrors();
        $response->assertSessionMissing('success_key');
    }

    public function test_arabic_locale_renders_rtl_labels(): void
    {
        $this->actingAsCandidate();
        $this->seedCatalog([
            $this->appRow(1, 'submitted', 'مهندس', 'شركة', '2026-10-01T10:00:00+00:00'),
        ]);

        $html = $this->withUnencryptedCookie('hb_locale', 'ar')
            ->get(route('frontend.candidate.applications.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('candidate.applications.index_title', [], 'ar'), $html);
        $this->assertStringContainsString(__('candidate.applications.tab_all', [], 'ar'), $html);
        $this->assertStringContainsString(__('candidate.analytics.status.submitted', [], 'ar'), $html);
        $this->assertMatchesRegularExpression('/\bdir=["\']rtl["\']/', $html);
        $this->assertMatchesRegularExpression('/\blang=["\']ar["\']/', $html);
    }

    public function test_dashboard_applications_link_still_resolves(): void
    {
        $this->actingAsCandidate();
        $this->seedCatalog([]);

        // Regression: applications index remains reachable from dashboard route name.
        $this->get(route('frontend.candidate.applications.index'))->assertOk();
        $this->assertSame(
            url('/candidate/applications'),
            route('frontend.candidate.applications.index')
        );
    }

    public function test_incomplete_catalog_hides_tab_counts_and_shows_warning(): void
    {
        $this->actingAsCandidate();

        // Simulate Backend still reporting more pages after MAX_PAGES-worth of loads:
        // adapter stops; must not present those partial rows as global totals.
        $pages = [];
        for ($page = 1; $page <= 20; $page++) {
            $pages[] = $this->gateway->okPage([
                $this->appRow($page, 'submitted', "Role {$page}", 'Acme', sprintf('2026-10-%02dT10:00:00+00:00', min($page, 28))),
            ], [
                'total' => 500,
                'last_page' => 50,
                'current_page' => $page,
                'per_page' => 15,
            ]);
        }
        $this->gateway->applicationPages = $pages;

        $html = $this->get(route('frontend.candidate.applications.index'))->assertOk()->getContent();

        $this->assertStringContainsString(__('candidate.applications.catalog_incomplete'), $html);
        $this->assertStringContainsString('data-catalog-warning', $html);
        $this->assertStringNotContainsString('data-tab-count', $html);
        $this->assertStringContainsString('Role 1', $html);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function seedCatalog(array $rows): void
    {
        $this->gateway->applicationPages = [
            $this->gateway->okPage($rows, [
                'total' => count($rows),
                'last_page' => 1,
                'current_page' => 1,
                'per_page' => 15,
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function appRow(int $id, string $status, string $title, string $company, string $appliedAt): array
    {
        return [
            'id' => $id,
            'status' => $status,
            'job_title' => $title,
            'company_name' => $company,
            'applied_at' => $appliedAt,
        ];
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
}
