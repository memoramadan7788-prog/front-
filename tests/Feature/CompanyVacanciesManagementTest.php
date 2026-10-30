<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
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
        $api->shouldReceive('getVacancies')->once()->andReturn(['statusCode' => 200, 'data' => [
            ['id' => 7, 'title' => 'Backend Engineer', 'status' => 'published', 'location' => 'Cairo', 'hiring_model' => 'direct', 'requisition_owner_id' => 5],
            ['id' => 8, 'title' => 'Draft Designer', 'status' => 'draft', 'location' => 'Remote'],
        ]]);
        $view = (new CompanyController($api))->getVacancies(Request::create('/company/vacancies?status=open'));
        $this->assertSame('frontend.company.vacancies', $view->name());
        $this->assertCount(1, $view->getData()['items']);
        $this->assertSame('Backend Engineer', data_get($view->getData()['items'][0], 'title'));
        $this->assertSame('direct', data_get($view->getData()['items'][0], 'hiring_model'));
    }

    public function test_company_vacancy_filter_validation_rejects_unknown_status(): void
    {
        $this->actingAsCompany();
        $this->from(route('frontend.company.vacancies.index'))->get(route('frontend.company.vacancies.index', ['status' => 'archived']))->assertSessionHasErrors('status');
    }

    public function test_company_vacancy_empty_and_api_error_states_are_distinct(): void
    {
        $emptyApi = Mockery::mock(CompanyAPI::class);
        $emptyApi->shouldReceive('getVacancies')->once()->andReturn(['statusCode' => 200, 'data' => []]);
        $emptyView = (new CompanyController($emptyApi))->getVacancies(Request::create('/company/vacancies'));
        $this->assertSame([], $emptyView->getData()['items']);
        $this->assertNull($emptyView->getData()['loadError']);

        $failedApi = Mockery::mock(CompanyAPI::class);
        $failedApi->shouldReceive('getVacancies')->once()->andReturn(['statusCode' => 503, 'message' => 'Unavailable']);
        $failedView = (new CompanyController($failedApi))->getVacancies(Request::create('/company/vacancies'));
        $this->assertSame([], $failedView->getData()['items']);
        $this->assertSame(__('company.vacancies_management.load_error'), $failedView->getData()['loadError']);
    }

    private function actingAsCompany(): void
    {
        ApiSession::store('company-token', ['id' => 22, 'email' => 'hr@example.test', 'name' => 'HR', 'account_type' => 'company', 'roles' => ['company_admin'], 'company' => ['id' => 5]]);
    }
}
