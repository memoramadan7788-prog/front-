<?php

namespace Tests\Feature;

use App\Frontend\Api\ApiResponse;
use App\Frontend\Api\CurlClient;
use App\Frontend\Auth\ApiSession;
use App\Models\ContactAPI;
use Mockery;
use Tests\TestCase;

class ContactApiContractTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_guest_contact_posts_contract_fields_without_bearer_authentication(): void
    {
        $payload = [
            'name' => 'Guest Example',
            'email' => 'guest@example.test',
            'title' => 'General question',
            'description' => 'Please contact me about this question.',
        ];
        $client = Mockery::mock(CurlClient::class);
        $client->shouldReceive('reset')->once()->andReturnSelf();
        $client->shouldReceive('method')->once()->with('POST')->andReturnSelf();
        $client->shouldReceive('url')->once()->with('/contact')->andReturnSelf();
        $client->shouldNotReceive('bearer');
        $client->shouldReceive('data')->once()->with($payload)->andReturnSelf();
        $client->shouldReceive('send')->once()->andReturn(new ApiResponse(true, 'Sent', null, null, null, null, 201));

        $api = new ContactAPI;
        $api->api = $client;

        $result = $api->submitContactMessage($payload, false);

        $this->assertSame(200, $result['statusCode']);
        $this->assertSame(201, $result['httpStatus']);
    }

    public function test_authenticated_contact_posts_contract_fields_with_session_bearer(): void
    {
        ApiSession::store('account-token', [
            'id' => 17,
            'name' => 'Account User',
            'email' => 'account@example.test',
            'account_type' => 'candidate',
        ]);
        $payload = [
            'name' => 'Account User',
            'email' => 'account@example.test',
            'title' => 'Account question',
            'description' => 'Please contact me about my account.',
        ];
        $client = Mockery::mock(CurlClient::class);
        $client->shouldReceive('reset')->once()->andReturnSelf();
        $client->shouldReceive('method')->once()->with('POST')->andReturnSelf();
        $client->shouldReceive('url')->once()->with('/contact')->andReturnSelf();
        $client->shouldReceive('bearer')->once()->with('account-token')->andReturnSelf();
        $client->shouldReceive('data')->once()->with($payload)->andReturnSelf();
        $client->shouldReceive('send')->once()->andReturn(new ApiResponse(true, 'Sent'));

        $api = new ContactAPI;
        $api->api = $client;

        $result = $api->submitContactMessage($payload, true);

        $this->assertSame(200, $result['statusCode']);
        $this->assertArrayNotHasKey('user_id', $payload);
    }
}
