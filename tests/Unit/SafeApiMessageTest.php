<?php

namespace Tests\Unit;

use App\Support\Frontend\SafeApiMessage;
use Tests\TestCase;

class SafeApiMessageTest extends TestCase
{
    public function test_suppresses_sqlstate_and_foreign_key_details(): void
    {
        $raw = "SQLSTATE[23000]: Integrity constraint violation: 1452 audit_logs_user_id_foreign (Connection: mysql, Host: 127.0.0.1, Database: recruitment_audit, SQL: insert into `audit_logs` (`user_id`) values (0))";

        $safe = SafeApiMessage::forUser($raw, 'Friendly fallback', ['action' => 'suspend']);

        $this->assertSame('Friendly fallback', $safe);
        $this->assertTrue(SafeApiMessage::looksSensitive($raw));
        $this->assertStringNotContainsString('SQLSTATE', $safe);
        $this->assertStringNotContainsString('127.0.0.1', $safe);
    }

    public function test_keeps_short_benign_api_messages(): void
    {
        $safe = SafeApiMessage::forUser('Only approved companies can be suspended.', 'fallback');

        $this->assertSame('Only approved companies can be suspended.', $safe);
    }
}
