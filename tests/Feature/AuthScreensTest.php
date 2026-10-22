<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthScreensTest extends TestCase
{
    public function test_login_screen_renders_figma_welcome_back_copy(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Welcome back', false)
            ->assertSee('Keep me signed in on this device', false)
            ->assertSee('Forgot password?', false)
            ->assertSee('Create an account', false)
            ->assertDontSee('Frontend UI only', false)
            ->assertDontSee('signed in successfully', false)
            ->assertDontSee('07 /', false)
            ->assertSee('Sign-in is not connected yet', false);
    }

    public function test_register_screen_renders_account_type_options(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Create your account', false)
            ->assertSee('I’m looking for a job', false)
            ->assertSee('I’m hiring', false)
            ->assertSee('Create candidate account', false)
            ->assertSee('Terms of Service', false)
            ->assertDontSee('Frontend UI only', false);
    }

    public function test_verify_email_screen_is_check_your_inbox_ui(): void
    {
        $this->get(route('verification.notice', ['email' => 'maya.chen@example.com']))
            ->assertOk()
            ->assertSee('Check your inbox', false)
            ->assertSee('maya.chen@example.com', false)
            ->assertSee('Verification code', false)
            ->assertSee('Resend code', false)
            ->assertDontSee('no verification email is sent', false);
    }

    public function test_reset_password_and_forgot_password_screens_render(): void
    {
        $this->get(route('password.reset', ['email' => 'maya.chen@example.com']))
            ->assertOk()
            ->assertSee('Set a new password', false)
            ->assertSee('Password requirements', false)
            ->assertSee('At least 12 characters', false)
            ->assertDontSee('passwords are not changed', false);

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Forgot your password?', false)
            ->assertDontSee('No reset email is sent', false)
            ->assertSee('Back to sign in', false);
    }

    public function test_public_navigation_links_to_auth_screens(): void
    {
        $home = $this->get(route('home'));
        $home->assertOk();
        $home->assertSee(route('login'), false);
        $home->assertSee(route('register'), false);

        $this->get(route('jobs.index'))
            ->assertOk()
            ->assertSee(route('register'), false);

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee(route('login'), false);
    }

    public function test_auth_routes_are_get_only_presentation_pages(): void
    {
        foreach (['login', 'register', 'password.request', 'verification.notice', 'password.reset'] as $name) {
            $this->get(route($name))->assertOk();
        }

        $this->post('/login')->assertMethodNotAllowed();
        $this->post('/register')->assertMethodNotAllowed();
    }
}
