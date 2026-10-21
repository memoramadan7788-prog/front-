<?php

namespace Tests\Feature;

use App\Http\Middleware\SetFrontendLocale;
use Tests\TestCase;

class CompanyTeamEmployeesScreensTest extends TestCase
{
    public function test_team_permissions_matches_frame_thirty_seven(): void
    {
        $this->get(route('company.employees.index'))
            ->assertOk()
            ->assertSee('37 / Company workspace / Team &amp; permissions', false)
            ->assertSee('Team &amp; permissions', false)
            ->assertSee('Give each teammate the access they need, and no more.', false)
            ->assertSee('Invite teammate', false)
            ->assertSee('Members (5)', false)
            ->assertSee('Invites (1)', false)
            ->assertSee('Roles &amp; scopes', false)
            ->assertSee('Olivia Bennett', false)
            ->assertSee('Daniel Brooks', false)
            ->assertSee('Alex Morgan', false)
            ->assertSee('Jordan Lee', false)
            ->assertSee('Jamie Rivera', false)
            ->assertSee('Invite a teammate', false)
            ->assertSee('taylor@northstarlabs.example', false)
            ->assertSee('Permission boundaries', false)
            ->assertSee('Access changes are audited', false)
            ->assertSee('Join requests (2)', false)
            ->assertDontSee('Frontend UI only', false);
    }

    public function test_employee_show_and_unknown_identifier(): void
    {
        $this->get(route('company.employees.show', 'olivia-bennett'))
            ->assertOk()
            ->assertSee('Olivia Bennett', false)
            ->assertSee('olivia@northstarlabs.example', false);

        $this->get(route('company.employees.show', 'missing-employee'))
            ->assertNotFound();
    }

    public function test_join_requests_screen_is_workflow_derived(): void
    {
        $this->get(route('company.employees.requests'))
            ->assertOk()
            ->assertSee('Employee join requests', false)
            ->assertSee('Samira Hassan', false)
            ->assertSee('Chris Nguyen', false)
            ->assertSee('Mira Cohen', false)
            ->assertSee('Preview approve', false)
            ->assertSee('Preview decline', false)
            ->assertDontSee('Frontend UI only', false);
    }

    public function test_employee_routes_are_get_only_and_static_before_dynamic(): void
    {
        $this->get(route('company.employees.index'))->assertOk();
        $this->get(route('company.employees.requests'))->assertOk();
        $this->post('/company/employees')->assertMethodNotAllowed();
        $this->post('/company/employees/requests')->assertMethodNotAllowed();
    }

    public function test_sidebar_and_jobs_permissions_link_to_team(): void
    {
        $this->get(route('company.employees.index'))
            ->assertOk()
            ->assertSee(route('company.employees.index'), false)
            ->assertSee('aria-current="page"', false);

        $this->get(route('company.jobs.index'))
            ->assertOk()
            ->assertSee(route('company.employees.index'), false)
            ->assertDontSee('Review permissions · Soon', false);
    }

    public function test_arabic_team_and_join_requests_render(): void
    {
        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('company.employees.index'))
            ->assertOk()
            ->assertSee('الفريق والصلاحيات', false)
            ->assertSee('دعوة زميل', false)
            ->assertSee('حدود الصلاحيات', false);

        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('company.employees.requests'))
            ->assertOk()
            ->assertSee('طلبات انضمام الموظفين', false)
            ->assertSee('معاينة الموافقة', false);
    }
}
