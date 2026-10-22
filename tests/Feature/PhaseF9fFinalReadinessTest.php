<?php

namespace Tests\Feature;

use App\Http\Middleware\SetFrontendLocale;
use App\Support\Frontend\FrontendStatusVocabulary;
use App\Support\Frontend\MockAdminData;
use App\Support\Frontend\MockCompanyData;
use App\Support\Frontend\MockInterviewerData;
use App\Support\Frontend\MockNotificationData;
use App\Support\Frontend\MockPublicData;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PhaseF9fFinalReadinessTest extends TestCase
{
    public function test_canonical_cross_role_identities_align(): void
    {
        $publicJob = MockPublicData::job('ns-204');
        $this->assertNotNull($publicJob);
        $this->assertSame('ns-204', $publicJob['id']);

        $companyJob = MockCompanyData::job('ns-204');
        $this->assertNotNull($companyJob);
        $this->assertSame('ns-204', $companyJob['id']);

        $maya = MockCompanyData::applicant('maya-chen');
        $this->assertNotNull($maya);
        $this->assertSame('maya-chen', $maya['id']);

        $interview = MockInterviewerData::interview('int-ns204-portfolio');
        $this->assertNotNull($interview);
        $this->assertSame('int-ns204-portfolio', $interview['id']);
        $this->assertSame('Alex Morgan', MockInterviewerData::interviewerName());

        $northstar = MockAdminData::organizationShow('org-ns-001');
        $this->assertSame('org-ns-001', $northstar['id']);
        $this->assertSame('Olivia Bennett', $northstar['owner']);

        $alex = collect(MockAdminData::usersIndex()['users'])->firstWhere('name', 'Alex Morgan');
        $this->assertNotNull($alex);
        $this->assertSame('org-ns-001', $alex['org_id']);
        $this->assertSame('interviewer', $alex['role_key']);
    }

    public function test_proposed_status_dictionaries_are_domain_separated(): void
    {
        $this->assertCount(12, FrontendStatusVocabulary::application());
        $this->assertArrayHasKey('ai_failed', FrontendStatusVocabulary::application());
        $this->assertArrayHasKey('expired', FrontendStatusVocabulary::offer());
        $this->assertSame('remote', FrontendStatusVocabulary::workModeKey('Remote'));
        $this->assertSame('On-site', FrontendStatusVocabulary::workModeLabels()['onsite']);
        $this->assertArrayHasKey('candidate', FrontendStatusVocabulary::accountTypeLabels());
        $this->assertArrayNotHasKey('admin', FrontendStatusVocabulary::accountTypeLabels());
    }

    public function test_production_ui_does_not_leak_gap_ids(): void
    {
        $routes = [
            route('admin.agencies.index'),
            route('admin.jobs.index'),
            route('admin.settings.index'),
            route('candidate.messages.index'),
            route('company.jobs.create'),
        ];

        foreach ($routes as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringNotContainsString('F8-G', $html, $url);
            $this->assertStringNotContainsString('F9E', $html, $url);
            $this->assertStringNotContainsString('F9F', $html, $url);
        }

        $scorecard = $this->get(route('interviewer.evaluations.create', 'int-ns204-portfolio'));
        if ($scorecard->status() === 200) {
            $scorecard->assertDontSee('F8-G28', false);
        }
    }

    public function test_actor_notifications_keep_canonical_targets(): void
    {
        $company = MockNotificationData::forActor('company');
        $this->assertTrue(collect($company)->contains(
            fn (array $n): bool => ($n['candidate_id'] ?? null) === 'maya-chen' && ($n['organization_id'] ?? null) === 'org-ns-001'
        ));

        $interviewer = MockNotificationData::forActor('interviewer');
        $this->assertTrue(collect($interviewer)->contains(
            fn (array $n): bool => ($n['interview_id'] ?? null) === 'int-ns204-portfolio'
        ));
    }

    public function test_representative_workflow_routes_remain_get_ok(): void
    {
        $paths = [
            route('onboarding.company.status', ['status' => 'pending']),
            route('candidate.membership'),
            route('company.employees.requests'),
            route('jobs.show', 'ns-204'),
            route('candidate.applications.apply', 'ns-204'),
            route('company.applicants.show', 'maya-chen'),
            route('candidate.assessments.index'),
            route('company.interviews.index'),
            route('interviewer.interviews.prepare', 'int-ns204-portfolio'),
            route('candidate.offers.index'),
            route('admin.organizations.show', 'org-ns-001'),
            route('candidate.notifications.index'),
            route('candidate.messages.index'),
            route('candidate.applications.index'),
        ];

        foreach ($paths as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_arabic_status_and_honest_disclaimers_render(): void
    {
        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('admin.agencies.index'))
            ->assertOk()
            ->assertSee('ارتباطات شركاء للمراجعة فقط', false)
            ->assertDontSee('F8-G', false);

        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('candidate.messages.index'))
            ->assertOk()
            ->assertSee('تسليم الرسائل غير متاح بعد', false);

        $this->assertSame('حضوري', FrontendStatusVocabulary::workModeLabels()['onsite']);
    }

    public function test_no_company_messages_route_exists(): void
    {
        $this->assertFalse(
            collect(Route::getRoutes())->contains(
                fn ($route): bool => str_contains($route->uri(), 'company/messages')
            )
        );
    }

    public function test_candidate_membership_draft_storage_key_documented_in_view(): void
    {
        $this->get(route('candidate.membership'))
            ->assertOk()
            ->assertSee('hb_candidate_membership_request_v1', false)
            ->assertSee('local preview', false);
    }
}
