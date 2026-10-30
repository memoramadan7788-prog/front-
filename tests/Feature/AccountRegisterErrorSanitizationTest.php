<?php

namespace Tests\Feature;

use App\Http\Middleware\SetFrontendLocale;
use App\Models\AccountAPI;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Mockery;
use Tests\TestCase;

class AccountRegisterErrorSanitizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PreventRequestForgery::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_register_get_shows_company_account_type_option(): void
    {
        $html = $this->get(route('frontend.account.register'))->assertOk()->getContent();

        $this->assertStringContainsString('name="account_type"', $html);
        $this->assertStringContainsString('value="company"', $html);
        $this->assertStringContainsString('I represent a company', $html);
        $this->assertStringContainsString('id="company-fields"', $html);
        $this->assertStringNotContainsString('SQLSTATE', $html);
        $this->assertStringNotContainsString('email_verification_codes', $html);
    }

    public function test_register_get_with_type_company_opens_company_fields(): void
    {
        $html = $this->get(route('frontend.account.register', ['type' => 'company']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('value="company"', $html);
        $this->assertStringContainsString('checked', $html);
        $this->assertStringContainsString('id="company-fields"', $html);
        $this->assertStringNotContainsString('SQLSTATE', $html);
    }

    public function test_company_register_sql_debug_is_not_flashed_and_success_is_not_claimed(): void
    {
        $leak = "SQLSTATE[42S02]: Base table or view not found: 1146 Table 'recruitment_audit.email_verification_codes' doesn't exist (Connection: mysql, Host: 127.0.0.1, Database: recruitment_audit, SQL: delete from `email_verification_codes` where `user_id` = 1 and `consumed_at` is null)";

        $api = Mockery::mock(AccountAPI::class);
        $api->shouldReceive('register')->once()->andReturn([
            'message' => $leak,
            'data' => null,
            'errors' => ['error' => $leak],
            'statusCode' => 500,
        ]);
        $this->app->instance(AccountAPI::class, $api);

        $this->from(route('frontend.account.register'))
            ->post(route('frontend.account.register.submit'), [
                'account_type' => 'company',
                'name' => 'Pat Lee',
                'email' => 'pat.lee@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'company_name' => 'Acme Audit Co',
            ])
            ->assertRedirect(route('frontend.account.register'))
            ->assertSessionHasErrors('error')
            ->assertSessionMissing('success_key');

        $this->assertSame(__('auth.register.unavailable'), session('errors')->first('error'));
        $this->assertStringNotContainsString('SQLSTATE', (string) session('errors')->first('error'));
        $this->assertStringNotContainsString('email_verification_codes', (string) session('errors')->first('error'));
    }

    public function test_arabic_register_unavailable_message_is_localized(): void
    {
        $leak = 'SQLSTATE[42S02]: Base table or view not found: 1146 Table email_verification_codes';

        $api = Mockery::mock(AccountAPI::class);
        $api->shouldReceive('register')->once()->andReturn([
            'message' => $leak,
            'data' => null,
            'errors' => [],
            'statusCode' => 500,
        ]);
        $this->app->instance(AccountAPI::class, $api);

        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->from(route('frontend.account.register'))
            ->post(route('frontend.account.register.submit'), [
                'account_type' => 'candidate',
                'name' => 'Sara',
                'email' => 'sara@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('error');

        $this->assertSame(
            'تعذّر إنشاء حسابك الآن. حاول مرة أخرى بعد لحظات. إذا استمر ذلك، فقد تكون خدمات الحساب غير متاحة مؤقتًا.',
            __('auth.register.unavailable')
        );
    }
}
