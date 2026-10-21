<?php

namespace App\Support\Frontend;

/**
 * Isolated mock presentation data for Company/HR frontend (Batch 5A).
 * Consistent with Candidate / Public Northstar Labs records — no Eloquent.
 */
class MockCompanyData
{
    /**
     * Figma Frame 25 — Company workspace overview (dashboard).
     *
     * @return array<string, mixed>
     */
    public static function dashboard(): array
    {
        return [
            'greeting' => 'Good morning, Olivia',
            'intro' => 'Northstar Labs hiring overview · Mock cohort as of October 20, 2026.',
            'date_range' => 'Last 30 days',
            'date_ranges' => [
                'Last 7 days',
                'Last 30 days',
                'This quarter',
                'Year to date',
            ],
            'stats' => [
                [
                    'label' => 'Open requisitions',
                    'value' => '3',
                    'meta' => '6 total including drafts',
                    'trend' => '+1 draft awaiting review',
                    'trend_tone' => 'neutral',
                ],
                [
                    'label' => 'Active candidates',
                    'value' => '28',
                    'meta' => 'Across open requisitions',
                    'trend' => '+4 this week',
                    'trend_tone' => 'up',
                ],
                [
                    'label' => 'Interviews scheduled',
                    'value' => '3',
                    'meta' => 'Next: Oct 22 · Maya Chen',
                    'trend' => 'This week',
                    'trend_tone' => 'neutral',
                ],
                [
                    'label' => 'Hired this month',
                    'value' => '1',
                    'meta' => 'Grace Liu · NS-204',
                    'trend' => 'Offer stage: 1 draft',
                    'trend_tone' => 'up',
                ],
            ],
            'pipeline' => [
                'title' => 'Applicant pipeline',
                'subtitle' => 'Current stage distribution · Open requisitions',
                'stages' => [
                    ['label' => 'Applied', 'count' => 8, 'share' => 100],
                    ['label' => 'Screening', 'count' => 8, 'share' => 100],
                    ['label' => 'Interview', 'count' => 8, 'share' => 100],
                    ['label' => 'Offer', 'count' => 3, 'share' => 38],
                    ['label' => 'Hired', 'count' => 1, 'share' => 13, 'tone' => 'success'],
                ],
                'footer' => '7 direct-hire · 5 agency-managed in active NS-204 board (demo subset). Full pipeline UI arrives in Batch 5C.',
            ],
            'requisitions' => [
                [
                    'code' => 'NS-204',
                    'title' => 'Senior Product Designer',
                    'owner' => 'Olivia Bennett',
                    'team' => 'Product & Design',
                    'status' => 'Open',
                    'pipeline' => '12 total · 3 interview',
                ],
                [
                    'code' => 'NS-205',
                    'title' => 'Backend Engineer',
                    'owner' => 'Jordan Lee',
                    'team' => 'Engineering',
                    'status' => 'Open',
                    'pipeline' => '9 total · 3 interview',
                ],
                [
                    'code' => 'NS-206',
                    'title' => 'Product Manager',
                    'owner' => 'Daniel Brooks',
                    'team' => 'Product',
                    'status' => 'Open',
                    'pipeline' => '7 total · 2 interview',
                ],
            ],
            'interviews' => [
                [
                    'when' => 'Wed · Oct 21',
                    'time' => '11:00 AM',
                    'candidate' => 'Luis Romero',
                    'role' => 'Team interview · NS-204',
                    'interviewer' => 'Alex Morgan',
                ],
                [
                    'when' => 'Thu · Oct 22',
                    'time' => '10:00–10:45 AM',
                    'candidate' => 'Maya Chen',
                    'role' => 'Portfolio review · NS-204',
                    'interviewer' => 'Alex Morgan',
                    'highlight' => true,
                ],
                [
                    'when' => 'Fri · Oct 23',
                    'time' => '11:00 AM',
                    'candidate' => 'Aisha Patel',
                    'role' => 'Portfolio · NS-204',
                    'interviewer' => 'Daniel Brooks',
                ],
            ],
            'activity' => [
                [
                    'time' => 'Oct 20 · 4:20 PM',
                    'body' => 'Daniel Brooks submitted hire recommendation for Ethan Cole (NS-204).',
                ],
                [
                    'time' => 'Oct 20 · 2:15 PM',
                    'body' => 'Interview brief attached for Maya Chen portfolio review.',
                ],
                [
                    'time' => 'Oct 19 · 11:40 AM',
                    'body' => 'Draft requisition NS-207 · Customer Success Manager saved — missing salary range.',
                ],
                [
                    'time' => 'Oct 16 · 9:05 AM',
                    'body' => 'Grace Liu marked Hired for Senior Product Designer (NS-204).',
                ],
            ],
            'pending' => [
                [
                    'title' => 'Draft awaiting review',
                    'body' => 'NS-207 · Customer Success Manager · Missing salary range and approval owner.',
                    'action' => 'Continue editing',
                    'pending_batch' => '5B',
                ],
                [
                    'title' => 'Offer approval pending',
                    'body' => 'OF-2026-021 · Ethan Cole · Compensation approval with Daniel Brooks.',
                    'action' => 'Open draft offer',
                    'pending_batch' => '5E',
                ],
            ],
        ];
    }

