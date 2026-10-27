<?php

namespace Tests;

use App\Support\Frontend\CandidateDashboard\CandidateDashboardKpiGateway;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Support\FakeCandidateDashboardKpiGateway;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Prevent Candidate Overview from calling a live Backend during Feature tests.
        $this->app->instance(CandidateDashboardKpiGateway::class, new FakeCandidateDashboardKpiGateway);
    }
}
