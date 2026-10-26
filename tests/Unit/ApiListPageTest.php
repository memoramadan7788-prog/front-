<?php

namespace Tests\Unit;

use App\Support\Frontend\ApiListPage;
use Tests\TestCase;

class ApiListPageTest extends TestCase
{
    public function test_error_message_prefers_backend_errors_then_message(): void
    {
        $this->assertSame(
            'Duplicate application',
            ApiListPage::errorMessage(['errors' => ['error' => 'Duplicate application'], 'message' => 'Nope'], 422)
        );

        $this->assertSame(
            'Upstream failed',
            ApiListPage::errorMessage(['message' => 'Upstream failed'], 503)
        );
    }

    public function test_error_message_uses_status_fallbacks_when_payload_empty(): void
    {
        $this->assertSame(__('candidate.pipeline.forbidden'), ApiListPage::errorMessage([], 403));
        $this->assertSame(__('candidate.pipeline.server_error'), ApiListPage::errorMessage([], 500));
        $this->assertSame(__('candidate.pipeline.load_error'), ApiListPage::errorMessage([], 418));
    }
}
