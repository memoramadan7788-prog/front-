<?php

namespace Tests\Feature;

use App\Http\Middleware\SetFrontendLocale;
use Tests\TestCase;

class CompanyOffersTalentPoolScreensTest extends TestCase
{
    public function test_company_create_offer_matches_frame_thirty_five(): void
    {
        $this->get(route('company.offers.create'))
            ->assertOk()
            ->assertSee('35 / Company workspace / Applicants', false)
            ->assertSee('Create offer · Ethan Cole', false)
            ->assertSee('OF-2026-021', false)
            ->assertSee('NS-204', false)
            ->assertSee('Senior Product Designer', false)
            ->assertSee('Draft · Not sent to candidate', false)
            ->assertSee('Compensation &amp; employment', false)
            ->assertSee('Annual base salary', false)
            ->assertSee('$160,000', false)
            ->assertSee('Target bonus', false)
            ->assertSee('Approvals &amp; documents', false)
            ->assertSee('Daniel Brooks', false)
            ->assertSee('Candidate preview', false)
            ->assertSee('Your offer from Northstar Labs', false)
            ->assertSee('Save draft', false)
            ->assertSee('Request approval', false)
            ->assertSee('Send offer', false)
            ->assertSee('Preview full letter', false)
            ->assertDontSee('Frontend UI only', false)
            ->assertDontSee('offer was sent', false);
    }

    public function test_create_offer_unknown_identifier_returns_not_found(): void
    {
        $this->get(route('company.offers.create', ['offer' => 'missing-offer']))
            ->assertNotFound();
    }

    public function test_create_offer_resolves_by_applicant(): void
    {
        $this->get(route('company.offers.create', ['applicant' => 'ethan-cole']))
            ->assertOk()
            ->assertSee('Ethan Cole', false)
            ->assertSee('OF-2026-021', false);
    }

    public function test_talent_pool_matches_frame_thirty_six(): void
    {
        $this->get(route('company.talent-pool.index'))
            ->assertOk()
            ->assertSee('36 / Company workspace / Talent pool', false)
            ->assertSee('Talent pool', false)
            ->assertSee('Only consented prospects appear here', false)
            ->assertSee('Add prospect', false)
            ->assertSee('All prospects (18)', false)
            ->assertSee('Product &amp; Design (7)', false)
            ->assertSee('Engineering (8)', false)
            ->assertSee('Search name, skill, or tag', false)
            ->assertSee('All relationships', false)
            ->assertSee('All sources', false)
            ->assertSee('Separate from application consent', false)
            ->assertSee('Maya Chen has not opted', false)
            ->assertSee('Manage talent tags', false)
            ->assertSee('Review expiring consent', false)
            ->assertSee('Riley Torres', false)
            ->assertDontSee('Frontend UI only', false);
    }

    public function test_offer_and_talent_pool_routes_are_get_only(): void
    {
        $this->get(route('company.offers.create'))->assertOk();
        $this->get(route('company.talent-pool.index'))->assertOk();
        $this->post('/company/offers/create')->assertMethodNotAllowed();
        $this->post('/company/talent-pool')->assertMethodNotAllowed();
    }

    public function test_sidebar_and_cross_links_wire_offers_and_talent_pool(): void
    {
        $this->get(route('company.talent-pool.index'))
            ->assertOk()
            ->assertSee(route('company.talent-pool.index'), false)
            ->assertSee('aria-current="page"', false);

        $this->get(route('company.evaluations.show', 'eval-ethan-cole'))
            ->assertOk()
            ->assertSee(route('company.offers.create', ['applicant' => 'ethan-cole']), false)
            ->assertDontSee('Offer drafting arrives in Batch 5E', false);

        $this->get(route('company.dashboard'))
            ->assertOk()
            ->assertSee(route('company.offers.create', ['applicant' => 'ethan-cole']), false);

        $this->get(route('company.applicants.show', 'ethan-cole'))
            ->assertOk()
            ->assertSee(route('company.offers.create', ['applicant' => 'ethan-cole']), false);
    }

    public function test_arabic_offer_and_talent_pool_render(): void
    {
        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('company.offers.create'))
            ->assertOk()
            ->assertSee('إنشاء عرض', false)
            ->assertSee('التعويض والتوظيف', false)
            ->assertSee('حفظ المسودة', false);

        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('company.talent-pool.index'))
            ->assertOk()
            ->assertSee('مجمع المواهب', false)
            ->assertSee('إضافة مرشح محتمل', false)
            ->assertSee('ابحث بالاسم أو المهارة أو الوسم', false);
    }
}
