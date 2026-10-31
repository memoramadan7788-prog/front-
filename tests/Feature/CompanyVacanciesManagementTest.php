<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Frontend\Api\ApiResponse;
use App\Frontend\Api\CurlClient;
use App\Frontend\Company\CompanyController;
use App\Models\CompanyAPI;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class CompanyVacanciesManagementTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_company_vacancies_are_authorized_local_filtered_and_show_only_returned_fields(): void
    {
        $this->get(route('frontend.company.vacancies.index'))->assertRedirect(route('frontend.account.login'));
        $this->actingAsCompany();
        $api = Mockery::mock(CompanyAPI::class);
        $api->shouldReceive('getVacancies')->once()->with(1)->andReturn(['statusCode' => 200, 'data' => [
            ['id' => 7, 'title' => 'Backend Engineer', 'status' => 'published', 'location' => 'Cairo', 'hiring_model' => 'direct', 'requisition_owner_id' => 5],
            ['id' => 8, 'title' => 'Draft Designer', 'status' => 'draft', 'location' => 'Remote'],
        ], 'meta' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 15, 'total' => 2]]);
        $view = (new CompanyController($api))->getVacancies(Request::create('/company/vacancies?status=open'));
        $this->assertSame('frontend.company.vacancies', $view->name());
        $this->assertCount(1, $view->getData()['items']);
        $this->assertSame('Backend Engineer', data_get($view->getData()['items'][0], 'title'));
        $this->assertSame('direct', data_get($view->getData()['items'][0], 'hiring_model'));
        $this->assertSame(['current_page' => 1, 'last_page' => 1, 'total' => 2], $view->getData()['pagination']);
    }

    public function test_company_vacancy_filter_validation_rejects_unknown_status(): void
    {
        $this->actingAsCompany();
        $this->from(route('frontend.company.vacancies.index'))->get(route('frontend.company.vacancies.index', ['status' => 'archived']))->assertSessionHasErrors('status');
    }

    public function test_company_vacancy_empty_and_api_error_states_are_distinct(): void
    {
        $emptyApi = Mockery::mock(CompanyAPI::class);
        $emptyApi->shouldReceive('getVacancies')->once()->with(1)->andReturn(['statusCode' => 200, 'data' => [], 'meta' => null]);
        $emptyView = (new CompanyController($emptyApi))->getVacancies(Request::create('/company/vacancies'));
        $this->assertSame([], $emptyView->getData()['items']);
        $this->assertNull($emptyView->getData()['loadError']);
        $this->assertNull($emptyView->getData()['pagination']);

        $failedApi = Mockery::mock(CompanyAPI::class);
        $failedApi->shouldReceive('getVacancies')->once()->with(1)->andReturn(['statusCode' => 503, 'message' => 'Unavailable', 'meta' => null]);
        $failedView = (new CompanyController($failedApi))->getVacancies(Request::create('/company/vacancies'));
        $this->assertSame([], $failedView->getData()['items']);
        $this->assertSame(__('company.vacancies_management.load_error'), $failedView->getData()['loadError']);
    }

    public function test_company_vacancies_send_page_and_render_pagination_with_preserved_filters(): void
    {
        $this->actingAsCompany();
        $api = Mockery::mock(CompanyAPI::class);
        $api->shouldReceive('getVacancies')->once()->with(2)->andReturn([
            'statusCode' => 200,
            'data' => [['id' => 18, 'title' => 'Backend Engineer', 'status' => 'open']],
            'meta' => ['current_page' => 2, 'last_page' => 3, 'per_page' => 15, 'total' => 40],
        ]);
        $this->app->instance(CompanyAPI::class, $api);

        $query = ['status' => 'open', 'q' => 'Engineer', 'location' => 'Cairo', 'hiring_model' => 'direct'];
        $response = $this->get(route('frontend.company.vacancies.index', $query + ['page' => 2]))->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString(__('company.vacancies_management.local_filter_scope'), $html);
        $this->assertStringContainsString(__('company.vacancies_management.pagination_summary', [
            'current' => 2,
            'last' => 3,
            'total' => 40,
        ]), $html);
        $this->assertStringContainsString(e(route('frontend.company.vacancies.index', $query + ['page' => 1], false)), $html);
        $this->assertStringContainsString(e(route('frontend.company.vacancies.index', $query + ['page' => 3], false)), $html);
    }

    public function test_pagination_disables_previous_and_next_at_the_first_and_last_pages(): void
    {
        $this->actingAsCompany();
        $api = Mockery::mock(CompanyAPI::class);
        $api->shouldReceive('getVacancies')->once()->with(1)->andReturn([
            'statusCode' => 200,
            'data' => [['id' => 1, 'title' => 'First page role', 'status' => 'open']],
            'meta' => ['current_page' => 1, 'last_page' => 2, 'total' => 16],
        ]);
        $api->shouldReceive('getVacancies')->once()->with(2)->andReturn([
            'statusCode' => 200,
            'data' => [['id' => 2, 'title' => 'Last page role', 'status' => 'open']],
            'meta' => ['current_page' => 2, 'last_page' => 2, 'total' => 16],
        ]);
        $this->app->instance(CompanyAPI::class, $api);

        $firstPage = $this->get(route('frontend.company.vacancies.index', ['page' => 1]))
            ->assertOk()
            ->getContent();
        $this->assertMatchesRegularExpression('/<button\b(?=[^>]*\bdisabled)[^>]*>.*?Previous.*?<\/button>/s', $firstPage);
        $defaultFilters = ['status' => 'all', 'q' => '', 'location' => '', 'hiring_model' => ''];
        $this->assertStringContainsString(e(route('frontend.company.vacancies.index', $defaultFilters + ['page' => 2], false)), $firstPage);

        $lastPage = $this->get(route('frontend.company.vacancies.index', ['page' => 2]))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString(e(route('frontend.company.vacancies.index', $defaultFilters + ['page' => 1], false)), $lastPage);
        $this->assertMatchesRegularExpression('/<button\b(?=[^>]*\bdisabled)[^>]*>.*?Next.*?<\/button>/s', $lastPage);
    }

    public function test_single_page_response_does_not_render_pagination_controls(): void
    {
        $this->actingAsCompany();
        $api = Mockery::mock(CompanyAPI::class);
        $api->shouldReceive('getVacancies')->once()->with(1)->andReturn([
            'statusCode' => 200,
            'data' => [],
            'meta' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 15, 'total' => 0],
        ]);
        $this->app->instance(CompanyAPI::class, $api);

        $html = $this->get(route('frontend.company.vacancies.index'))->assertOk()->getContent();

        $this->assertStringNotContainsString('data-pagination-pages', $html);
        $this->assertStringNotContainsString('aria-label="'.__('company.vacancies_management.pagination').'"', $html);
    }

    public function test_company_api_passes_page_query_and_exposes_backend_pagination(): void
    {
        ApiSession::store('company-token', ['id' => 22, 'account_type' => 'company']);
        $client = Mockery::mock(CurlClient::class);
        $client->shouldReceive('reset')->once()->andReturnSelf();
        $client->shouldReceive('method')->once()->with('GET')->andReturnSelf();
        $client->shouldReceive('url')->once()->with('/company/jobs')->andReturnSelf();
        $client->shouldReceive('query')->once()->with(['page' => 4])->andReturnSelf();
        $client->shouldReceive('bearer')->once()->with('company-token')->andReturnSelf();
        $client->shouldReceive('send')->once()->andReturn(new ApiResponse(
            true,
            'OK',
            [['id' => 34, 'title' => 'Page four role']],
            null,
            null,
            ['pagination' => ['current_page' => 4, 'last_page' => 5, 'per_page' => 15, 'total' => 65]],
            200
        ));

        $api = new CompanyAPI;
        $api->api = $client;
        $response = $api->getVacancies(4);

        $this->assertSame(200, $response['statusCode']);
        $this->assertSame([['id' => 34, 'title' => 'Page four role']], $response['data']);
        $this->assertSame([
            'current_page' => 4,
            'last_page' => 5,
            'per_page' => 15,
            'total' => 65,
        ], $response['meta']);
    }

    public function test_invalid_requested_page_is_rejected(): void
    {
        $this->actingAsCompany();

        $this->from(route('frontend.company.vacancies.index'))
            ->get(route('frontend.company.vacancies.index', ['page' => 0]))
            ->assertSessionHasErrors('page');
    }

    public function test_invalid_pagination_metadata_is_not_rendered_as_controls(): void
    {
        $this->actingAsCompany();
        $api = Mockery::mock(CompanyAPI::class);
        $api->shouldReceive('getVacancies')->once()->with(1)->andReturn([
            'statusCode' => 200,
            'data' => [['id' => 1, 'title' => 'Role', 'status' => 'open']],
            'meta' => ['current_page' => 4, 'last_page' => 2, 'total' => 30],
        ]);

        $view = (new CompanyController($api))->getVacancies(Request::create('/company/vacancies'));

        $this->assertNull($view->getData()['pagination']);
    }

    public function test_pagination_and_filter_scope_render_in_arabic(): void
    {
        app()->setLocale('ar');
        $this->actingAsCompany();
        $api = Mockery::mock(CompanyAPI::class);
        $api->shouldReceive('getVacancies')->once()->with(2)->andReturn([
            'statusCode' => 200,
            'data' => [['id' => 18, 'title' => 'Role', 'status' => 'open']],
            'meta' => ['current_page' => 2, 'last_page' => 3, 'total' => 40],
        ]);
        $this->app->instance(CompanyAPI::class, $api);

        $this->get(route('frontend.company.vacancies.index', ['page' => 2]))
            ->assertOk()
            ->assertSee(__('company.vacancies_management.local_filter_scope'))
            ->assertSee(__('company.vacancies_management.pagination_summary', [
                'current' => 2,
                'last' => 3,
                'total' => 40,
            ]));
    }

    private function actingAsCompany(): void
    {
        ApiSession::store('company-token', ['id' => 22, 'email' => 'hr@example.test', 'name' => 'HR', 'account_type' => 'company', 'roles' => ['company_admin'], 'company' => ['id' => 5]]);
    }
}
