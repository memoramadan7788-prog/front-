<?php

namespace Tests\Unit;

use App\Frontend\Api\ApiResponse;
use App\Frontend\Api\CurlClient;
use App\Frontend\Auth\ApiSession;
use App\Support\Frontend\Notifications\HttpNotificationsGateway;
use Tests\TestCase;

class HttpNotificationsGatewayTest extends TestCase
{
    public function test_is_available_only_for_candidate_account_type(): void
    {
        $gateway = new HttpNotificationsGateway;

        ApiSession::store('t', [
            'id' => 1,
            'email' => 'c@example.com',
            'name' => 'C',
            'account_type' => 'candidate',
        ]);
        $this->assertTrue($gateway->isAvailable());

        ApiSession::store('t', [
            'id' => 2,
            'email' => 'a@example.com',
            'name' => 'A',
            'account_type' => 'admin',
        ]);
        $this->assertFalse($gateway->isAvailable());

        ApiSession::store('t', [
            'id' => 3,
            'email' => 'co@example.com',
            'name' => 'Co',
            'account_type' => 'company',
        ]);
        $this->assertFalse($gateway->isAvailable());
    }

    public function test_list_returns_unavailable_envelope_for_non_candidate(): void
    {
        ApiSession::store('t', [
            'id' => 2,
            'email' => 'a@example.com',
            'name' => 'A',
            'account_type' => 'admin',
        ]);

        $result = (new HttpNotificationsGateway)->list();

        $this->assertFalse($result['available']);
        $this->assertSame([], $result['data']);
        $this->assertNull($result['meta']);
    }

    public function test_list_parses_items_and_unread_count_from_meta(): void
    {
        $this->actingAsCandidate();

        $client = new class extends CurlClient
        {
            public string $capturedMethod = '';

            public string $capturedUrl = '';

            public function method(string $m): self
            {
                $this->capturedMethod = strtoupper($m);

                return parent::method($m);
            }

            public function url(string $u): self
            {
                $this->capturedUrl = $u;

                return parent::url($u);
            }

            public function send(): ApiResponse
            {
                return new ApiResponse(
                    true,
                    'OK',
                    [[
                        'id' => 7,
                        'title' => 'Offer ready',
                        'body' => 'Review your offer',
                        'read_at' => null,
                        'type' => 'offer.sent',
                    ]],
                    null,
                    null,
                    [
                        'pagination' => [
                            'current_page' => 1,
                            'last_page' => 2,
                            'per_page' => 15,
                            'total' => 16,
                            'unread_count' => 4,
                        ],
                    ],
                    200
                );
            }
        };

        $result = (new HttpNotificationsGateway($client))->list(1, 15);

        $this->assertTrue($result['available']);
        $this->assertSame(200, $result['statusCode']);
        $this->assertCount(1, $result['data']);
        $this->assertSame(4, $result['meta']['unread_count']);
        $this->assertSame(2, $result['meta']['last_page']);
        $this->assertSame('GET', $client->capturedMethod);
        $this->assertStringStartsWith('/candidate/notifications?', $client->capturedUrl);
    }

    public function test_mark_as_read_uses_patch_against_candidate_path(): void
    {
        $this->actingAsCandidate();

        $client = new class extends CurlClient
        {
            public string $capturedMethod = '';

            public string $capturedUrl = '';

            public function method(string $m): self
            {
                $this->capturedMethod = strtoupper($m);

                return parent::method($m);
            }

            public function url(string $u): self
            {
                $this->capturedUrl = $u;

                return parent::url($u);
            }

            public function send(): ApiResponse
            {
                return new ApiResponse(
                    true,
                    'Notification marked as read.',
                    ['id' => 12, 'read_at' => '2026-10-27T12:00:00+00:00'],
                    null,
                    null,
                    null,
                    200
                );
            }
        };

        $result = (new HttpNotificationsGateway($client))->markAsRead(12);

        $this->assertSame(200, $result['statusCode']);
        $this->assertSame('PATCH', $client->capturedMethod);
        $this->assertSame('/candidate/notifications/12/read', $client->capturedUrl);
        $this->assertSame(12, data_get($result['data'], 'id'));
    }

    public function test_mark_as_read_and_list_fail_closed_for_company(): void
    {
        ApiSession::store('t', [
            'id' => 9,
            'email' => 'hr@example.com',
            'name' => 'HR',
            'account_type' => 'company',
        ]);

        $client = new class extends CurlClient
        {
            public int $sendCalls = 0;

            public function send(): ApiResponse
            {
                $this->sendCalls++;

                return new ApiResponse(true, 'OK', [], null, null, null, 200);
            }
        };

        $gateway = new HttpNotificationsGateway($client);

        $this->assertFalse($gateway->markAsRead(1)['available']);
        $this->assertFalse($gateway->list()['available']);
        $this->assertSame(0, $client->sendCalls);
    }

    private function actingAsCandidate(): void
    {
        ApiSession::store('cand-token', [
            'id' => 41,
            'email' => 'notify@example.com',
            'name' => 'Notify Candidate',
            'account_type' => 'candidate',
            'roles' => ['candidate'],
        ]);
    }
}
