<?php

namespace Tests\Unit;

use App\Frontend\Candidate\CandidateDashboardAdapter;
use Carbon\Carbon;
use Tests\Support\FakeCandidateDashboardKpiGateway;
use Tests\TestCase;

class CandidateDashboardAdapterTest extends TestCase
{
    private FakeCandidateDashboardKpiGateway $gateway;

    private CandidateDashboardAdapter $adapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = new FakeCandidateDashboardKpiGateway;
        $this->adapter = new CandidateDashboardAdapter($this->gateway);
        Carbon::setTestNow(Carbon::parse('2026-10-27T12:00:00+00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_real_zero_counts_are_available(): void
    {
        $payload = $this->adapter->load();

        $this->assertFalse($payload['unauthorized']);
        $this->assertNull($payload['loadError']);

        foreach (['total_applications', 'applications_in_progress', 'upcoming_interviews', 'offers_received'] as $key) {
            $this->assertTrue($payload['kpis'][$key]['available'], $key);
            $this->assertSame(0, $payload['kpis'][$key]['value'], $key);
            $this->assertSame('0', $payload['kpis'][$key]['display'], $key);
        }

        $this->assertFalse($payload['kpis']['pending_assessments']['available']);
        $this->assertNull($payload['kpis']['pending_assessments']['value']);
        $this->assertTrue($payload['kpis']['profile_completion']['available']);
        $this->assertSame(0, $payload['kpis']['profile_completion']['value']);
    }

    public function test_populated_counts_and_in_progress_exclude_terminal_statuses(): void
    {
        $this->gateway->applicationPages = [
            $this->gateway->okPage([
                ['id' => 1, 'status' => 'submitted'],
                ['id' => 2, 'status' => 'screening'],
                ['id' => 3, 'status' => 'hired'],
                ['id' => 4, 'status' => 'rejected'],
                ['id' => 5, 'status' => 'withdrawn'],
                ['id' => 6, 'status' => 'offered'],
            ], ['total' => 6, 'last_page' => 1, 'current_page' => 1, 'per_page' => 100]),
        ];
        $this->gateway->offersResponse = $this->gateway->okPage([
            ['id' => 10, 'status' => 'sent'],
            ['id' => 11, 'status' => 'accepted'],
        ]);
        $this->gateway->interviewPages = [
            $this->gateway->okPage([
                [
                    'id' => 1,
                    'status' => 'scheduled',
                    'start_at' => '2026-10-28T09:00:00+00:00',
                ],
                [
                    'id' => 2,
                    'status' => 'scheduled',
                    'start_at' => '2026-10-20T09:00:00+00:00', // past
                ],
                [
                    'id' => 3,
                    'status' => 'cancelled',
                    'start_at' => '2026-10-29T09:00:00+00:00',
                ],
                [
                    'id' => 4,
                    'status' => 'rescheduled',
                    'start_at' => '2026-11-01T09:00:00+00:00',
                ],
            ], ['total' => 4, 'last_page' => 1, 'current_page' => 1, 'per_page' => 100]),
        ];

        $payload = $this->adapter->load();
        $kpis = $payload['kpis'];

        $this->assertSame(6, $kpis['total_applications']['value']);
        $this->assertSame(3, $kpis['applications_in_progress']['value']); // submitted, screening, offered
        $this->assertSame(2, $kpis['upcoming_interviews']['value']); // scheduled future + rescheduled future
        $this->assertSame(2, $kpis['offers_received']['value']);
    }

    public function test_pagination_meta_total_is_preferred_for_total_applications(): void
    {
        $this->gateway->applicationPages = [
            $this->gateway->okPage([
                ['id' => 1, 'status' => 'submitted'],
                ['id' => 2, 'status' => 'hired'],
            ], ['total' => 42, 'last_page' => 1, 'current_page' => 1, 'per_page' => 100]),
        ];

        $payload = $this->adapter->load();

        $this->assertSame(42, $payload['kpis']['total_applications']['value']);
        $this->assertSame(1, $payload['kpis']['applications_in_progress']['value']);
    }

    public function test_unknown_application_statuses_are_not_counted_as_in_progress(): void
    {
        $this->gateway->applicationPages = [
            $this->gateway->okPage([
                ['id' => 1, 'status' => 'mystery_stage'],
                ['id' => 2, 'status' => 'ai_review'],
                ['id' => 3, 'status' => 'interviewing'],
            ], ['total' => 3, 'last_page' => 1, 'current_page' => 1, 'per_page' => 100]),
        ];

        $payload = $this->adapter->load();

        $this->assertSame(3, $payload['kpis']['total_applications']['value']);
        $this->assertSame(1, $payload['kpis']['applications_in_progress']['value']);
    }

    public function test_pending_assessments_remain_unavailable_without_backend_list(): void
    {
        $payload = $this->adapter->load();

        $this->assertFalse($payload['kpis']['pending_assessments']['available']);
        $this->assertNull($payload['kpis']['pending_assessments']['value']);
        $this->assertSame('kpi.assessments_unavailable', $payload['kpis']['pending_assessments']['meta_key']);
    }

    public function test_api_failure_marks_metrics_unavailable_without_fabricating_zero(): void
    {
        $this->gateway->applicationPages = [$this->gateway->error(503, 'Service unavailable')];
        $this->gateway->interviewPages = [$this->gateway->error(500, 'Boom')];
        $this->gateway->offersResponse = $this->gateway->error(403, 'Forbidden');
        $this->gateway->profileResponse = $this->gateway->error(503, 'Down');

        $payload = $this->adapter->load();

        $this->assertFalse($payload['unauthorized']);
        $this->assertNotNull($payload['loadError']);

        foreach (['total_applications', 'applications_in_progress', 'upcoming_interviews', 'offers_received', 'profile_completion'] as $key) {
            $this->assertFalse($payload['kpis'][$key]['available'], $key);
            $this->assertNull($payload['kpis'][$key]['value'], $key);
            $this->assertSame('—', $payload['kpis'][$key]['display'], $key);
        }
    }

    public function test_unauthorized_sets_flag(): void
    {
        $this->gateway->applicationPages = [$this->gateway->error(401, 'Unauthenticated')];

        $payload = $this->adapter->load();

        $this->assertTrue($payload['unauthorized']);
        $this->assertFalse($payload['kpis']['total_applications']['available']);
    }

    public function test_profile_completion_calculates_documented_fields(): void
    {
        $this->gateway->profileResponse = $this->gateway->okPage([
            'phone' => '+20100',
            'location' => 'Cairo',
            'headline' => 'Engineer',
            'bio' => 'Hello',
            'has_no_experience' => true,
            'years_experience' => null,
            'experiences' => [],
            'educations' => [['id' => 1, 'degree' => 'BSc']],
            'cvs' => [['id' => 9, 'file_name' => 'cv.pdf']],
        ]);

        $payload = $this->adapter->load();
        $profile = $payload['kpis']['profile_completion'];

        $this->assertTrue($profile['available']);
        $this->assertSame(100, $profile['value']);
        $this->assertSame('100%', $profile['display']);
        $this->assertSame(7, $profile['total_fields']);
        $this->assertTrue($profile['checks']['resume']);
    }

    public function test_profile_completion_partial_and_unavailable_without_profile_payload(): void
    {
        $this->gateway->profileResponse = $this->gateway->okPage([
            'phone' => '+20100',
            'location' => null,
            'headline' => null,
            'bio' => null,
            'has_no_experience' => false,
            'years_experience' => null,
            'experiences' => [],
            'educations' => [],
            'cvs' => [],
        ]);

        $partial = $this->adapter->load()['kpis']['profile_completion'];
        $this->assertSame(14, $partial['value']); // 1/7 ≈ 14%
        $this->assertSame('14%', $partial['display']);

        $this->gateway->profileResponse = [
            'statusCode' => 200,
            'message' => 'OK',
            'data' => null,
            'meta' => null,
            'errors' => null,
        ];

        $missing = $this->adapter->load()['kpis']['profile_completion'];
        $this->assertFalse($missing['available']);
        $this->assertNull($missing['value']);
    }

    public function test_walks_application_pages_for_in_progress_counts(): void
    {
        $this->gateway->applicationPages = [
            $this->gateway->okPage(
                [['id' => 1, 'status' => 'submitted']],
                ['total' => 2, 'last_page' => 2, 'current_page' => 1, 'per_page' => 100]
            ),
            $this->gateway->okPage(
                [['id' => 2, 'status' => 'interviewing']],
                ['total' => 2, 'last_page' => 2, 'current_page' => 2, 'per_page' => 100]
            ),
        ];

        $payload = $this->adapter->load();

        $this->assertSame(2, $this->gateway->applicationCalls);
        $this->assertSame(2, $payload['kpis']['total_applications']['value']);
        $this->assertSame(2, $payload['kpis']['applications_in_progress']['value']);
    }
}