    /**
     * Company profile — workflow / onboarding-derived (no dedicated Figma profile frame verified).
     *
     * @return array<string, mixed>
     */
    public static function profile(): array
    {
        $public = MockPublicData::company('northstar-labs') ?? [];

        return [
            'design_source' => 'workflow_onboarding',
            'design_note' => 'No dedicated Company Profile Figma frame was verified for Batch 5A. Fields reuse Company onboarding (Frame 12) and public company profile patterns.',
            'verification' => [
                'status' => 'Verified',
                'detail' => 'Workspace verified · Mock presentation only — not live platform verification.',
                'document' => 'Northstar_Labs_Business_Registration.pdf',
                'reviewed_at' => 'Oct 8, 2026',
            ],
            'identity' => [
                'legal_name' => 'Northstar Labs, Inc.',
                'display_name' => $public['name'] ?? 'Northstar Labs',
                'logo_initials' => 'NS',
                'tagline' => $public['tagline'] ?? 'Helping operations teams do their best work, together.',
            ],
            'workspace' => [
                'slug' => $public['slug'] ?? 'northstar-labs',
                'website' => 'https://northstarlabs.example',
                'website_label' => $public['website'] ?? 'northstarlabs.example',
                'industry' => $public['industry'] ?? 'Software & collaboration',
                'industry_value' => 'software',
                'company_size' => '51–200 employees',
                'company_size_value' => '51-200',
                'headquarters' => 'Austin, Texas, United States',
                'location_short' => $public['location'] ?? 'Austin, TX · Hybrid & remote teams',
                'timezone' => 'America/Chicago',
                'timezone_label' => 'Central Time · America/Chicago',
                'description' => 'Northstar Labs builds collaborative software that helps operations teams simplify complex work.',
                'overview' => $public['overview'] ?? '',
            ],
            'contact' => [
                'primary_hr' => 'Olivia Bennett',
                'primary_email' => 'olivia.bennett@northstarlabs.example',
                'hiring_phone' => '+1 (512) 555-0142',
            ],
            'industries' => [
                'software' => 'Software & collaboration',
                'health' => 'Health & life sciences',
                'finance' => 'Financial services',
                'agency' => 'Design & creative',
                'other' => 'Other',
            ],
            'sizes' => [
                '1-10' => '1–10 employees',
                '11-50' => '11–50 employees',
                '51-200' => '51–200 employees',
                '201-1000' => '201–1,000 employees',
                '1000+' => '1,000+ employees',
            ],
            'timezones' => [
                'America/Chicago' => 'Central Time · America/Chicago',
                'America/New_York' => 'Eastern Time · America/New_York',
                'America/Denver' => 'Mountain Time · America/Denver',
                'America/Los_Angeles' => 'Pacific Time · America/Los_Angeles',
                'UTC' => 'UTC',
            ],
            'public_slug' => 'northstar-labs',
        ];
    }
}
