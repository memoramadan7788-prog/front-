<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Models\AdminAPI;
use Mockery;
use Tests\TestCase;

class AdminDashboardAnalyticsTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_populated_dashboard_and_analytics_render_kpis_and_charts(): void
    {
        $this->actingAsPlatformAdmin();
        $this->bindAdminApis(
            dashboard: $this->dashboardPayload([
                'users' => [
                    'total' => 100,
                    'new_30d' => 12,
                    'prev_30d' => 10,
                    'inactive' => 4,
                    'by_type' => ['candidate' => 70, 'company' => 25, 'admin' => 5],
                ],
                'companies' => [
                    'total' => 20,
                    'new_30d' => 2,
                    'by_status' => ['pending' => 3, 'approved' => 15, 'rejected' => 1, 'suspended' => 1],
                ],
                'jobs' => ['total' => 40, 'published' => 22],
                'applications' => ['total' => 200, 'last_30d' => 30, 'prev_30d' => 20],
                'interviews' => ['scheduled' => 8],
                'trend' => [
                    ['month' => '2026-05', 'label' => 'May', 'users' => 5, 'applications' => 10],
                    ['month' => '2026-06', 'label' => 'Jun', 'users' => 8, 'applications' => 12],
                ],
                'pending_companies' => [
                    ['id' => 9, 'name' => 'Pending Co', 'industry' => 'Tech', 'created_at' => '2026-10-01T00:00:00+00:00'],
                ],
                'recent_activity' => [
                    ['id' => 1, 'action' => 'company.approved', 'auditable_type' => 'Company', 'user' => 'Super', 'created_at' => '2026-10-27T10:00:00+00:00'],
                ],
            ]),
            analytics: $this->analyticsPayload([
                'period' => ['months' => 6, 'from' => '2026-05-01T00:00:00+00:00', 'to' => '2026-10-27T00:00:00+00:00'],
                'recruitment_funnel' => [
                    'applications' => 200,
                    'shortlisted' => 40,
                    'interviewing' => 20,
                    'offered' => 10,
                    'hired' => 5,
                    'hire_rate' => 2.5,
                    'shortlist_rate' => 20.0,
                ],
                'breakdowns' => [
                    'applications_by_status' => ['submitted' => 50, 'hired' => 5, 'rejected' => 10],
                ],
                'trends' => [
                    'monthly' => [
                        ['month' => '2026-05', 'users' => 5, 'applications' => 10, 'jobs' => 2, 'companies' => 1, 'interviews' => 1, 'offers' => 0, 'hired' => 0],
                        ['month' => '2026-06', 'users' => 8, 'applications' => 12, 'jobs' => 3, 'companies' => 1, 'interviews' => 2, 'offers' => 1, 'hired' => 1],
                    ],
                ],
                'top_companies' => [
                    ['id' => 1, 'name' => 'Acme', 'status' => 'approved', 'jobs' => 4, 'applications' => 40, 'hired' => 2],
                ],
                'top_jobs' => [
                    ['id' => 2, 'title' => 'Engineer', 'company' => 'Acme', 'status' => 'published', 'applications' => 18, 'hired' => 1],
                ],
            ])
        );

        $html = $this->get(route('frontend.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('100', $html);
        $this->assertStringContainsString('22', $html); // published jobs
        $this->assertStringContainsString('200', $html); // applications
        $this->assertStringContainsString('8', $html); // interviews
        $this->assertStringContainsString('Pending Co', $html);
        $this->assertStringContainsString('company.approved', $html);
        $this->assertStringContainsString('data-admin-analytics', $html);
        $this->assertStringContainsString(__('admin.overview.funnel_title'), $html);
        $this->assertStringContainsString('Acme', $html);
        $this->assertStringContainsString('Engineer', $html);
        $this->assertStringContainsString(__('Open jobs'), $html);
        $this->assertStringContainsString(__('of'), $html);
        $this->assertStringContainsString(__('jobs in total'), $html);
    }

    public function test_genuine_zeros_are_shown_not_as_unavailable(): void
    {
        $this->actingAsPlatformAdmin();
        $this->bindAdminApis(
            dashboard: $this->dashboardPayload([
                'users' => [
                    'total' => 0,
                    'new_30d' => 0,
                    'prev_30d' => 0,
                    'inactive' => 0,
                    'by_type' => ['candidate' => 0, 'company' => 0, 'admin' => 0],
                ],
                'companies' => [
                    'total' => 0,
                    'new_30d' => 0,
                    'by_status' => ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'suspended' => 0],
                ],
                'jobs' => ['total' => 0, 'published' => 0],
                'applications' => ['total' => 0, 'last_30d' => 0, 'prev_30d' => 0],
                'interviews' => ['scheduled' => 0],
                'trend' => [],
                'pending_companies' => [],
                'recent_activity' => [],
            ]),
            analytics: [
                'message' => 'OK',
                'data' => null,
                'statusCode' => 503,
            ]
        );

        $html = $this->get(route('frontend.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('data-admin-analytics-unavailable', $html);
        $this->assertStringContainsString(__('Open jobs'), $html);
        $this->assertStringContainsString(__('of'), $html);
        // Published jobs zero is formatted, not N/A placeholder for that card.
        $this->assertMatchesRegularExpression('/Open jobs[\s\S]{0,240}?>\s*0\s*</u', $html);
    }

    public function test_months_query_is_forwarded_to_analytics_api(): void
    {
        $this->actingAsPlatformAdmin();

        $api = Mockery::mock(AdminAPI::class);
        $this->expectPlatformMe($api);
        $api->shouldReceive('getDashboard')->once()->andReturn($this->dashboardPayload());
        $api->shouldReceive('getAnalytics')->once()->with(['months' => 3])->andReturn($this->analyticsPayload([
            'period' => ['months' => 3],
            'trends' => ['monthly' => [
                ['month' => '2026-08', 'users' => 1, 'applications' => 1, 'jobs' => 0],
            ]],
        ]));
        $api->shouldNotReceive('getUsers');
        $api->shouldNotReceive('getCompanies');
        $this->app->instance(AdminAPI::class, $api);

        $html = $this->get(route('frontend.dashboard', ['months' => 3]))->assertOk()->getContent();
        $this->assertStringContainsString('name="months"', $html);
        $this->assertStringContainsString('2026-08', $html);
        $this->assertMatchesRegularExpression('/<option[^>]*value="3"[^>]*selected/i', $html);
    }

    public function test_dashboard_401_clears_session_for_platform_admin(): void
    {
        $this->actingAsPlatformAdmin();

        $api = Mockery::mock(AdminAPI::class);
        $this->expectPlatformMe($api);
        $api->shouldReceive('getDashboard')->once()->andReturn([
            'message' => 'Unauthenticated.',
            'data' => null,
            'statusCode' => 401,
        ]);
        $api->shouldNotReceive('getAnalytics');
        $this->app->instance(AdminAPI::class, $api);

        $this->get(route('frontend.dashboard'))
            ->assertRedirect(route('frontend.account.admin-login'));
        $this->assertFalse(ApiSession::check());
    }

    public function test_dashboard_403_keeps_session_and_shows_forbidden_state(): void
    {
        $this->actingAsPlatformAdmin();

        $api = Mockery::mock(AdminAPI::class);
        $this->expectPlatformMe($api);
        $api->shouldReceive('getDashboard')->once()->andReturn([
            'message' => 'Forbidden. Super Admin access only.',
            'data' => null,
            'statusCode' => 403,
        ]);
        $api->shouldReceive('getAnalytics')->once()->andReturn([
            'message' => 'Forbidden. Super Admin access only.',
            'data' => null,
            'statusCode' => 403,
        ]);
        $this->app->instance(AdminAPI::class, $api);

        $html = $this->get(route('frontend.dashboard'))->assertOk()->getContent();
        $this->assertStringContainsString(__('admin.overview.forbidden_body'), $html);
        $this->assertTrue(ApiSession::check());
        $this->assertSame('platform-token', ApiSession::token());
    }

    public function test_does_not_fan_out_list_apis_when_dashboard_succeeds(): void
    {
        $this->actingAsPlatformAdmin();

        $api = Mockery::mock(AdminAPI::class);
        $this->expectPlatformMe($api);
        $api->shouldReceive('getDashboard')->once()->andReturn($this->dashboardPayload());
        $api->shouldReceive('getAnalytics')->once()->andReturn($this->analyticsPayload());
        $api->shouldNotReceive('getUsers');
        $api->shouldNotReceive('getCompanies');
        $api->shouldNotReceive('getAuditLogs');
        $this->app->instance(AdminAPI::class, $api);

        $this->get(route('frontend.dashboard'))->assertOk();
    }

    public function test_admin_nav_still_reachable_from_overview(): void
    {
        $this->actingAsPlatformAdmin();
        $this->bindAdminApis(
            dashboard: $this->dashboardPayload(),
            analytics: $this->analyticsPayload()
        );

        $html = $this->get(route('frontend.dashboard'))->assertOk()->getContent();
        $this->assertStringContainsString(route('frontend.admin.companies.index', [], false), $html);
        $this->assertStringContainsString(route('frontend.admin.users.index', [], false), $html);
        $this->assertStringContainsString(route('frontend.admin.audit-logs.index', [], false), $html);
    }

    public function test_zero_previous_period_does_not_show_100_percent_growth(): void
    {
        $this->actingAsPlatformAdmin();
        $this->bindAdminApis(
            dashboard: $this->dashboardPayload([
                'users' => [
                    'total' => 6,
                    'new_30d' => 4,
                    'prev_30d' => 0,
                    'inactive' => 1,
                    'by_type' => ['candidate' => 4, 'company' => 2, 'admin' => 0],
                ],
                'applications' => [
                    'total' => 1,
                    'last_30d' => 1,
                    'prev_30d' => 0,
                ],
            ]),
            analytics: $this->analyticsPayload()
        );

        $html = $this->get(route('frontend.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString(__('admin.overview.growth_new_baseline'), $html);
        $this->assertStringNotContainsString('▲ 100%', $html);
        $this->assertStringNotContainsString('▼ 100%', $html);
        $this->assertDoesNotMatchRegularExpression('/data-admin-growth="users"[\s\S]{0,160}100%/u', $html);
        $this->assertDoesNotMatchRegularExpression('/data-admin-growth="applications"[\s\S]{0,160}100%/u', $html);
    }

    public function test_period_filter_updates_chart_titles_and_trend_months(): void
    {
        $this->actingAsPlatformAdmin();

        $api = Mockery::mock(AdminAPI::class);
        $this->expectPlatformMe($api);
        $api->shouldReceive('getDashboard')->once()->andReturn($this->dashboardPayload([
            'trend' => [
                ['month' => '2026-05', 'label' => 'May', 'users' => 1, 'applications' => 0],
            ],
        ]));
        $api->shouldReceive('getAnalytics')->once()->with(['months' => 9])->andReturn($this->analyticsPayload([
            'period' => ['months' => 9, 'from' => '2026-02-01T00:00:00+00:00', 'to' => '2026-10-27T00:00:00+00:00'],
            'trends' => [
                'monthly' => [
                    ['month' => '2026-02', 'users' => 0, 'applications' => 0, 'jobs' => 0],
                    ['month' => '2026-03', 'users' => 1, 'applications' => 0, 'jobs' => 0],
                    ['month' => '2026-04', 'users' => 0, 'applications' => 1, 'jobs' => 0],
                    ['month' => '2026-05', 'users' => 1, 'applications' => 0, 'jobs' => 1],
                    ['month' => '2026-06', 'users' => 0, 'applications' => 0, 'jobs' => 0],
                    ['month' => '2026-07', 'users' => 2, 'applications' => 0, 'jobs' => 0],
                    ['month' => '2026-08', 'users' => 0, 'applications' => 0, 'jobs' => 1],
                    ['month' => '2026-09', 'users' => 1, 'applications' => 0, 'jobs' => 0],
                    ['month' => '2026-10', 'users' => 1, 'applications' => 0, 'jobs' => 1],
                ],
            ],
        ]));
        $this->app->instance(AdminAPI::class, $api);

        $html = $this->get(route('frontend.dashboard', ['months' => 9]))->assertOk()->getContent();

        $this->assertStringContainsString(__('admin.overview.growth_title', ['count' => 9]), $html);
        $this->assertStringContainsString(__('admin.overview.monthly_title', ['count' => 9]), $html);
        $this->assertStringContainsString('data-trend-months="9"', $html);
        $this->assertStringContainsString('data-months="9"', $html);
        $this->assertStringContainsString('2026-02', $html);
        $this->assertMatchesRegularExpression('/<option[^>]*value="9"[^>]*selected/i', $html);
    }

    public function test_prefers_analytics_recent_activity_with_admin_actor(): void
    {
        $this->actingAsPlatformAdmin();
        $this->bindAdminApis(
            dashboard: $this->dashboardPayload([
                'recent_activity' => [
                    ['id' => 1, 'action' => 'dashboard.only', 'auditable_type' => 'User', 'user' => 'Legacy', 'created_at' => '2026-10-01T00:00:00+00:00'],
                ],
            ]),
            analytics: $this->analyticsPayload([
                'recent_activity' => [
                    [
                        'id' => 99,
                        'action' => 'company.approved',
                        'auditable_type' => 'Company',
                        'actor' => 'Platform Super',
                        'actor_type' => 'admin',
                        'created_at' => '2026-10-27T15:30:00+00:00',
                    ],
                ],
            ])
        );

        $html = $this->get(route('frontend.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('data-activity-source="analytics"', $html);
        $this->assertStringContainsString('company.approved', $html);
        $this->assertStringContainsString('Platform Super', $html);
        $this->assertStringContainsString('admin', $html);
        $this->assertStringNotContainsString('dashboard.only', $html);
        $this->assertStringContainsString('datetime="2026-10-27T15:30:00+00:00"', $html);
    }

    public function test_funnel_is_labeled_as_current_status_snapshot(): void
    {
        $this->actingAsPlatformAdmin();
        $this->bindAdminApis(
            dashboard: $this->dashboardPayload(),
            analytics: $this->analyticsPayload([
                'recruitment_funnel' => [
                    'applications' => 10,
                    'shortlisted' => 2,
                    'interviewing' => 1,
                    'offered' => 0,
                    'hired' => 1,
                    'hire_rate' => 10,
                    'shortlist_rate' => 20,
                ],
            ])
        );

        $html = $this->get(route('frontend.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('data-funnel-definition="current_status"', $html);
        $this->assertStringContainsString(__('admin.overview.funnel_title'), $html);
        $this->assertStringContainsString(__('admin.overview.funnel_subtitle'), $html);
        $this->assertStringContainsString(__('admin.overview.funnel_applications'), $html);
    }

    public function test_distribution_mismatch_note_when_status_sum_differs(): void
    {
        $this->actingAsPlatformAdmin();
        $this->bindAdminApis(
            dashboard: $this->dashboardPayload([
                'companies' => [
                    'total' => 5,
                    'new_30d' => 0,
                    'by_status' => ['pending' => 0, 'approved' => 2, 'rejected' => 0, 'suspended' => 0],
                ],
                'users' => [
                    'total' => 10,
                    'new_30d' => 0,
                    'prev_30d' => 0,
                    'inactive' => 0,
                    'by_type' => ['candidate' => 4, 'company' => 2, 'admin' => 0],
                ],
            ]),
            analytics: $this->analyticsPayload()
        );

        $html = $this->get(route('frontend.dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('data-admin-distribution-mismatch="companies"', $html);
        $this->assertStringContainsString('data-admin-distribution-mismatch="users"', $html);
        $this->assertStringContainsString(__('admin.overview.distribution_mismatch', ['sum' => 2, 'total' => 5]), $html);
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

    private function bindAdminApis(array $dashboard, array $analytics): void
    {
        $api = Mockery::mock(AdminAPI::class);
        $this->expectPlatformMe($api);
        $api->shouldReceive('getDashboard')->once()->andReturn($dashboard);
        $api->shouldReceive('getAnalytics')->once()->andReturn($analytics);
        $this->app->instance(AdminAPI::class, $api);
    }

    private function expectPlatformMe(mixed $api): void
    {
        $api->shouldReceive('me')->once()->andReturn([
            'message' => 'OK',
            'data' => [
                'id' => 7,
                'email' => 'super@example.com',
                'name' => 'Super Admin',
                'roles' => ['super_admin'],
                'permissions' => ['reports.view'],
            ],
            'errors' => null,
            'statusCode' => 200,
        ]);
    }

    /** @param  array<string, mixed>  $data */
    private function dashboardPayload(array $data = []): array
    {
        return [
            'message' => 'OK',
            'data' => $data + [
                'users' => [
                    'total' => 1,
                    'new_30d' => 0,
                    'prev_30d' => 0,
                    'inactive' => 0,
                    'by_type' => ['candidate' => 1, 'company' => 0, 'admin' => 0],
                ],
                'companies' => [
                    'total' => 0,
                    'new_30d' => 0,
                    'by_status' => ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'suspended' => 0],
                ],
                'jobs' => ['total' => 0, 'published' => 0],
                'applications' => ['total' => 0, 'last_30d' => 0, 'prev_30d' => 0],
                'interviews' => ['scheduled' => 0],
                'trend' => [],
                'pending_companies' => [],
                'recent_activity' => [],
                'generated_at' => '2026-10-27T12:00:00+00:00',
            ],
            'statusCode' => 200,
        ];
    }

    /** @param  array<string, mixed>  $data */
    private function analyticsPayload(array $data = []): array
    {
        return [
            'message' => 'OK',
            'data' => $data + [
                'period' => ['months' => 6],
                'overview' => [],
                'breakdowns' => [],
                'recruitment_funnel' => [
                    'applications' => 0,
                    'shortlisted' => 0,
                    'interviewing' => 0,
                    'offered' => 0,
                    'hired' => 0,
                    'hire_rate' => 0,
                    'shortlist_rate' => 0,
                ],
                'trends' => ['monthly' => []],
                'top_companies' => [],
                'top_jobs' => [],
                'pending_actions' => [],
                'recent_activity' => [],
                'generated_at' => '2026-10-27T12:00:00+00:00',
            ],
            'statusCode' => 200,
        ];
    }
}
