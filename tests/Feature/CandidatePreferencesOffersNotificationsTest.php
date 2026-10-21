<?php

namespace Tests\Feature;

use Tests\TestCase;

class CandidatePreferencesOffersNotificationsTest extends TestCase
{
    public function test_preferences_page_matches_frame_twenty_three_structure(): void
    {
        $this->get(route('candidate.preferences'))
            ->assertOk()
            ->assertSee('23 / My workspace / Preferences &amp; alerts', false)
            ->assertSee('Job preferences &amp; alerts', false)
            ->assertSee('Shape your recommendations. Choose what reaches your inbox.', false)
            ->assertSee('Your next role', false)
            ->assertSee('Desired titles', false)
            ->assertSee('Senior Product Designer · Lead Product Designer', false)
            ->assertSee('Austin, TX + Remote US', false)
            ->assertSee('Minimum base salary · USD', false)
            ->assertSee('$140,000 / year', false)
            ->assertSee('Include agency-managed opportunities', false)
            ->assertSee('Willing to relocate', false)
            ->assertSee('Weekly personalized job digest', false)
            ->assertSee('Product news and marketing', false)
            ->assertSee('Privacy controls', false)
            ->assertSee('Saved-search alert', false)
            ->assertSee('Senior design · Austin + Remote', false)
            ->assertSee('Save preferences', false)
            ->assertSee('Cancel changes', false)
            ->assertDontSee('Frontend UI only', false)
            ->assertDontSee('Browser-local preview', false);
    }

    public function test_offer_show_matches_frame_twenty_four_structure(): void
    {
        $this->get(route('candidate.offers.show', 'offer-ms108'))
            ->assertOk()
            ->assertSee('24 / My workspace / My applications', false)
            ->assertSee('Your offer from Meridian Studio', false)
            ->assertSee('Lead Product Designer · MS-108', false)
            ->assertSee('Offer received October 19, 2026', false)
            ->assertSee('Review your offer by October 28', false)
            ->assertSee('Awaiting your response', false)
            ->assertSee('Agency-managed', false)
            ->assertSee('A new chapter at Meridian', false)
            ->assertSee('$165,000 USD', false)
            ->assertSee('Meridian_Offer_Maya_Chen.pdf', false)
            ->assertSee('I have reviewed the offer documents', false)
            ->assertSee('Accept offer', false)
            ->assertSee('Ask a question', false)
            ->assertSee('Decline offer', false)
            ->assertSee('Jamie Rivera', false)
            ->assertSee('After you accept', false)
            ->assertDontSee('Frontend offer preview', false);

        $this->get(route('candidate.offers.show', 'hb-ms108-mc-1019'))
            ->assertOk()
            ->assertSee('Meridian Studio', false);

        $this->get(route('candidate.offers.show', 'missing'))->assertNotFound();
    }

    public function test_offers_index_lists_meridian_offer(): void
    {
        $this->get(route('candidate.offers.index'))
            ->assertOk()
            ->assertSee('Your offer from Meridian Studio', false)
            ->assertSee('Review offer', false)
            ->assertSee(route('candidate.offers.show', 'offer-ms108'), false);
    }

    public function test_notifications_index_and_dropdown_links(): void
    {
        $this->get(route('candidate.notifications.index'))
            ->assertOk()
            ->assertSee('Notifications', false)
            ->assertSee('Portfolio interview confirmed', false)
            ->assertSee('New message from Olivia Bennett', false)
            ->assertSee('Offer response reminder', false)
            ->assertSee('Mark all as read', false)
            ->assertDontSee('Frontend UI only', false);

        $this->get(route('candidate.dashboard'))
            ->assertOk()
            ->assertSee('View all notifications', false)
            ->assertSee(route('candidate.notifications.index'), false)
            ->assertSee(route('candidate.offers.show', 'offer-ms108'), false);
    }

    public function test_batch_four_e_routes_are_get_only(): void
    {
        $this->get(route('candidate.preferences'))->assertOk();
        $this->get(route('candidate.offers.index'))->assertOk();
        $this->get(route('candidate.offers.show', 'offer-ms108'))->assertOk();
        $this->get(route('candidate.notifications.index'))->assertOk();
        $this->post('/candidate/preferences')->assertMethodNotAllowed();
        $this->post('/candidate/offers')->assertMethodNotAllowed();
        $this->post('/candidate/notifications')->assertMethodNotAllowed();
    }

    public function test_cross_links_from_applications_and_sidebar(): void
    {
        $this->get(route('candidate.applications.index'))
            ->assertOk()
            ->assertSee(route('candidate.offers.show', 'hb-ms108-mc-1019'), false)
            ->assertSee('Review offer', false);

        $this->get(route('candidate.applications.show', 'hb-ms108-mc-1019'))
            ->assertOk()
            ->assertSee(route('candidate.offers.show', 'hb-ms108-mc-1019'), false);

        $this->get(route('candidate.dashboard'))
            ->assertOk()
            ->assertSee(route('candidate.preferences'), false)
            ->assertSee(route('candidate.offers.index'), false)
            ->assertSee(route('candidate.notifications.index'), false);
    }
}
