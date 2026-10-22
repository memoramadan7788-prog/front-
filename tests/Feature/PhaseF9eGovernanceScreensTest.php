<?php

namespace Tests\Feature;

use App\Http\Middleware\SetFrontendLocale;
use App\Support\Frontend\FrontendStatusVocabulary;
use App\Support\Frontend\MockAdminData;
use App\Support\Frontend\MockNotificationData;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PhaseF9eGovernanceScreensTest extends TestCase
{
    public function test_admin_organization_governance_states_and_review_only(): void
    {
        $this->get(route('admin.organizations.show', 'org-ns-001'))
            ->assertOk()
            ->assertSee('Northstar Labs', false)
            ->assertSee('data-org-id="org-ns-001"', false)
            ->assertSee('Verification governance', false)
            ->assertSee('Olivia Bennett', false)
            ->assertSee('Maya Chen', false)
            ->assertSee('req-maya-chen', false)
            ->assertSee('Membership pending review', false)
            ->assertSee('review-only previews', false)
            ->assertSee('org_status=approved', false)
            ->assertSee('No verification status was changed', false);

        $this->get(route('admin.organizations.show', 'org-rj-001'))
            ->assertOk()
            ->assertSee('Redwood Insights', false)
            ->assertSee('data-lifecycle="rejected"', false)
            ->assertSee('Incomplete legal entity evidence', false)
            ->assertSee('org_status=rejected', false);

        $this->get(route('admin.organizations.show', 'org-pr-001'))
            ->assertOk()
            ->assertSee('Pinecrest Recruiting', false)
            ->assertSee('data-lifecycle="suspended"', false)
            ->assertSee('Active support case', false)
            ->assertSee('Suspension reason', false);
    }

    public function test_company_org_status_consistency_with_admin(): void
    {
        $this->get(route('company.dashboard', ['org_status' => 'pending']))
            ->assertOk()
            ->assertSee('Organization pending Super Admin review', false)
            ->assertSee('aria-disabled="true"', false);

        $this->get(route('company.dashboard', ['org_status' => 'suspended']))
            ->assertOk()
            ->assertSee('Organization workspace is suspended', false);

        $this->get(route('company.jobs.create', ['org_status' => 'rejected']))
            ->assertOk()
            ->assertSee('data-org-can-publish="0"', false);
    }

    public function test_membership_not_shown_as_approved_employee(): void
    {
        $users = MockAdminData::usersIndex()['users'];
        $maya = collect($users)->firstWhere('id', 'usr-maya');

        $this->assertNotNull($maya);
        $this->assertSame('pending', $maya['membership_status']);
        $this->assertSame('org-ns-001', $maya['org_id']);
        $this->assertSame('candidate', $maya['account_type']);
        $this->assertNull($maya['company_role']);
        $this->assertStringContainsString('Not listed as an approved employee', (string) $maya['membership_note']);

        $northstar = MockAdminData::organizationShow('org-ns-001');
        $request = collect($northstar['membership_requests'] ?? [])->firstWhere('id', 'req-maya-chen');
        $this->assertNotNull($request);
        $this->assertSame('pending', $request['status_key']);
        $this->assertSame('maya-chen', $request['candidate_id']);

        $memberNames = collect($northstar['members'] ?? [])->pluck('name')->all();
        $this->assertNotContains('Maya Chen', $memberNames);

        $this->get(route('admin.users.index', ['q' => 'Maya']))
            ->assertOk()
            ->assertSee('Maya Chen', false)
            ->assertSee('Northstar Labs (pending join)', false)
            ->assertSee('Membership pending review', false);

        $this->get(route('company.employees.requests'))
            ->assertOk()
            ->assertSee('Maya Chen', false)
            ->assertSee('frontend previews only', false);
    }

    public function test_job_moderation_contract_and_ns204_alignment(): void
    {
        $jobs = MockAdminData::jobsModerationIndex();
        $ns204 = collect($jobs['jobs'])->firstWhere('id', 'job-ns-204');

        $this->assertNotNull($ns204);
        $this->assertSame('org-ns-001', $ns204['org_id']);
        $this->assertSame('ns-204', $ns204['job_id']);
        $this->assertSame('approved', $ns204['status']);

        $this->get(route('admin.jobs.index'))
            ->assertOk()
            ->assertSee('data-jobs-moderation-contract', false)
            ->assertSee('does not change published job listings', false)
            ->assertSee('NS-204', false)
            ->assertSee('No job status was changed', false);

        $this->get(route('jobs.show', 'ns-204'))
            ->assertOk()
            ->assertSee('Senior Product Designer', false);
    }

    public function test_agency_operations_scope_banner_f8_g26(): void
    {
        $this->get(route('admin.agencies.index'))
            ->assertOk()
            ->assertSee('data-agency-scope-banner', false)
            ->assertSee('Review-only partner engagements', false)
            ->assertSee('Creating or changing engagements is not available', false)
            ->assertSee('data-review-scope="partner-engagements"', false)
            ->assertDontSee('F8-G26', false);
    }

    public function test_actor_scoped_notifications_matrix(): void
    {
        $candidate = MockNotificationData::forActor('candidate');
        $company = MockNotificationData::forActor('company');
        $interviewer = MockNotificationData::forActor('interviewer');
        $admin = MockNotificationData::forActor('admin');

        $this->assertSame('candidate', $candidate[0]['actor']);
        $this->assertSame('company', $company[0]['actor']);
        $this->assertSame('interviewer', $interviewer[0]['actor']);
        $this->assertSame('admin', $admin[0]['actor']);

        $this->assertTrue(collect($candidate)->contains(fn (array $n): bool => ($n['event'] ?? '') === 'interview_scheduled'));
        $this->assertTrue(collect($company)->contains(fn (array $n): bool => ($n['candidate_id'] ?? '') === 'maya-chen'));
        $this->assertTrue(collect($interviewer)->contains(fn (array $n): bool => str_contains((string) ($n['body'] ?? ''), 'int-ns204-portfolio')));
        $this->assertTrue(collect($admin)->contains(fn (array $n): bool => ($n['event'] ?? '') === 'org_pending_verification' || str_contains((string) ($n['body'] ?? ''), 'org-cd-001')));

        $this->get(route('candidate.dashboard'))
            ->assertOk()
            ->assertSee('data-notif-actor="candidate"', false)
            ->assertSee('Sample notifications for this workspace role', false);

        $this->get(route('company.dashboard'))
            ->assertOk()
            ->assertSee('data-notif-actor="company"', false)
            ->assertSee('Maya Chen requested Employee membership', false);

        $this->get(route('interviewer.dashboard'))
            ->assertOk()
            ->assertSee('data-notif-actor="interviewer"', false);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-notif-actor="admin"', false);
    }

    public function test_messages_scope_note_preserved_without_company_messages(): void
    {
        $this->get(route('candidate.messages.index'))
            ->assertOk()
            ->assertSee('data-messages-scope-note', false)
            ->assertSee('Message delivery is not available yet', false)
            ->assertSee('Company messaging is not part of this workspace', false)
            ->assertDontSee('F8-G27', false);

        $this->assertFalse(
            collect(Route::getRoutes())->contains(
                fn ($route): bool => str_contains($route->uri(), 'company/messages')
            )
        );
    }

    public function test_status_vocabulary_and_audit_sample_labels(): void
    {
        $org = FrontendStatusVocabulary::organization();
        $this->assertSame('Rejected', $org['rejected']['label']);
        $this->assertSame('Suspended', $org['suspended']['label']);

        $moderation = FrontendStatusVocabulary::jobModeration();
        $this->assertStringContainsString('published listings', $moderation['pending']['note']);
        $this->assertCount(12, FrontendStatusVocabulary::application());
        $this->assertSame('hybrid', FrontendStatusVocabulary::workModeKey('Hybrid · Two office days'));
        $this->assertSame('onsite', FrontendStatusVocabulary::workModeKey('On-site'));

        $this->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('data-audit-sample-banner', false)
            ->assertSee('not a live audit feed', false)
            ->assertSee('data-is-sample="true"', false);
    }

    public function test_arabic_admin_governance_and_agency_banner(): void
    {
        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('admin.organizations.show', 'org-ns-001'))
            ->assertOk()
            ->assertSee('حوكمة التحقق', false)
            ->assertSee('معاينات للمراجعة فقط', false);

        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('admin.agencies.index'))
            ->assertOk()
            ->assertSee('ارتباطات شركاء للمراجعة فقط', false)
            ->assertDontSee('F8-G26', false);

        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('candidate.messages.index'))
            ->assertOk()
            ->assertSee('تسليم الرسائل غير متاح بعد', false)
            ->assertDontSee('F8-G27', false);
    }

    public function test_admin_governance_routes_remain_get_only(): void
    {
        $this->get(route('admin.organizations.show', 'org-ns-001'))->assertOk();
        $this->post('/admin/organizations/org-ns-001')->assertMethodNotAllowed();
        $this->post('/admin/jobs')->assertMethodNotAllowed();
        $this->post('/admin/agencies')->assertMethodNotAllowed();
        $this->post('/admin/users')->assertMethodNotAllowed();
    }
}
