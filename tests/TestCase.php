<?php

namespace Tests;

use App\Support\Frontend\CandidateDashboard\CandidateDashboardKpiGateway;
use App\Support\Frontend\Notifications\NotificationsGateway;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Support\FakeCandidateDashboardKpiGateway;
use Tests\Support\FakeNotificationsGateway;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Prevent Candidate Overview / topbar from calling a live Backend during Feature tests.
        $this->app->instance(CandidateDashboardKpiGateway::class, new FakeCandidateDashboardKpiGateway);
        $this->app->instance(NotificationsGateway::class, new FakeNotificationsGateway);
    }
}
