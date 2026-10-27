<?php

namespace Tests\Unit;

use App\Frontend\Candidate\CandidateDashboardAdapter;
use App\Frontend\Candidate\CandidateDashboardAnalytics;
use Carbon\Carbon;
use Tests\Support\FakeCandidateDashboardKpiGateway;
use Tests\TestCase;

class CandidateDashboardAnalyticsTest extends TestCase
{
    private FakeCandidateDashboardKpiGateway $gateway;

    private CandidateDashboardAdapter $adapter;

    private CandidateDashboardAnalytics $analytics;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = new FakeCandidateDashboardKpiGateway;
        $this->analytics = new CandidateDashboardAnalytics;
        $this->adapter = new CandidateDashboardAdapter($this->gateway, $this->analytics);
        Carbon::setTestNow(Carbon::parse('2026-10-27T12:00:00+00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_empty_account_analytics_are_available_with_empty_states(): void
    {
        $payload = $this->adapter->load();
        $a = $payload['analytics'];

        $this->assertTrue($a['activity']['available']);
        $this->assertTrue($a['activity']['empty']);
        $this->assertSame(0, $a['activity']['periods']['30']['total']);
        $this->assertCount(30, $a['activity']['periods']['30']['values']);
        $this->assertTrue($a['pipeline']['available']);
        $this->assertTrue($a['pipeline']['empty']);
        $this->assertSame(0, $a['pipeline']['total']);
        $this->assertTrue($a['pipeline']['reconciled']);
        $this->assertTrue($a['interview_timeline']['available']);
        $this->assertTrue($a['interview_timeline']['empty']);
        $this->assertTrue($a['profile_ring']['available']);
        $this->assertSame(0, $a['profile_ring']['percent']);
    }

    public function test_populated_applications_feed_activity_and_pipeline(): void
    {
        $this->gateway->applicationPages = [
            $this->gateway->okPage([
                ['id' => 1, 'status' => 'submitted', 'applied_at' => '2026-10-20T10:00:00+00:00'],
                ['id' => 2, 'status' => 'interviewing', 'applied_at' => '2026-10-25T10:00:00+00:00'],
                ['id' => 3, 'status' => 'hired', 'applied_at' => '2026-09-01T10:00:00+00:00'],
            ], ['total' => 3, 'last_page' => 1, 'current_page' => 1, 'per_page' => 100]),
        ];

        $a = $this->adapter->load()['analytics'];

        $this->assertTrue($a['activity']['available']);
        $this->assertFalse($a['activity']['empty']);
        $this->assertSame(2, $a['activity']['periods']['30']['total']); // hired is outside 30d
        $this->assertSame(3, $a['activity']['periods']['90']['total']);
        $this->assertSame(3, $a['pipeline']['total']);
        $this->assertTrue($a['pipeline']['reconciled']);

        $keys = array_column($a['pipeline']['segments'], 'key');
        $this->assertContains('submitted', $keys);
        $this->assertContains('interviewing', $keys);
        $this->assertContains('hired', $keys);
    }

    public function test_status_breakdown_keeps_unknown_separate(): void
    {
        $pipeline = $this->analytics->pipeline([
            ['id' => 1, 'status' => 'submitted'],
            ['id' => 2, 'status' => 'mystery'],
            ['id' => 3, 'status' => 'ai_review'],
            ['id' => 4, 'status' => 'screening'],
        ], true, 4);

        $this->assertTrue($pipeline['available']);
        $this->assertSame(['mystery', 'ai_review'], $pipeline['unknown_statuses']);
        $unknown = collect($pipeline['segments'])->firstWhere('key', 'unknown');
        $this->assertNotNull($unknown);
        $this->assertSame(2, $unknown['count']);
        $this->assertFalse($unknown['known']);
        $this->assertSame(4, $pipeline['total']);
        $this->assertTrue($pipeline['reconciled']);
    }

    public function test_date_range_filtering_uses_applied_at_only_within_window(): void
    {
        $now = Carbon::parse('2026-10-27T12:00:00+00:00');
        $activity = $this->analytics->activity([
            ['id' => 1, 'status' => 'submitted', 'applied_at' => '2026-10-26T08:00:00+00:00'],
            ['id' => 2, 'status' => 'submitted', 'applied_at' => '2026-10-10T08:00:00+00:00'],
            ['id' => 3, 'status' => 'submitted', 'applied_at' => '2026-07-01T08:00:00+00:00'],
        ], true, $now);

        $this->assertSame(1, $activity['periods']['7']['total']);
        $this->assertSame(2, $activity['periods']['30']['total']);
        $this->assertSame(2, $activity['periods']['90']['total']); // Jul 1 is >90d before Oct 27
    }

    public function test_paginated_applications_are_merged_for_analytics(): void
    {
        $this->gateway->applicationPages = [
            $this->gateway->okPage(
                [['id' => 1, 'status' => 'submitted', 'applied_at' => '2026-10-20T10:00:00+00:00']],
                ['total' => 2, 'last_page' => 2, 'current_page' => 1, 'per_page' => 100]
            ),
            $this->gateway->okPage(
                [['id' => 2, 'status' => 'offered', 'applied_at' => '2026-10-22T10:00:00+00:00']],
                ['total' => 2, 'last_page' => 2, 'current_page' => 2, 'per_page' => 100]
            ),
        ];

        $payload = $this->adapter->load();

        $this->assertSame(2, $this->gateway->applicationCalls);
        $this->assertSame(2, $payload['kpis']['total_applications']['value']);
        $this->assertSame(2, $payload['analytics']['pipeline']['total']);
        $this->assertSame(2, $payload['analytics']['activity']['periods']['30']['total']);
        $this->assertTrue($payload['analytics']['pipeline']['reconciled']);
    }

    public function test_incomplete_history_marks_activity_and_pipeline_unavailable(): void
    {
        // Force incomplete scan: last_page high but only one page provided → hits max pages path
        // Simpler: gateway returns error after first success? Use incomplete via meta last_page with
        // MAX_PAGES exceeded — or return complete=false by exhausting pages.
        // Use applications incomplete: last_page=25 with only 1 page of data repeatedly from fake fallback.
        $pages = [];
        for ($i = 1; $i <= 25; $i++) {
            $pages[] = $this->gateway->okPage(
                [['id' => $i, 'status' => 'submitted', 'applied_at' => '2026-10-20T10:00:00+00:00']],
                ['total' => 25, 'last_page' => 25, 'current_page' => $i, 'per_page' => 1]
            );
        }
        $this->gateway->applicationPages = $pages;

        $a = $this->adapter->load()['analytics'];

        // MAX_PAGES=20 → incomplete
        $this->assertFalse($a['activity']['available']);
        $this->assertSame('incomplete', $a['activity']['reason']);
        $this->assertFalse($a['pipeline']['available']);
    }

    public function test_missing_applied_at_marks_historical_unavailable_not_fake_zeros(): void
    {
        $activity = $this->analytics->activity([
            ['id' => 1, 'status' => 'submitted', 'applied_at' => null],
            ['id' => 2, 'status' => 'submitted', 'applied_at' => '2026-10-20T10:00:00+00:00'],
        ], true, Carbon::now());

        $this->assertFalse($activity['available']);
        $this->assertSame('missing_dates', $activity['reason']);
        $this->assertSame([], (array) $activity['periods']);
    }

    public function test_api_failure_marks_visualizations_unavailable(): void
    {
        $this->gateway->applicationPages = [$this->gateway->error(503, 'Down')];
        $this->gateway->interviewPages = [$this->gateway->error(500, 'Boom')];

        $payload = $this->adapter->load();
        $a = $payload['analytics'];

        $this->assertFalse($a['activity']['available']);
        $this->assertFalse($a['pipeline']['available']);
        $this->assertFalse($a['interview_timeline']['available']);
        $this->assertNotNull($payload['loadError']);
    }

    public function test_profile_ring_mirrors_seven_field_calculation(): void
    {
        $this->gateway->profileResponse = $this->gateway->okPage([
            'phone' => '+1',
            'location' => 'Cairo',
            'headline' => null,
            'bio' => null,
            'has_no_experience' => true,
            'experiences' => [],
            'educations' => [],
            'cvs' => [],
        ]);

        $ring = $this->adapter->load()['analytics']['profile_ring'];

        $this->assertTrue($ring['available']);
        $this->assertSame(3, $ring['filled']); // phone, location, experience
        $this->assertSame(7, $ring['total_fields']);
        $this->assertSame(43, $ring['percent']); // round(3/7*100)
        $this->assertTrue($ring['checks']['phone']);
        $this->assertFalse($ring['checks']['resume']);
    }

    public function test_interview_timeline_orders_upcoming_only(): void
    {
        $this->gateway->interviewPages = [
            $this->gateway->okPage([
                [
                    'id' => 2,
                    'status' => 'scheduled',
                    'start_at' => '2026-11-05T09:00:00+00:00',
                    'application' => ['job_title' => 'Later', 'company_name' => 'B'],
                ],
                [
                    'id' => 1,
                    'status' => 'rescheduled',
                    'start_at' => '2026-10-28T09:00:00+00:00',
                    'application' => ['job_title' => 'Soon', 'company_name' => 'A'],
                ],
                [
                    'id' => 3,
                    'status' => 'scheduled',
                    'start_at' => '2026-10-01T09:00:00+00:00', // past
                    'application' => ['job_title' => 'Past', 'company_name' => 'C'],
                ],
                [
                    'id' => 4,
                    'status' => 'cancelled',
                    'start_at' => '2026-11-01T09:00:00+00:00',
                    'application' => ['job_title' => 'Cancelled', 'company_name' => 'D'],
                ],
            ], ['total' => 4, 'last_page' => 1, 'current_page' => 1, 'per_page' => 100]),
        ];

        $timeline = $this->adapter->load()['analytics']['interview_timeline'];

        $this->assertTrue($timeline['available']);
        $this->assertFalse($timeline['empty']);
        $this->assertCount(2, $timeline['items']);
        $this->assertSame(1, $timeline['items'][0]['id']);
        $this->assertSame('Soon', $timeline['items'][0]['job_title']);
        $this->assertSame(2, $timeline['items'][1]['id']);
        $this->assertSame(2, $this->adapter->load()['kpis']['upcoming_interviews']['value']);
    }
}
