<?php

namespace Tests\Unit;

use App\Frontend\Auth\ApiSession;
use Tests\TestCase;

class ApiSessionTest extends TestCase
{
    public function test_store_and_refresh_user_preserve_token(): void
    {
        ApiSession::store('token-1', [
            'id' => 1,
            'email' => 'admin@example.com',
            'name' => 'Admin',
            'account_type' => 'admin',
        ]);

        $this->assertTrue(ApiSession::check());
        $this->assertSame('token-1', ApiSession::token());
        $this->assertSame('admin', ApiSession::accountType());

        ApiSession::refreshUser([
            'id' => 1,
            'email' => 'admin@example.com',
            'name' => 'Admin Updated',
            'account_type' => 'candidate',
        ]);

        $this->assertSame('token-1', ApiSession::token());
        $this->assertSame('candidate', ApiSession::accountType());
        $this->assertSame('Admin Updated', ApiSession::user()['name'] ?? null);
    }

    public function test_forget_clears_token_and_user(): void
    {
        ApiSession::store('token-2', [
            'id' => 2,
            'email' => 'user@example.com',
            'account_type' => 'company',
        ]);

        ApiSession::forget();

        $this->assertFalse(ApiSession::check());
        $this->assertNull(ApiSession::token());
        $this->assertNull(ApiSession::user());
    }
}
