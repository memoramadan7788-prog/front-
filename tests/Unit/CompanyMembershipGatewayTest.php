<?php

namespace Tests\Unit;

use App\Models\EmployeeAPI;
use App\Support\Frontend\CompanyMembership\HttpCompanyMembershipGateway;
use Mockery;
use Tests\TestCase;

class CompanyMembershipGatewayTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_discovery_is_unavailable_without_configured_path(): void
    {
        config(['services.backend.company_discovery_path' => null]);
        config(['services.backend.candidate_membership_path' => null]);

        $employees = Mockery::mock(EmployeeAPI::class);
        $employees->shouldReceive('requestToJoin')->never();

        $gateway = new HttpCompanyMembershipGateway($employees);
        $discovery = $gateway->searchCompanies('Acme');
        $mine = $gateway->myMembership();

        $this->assertFalse($discovery['available']);
        $this->assertSame([], $discovery['data']);
        $this->assertFalse($mine['available']);
        $this->assertNull($mine['data']);
        $this->assertFalse($gateway->canSubmitJoinRequest());
    }

    public function test_request_to_join_delegates_to_employee_api_without_fabricating(): void
    {
        $employees = Mockery::mock(EmployeeAPI::class);
        $employees->shouldReceive('requestToJoin')
            ->once()
            ->with([
                'company_id' => 7,
                'requested_job_title' => 'HR',
                'requested_department' => 'People',
            ])
            ->andReturn([
                'statusCode' => 200,
                'message' => 'Join request submitted successfully. Awaiting company review.',
                'data' => ['employee' => ['id' => 1, 'status' => 'pending']],
                'errors' => null,
            ]);

        $gateway = new HttpCompanyMembershipGateway($employees);
        $response = $gateway->requestToJoin([
            'company_id' => 7,
            'requested_job_title' => 'HR',
            'requested_department' => 'People',
        ]);

        $this->assertSame(200, $response['statusCode']);
        $this->assertSame('pending', $response['data']['employee']['status']);
    }
}
