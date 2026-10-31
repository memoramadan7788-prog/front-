<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Models\CandidateAPI;
use App\Models\JobAPI;
use Illuminate\Support\Facades\App;
use Mockery;
use Tests\TestCase;

class CandidateVacanciesSearchTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_keyword_search_renders_job_title_and_company_matches_from_jobs_api(): void
    {
        $this->actingAsCandidate();
        $this->mockCandidateContext();
        $this->mockJobs(['q' => 'Backend Developer'], [
            $this->job(1, 'Backend Developer', 'Northwind'),
            $this->job(2, 'Product Engineer', 'Backend Developer Inc.'),
        ]);

        $this->get(route('frontend.candidate.vacancies.index', ['q' => 'Backend Developer']))
            ->assertOk()
            ->assertSee('Backend Developer')
            ->assertSee('Northwind')
            ->assertSee('Product Engineer')
            ->assertSee('Backend Developer Inc.');
    }

    public function test_search_and_location_are_forwarded_together(): void
    {
        $this->actingAsCandidate();
        $this->mockCandidateContext();
        $this->mockJobs(['q' => 'Designer', 'location' => 'Cairo'], [
            $this->job(3, 'Product Designer', 'Studio', 'Cairo'),
        ]);

        $this->get(route('frontend.candidate.vacancies.index', ['q' => 'Designer', 'location' => 'Cairo']))
            ->assertOk()
            ->assertSee('Product Designer')
            ->assertSee('value="Designer"', false)
            ->assertSee('value="Cairo"', false);
    }

    public function test_search_and_work_mode_are_forwarded_together(): void
    {
        $this->actingAsCandidate();
        $this->mockCandidateContext();
        $this->mockJobs(['q' => 'Platform', 'work_mode' => 'remote'], [
            $this->job(4, 'Platform Engineer', 'Cloud Co', 'Remote', 'remote'),
        ]);

        $this->get(route('frontend.candidate.vacancies.index', ['q' => 'Platform', 'work_mode' => 'remote']))
            ->assertOk()
            ->assertSee('Platform Engineer')
            ->assertSee('value="Platform"', false)
            ->assertSee('value="remote" selected', false);
    }

    public function test_all_filters_and_server_pagination_are_combined_and_links_keep_filters(): void
    {
        $this->actingAsCandidate();
        $this->mockCandidateContext();
        $this->mockJobs([
            'q' => 'Design Studio',
            'location' => 'Cairo',
            'work_mode' => 'remote',
            'page' => '2',
        ], [$this->job(5, 'Product Designer', 'Design Studio', 'Cairo', 'remote')], [
            'current_page' => 2,
            'last_page' => 3,
            'per_page' => 15,
            'total' => 45,
            'from' => 16,
            'to' => 30,
        ]);

        $html = $this->get(route('frontend.candidate.vacancies.index', [
            'q' => 'Design Studio',
            'location' => 'Cairo',
            'work_mode' => 'remote',
            'page' => 2,
        ]))->assertOk()->getContent();

        $this->assertStringContainsString('2 / 3', $html);
        $this->assertStringContainsString('q=Design', $html);
        $this->assertStringContainsString('location=Cairo', $html);
        $this->assertStringContainsString('work_mode=remote', $html);
        $this->assertStringContainsString('page=1', $html);
        $this->assertStringContainsString('page=3', $html);
        $this->assertStringContainsString("pageInput.value = '1'", $html);
        $this->assertStringContainsString('scheduleFilterSubmit(300)', $html);
        $this->assertStringContainsString('Updating vacancies', $html);
        $this->assertStringContainsString('name="q"', $html);
    }

    public function test_clearing_search_keeps_other_form_filters_and_resets_page(): void
    {
        $this->actingAsCandidate();
        $this->mockCandidateContext();
        $this->mockJobs(['location' => 'Alexandria', 'work_mode' => 'hybrid', 'page' => '1'], [
            $this->job(6, 'Researcher', 'Studio', 'Alexandria', 'hybrid'),
        ]);

        $html = $this->get(route('frontend.candidate.vacancies.index', [
            'q' => '',
            'location' => 'Alexandria',
            'work_mode' => 'hybrid',
            'page' => 1,
        ]))->assertOk()->getContent();

        $this->assertStringContainsString('name="q" value=""', $html);
        $this->assertStringContainsString('name="location" value="Alexandria"', $html);
        $this->assertStringContainsString('value="hybrid" selected', $html);
        $this->assertStringContainsString("searchInput.value = ''", $html);
        $this->assertStringContainsString('form.requestSubmit()', $html);
    }

    public function test_empty_results_show_empty_state_without_pagination(): void
    {
        $this->actingAsCandidate();
        $this->mockCandidateContext();
        $this->mockJobs(['q' => 'No such role'], [], [
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 15,
            'total' => 0,
        ]);

        $this->get(route('frontend.candidate.vacancies.index', ['q' => 'No such role']))
            ->assertOk()
            ->assertSee(__('candidate.vacancies_search.empty'))
            ->assertDontSee('aria-label="Pagination"', false);
    }

    public function test_search_labels_and_empty_state_render_in_arabic(): void
    {
        App::setLocale('ar');
        $this->actingAsCandidate();
        $this->mockCandidateContext();
        $this->mockJobs(['q' => 'لا توجد نتيجة'], []);

        $this->get(route('frontend.candidate.vacancies.index', ['q' => 'لا توجد نتيجة']))
            ->assertOk()
            ->assertSee(__('candidate.vacancies_search.search_label'))
            ->assertSee(__('candidate.vacancies_search.clear_search'))
            ->assertSee(__('candidate.vacancies_search.empty'));
    }

    public function test_api_failure_renders_safe_error_state_not_empty_success(): void
    {
        $this->actingAsCandidate();
        $this->mockCandidateContext();
        $jobs = Mockery::mock(JobAPI::class);
        $jobs->shouldReceive('getJobs')->once()->with(['q' => 'Developer'])->andReturn([
            'message' => 'SQLSTATE[23000] private backend error',
            'errors' => [],
            'statusCode' => 500,
        ]);
        $this->app->instance(JobAPI::class, $jobs);

        $html = $this->get(route('frontend.candidate.vacancies.index', ['q' => 'Developer']))
            ->assertOk()
            ->assertSee('data-vacancies-load-error', false)
            ->assertSee(__('candidate.vacancies_search.load_error'))
            ->assertDontSee('SQLSTATE')
            ->assertDontSee(__('candidate.vacancies_search.empty'))
            ->getContent();

        $this->assertStringContainsString(__('candidate.vacancies_search.retry'), $html);
    }

    public function test_invalid_filters_are_rejected_before_backend_request(): void
    {
        $this->actingAsCandidate();
        $candidate = Mockery::mock(CandidateAPI::class);
        $candidate->shouldNotReceive('getProfile');
        $candidate->shouldNotReceive('getSkills');
        $candidate->shouldNotReceive('getApplications');
        $this->app->instance(CandidateAPI::class, $candidate);
        $jobs = Mockery::mock(JobAPI::class);
        $jobs->shouldNotReceive('getJobs');
        $this->app->instance(JobAPI::class, $jobs);

        $this->from(route('frontend.candidate.vacancies.index'))
            ->get(route('frontend.candidate.vacancies.index', ['q' => str_repeat('x', 101), 'work_mode' => 'everywhere', 'page' => 0]))
            ->assertRedirect(route('frontend.candidate.vacancies.index'))
            ->assertSessionHasErrors(['q', 'work_mode', 'page']);
    }

    private function actingAsCandidate(): void
    {
        ApiSession::store('candidate-token', [
            'id' => 41,
            'name' => 'Taylor Candidate',
            'email' => 'taylor@example.test',
            'account_type' => 'candidate',
        ]);
    }

    private function mockCandidateContext(): void
    {
        $api = Mockery::mock(CandidateAPI::class);
        $api->shouldReceive('getProfile')->once()->andReturn(['data' => [], 'statusCode' => 200]);
        $api->shouldReceive('getSkills')->once()->andReturn(['data' => [], 'statusCode' => 200]);
        $api->shouldReceive('getApplications')->once()->andReturn(['data' => [], 'statusCode' => 200]);
        $this->app->instance(CandidateAPI::class, $api);
    }

    /** @param  array<string, mixed>  $filters
     * @param  list<array<string, mixed>>  $rows
     * @param  array<string, mixed>|null  $pagination
     */
    private function mockJobs(array $filters, array $rows, ?array $pagination = null): void
    {
        $jobs = Mockery::mock(JobAPI::class);
        $jobs->shouldReceive('getJobs')->once()->with($filters)->andReturn([
            'message' => 'OK',
            'data' => $rows,
            'errors' => [],
            'meta' => $pagination === null ? [] : ['pagination' => $pagination],
            'statusCode' => 200,
        ]);
        $this->app->instance(JobAPI::class, $jobs);
    }

    /** @return array<string, mixed> */
    private function job(int $id, string $title, string $company, string $location = 'Remote', string $workMode = 'remote'): array
    {
        return [
            'id' => $id,
            'title' => $title,
            'company' => ['name' => $company],
            'location' => $location,
            'work_mode' => $workMode,
            'deadline' => '2026-12-01',
        ];
    }
}
