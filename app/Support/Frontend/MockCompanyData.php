<?php

namespace App\Support\Frontend;

/**
 * Isolated mock presentation data for Company/HR frontend (Batches 5A–5B).
 * Consistent with Candidate / Public Northstar Labs records — no Eloquent.
 */
class MockCompanyData
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function jobs(): array
    {
        $ns204 = MockPublicData::jobs()[0] ?? [];

        return [
            [
                'id' => 'ns-204',
                'code' => 'NS-204',
                'title' => 'Senior Product Designer',
                'owner' => 'Olivia Bennett',
                'team' => 'Product & Design',
                'department' => 'Product & Design',
                'model' => 'Direct + agency',
                'model_key' => 'direct_agency',
                'pipeline' => '12 total · 3 interview',
                'pipeline_total' => 12,
                'pipeline_interview' => 3,
                'status' => 'Open',
                'status_key' => 'open',
                'employment' => 'Full-time',
                'work_model' => 'Hybrid · Two office days',
                'location' => 'Austin, TX',
                'salary_label' => '$140k–$170k USD',
                'salary_min' => '$140,000',
                'salary_max' => '$170,000',
                'location_short' => 'Austin hybrid',
                'posted_at' => 'October 9, 2026',
                'published_label' => 'Published Oct 9',
                'hiring_manager' => 'Daniel Brooks',
                'description' => $ns204['description'] ?? '',
                'responsibilities' => $ns204['responsibilities'] ?? [],
                'requirements' => $ns204['requirements'] ?? [],
                'benefits' => $ns204['benefits'] ?? '',
                'process' => $ns204['process'] ?? '',
                'summary' => $ns204['summary'] ?? 'Design tools that make complex work feel simple',
                'stages' => [
                    ['label' => 'Applied', 'count' => 3],
                    ['label' => 'Screening', 'count' => 3],
                    ['label' => 'Interview', 'count' => 3],
                    ['label' => 'Offer', 'count' => 2],
                    ['label' => 'Hired', 'count' => 1],
                ],
                'stat_cards' => [
                    ['label' => 'Applied', 'value' => '3', 'meta' => 'New applications'],
                    ['label' => 'Screening', 'value' => '3', 'meta' => 'Review needed'],
                    ['label' => 'Interview', 'value' => '3', 'meta' => 'Maya · Luis · Aisha'],
                ],
                'offer_hired' => [
                    'value' => '2 / 1',
                    'meta' => '17 total candidates',
                    'label' => 'Offer / Hired',
                ],
                'forward_rows' => [
                    [
                        'candidate' => 'Maya Chen',
                        'stage' => 'Interview',
                        'next' => 'Portfolio review · Oct 22, 10:00 AM',
                    ],
                    [
                        'candidate' => 'Luis Romero',
                        'stage' => 'Interview',
                        'next' => 'Team interview · Oct 21, 11:00 AM',
                    ],
                    [
                        'candidate' => 'Aisha Patel',
                        'stage' => 'Interview',
                        'next' => 'Portfolio review · Oct 23, 11:00 AM',
                    ],
                    [
                        'candidate' => 'Ethan Cole',
                        'stage' => 'Offer',
                        'next' => 'Draft offer · awaiting approval',
                    ],
                ],
                'hiring_team' => [
                    ['name' => 'Olivia Bennett', 'role' => 'Requisition owner · Recruiter / HR'],
                    ['name' => 'Daniel Brooks', 'role' => 'Hiring manager'],
                    ['name' => 'Alex Morgan', 'role' => 'Portfolio interviewer'],
                ],
                'sourcing' => [
                    [
                        'label' => 'Direct hire',
                        'recruiter' => 'Recruiter A',
                        'ownership' => 'Requisition hiring team',
                    ],
                    [
                        'label' => 'Agency-managed',
                        'recruiter' => 'Recruiter B',
                        'ownership' => 'Jamie Rivera · Hirebridge Talent',
                    ],
                ],
                'publication_notes' => [
                    'Public on Hirebridge',
                    'Company careers link enabled',
                    'Agency submissions require candidate consent',
                ],
                'stage_note' => 'Use Rejected or Withdrawn for closed outcomes. Changing stages records an activity event; candidate notifications are reviewed separately.',
                'publish_to_search' => true,
                'agency_submissions' => true,
                'accommodation_info' => true,
                'last_edited' => 'Oct 18, 2026',
                'footer' => 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026',
            ],
            [
                'id' => 'ns-205',
                'code' => 'NS-205',
                'title' => 'Senior Software Engineer',
                'owner' => 'Jordan Lee',
                'team' => 'Engineering',
                'department' => 'Engineering',
                'model' => 'Direct hire',
                'model_key' => 'direct',
                'pipeline' => '9 total · 3 interview',
                'pipeline_total' => 9,
                'pipeline_interview' => 3,
                'status' => 'Open',
                'status_key' => 'open',
                'employment' => 'Full-time',
                'work_model' => 'Hybrid · Two office days',
                'location' => 'Austin, TX',
                'salary_label' => '$160k–$190k',
                'salary_min' => '$160,000',
                'salary_max' => '$190,000',
                'posted_at' => 'October 5, 2026',
                'hiring_manager' => 'Jordan Lee',
                'description' => 'Join Northstar Labs engineering to ship robust services for collaborative workflows.',
                'responsibilities' => [
                    'Design and implement backend services.',
                    'Improve reliability and observability.',
                    'Collaborate closely with product design.',
                ],
                'requirements' => [
                    '5+ years software engineering experience.',
                    'Comfortable with modern PHP/Laravel or equivalent stacks.',
                ],
                'benefits' => 'Hybrid Austin benefits with learning budget.',
                'process' => 'Technical screen → Pairing → Team interview.',
                'summary' => 'Build reliable collaborative software for operations teams.',
                'stages' => [
                    ['label' => 'Applied', 'count' => 3],
                    ['label' => 'Screening', 'count' => 2],
                    ['label' => 'Interview', 'count' => 3],
                    ['label' => 'Offer', 'count' => 1],
                    ['label' => 'Hired', 'count' => 0],
                ],
                'publish_to_search' => true,
                'agency_submissions' => false,
                'accommodation_info' => true,
                'last_edited' => 'Oct 17, 2026',
            ],
            [
                'id' => 'ns-206',
                'code' => 'NS-206',
                'title' => 'Product Manager',
                'owner' => 'Daniel Brooks',
                'team' => 'Product',
                'department' => 'Product',
                'model' => 'Direct hire',
                'model_key' => 'direct',
                'pipeline' => '7 total · 2 interview',
                'pipeline_total' => 7,
                'pipeline_interview' => 2,
                'status' => 'Open',
                'status_key' => 'open',
                'employment' => 'Full-time',
                'work_model' => 'Hybrid · Two office days',
                'location' => 'Austin, TX',
                'salary_label' => '$145k–$175k',
                'salary_min' => '$145,000',
                'salary_max' => '$175,000',
                'posted_at' => 'October 4, 2026',
                'hiring_manager' => 'Daniel Brooks',
                'description' => 'Northstar Labs is hiring a product manager to guide roadmap decisions with design and engineering partners.',
                'responsibilities' => [
                    'Define outcomes and success metrics.',
                    'Facilitate discovery with customers.',
                    'Coordinate delivery across squads.',
                ],
                'requirements' => [
                    '4+ years B2B product management.',
                    'Evidence of shipping complex workflows.',
                ],
                'benefits' => 'Hybrid Austin package with flexible PTO.',
                'process' => 'Recruiter screen → Case discussion → Leadership interview.',
                'summary' => 'Own discovery and delivery for core workspace outcomes.',
                'stages' => [
                    ['label' => 'Applied', 'count' => 2],
                    ['label' => 'Screening', 'count' => 3],
                    ['label' => 'Interview', 'count' => 2],
                    ['label' => 'Offer', 'count' => 0],
                    ['label' => 'Hired', 'count' => 0],
                ],
                'publish_to_search' => true,
                'agency_submissions' => false,
                'accommodation_info' => true,
                'last_edited' => 'Oct 16, 2026',
            ],
            [
                'id' => 'ns-207',
                'code' => 'NS-207',
                'title' => 'Customer Success Manager',
                'owner' => 'Olivia Bennett',
                'team' => 'Customer Experience',
                'department' => 'Customer Experience',
                'model' => 'Direct hire',
                'model_key' => 'direct',
                'pipeline' => '0 total · Not published',
                'pipeline_total' => 0,
                'pipeline_interview' => 0,
                'status' => 'Draft',
                'status_key' => 'draft',
                'employment' => 'Full-time',
                'work_model' => 'Hybrid · Two office days',
                'location' => 'Austin, TX',
                'salary_label' => 'Missing salary range',
                'salary_min' => '',
                'salary_max' => '',
                'posted_at' => null,
                'hiring_manager' => 'Jordan Lee',
                'description' => 'Turn operational challenges into lasting customer value with Northstar Labs.',
                'responsibilities' => [
                    'Own a portfolio of mid-market customer accounts.',
                    'Partner with product on adoption feedback loops.',
                ],
                'requirements' => [
                    '4+ years in B2B customer success.',
                    'Comfortable with SaaS onboarding and renewals.',
                ],
                'benefits' => 'Health coverage, flexible PTO, learning budget.',
                'process' => 'Screening → Hiring manager interview → Offer.',
                'summary' => 'Help customers adopt collaborative operations software.',
                'stages' => [],
                'publish_to_search' => true,
                'agency_submissions' => false,
                'accommodation_info' => true,
                'last_edited' => 'Oct 19, 2026',
                'draft_alert' => 'Missing salary range and approval owner.',
            ],
            [
                'id' => 'ns-198',
                'code' => 'NS-198',
                'title' => 'Design Operations Specialist',
                'owner' => 'Olivia Bennett',
                'team' => 'Product & Design',
                'department' => 'Product & Design',
                'model' => 'Direct hire',
                'model_key' => 'direct',
                'pipeline' => '4 total · Closed',
                'pipeline_total' => 4,
                'pipeline_interview' => 0,
                'status' => 'Closed',
                'status_key' => 'closed',
                'employment' => 'Full-time',
                'work_model' => 'Hybrid · Two office days',
                'location' => 'Austin, TX',
                'salary_label' => '$95k–$115k',
                'salary_min' => '$95,000',
                'salary_max' => '$115,000',
                'posted_at' => 'August 12, 2026',
                'hiring_manager' => 'Daniel Brooks',
                'description' => 'Closed requisition kept for audit history.',
                'responsibilities' => [],
                'requirements' => [],
                'benefits' => '',
                'process' => '',
                'summary' => 'Filled September 2026.',
                'stages' => [
                    ['label' => 'Hired', 'count' => 1],
                ],
                'publish_to_search' => false,
                'agency_submissions' => false,
                'accommodation_info' => true,
                'last_edited' => 'Sep 30, 2026',
                'closed_reason' => 'Role filled',
            ],
            [
                'id' => 'ns-191',
                'code' => 'NS-191',
                'title' => 'People Operations Coordinator',
                'owner' => 'Olivia Bennett',
                'team' => 'People',
                'department' => 'People',
                'model' => 'Direct + agency',
                'model_key' => 'direct_agency',
                'pipeline' => '6 total · Closed',
                'pipeline_total' => 6,
                'pipeline_interview' => 0,
                'status' => 'Closed',
                'status_key' => 'closed',
                'employment' => 'Full-time',
                'work_model' => 'On-site',
                'location' => 'Austin, TX',
                'salary_label' => '$70k–$85k',
                'salary_min' => '$70,000',
                'salary_max' => '$85,000',
                'posted_at' => 'July 2, 2026',
                'hiring_manager' => 'Olivia Bennett',
                'description' => 'Closed requisition kept for audit history.',
                'responsibilities' => [],
                'requirements' => [],
                'benefits' => '',
                'process' => '',
                'summary' => 'Cancelled — headcount reallocated.',
                'stages' => [],
                'publish_to_search' => false,
                'agency_submissions' => true,
                'accommodation_info' => true,
                'last_edited' => 'Aug 15, 2026',
                'closed_reason' => 'Cancelled',
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function job(string $id): ?array
    {
        foreach (self::jobs() as $job) {
            if (($job['id'] ?? '') === $id || ($job['code'] ?? '') === strtoupper($id)) {
                return self::enrichJobDetail($job);
            }
        }

        return null;
    }

    /**
     * Ensure Frame 28 detail sections exist for every requisition.
     *
     * @param  array<string, mixed>  $job
     * @return array<string, mixed>
     */
    private static function enrichJobDetail(array $job): array
    {
        $stages = $job['stages'] ?? [];
        $applied = collect($stages)->firstWhere('label', 'Applied')['count'] ?? 0;
        $screening = collect($stages)->firstWhere('label', 'Screening')['count'] ?? 0;
        $interview = collect($stages)->firstWhere('label', 'Interview')['count'] ?? 0;
        $offer = collect($stages)->firstWhere('label', 'Offer')['count'] ?? 0;
        $hired = collect($stages)->firstWhere('label', 'Hired')['count'] ?? 0;

        $job['location_short'] ??= str_contains((string) ($job['work_model'] ?? ''), 'Hybrid')
            ? 'Austin hybrid'
            : ($job['location'] ?? '');
        $job['published_label'] ??= ! empty($job['posted_at'])
            ? 'Published '.preg_replace('/^October /', 'Oct ', (string) $job['posted_at'])
            : 'Not published';
        $job['stat_cards'] ??= [
            ['label' => 'Applied', 'value' => (string) $applied, 'meta' => 'Applications'],
            ['label' => 'Screening', 'value' => (string) $screening, 'meta' => 'In review'],
            ['label' => 'Interview', 'value' => (string) $interview, 'meta' => 'Active interviews'],
        ];
        $job['offer_hired'] ??= [
            'label' => 'Offer / Hired',
            'value' => $offer.' / '.$hired,
            'meta' => ($job['pipeline_total'] ?? 0).' total candidates',
        ];
        $job['forward_rows'] ??= [];
        $job['hiring_team'] ??= [
            ['name' => $job['owner'] ?? 'Olivia Bennett', 'role' => 'Requisition owner · Recruiter / HR'],
            ['name' => $job['hiring_manager'] ?? 'Hiring manager', 'role' => 'Hiring manager'],
        ];
        $job['sourcing'] ??= [
            [
                'label' => $job['model'] ?? 'Direct hire',
                'recruiter' => $job['owner'] ?? 'Olivia Bennett',
                'ownership' => 'Requisition hiring team',
            ],
        ];
        $job['publication_notes'] ??= [
            ! empty($job['publish_to_search']) ? 'Public on Hirebridge' : 'Not listed publicly',
            'Company careers link enabled',
        ];
        $job['stage_note'] ??= 'Use Rejected or Withdrawn for closed outcomes. Changing stages records an activity event; candidate notifications are reviewed separately.';
        $job['footer'] ??= 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026';

        return $job;
    }

    /**
     * Figma Frame 26 — Jobs & requisitions listing.
     *
     * @return array<string, mixed>
     */
    public static function jobsIndex(): array
    {
        $jobs = self::jobs();
        $counts = [
            'open' => count(array_filter($jobs, fn (array $j): bool => ($j['status_key'] ?? '') === 'open')),
            'draft' => count(array_filter($jobs, fn (array $j): bool => ($j['status_key'] ?? '') === 'draft')),
            'closed' => count(array_filter($jobs, fn (array $j): bool => ($j['status_key'] ?? '') === 'closed')),
            'all' => count($jobs),
        ];

        $draft = self::job('ns-207');

        return [
            'title' => 'Jobs & requisitions',
            'intro' => 'Manage ownership, publication, and hiring progress across Northstar Labs.',
            'tabs' => [
                ['key' => 'open', 'label' => 'Open', 'count' => $counts['open']],
                ['key' => 'draft', 'label' => 'Draft', 'count' => $counts['draft']],
                ['key' => 'closed', 'label' => 'Closed', 'count' => $counts['closed']],
                ['key' => 'all', 'label' => 'All', 'count' => $counts['all']],
            ],
            'default_tab' => 'open',
            'owners' => ['Olivia Bennett', 'Jordan Lee', 'Daniel Brooks'],
            'models' => [
                ['key' => 'direct', 'label' => 'Direct hire'],
                ['key' => 'direct_agency', 'label' => 'Direct + agency'],
            ],
            'jobs' => $jobs,
            'draft_card' => [
                'code' => $draft['code'] ?? 'NS-207',
                'title' => $draft['title'] ?? 'Customer Success Manager',
                'owner' => $draft['owner'] ?? 'Olivia Bennett',
                'last_edited' => 'Oct 19',
                'alert' => $draft['draft_alert'] ?? 'Missing salary range and approval owner.',
                'edit_id' => $draft['id'] ?? 'ns-207',
            ],
            'permissions_card' => [
                'title' => 'Clear publication ownership',
                'body' => 'Company admins can publish approved roles. Hiring managers can edit assigned requisitions. Agency recruiters can submit candidates only to their assigned engagements.',
            ],
            'footer' => 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026',
        ];
    }

    /**
     * Figma Frame 27 — Create a job wizard.
     *
     * @return array<string, mixed>
     */
    public static function jobWizard(): array
    {
        $draft = self::job('ns-207') ?? [];

        return [
            'code' => $draft['code'] ?? 'NS-207',
            'title_default' => $draft['title'] ?? 'Customer Success Manager',
            'draft_saved_at' => '9:40 AM',
            'steps' => [
                ['key' => 'role', 'label' => 'Role details'],
                ['key' => 'description', 'label' => 'Description'],
                ['key' => 'hiring', 'label' => 'Hiring process'],
                ['key' => 'review', 'label' => 'Review & publish'],
            ],
            'departments' => [
                'Customer Experience',
                'Product & Design',
                'Engineering',
                'Product',
                'People',
            ],
            'employment_types' => ['Full-time', 'Part-time', 'Contract'],
            'work_models' => [
                'Hybrid · Two office days',
                'Remote',
                'On-site',
            ],
            'owners' => ['Olivia Bennett', 'Jordan Lee', 'Daniel Brooks'],
            'hiring_managers' => ['Jordan Lee', 'Daniel Brooks', 'Olivia Bennett', 'Alex Morgan'],
            'defaults' => [
                'title' => $draft['title'] ?? 'Customer Success Manager',
                'department' => $draft['department'] ?? 'Customer Experience',
                'employment' => $draft['employment'] ?? 'Full-time',
                'location' => $draft['location'] ?? 'Austin, TX',
                'work_model' => $draft['work_model'] ?? 'Hybrid · Two office days',
                'salary_min' => '$110,000',
                'salary_max' => '$135,000',
                'about' => 'Turn operational challenges into lasting customer value. You’ll guide mid-market accounts through adoption, renewals, and product feedback loops with design and engineering partners.',
                'requirements' => "4+ years in B2B customer success\nExperience with SaaS onboarding and renewals\nClear written and verbal communication\nComfort collaborating with product teams",
                'benefits' => 'Health coverage, flexible PTO, learning budget, hybrid Austin rhythm.',
                'owner' => 'Olivia Bennett',
                'hiring_manager' => 'Jordan Lee',
                'publish_to_search' => true,
                'agency_submissions' => false,
                'accommodation_info' => true,
            ],
            'checklist' => [
                ['key' => 'role', 'label' => 'Role and location', 'done' => true],
                ['key' => 'salary', 'label' => 'Salary range', 'done' => true],
                ['key' => 'description', 'label' => 'Description and requirements', 'done' => true],
                ['key' => 'privacy', 'label' => 'Privacy & equal-opportunity text', 'done' => true],
                ['key' => 'hiring', 'label' => 'Hiring process', 'done' => false],
                ['key' => 'approval', 'label' => 'Approval review', 'done' => false],
            ],
            'hiring_stages' => [
                'Applied',
                'Screening',
                'Interview',
                'Offer',
                'Hired',
            ],
            'footer' => 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026',
        ];
    }

    /**
     * Figma Frame 25 — Company workspace overview (dashboard).
     *
     * @return array<string, mixed>
     */
    public static function dashboard(): array
    {
        return [
            'greeting' => 'Good morning, Olivia',
            'intro' => 'Northstar Labs hiring overview · Updated October 20, 2026.',
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
                'footer' => '7 direct-hire · 5 agency-managed in the active NS-204 board.',
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
                    'title' => 'Senior Software Engineer',
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
                'detail' => 'Workspace verified for Northstar Labs.',
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

    /**
     * Hiring stages shared by company applicant pipeline boards.
     *
     * @return list<string>
     */
    public static function applicantStages(): array
    {
        return ['Applied', 'Screening', 'Interview', 'Offer', 'Hired'];
    }

    /**
     * Figma Frame 30 — Applicant pipeline board (NS-204 primary).
     *
     * @return list<array<string, mixed>>
     */
    public static function applicants(): array
    {
        return [
            [
                'id' => 'sofia-nguyen',
                'name' => 'Sofia Nguyen',
                'job_id' => 'ns-204',
                'job_code' => 'NS-204',
                'job_title' => 'Senior Product Designer',
                'stage' => 'Applied',
                'stage_key' => 'applied',
                'skills' => 'Figma · Research',
                'source' => 'Direct hire',
                'source_key' => 'direct',
                'owner' => 'Olivia Bennett',
                'applied_on' => 'Oct 19',
                'applied_on_long' => 'October 19, 2026',
                'location' => 'Austin, TX',
                'headline' => 'Product Designer',
                'years_experience' => 5,
                'email' => 'sofia.nguyen@example.com',
                'portfolio' => null,
            ],
            [
                'id' => 'marcus-reed',
                'name' => 'Marcus Reed',
                'job_id' => 'ns-204',
                'job_code' => 'NS-204',
                'job_title' => 'Senior Product Designer',
                'stage' => 'Applied',
                'stage_key' => 'applied',
                'skills' => 'Interaction · SaaS',
                'source' => 'Agency-managed',
                'source_key' => 'agency',
                'owner' => 'Olivia Bennett',
                'applied_on' => 'Oct 18',
                'applied_on_long' => 'October 18, 2026',
                'location' => 'Remote US',
                'headline' => 'Interaction Designer',
                'years_experience' => 6,
                'email' => 'marcus.reed@example.com',
                'portfolio' => null,
            ],
            [
                'id' => 'zoe-park',
                'name' => 'Zoe Park',
                'job_id' => 'ns-204',
                'job_code' => 'NS-204',
                'job_title' => 'Senior Product Designer',
                'stage' => 'Applied',
                'stage_key' => 'applied',
                'skills' => 'Systems · UI',
                'source' => 'Direct hire',
                'source_key' => 'direct',
                'owner' => 'Olivia Bennett',
                'applied_on' => 'Oct 18',
                'applied_on_long' => 'October 18, 2026',
                'location' => 'Austin, TX',
                'headline' => 'UI Designer',
                'years_experience' => 4,
                'email' => 'zoe.park@example.com',
                'portfolio' => null,
            ],
            [
                'id' => 'noah-williams',
                'name' => 'Noah Williams',
                'job_id' => 'ns-204',
                'job_code' => 'NS-204',
                'job_title' => 'Senior Product Designer',
                'stage' => 'Screening',
                'stage_key' => 'screening',
                'skills' => 'Research · Strategy',
                'source' => 'Direct hire',
                'source_key' => 'direct',
                'owner' => 'Olivia Bennett',
                'applied_on' => 'Oct 16',
                'applied_on_long' => 'October 16, 2026',
                'location' => 'Austin, TX',
                'headline' => 'UX Researcher',
                'years_experience' => 7,
                'email' => 'noah.williams@example.com',
                'portfolio' => null,
            ],
            [
                'id' => 'ella-davis',
                'name' => 'Ella Davis',
                'job_id' => 'ns-204',
                'job_code' => 'NS-204',
                'job_title' => 'Senior Product Designer',
                'stage' => 'Screening',
                'stage_key' => 'screening',
                'skills' => 'Figma · Systems',
                'source' => 'Agency-managed',
                'source_key' => 'agency',
                'owner' => 'Olivia Bennett',
                'applied_on' => 'Oct 16',
                'applied_on_long' => 'October 16, 2026',
                'location' => 'Chicago, IL',
                'headline' => 'Visual Designer',
                'years_experience' => 5,
                'email' => 'ella.davis@example.com',
                'portfolio' => null,
            ],
            [
                'id' => 'ben-carter',
                'name' => 'Ben Carter',
                'job_id' => 'ns-204',
                'job_code' => 'NS-204',
                'job_title' => 'Senior Product Designer',
                'stage' => 'Screening',
                'stage_key' => 'screening',
                'skills' => 'Prototyping · UI',
                'source' => 'Direct hire',
                'source_key' => 'direct',
                'owner' => 'Olivia Bennett',
                'applied_on' => 'Oct 15',
                'applied_on_long' => 'October 15, 2026',
                'location' => 'Austin, TX',
                'headline' => 'Product Designer',
                'years_experience' => 6,
                'email' => 'ben.carter@example.com',
                'portfolio' => null,
            ],
            [
                'id' => 'maya-chen',
                'name' => 'Maya Chen',
                'job_id' => 'ns-204',
                'job_code' => 'NS-204',
                'job_title' => 'Senior Product Designer',
                'stage' => 'Interview',
                'stage_key' => 'interview',
                'skills' => 'Systems · Research',
                'source' => 'Direct hire',
                'source_key' => 'direct',
                'owner' => 'Olivia Bennett',
                'applied_on' => 'Oct 12',
                'applied_on_long' => 'October 12, 2026',
                'location' => 'Austin, TX',
                'headline' => 'Senior Product Designer',
                'years_experience' => 8,
                'email' => 'maya.chen@example.com',
                'portfolio' => 'https://mayachen.design',
                'portfolio_label' => 'mayachen.design',
                'highlighted' => true,
                'flag' => 'Oct 22 · 10:00 AM · Portfolio interview',
            ],
            [
                'id' => 'luis-romero',
                'name' => 'Luis Romero',
                'job_id' => 'ns-204',
                'job_code' => 'NS-204',
                'job_title' => 'Senior Product Designer',
                'stage' => 'Interview',
                'stage_key' => 'interview',
                'skills' => 'SaaS · Strategy',
                'source' => 'Agency-managed',
                'source_key' => 'agency',
                'owner' => 'Olivia Bennett',
                'applied_on' => 'Oct 11',
                'applied_on_long' => 'October 11, 2026',
                'location' => 'Austin, TX',
                'headline' => 'Product Designer',
                'years_experience' => 7,
                'email' => 'luis.romero@example.com',
                'portfolio' => null,
                'flag' => 'Oct 21 · 11:00 AM · Team interview',
            ],
            [
                'id' => 'aisha-patel',
                'name' => 'Aisha Patel',
                'job_id' => 'ns-204',
                'job_code' => 'NS-204',
                'job_title' => 'Senior Product Designer',
                'stage' => 'Interview',
                'stage_key' => 'interview',
                'skills' => 'Research · Figma',
                'source' => 'Direct hire',
                'source_key' => 'direct',
                'owner' => 'Olivia Bennett',
                'applied_on' => 'Oct 10',
                'applied_on_long' => 'October 10, 2026',
                'location' => 'Austin, TX',
                'headline' => 'Product Designer',
                'years_experience' => 6,
                'email' => 'aisha.patel@example.com',
                'portfolio' => null,
                'flag' => 'Oct 23 · 11:00 AM · Portfolio review',
            ],
            [
                'id' => 'ethan-cole',
                'name' => 'Ethan Cole',
                'job_id' => 'ns-204',
                'job_code' => 'NS-204',
                'job_title' => 'Senior Product Designer',
                'stage' => 'Offer',
                'stage_key' => 'offer',
                'skills' => 'Systems · SaaS',
                'source' => 'Direct hire',
                'source_key' => 'direct',
                'owner' => 'Olivia Bennett',
                'applied_on' => 'Oct 05',
                'applied_on_long' => 'October 5, 2026',
                'location' => 'Austin, TX',
                'headline' => 'Senior Designer',
                'years_experience' => 9,
                'email' => 'ethan.cole@example.com',
                'portfolio' => null,
                'badge' => ['variant' => 'pending', 'label' => 'Draft offer'],
            ],
            [
                'id' => 'nora-kim',
                'name' => 'Nora Kim',
                'job_id' => 'ns-204',
                'job_code' => 'NS-204',
                'job_title' => 'Senior Product Designer',
                'stage' => 'Offer',
                'stage_key' => 'offer',
                'skills' => 'UI · Research',
                'source' => 'Agency-managed',
                'source_key' => 'agency',
                'owner' => 'Olivia Bennett',
                'applied_on' => 'Oct 04',
                'applied_on_long' => 'October 4, 2026',
                'location' => 'Remote US',
                'headline' => 'Product Designer',
                'years_experience' => 8,
                'email' => 'nora.kim@example.com',
                'portfolio' => null,
            ],
            [
                'id' => 'grace-liu',
                'name' => 'Grace Liu',
                'job_id' => 'ns-204',
                'job_code' => 'NS-204',
                'job_title' => 'Senior Product Designer',
                'stage' => 'Hired',
                'stage_key' => 'hired',
                'skills' => 'Systems · Figma',
                'source' => 'Agency-managed',
                'source_key' => 'agency',
                'owner' => 'Olivia Bennett',
                'applied_on' => 'Sep 28',
                'applied_on_long' => 'September 28, 2026',
                'location' => 'Austin, TX',
                'headline' => 'Design Lead',
                'years_experience' => 10,
                'email' => 'grace.liu@example.com',
                'portfolio' => null,
                'badge' => ['variant' => 'success', 'label' => 'Hired Oct 16'],
            ],
        ];
    }

    /**
     * Frame 30 pipeline page payload. Optional job filter (default NS-204).
     *
     * @return array<string, mixed>
     */
    public static function applicantsPipeline(?string $jobId = null): array
    {
        $jobId = $jobId ?: 'ns-204';
        $job = self::job($jobId);
        $applicants = array_values(array_filter(
            self::applicants(),
            static fn (array $row): bool => ($row['job_id'] ?? '') === $jobId
        ));

        $stages = self::applicantStages();
        $columns = [];
        foreach ($stages as $stage) {
            $cards = array_values(array_filter(
                $applicants,
                static fn (array $row): bool => ($row['stage'] ?? '') === $stage
            ));
            $columns[] = [
                'title' => $stage,
                'key' => strtolower($stage),
                'count' => count($cards),
                'cards' => $cards,
            ];
        }

        $direct = count(array_filter($applicants, static fn (array $r): bool => ($r['source_key'] ?? '') === 'direct'));
        $agency = count(array_filter($applicants, static fn (array $r): bool => ($r['source_key'] ?? '') === 'agency'));

        $owners = array_values(array_unique(array_map(
            static fn (array $r): string => $r['owner'] ?? '',
            $applicants
        )));

        $jobs = array_values(array_filter(
            self::jobs(),
            static fn (array $j): bool => ($j['status_key'] ?? '') === 'open'
        ));

        return [
            'job_id' => $jobId,
            'job_code' => $job['code'] ?? 'NS-204',
            'job_title' => $job['title'] ?? 'Senior Product Designer',
            'owner' => $job['owner'] ?? 'Olivia Bennett',
            'candidate_count' => count($applicants),
            'applicants' => $applicants,
            'columns' => $columns,
            'stages' => $stages,
            'owners' => array_values(array_filter($owners)),
            'jobs' => array_map(static fn (array $j): array => [
                'id' => $j['id'],
                'code' => $j['code'],
                'title' => $j['title'],
            ], $jobs),
            'summary' => [
                'direct' => $direct,
                'agency' => $agency,
                'rejected' => 0,
                'withdrawn' => 0,
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
                    'body' => 'Sofia Nguyen applied to Senior Product Designer (NS-204).',
                ],
                [
                    'time' => 'Oct 16 · 9:05 AM',
                    'body' => 'Grace Liu marked Hired for Senior Product Designer (NS-204).',
                ],
                [
                    'time' => 'Oct 15 · 11:30 AM',
                    'body' => 'Olivia Bennett completed screening with Maya Chen.',
                ],
            ],
            'footer' => 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026',
        ];
    }

    /**
     * Figma Frame 31 — Applicant profile / details.
     *
     * @return array<string, mixed>|null
     */
    public static function applicant(string $id): ?array
    {
        $base = collect(self::applicants())->firstWhere('id', $id);

        if ($base === null) {
            return null;
        }

        $stages = self::applicantStages();
        $currentIndex = array_search($base['stage'], $stages, true);
        if ($currentIndex === false) {
            $currentIndex = 0;
        }

        $pipeline = [];
        foreach ($stages as $index => $label) {
            $status = 'pending';
            if ($index < $currentIndex) {
                $status = 'completed';
            } elseif ($index === $currentIndex) {
                $status = 'current';
            }
            $pipeline[] = ['label' => $label, 'status' => $status];
        }

        $profile = MockCandidateData::profile();
        $isMaya = $id === 'maya-chen';

        $experience = $isMaya
            ? [
                [
                    'title' => 'Senior Product Designer',
                    'company' => 'Fieldwork',
                    'dates' => 'Mar 2022–Present',
                    'summary' => 'SaaS workflow design, research, design systems',
                ],
                [
                    'title' => 'Product Designer',
                    'company' => 'Parcel Systems',
                    'dates' => 'Jun 2018–Feb 2022',
                    'summary' => 'Enterprise planning and reporting tools',
                ],
            ]
            : [
                [
                    'title' => $base['headline'],
                    'company' => 'Previous employer',
                    'dates' => 'Recent',
                    'summary' => $base['skills'],
                ],
            ];

        $screening = $isMaya
            ? [
                [
                    'when' => 'Oct 15 · Olivia Bennett',
                    'body' => 'Evidence / next step: Confirmed Austin hybrid availability and $155k–$165k expectations.',
                ],
                [
                    'when' => 'Oct 19 · Interview planned',
                    'body' => 'Evidence / next step: Portfolio review with Alex · Oct 22, 10:00–10:45 AM Central.',
                ],
                [
                    'when' => 'Oct 20 · Preparation',
                    'body' => 'Evidence / next step: Agenda shared with Maya; portfolio projects requested.',
                ],
            ]
            : [
                [
                    'when' => $base['applied_on_long'].' · Application',
                    'body' => 'Evidence / next step: Application received for '.$base['job_title'].' ('.$base['job_code'].').',
                ],
                [
                    'when' => 'Current · '.$base['stage'],
                    'body' => 'Evidence / next step: '.$base['name'].' is in '.$base['stage'].' with owner '.$base['owner'].'.',
                ],
            ];

        $interview = $isMaya
            ? [
                'status' => 'Confirmed',
                'title' => 'Portfolio review',
                'when' => 'Oct 22 · 10:00–10:45 AM',
                'timezone' => 'Central Time · Video meeting',
                'interviewer' => 'Alex Morgan',
                'interviewer_meta' => 'Interviewer · Structured portfolio rubric',
            ]
            : (! empty($base['flag'])
                ? [
                    'status' => 'Scheduled',
                    'title' => 'Upcoming conversation',
                    'when' => $base['flag'],
                    'timezone' => 'Central Time',
                    'interviewer' => 'Alex Morgan',
                    'interviewer_meta' => 'Interviewer',
                ]
                : null);

        $interviewPlan = $isMaya
            ? [
                'agenda' => [
                    'Walk through two portfolio projects (20 min)',
                    'Systems and collaboration discussion (15 min)',
                    'Candidate questions (10 min)',
                ],
                'prep_shared' => true,
                'rubric' => 'Structured portfolio rubric · Alex Morgan',
            ]
            : [
                'agenda' => [
                    'Role fit and experience review',
                    'Team collaboration discussion',
                    'Candidate questions',
                ],
                'prep_shared' => false,
                'rubric' => 'Standard interview guide',
            ];

        $communications = [
            [
                'when' => 'Oct 20 · 2:15 PM',
                'channel' => 'Email',
                'summary' => 'Interview agenda shared with '.$base['name'].'.',
            ],
            [
                'when' => 'Oct 15 · 11:30 AM',
                'channel' => 'Call',
                'summary' => 'Screening conversation with '.$base['owner'].'.',
            ],
        ];

        $notesActivity = [
            [
                'when' => 'Oct 20 · 2:18 PM',
                'author' => 'Olivia Bennett',
                'body' => 'Agenda shared; waiting on two portfolio projects from candidate.',
            ],
            [
                'when' => 'Oct 15 · 11:45 AM',
                'author' => 'Olivia Bennett',
                'body' => 'Hybrid availability confirmed. Compensation expectations documented.',
            ],
        ];

        return array_merge($base, [
            'subtitle' => $base['headline'].' · '.$base['location'].' · '.$base['years_experience'].' years’ experience',
            'pipeline' => $pipeline,
            'experience' => $experience,
            'skills_list' => $isMaya
                ? array_column($profile['skills'], 'label')
                : array_map('trim', explode('·', $base['skills'])),
            'education' => $isMaya
                ? 'BFA Communication Design, Texas State University'
                : null,
            'resume' => $isMaya
                ? $profile['resume']
                : [
                    'filename' => str_replace(' ', '_', $base['name']).'_Resume.pdf',
                    'size' => '210 KB',
                    'updated' => $base['applied_on'],
                ],
            'screening' => $screening,
            'interview' => $interview,
            'interview_plan' => $interviewPlan,
            'communications' => $communications,
            'notes_activity' => $notesActivity,
            'consent' => [
                'processing' => 'Application-processing consent: '.$base['applied_on'],
                'talent_pool' => $isMaya ? 'Talent pool opt-in: Not selected' : 'Talent pool opt-in: Not recorded',
                'access' => 'Access restricted to assigned hiring team.',
            ],
            'footer' => 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026',
        ]);
    }
}
