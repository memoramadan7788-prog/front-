<?php

namespace Tests\Feature;

use App\Frontend\Auth\ApiSession;
use App\Models\CompanyAPI;
use App\Models\EmployeeAPI;
use Mockery;
use Tests\TestCase;

class CompanyScheduleInterviewTest extends TestCase
{
    private $companyApi;

    private $employeeApi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyApi = Mockery::mock(CompanyAPI::class);
        $this->employeeApi = Mockery::mock(EmployeeAPI::class);
        $this->app->instance(CompanyAPI::class, $this->companyApi);
        $this->app->instance(EmployeeAPI::class, $this->employeeApi);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_guest_is_redirected_from_schedule(): void
    {
        $this->get(route('frontend.company.interviews.schedule', ['application_id' => 9]))
            ->assertRedirect(route('frontend.account.login'));
    }

    public function test_candidate_cannot_open_schedule(): void
    {
        $this->actingAsCandidate();

        $this->get(route('frontend.company.interviews.schedule', ['application_id' => 9]))
            ->assertRedirect();
    }

    public function test_company_without_application_sees_empty_guidance(): void
    {
        $this->actingAsCompany();

        $html = $this->get(route('frontend.company.interviews.schedule'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-mode="empty"', $html);
        $this->assertStringContainsString(__('company.interviews.schedule_need_application'), $html);
    }

    public function test_company_form_renders_supported_types_and_slots(): void
    {
        $this->actingAsCompany();
        $this->mockHappyPathApis();

        $html = $this->get(route('frontend.company.interviews.schedule', ['application_id' => 42]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-schedule-interview', $html);
        $this->assertStringContainsString('value="online"', $html);
        $this->assertStringContainsString('value="on_site"', $html);
        $this->assertStringContainsString('value="exam"', $html);
        $this->assertStringNotContainsString('value="phone"', $html);
        $this->assertStringNotContainsString('value="ai"', $html);
        $this->assertStringContainsString('Amina Hassan', $html);
        $this->assertStringContainsString('Dr. Hisham', $html);
        $this->assertStringContainsString(__('company.interviews.timezone_display_only'), $html);
        $this->assertStringContainsString(__('company.interviews.duration_from_slot'), $html);
        $this->assertStringContainsString(__('company.interviews.pick_interviewer_first'), $html);
        $this->assertStringContainsString('data-conflict-state="unverified"', $html);
        $this->assertStringContainsString(__('company.interviews.invitation_honest'), $html);
    }

    public function test_rejects_invalid_interview_type(): void
    {
        $this->actingAsCompany();
        $this->mockHappyPathApis(expectSchedule: false);

        $this->from(route('frontend.company.interviews.schedule', ['application_id' => 42]))
            ->post(route('frontend.company.interviews.schedule.submit'), [
                'application_id' => 42,
                'interview_type' => 'phone',
                'display_timezone' => 'UTC',
                'schedule_action' => 'refresh',
            ])
            ->assertSessionHasErrors('interview_type');
    }

    public function test_confirm_requires_interviewer_and_slot(): void
    {
        $this->actingAsCompany();
        $this->mockHappyPathApis(expectSchedule: false);

        $this->from(route('frontend.company.interviews.schedule', ['application_id' => 42]))
            ->post(route('frontend.company.interviews.schedule.submit'), [
                'application_id' => 42,
                'interview_type' => 'online',
                'display_timezone' => 'Africa/Cairo',
                'schedule_action' => 'confirm',
            ])
            ->assertRedirect(route('frontend.company.interviews.schedule', ['application_id' => 42]))
            ->assertSessionHasErrors(['interviewer_id', 'availability_id']);
    }

    public function test_forbidden_application_shows_unauthorized_state(): void
    {
        $this->actingAsCompany();
        $this->companyApi->shouldReceive('getApplication')->once()->with(99)->andReturn([
            'statusCode' => 403,
            'message' => 'Forbidden',
            'data' => null,
        ]);

        $html = $this->get(route('frontend.company.interviews.schedule', ['application_id' => 99]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-unauthorized="1"', $html);
        $this->assertStringContainsString(__('company.interviews.forbidden_application'), $html);
    }

    public function test_missing_application_shows_unavailable_empty_state(): void
    {
        $this->actingAsCompany();
        $this->companyApi->shouldReceive('getApplication')->once()->with(4040)->andReturn([
            'statusCode' => 404,
            'message' => 'Not found',
            'data' => null,
        ]);

        $html = $this->get(route('frontend.company.interviews.schedule', ['application_id' => 4040]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-mode="empty"', $html);
        // SafeApiMessage may surface a short Backend message (e.g. "Not found") instead of the fallback.
        $this->assertTrue(
            str_contains($html, __('company.interviews.application_unavailable'))
            || str_contains($html, 'Not found')
        );
        $this->assertStringNotContainsString('data-unauthorized="1"', $html);
        $this->assertStringNotContainsString('data-schedule-form', $html);
    }

    public function test_confirm_posts_bookings_payload_and_does_not_claim_email_invite(): void
    {
        $this->actingAsCompany();
        $this->mockHappyPathApis(expectSchedule: true);

        $this->from(route('frontend.company.interviews.schedule', ['application_id' => 42]))
            ->post(route('frontend.company.interviews.schedule.submit'), [
                'application_id' => 42,
                'interview_type' => 'online',
                'interviewer_id' => 77,
                'availability_id' => 501,
                'display_timezone' => 'UTC',
                'round_no' => 1,
                'schedule_action' => 'confirm',
            ])
            ->assertRedirect(route('frontend.company.interview-calendar'))
            ->assertSessionHas('success_key', 'company.interviews.flash_scheduled');
    }

    public function test_backend_error_does_not_flash_success(): void
    {
        $this->actingAsCompany();
        $this->mockHappyPathApis(expectSchedule: true, scheduleCode: 422, scheduleErrors: [
            'bookings.0.availability_id' => ['Availability slot #501 is not available.'],
        ]);

        $this->from(route('frontend.company.interviews.schedule', ['application_id' => 42]))
            ->post(route('frontend.company.interviews.schedule.submit'), [
                'application_id' => 42,
                'interview_type' => 'on_site',
                'interviewer_id' => 77,
                'availability_id' => 501,
                'display_timezone' => 'UTC',
                'schedule_action' => 'confirm',
            ])
            ->assertRedirect(route('frontend.company.interviews.schedule', ['application_id' => 42]))
            ->assertSessionMissing('success_key')
            ->assertSessionHasErrors();
    }

    public function test_conflict_blocks_confirm(): void
    {
        $this->actingAsCompany();
        $this->mockHappyPathApis(expectSchedule: false, hasConflict: true);

        $this->from(route('frontend.company.interviews.schedule', ['application_id' => 42]))
            ->post(route('frontend.company.interviews.schedule.submit'), [
                'application_id' => 42,
                'interview_type' => 'online',
                'interviewer_id' => 77,
                'availability_id' => 501,
                'display_timezone' => 'UTC',
                'schedule_action' => 'confirm',
            ])
            ->assertRedirect(route('frontend.company.interviews.schedule', ['application_id' => 42]))
            ->assertSessionHasErrors('error');
    }

    public function test_session_401_clears_and_redirects_login(): void
    {
        $this->actingAsCompany();
        $this->companyApi->shouldReceive('getApplication')->once()->andReturn([
            'statusCode' => 401,
            'message' => 'Unauthenticated',
            'data' => null,
        ]);

        $this->get(route('frontend.company.interviews.schedule', ['application_id' => 42]))
            ->assertRedirect(route('frontend.account.login'));
        $this->assertNull(ApiSession::token());
    }

    public function test_arabic_labels_render(): void
    {
        app()->setLocale('ar');
        $this->actingAsCompany();
        $this->mockHappyPathApis(expectSchedule: false);

        $html = $this->get(route('frontend.company.interviews.schedule', ['application_id' => 42]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('company.interviews.schedule_title'), $html);
        $this->assertStringContainsString(e(__('company.interviews.invitation_honest')), $html);
    }

    public function test_applications_list_links_to_schedule(): void
    {
        $this->actingAsCompany();
        $this->companyApi->shouldReceive('getApplications')->once()->andReturn([
            'statusCode' => 200,
            'message' => 'OK',
            'data' => [[
                'id' => 42,
                'status' => 'screening',
                'candidate' => ['name' => 'Amina Hassan'],
                'applied_at' => now()->toIso8601String(),
            ]],
        ]);

        $html = $this->get(route('frontend.company.vacancies.applications', ['vacancyId' => 3]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(
            route('frontend.company.interviews.schedule', ['application_id' => 42], false),
            $html
        );
    }

    public function test_company_sidebar_and_calendar_expose_schedule_navigation(): void
    {
        $this->actingAsCompany();

        // Empty schedule page still renders company sidebar (no application API call).
        $scheduleNav = $this->get(route('frontend.company.interviews.schedule'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(
            route('frontend.company.interviews.schedule', absolute: false),
            $scheduleNav
        );
        $this->assertStringContainsString(__('company.interviews.schedule_interview'), $scheduleNav);

        $calendar = $this->get(route('frontend.company.interview-calendar'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(
            route('frontend.company.interviews.schedule', absolute: false),
            $calendar
        );
        $this->assertStringContainsString(__('company.interviews.calendar_schedule_hint'), $calendar);
        $this->assertStringContainsString(__('company.interviews.schedule_interview'), $calendar);
    }

    public function test_duplicate_submit_guard_present(): void
    {
        $this->actingAsCompany();
        $this->mockHappyPathApis(expectSchedule: false);

        $html = $this->get(route('frontend.company.interviews.schedule', ['application_id' => 42]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-submit-once', $html);
    }

    private function actingAsCompany(): void
    {
        ApiSession::store('co-token', [
            'id' => 20,
            'email' => 'hr@example.com',
            'name' => 'HR User',
            'account_type' => 'company',
            'roles' => ['company_admin'],
            'company' => ['id' => 5, 'name' => 'Acme'],
        ]);
    }

    private function actingAsCandidate(): void
    {
        ApiSession::store('cand-token', [
            'id' => 11,
            'email' => 'candidate@example.com',
            'name' => 'Candidate User',
            'account_type' => 'candidate',
            'roles' => ['candidate'],
        ]);
    }

    private function mockHappyPathApis(bool $expectSchedule = false, int $scheduleCode = 201, ?array $scheduleErrors = null, bool $hasConflict = false): void
    {
        $this->companyApi->shouldReceive('getApplication')->byDefault()->andReturn([
            'statusCode' => 200,
            'message' => 'OK',
            'data' => [
                'id' => 42,
                'status' => 'screening',
                'candidate' => ['user' => ['name' => 'Amina Hassan']],
                'job' => ['id' => 3, 'title' => 'Backend Engineer', 'company_id' => 5],
            ],
        ]);

        $this->employeeApi->shouldReceive('getEmployees')->byDefault()->andReturn([
            'statusCode' => 200,
            'message' => 'OK',
            'data' => [[
                'id' => 12,
                'status' => 'approved',
                'user' => ['id' => 77, 'name' => 'Dr. Hisham', 'email' => 'interviewer@example.com'],
                'role' => ['name' => 'interviewer'],
            ]],
        ]);

        $this->companyApi->shouldReceive('getApplicationAvailableSlots')->byDefault()->andReturn([
            'statusCode' => 200,
            'message' => 'OK',
            'data' => [[
                'id' => 501,
                'interviewer_id' => 77,
                'start_at' => '2026-11-05T10:00:00+00:00',
                'end_at' => '2026-11-05T10:45:00+00:00',
                'duration_minutes' => 45,
                'availability_status' => 'open',
            ]],
        ]);

        $this->companyApi->shouldReceive('checkInterviewConflicts')->byDefault()->andReturn([
            'statusCode' => 200,
            'message' => 'OK',
            'data' => ['has_conflict' => $hasConflict],
        ]);

        if ($expectSchedule) {
            $this->companyApi->shouldReceive('scheduleInterview')
                ->once()
                ->with(Mockery::on(function (array $payload): bool {
                    return (int) $payload['application_id'] === 42
                        && in_array($payload['interview_type'], ['online', 'on_site', 'exam'], true)
                        && isset($payload['bookings'][0]['interviewer_id'], $payload['bookings'][0]['availability_id'])
                        && (int) $payload['bookings'][0]['interviewer_id'] === 77
                        && (int) $payload['bookings'][0]['availability_id'] === 501
                        && ! array_key_exists('timezone', $payload)
                        && ! array_key_exists('duration', $payload)
                        && ! array_key_exists('start_at', $payload);
                }))
                ->andReturn([
                    'statusCode' => $scheduleCode,
                    'message' => $scheduleCode >= 200 && $scheduleCode < 300 ? 'Scheduled' : 'Failed',
                    'data' => $scheduleCode >= 200 && $scheduleCode < 300 ? ['id' => 900] : null,
                    'errors' => $scheduleErrors,
                ]);
        } else {
            $this->companyApi->shouldReceive('scheduleInterview')->never();
        }
    }
}
