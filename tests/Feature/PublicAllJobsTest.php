<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Http\Middleware\SetFrontendLocale;
use App\Models\JobAPI;
use App\Models\LookupAPI;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\App;
use Mockery;
use Tests\TestCase;

class PublicAllJobsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Laravel 13 web stack uses PreventRequestForgery (CSRF); disable for Feature POSTs.
        $this->withoutMiddleware(PreventRequestForgery::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_renders_filters_sort_count_badges_and_cta(): void
    {
        $this->mockJobsList([
            $this->jobRow(1, [
                'title' => 'Platform Engineer',
                'hiring_model' => 'direct',
                'employment_type' => 'full_time',
                'salary' => ['min' => 90000, 'max' => 120000, 'currency' => 'USD', 'period' => 'year'],
            ]),
            $this->jobRow(2, [
                'title' => 'Agency Recruiter Role',
                'hiring_model' => 'agency',
                'employment_type' => 'contract',
            ]),
        ], pagination: [
            'current_page' => 1,
            'last_page' => 2,
            'per_page' => 15,
            'total' => 16,
            'from' => 1,
            'to' => 15,
        ]);

        $this->mockCategories([
            ['id' => 3, 'name' => 'Engineering'],
            ['id' => 5, 'name' => 'Design'],
        ]);

        $html = $this->get(route('frontend.jobs.index'))->assertOk()->getContent();

        $this->assertStringContainsString(__('public.jobs.index_title'), $html);
        $this->assertStringContainsString('name="employment_type"', $html);
        $this->assertStringContainsString('name="salary_min"', $html);
        $this->assertStringContainsString('name="salary_max"', $html);
        $this->assertStringContainsString('name="discipline"', $html);
        $this->assertStringContainsString('name="sort"', $html);
        $this->assertStringContainsString(__('public.jobs.sort_newest'), $html);
        $this->assertStringContainsString(__('public.jobs.sort_salary'), $html);
        $this->assertStringContainsString(__('public.jobs.sort_title'), $html);
        $this->assertStringContainsString(__('public.jobs.sort_deadline'), $html);
        $this->assertStringNotContainsString('value="company"', $html);
        $this->assertStringNotContainsString('value="relevance"', $html);
        $this->assertStringContainsString('name="q"', $html);
        $this->assertStringContainsString('name="location"', $html);
        $this->assertStringContainsString('name="work_mode"', $html);
        $this->assertStringContainsString(__('public.jobs.clear_filters'), $html);
        $this->assertStringContainsString(__('public.jobs.match_count', ['count' => 16]), $html);
        $this->assertStringContainsString(__('common.sources.direct_hire'), $html);
        $this->assertStringContainsString(__('common.sources.agency_managed'), $html);
        $this->assertStringContainsString(__('public.jobs.alert_title'), $html);
        $this->assertStringContainsString(__('public.jobs.pagination_status', ['from' => 1, 'to' => 15, 'total' => 16]), $html);
        $this->assertStringContainsString('Platform Engineer', $html);
    }

    public function test_employment_type_filter_is_forwarded_to_jobs_api(): void
    {
        $jobs = Mockery::mock(JobAPI::class);
        $jobs->shouldReceive('getJobs')->once()->with(Mockery::on(function (array $query) {
            return ($query['employment_type'] ?? null) === 'full_time'
                && ($query['page'] ?? null) === '1';
        }))->andReturn($this->okListResponse([$this->jobRow(1)]));
        $this->app->instance(JobAPI::class, $jobs);
        $this->mockCategories();

        $this->get(route('frontend.jobs.index', ['employment_type' => 'full_time', 'page' => 1]))
            ->assertOk()
            ->assertSee('Platform Engineer');
    }

    public function test_salary_and_discipline_filters_are_forwarded_using_backend_category_id(): void
    {
        $jobs = Mockery::mock(JobAPI::class);
        $jobs->shouldReceive('getJobs')->once()->with(Mockery::on(function (array $query) {
            return (string) ($query['salary_min'] ?? '') === '80000'
                && (string) ($query['salary_max'] ?? '') === '140000'
                && (string) ($query['category_id'] ?? '') === '3'
                && ! array_key_exists('discipline', $query);
        }))->andReturn($this->okListResponse([$this->jobRow(1)]));
        $this->app->instance(JobAPI::class, $jobs);
        $this->mockCategories([['id' => 3, 'name' => 'Engineering']]);

        $this->get(route('frontend.jobs.index', [
            'salary_min' => 80000,
            'salary_max' => 140000,
            'discipline' => 3,
        ]))->assertOk();
    }

    public function test_public_job_search_validates_keyword_and_location_lengths(): void
    {
        $this->get(route('frontend.jobs.index', ['q' => str_repeat('x', 101)]))
            ->assertSessionHasErrors('q');

        $this->get(route('frontend.jobs.index', ['location' => str_repeat('x', 101)]))
            ->assertSessionHasErrors('location');
    }

    public function test_sorting_maps_to_backend_sort_fields(): void
    {
        $jobs = Mockery::mock(JobAPI::class);
        $jobs->shouldReceive('getJobs')->once()->with(Mockery::on(function (array $query) {
            return ($query['sort_by'] ?? null) === 'salary_min'
                && ($query['sort_dir'] ?? null) === 'desc';
        }))->andReturn($this->okListResponse([$this->jobRow(1)]));
        $this->app->instance(JobAPI::class, $jobs);
        $this->mockCategories();

        $this->get(route('frontend.jobs.index', ['sort' => 'salary']))->assertOk();
    }

    public function test_unsupported_company_sort_falls_back_to_newest_not_title(): void
    {
        $jobs = Mockery::mock(JobAPI::class);
        $jobs->shouldReceive('getJobs')->once()->with(Mockery::on(function (array $query) {
            return ($query['sort_by'] ?? null) === 'published_at'
                && ($query['sort_dir'] ?? null) === 'desc';
        }))->andReturn($this->okListResponse([$this->jobRow(1)]));
        $this->app->instance(JobAPI::class, $jobs);
        $this->mockCategories();

        $this->get(route('frontend.jobs.index', ['sort' => 'company']))->assertOk();
    }

    public function test_pagination_preserves_active_filters_in_links(): void
    {
        $this->mockJobsList([$this->jobRow(1)], pagination: [
            'current_page' => 1,
            'last_page' => 3,
            'per_page' => 15,
            'total' => 40,
            'from' => 1,
            'to' => 15,
        ]);
        $this->mockCategories();

        $html = $this->get(route('frontend.jobs.index', [
            'employment_type' => 'contract',
            'location' => 'Cairo',
            'sort' => 'newest',
        ]))->assertOk()->getContent();

        $this->assertStringContainsString('employment_type=contract', $html);
        $this->assertStringContainsString('location=Cairo', $html);
        $this->assertStringContainsString('page=2', $html);
    }

    public function test_empty_results_show_localized_empty_state(): void
    {
        $this->mockJobsList([], pagination: [
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 15,
            'total' => 0,
            'from' => null,
            'to' => null,
        ]);
        $this->mockCategories();

        $this->get(route('frontend.jobs.index', ['q' => 'zzzz']))
            ->assertOk()
            ->assertSee(__('public.jobs.empty_title'))
            ->assertSee(__('public.jobs.empty_body'));
    }

    public function test_backend_error_is_shown_on_jobs_page(): void
    {
        $jobs = Mockery::mock(JobAPI::class);
        $jobs->shouldReceive('getJobs')->once()->andReturn([
            'message' => 'Upstream unavailable',
            'data' => null,
            'errors' => ['error' => 'Upstream unavailable'],
            'meta' => null,
            'statusCode' => 503,
        ]);
        $this->app->instance(JobAPI::class, $jobs);
        $this->mockCategories();

        $this->get(route('frontend.jobs.index'))
            ->assertOk()
            ->assertSee(__('public.jobs.load_error_title'))
            ->assertSee('Upstream unavailable');
    }

    public function test_backend_sql_debug_payload_is_not_shown_on_jobs_page(): void
    {
        $sqlLeak = "SQLSTATE[42S22]: Column not found: 1054 Unknown column 'jobs.requisition_code' in 'field list' (Connection: mysql, Host: 127.0.0.1, Port: 3306, Database: recruitment_audit, SQL: select `jobs`.`requisition_code`)";

        $jobs = Mockery::mock(JobAPI::class);
        $jobs->shouldReceive('getJobs')->once()->andReturn([
            'message' => $sqlLeak,
            'data' => null,
            'errors' => ['error' => $sqlLeak],
            'meta' => null,
            'statusCode' => 500,
        ]);
        $this->app->instance(JobAPI::class, $jobs);
        $this->mockCategories();

        $html = $this->get(route('frontend.jobs.index'))->assertOk()->getContent();

        $this->assertStringContainsString(__('public.jobs.load_error_title'), $html);
        $this->assertStringContainsString(__('public.jobs.load_error_body'), $html);
        $this->assertStringNotContainsString('SQLSTATE', $html);
        $this->assertStringNotContainsString('requisition_code', $html);
        $this->assertStringNotContainsString('recruitment_audit', $html);
        $this->assertStringNotContainsString('Connection: mysql', $html);
        $this->assertStringNotContainsString('Host: 127.0.0.1', $html);
    }

    public function test_guest_save_redirects_to_login_with_return_path(): void
    {
        $this->post(route('frontend.jobs.save', ['jobId' => 42]))
            ->assertRedirect(route('frontend.account.login'));
    }

    public function test_candidate_can_save_job_via_mocked_api(): void
    {
        $this->actingAsCandidate();

        $jobs = Mockery::mock(JobAPI::class);
        $jobs->shouldReceive('saveJob')->once()->with(42)->andReturn([
            'message' => 'Saved',
            'data' => ['job_id' => 42],
            'errors' => [],
            'statusCode' => 200,
        ]);
        $this->app->instance(JobAPI::class, $jobs);

        $this->from(route('frontend.jobs.index'))
            ->post(route('frontend.jobs.save', ['jobId' => 42]))
            ->assertRedirect(route('frontend.jobs.index'))
            ->assertSessionHas('success_key', 'public.jobs.flash.saved');
    }

    public function test_candidate_save_failure_surfaces_api_message(): void
    {
        $this->actingAsCandidate();

        $jobs = Mockery::mock(JobAPI::class);
        $jobs->shouldReceive('saveJob')->once()->with(42)->andReturn([
            'message' => 'Already saved',
            'data' => null,
            'errors' => ['error' => 'Already saved'],
            'statusCode' => 409,
        ]);
        $this->app->instance(JobAPI::class, $jobs);

        $this->from(route('frontend.jobs.index'))
            ->post(route('frontend.jobs.save', ['jobId' => 42]))
            ->assertRedirect(route('frontend.jobs.index'))
            ->assertSessionHasErrors();
    }

    public function test_company_account_cannot_save_job(): void
    {
        ApiSession::store('co-token', [
            'id' => 22,
            'email' => 'hr@example.com',
            'name' => 'HR',
            'account_type' => 'company',
            'roles' => ['company_admin'],
        ]);

        $this->post(route('frontend.jobs.save', ['jobId' => 42]))
            ->assertRedirect();
    }

    public function test_arabic_locale_renders_rtl_and_localized_jobs_copy(): void
    {
        $this->mockJobsList([$this->jobRow(1)], pagination: [
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 15,
            'total' => 1,
            'from' => 1,
            'to' => 1,
        ]);
        $this->mockCategories();

        $html = $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('frontend.jobs.index'))
            ->assertOk()
            ->getContent();

        $this->assertSame('ar', App::getLocale());
        $this->assertMatchesRegularExpression('/<html[^>]*\sdir="rtl"/', $html);
        $this->assertStringContainsString(__('public.jobs.index_title'), $html);
        $this->assertStringContainsString(__('public.jobs.clear_filters'), $html);
    }

    public function test_existing_keyword_location_work_mode_search_still_works(): void
    {
        $jobs = Mockery::mock(JobAPI::class);
        $jobs->shouldReceive('getJobs')->once()->with(Mockery::on(function (array $query) {
            return ($query['q'] ?? null) === 'laravel'
                && ($query['location'] ?? null) === 'Remote'
                && ($query['work_mode'] ?? null) === 'remote';
        }))->andReturn($this->okListResponse([$this->jobRow(1, ['title' => 'Laravel Developer'])]));
        $this->app->instance(JobAPI::class, $jobs);
        $this->mockCategories();

        $this->get(route('frontend.jobs.index', [
            'q' => 'laravel',
            'location' => 'Remote',
            'work_mode' => 'remote',
        ]))
            ->assertOk()
            ->assertSee('Laravel Developer');
    }

    public function test_saved_job_shows_unsave_control_for_candidate(): void
    {
        $this->actingAsCandidate();
        $this->mockJobsList([
            $this->jobRow(9, ['title' => 'Saved Role', 'saved_by_me' => true]),
        ]);
        $this->mockCategories();

        $html = $this->get(route('frontend.jobs.index'))->assertOk()->getContent();

        $this->assertStringContainsString(__('public.jobs.unsave_job'), $html);
        $this->assertStringContainsString(route('frontend.jobs.unsave', ['jobId' => 9], false), $html);
    }

    /** @param list<array<string, mixed>> $rows */
    private function mockJobsList(array $rows, ?array $pagination = null): void
    {
        $jobs = Mockery::mock(JobAPI::class);
        $jobs->shouldReceive('getJobs')->andReturn($this->okListResponse($rows, $pagination));
        $this->app->instance(JobAPI::class, $jobs);
    }

    /** @param list<array{id:int|string,name:string}> $categories */
    private function mockCategories(array $categories = []): void
    {
        $lookup = Mockery::mock(LookupAPI::class);
        $lookup->shouldReceive('getCategories')->andReturn([
            'message' => 'OK',
            'data' => $categories,
            'errors' => [],
            'statusCode' => 200,
        ]);
        $this->app->instance(LookupAPI::class, $lookup);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  array<string, mixed>|null  $pagination
     * @return array{message:string,data:mixed,errors:array,meta:?array,statusCode:int}
     */
    private function okListResponse(array $rows, ?array $pagination = null): array
    {
        $pagination ??= [
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 15,
            'total' => count($rows),
            'from' => count($rows) ? 1 : null,
            'to' => count($rows) ?: null,
        ];

        return [
            'message' => 'OK',
            'data' => $rows,
            'errors' => [],
            'meta' => ['pagination' => $pagination],
            'statusCode' => 200,
        ];
    }

    /** @param array<string, mixed> $overrides */
    private function jobRow(int $id, array $overrides = []): array
    {
        return array_merge([
            'id' => $id,
            'title' => 'Platform Engineer',
            'company' => ['name' => 'Acme'],
            'location' => 'Remote',
            'work_mode' => 'remote',
            'employment_type' => 'full_time',
            'hiring_model' => null,
            'saved_by_me' => false,
            'salary' => ['min' => null, 'max' => null, 'currency' => null, 'period' => null],
        ], $overrides);
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
