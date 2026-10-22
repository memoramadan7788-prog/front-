<?php

namespace App\Support\Frontend;

/**
 * Isolated mock presentation data for Company/HR frontend (Batches 5A–5B).
 * Consistent with Candidate / Public Northstar Labs records — no Eloquent.
 */
class MockCompanyData
{
    /**
     * Organization status + presentation capability flags (F9A).
     * Query `?org_status=pending|approved|rejected|suspended` switches the demo variant.
     * Not authorization — frontend presentation only.
     *
     * @return array{organization: array<string, mixed>, capabilities: array<string, bool>, status_banner: array<string, mixed>|null}
     */
    public static function organizationContext(?string $statusOverride = null): array
    {
        $status = $statusOverride
            ?? (is_string(request()->query('org_status')) ? request()->query('org_status') : null)
            ?? 'approved';

        if (! in_array($status, ['pending', 'approved', 'rejected', 'suspended'], true)) {
            $status = 'approved';
        }

        $approved = $status === 'approved';

        $banners = [
            'pending' => [
                'variant' => 'warning',
                'title_key' => 'company.organization.banner_pending_title',
                'body_key' => 'company.organization.banner_pending_body',
            ],
            'rejected' => [
                'variant' => 'error',
                'title_key' => 'company.organization.banner_rejected_title',
                'body_key' => 'company.organization.banner_rejected_body',
            ],
            'suspended' => [
                'variant' => 'error',
                'title_key' => 'company.organization.banner_suspended_title',
                'body_key' => 'company.organization.banner_suspended_body',
            ],
            'approved' => null,
        ];

        return [
            'organization' => [
                'id' => 'org-ns-001',
                'name' => 'Northstar Labs',
                'status' => $status,
                'owner_name' => 'Olivia Bennett',
                'owner_email' => 'olivia@northstarlabs.example',
                'rejection_reason' => $status === 'rejected'
                    ? 'Business registration document could not be verified against the listed legal entity.'
                    : null,
                'reviewed_at' => $status === 'approved' ? 'Oct 8, 2026' : ($status === 'rejected' ? 'Oct 12, 2026' : null),
            ],
            'capabilities' => [
                'can_publish_jobs' => $approved,
                'can_create_jobs' => $approved,
                'can_manage_members' => $approved || $status === 'pending',
                'can_assign_roles' => $approved,
                'can_edit_company_profile' => $status !== 'suspended',
            ],
            'status_banner' => $banners[$status],
            'frontend_only' => true,
            'presentation_note' => 'Capability flags are presentation-only. They do not enforce access control.',
        ];
    }

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
                'organization_id' => 'org-ns-001',
                'owner' => 'Olivia Bennett',
                'team' => 'Product & Design',
                'department' => 'Product & Design',
                'category' => 'Product & Design',
                'model' => 'Direct + agency',
                'model_key' => 'direct_agency',
                'pipeline' => '12 total · 3 interview',
                'pipeline_total' => 12,
                'pipeline_interview' => 3,
                'status' => 'Open',
                'status_key' => 'open',
                'publication_status' => 'published',
                'visibility' => 'public',
                'private_apply_mode' => null,
                'application_mode' => 'open',
                'openings_count' => 2,
                'deadline' => 'November 30, 2026',
                'skills' => [
                    ['label' => 'Figma', 'is_required' => true],
                    ['label' => 'Design systems', 'is_required' => true],
                    ['label' => 'User research', 'is_required' => false],
                ],
                'stages_config' => [
                    ['type' => 'screening', 'label' => 'Screening', 'required' => true, 'order' => 1, 'exam_id' => null],
                    ['type' => 'exam', 'label' => 'Design practical', 'required' => false, 'order' => 2, 'exam_id' => 'exam-ns204-systems'],
                    ['type' => 'interview', 'label' => 'Portfolio interview', 'required' => true, 'order' => 3, 'rounds' => 1],
                    ['type' => 'offer', 'label' => 'Offer', 'required' => false, 'order' => 4],
                ],
                'requirements_flags' => [
                    'cv_required' => true,
                    'ai_consent_required' => true,
                    'cover_note_required' => false,
                ],
                'employees_cannot_apply_public' => true,
                'employment' => 'Full-time',
                'work_model' => 'Hybrid · Two office days',
                'work_mode' => 'hybrid',
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
                    ['label' => 'Applied', 'key' => 'Applied', 'count' => 3],
                    ['label' => 'Screening', 'key' => 'Screening', 'count' => 3],
                    ['label' => 'Interview', 'key' => 'Interview', 'count' => 3],
                    ['label' => 'Offer', 'key' => 'Offer', 'count' => 2],
                    ['label' => 'Hired', 'key' => 'Hired', 'count' => 1],
                ],
                'stat_cards' => [
                    ['label' => 'Applied', 'value' => '3', 'meta' => 'New applications', 'stage' => 'Applied'],
                    ['label' => 'Screening', 'value' => '3', 'meta' => 'Review needed', 'stage' => 'Screening'],
                    ['label' => 'Interview', 'value' => '3', 'meta' => 'Maya · Luis · Aisha', 'stage' => 'Interview'],
                ],
                'offer_hired' => [
                    'value' => '2 / 1',
                    'meta' => '17 total candidates',
                    'label' => 'Offer / Hired',
                ],
                'forward_rows' => [
                    [
                        'applicant_id' => 'maya-chen',
                        'candidate' => 'Maya Chen',
                        'stage' => 'Interview',
                        'stage_key' => 'interview',
                        'next' => 'Portfolio review · Oct 22, 10:00 AM',
                    ],
                    [
                        'applicant_id' => 'luis-romero',
                        'candidate' => 'Luis Romero',
                        'stage' => 'Interview',
                        'stage_key' => 'interview',
                        'next' => 'Team interview · Oct 21, 11:00 AM',
                    ],
                    [
                        'applicant_id' => 'aisha-patel',
                        'candidate' => 'Aisha Patel',
                        'stage' => 'Interview',
                        'stage_key' => 'interview',
                        'next' => 'Portfolio review · Oct 23, 11:00 AM',
                    ],
                    [
                        'applicant_id' => 'ethan-cole',
                        'candidate' => 'Ethan Cole',
                        'stage' => 'Offer',
                        'stage_key' => 'offer',
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
                'publication_status' => 'draft',
                'visibility' => 'public',
            ],
            [
                'id' => 'ns-208',
                'code' => 'NS-208',
                'title' => 'Internal Design Systems Lead',
                'organization_id' => 'org-ns-001',
                'owner' => 'Olivia Bennett',
                'team' => 'Product & Design',
                'department' => 'Product & Design',
                'category' => 'Product & Design',
                'model' => 'Direct hire',
                'model_key' => 'direct',
                'pipeline' => '3 total · Private employees',
                'pipeline_total' => 3,
                'pipeline_interview' => 1,
                'status' => 'Open',
                'status_key' => 'open',
                'publication_status' => 'published',
                'visibility' => 'private',
                'private_apply_mode' => 'all_employees',
                'application_mode' => 'all_employees',
                'openings_count' => 1,
                'deadline' => 'December 15, 2026',
                'skills' => [
                    ['label' => 'Design systems', 'is_required' => true],
                    ['label' => 'Figma', 'is_required' => true],
                ],
                'stages_config' => [
                    ['type' => 'screening', 'label' => 'Screening', 'required' => true, 'order' => 1],
                    ['type' => 'interview', 'label' => 'Interview', 'required' => true, 'order' => 2],
                    ['type' => 'offer', 'label' => 'Offer', 'required' => false, 'order' => 3],
                ],
                'requirements_flags' => [
                    'cv_required' => true,
                    'ai_consent_required' => true,
                    'cover_note_required' => false,
                ],
                'employment' => 'Full-time',
                'work_model' => 'Hybrid · Two office days',
                'work_mode' => 'hybrid',
                'location' => 'Austin, TX',
                'salary_label' => '$150k–$180k USD',
                'salary_min' => '$150,000',
                'salary_max' => '$180,000',
                'location_short' => 'Austin hybrid',
                'posted_at' => 'October 18, 2026',
                'published_label' => 'Private · Employees · Oct 18',
                'hiring_manager' => 'Daniel Brooks',
                'description' => 'Private employee-eligible role for Northstar Labs members only.',
                'responsibilities' => ['Own the internal design system roadmap.'],
                'requirements' => ['Approved Northstar Labs employee membership.'],
                'benefits' => 'Internal mobility benefits.',
                'process' => 'Internal screen → Portfolio → Offer.',
                'summary' => 'Lead internal design systems for approved employees.',
                'stages' => [
                    ['label' => 'Applied', 'key' => 'Applied', 'count' => 1],
                    ['label' => 'Screening', 'key' => 'Screening', 'count' => 1],
                    ['label' => 'Interview', 'key' => 'Interview', 'count' => 1],
                ],
                'publish_to_search' => false,
                'agency_submissions' => false,
                'accommodation_info' => true,
            ],
            [
                'id' => 'ns-209',
                'code' => 'NS-209',
                'title' => 'Nominated Product Designer',
                'organization_id' => 'org-ns-001',
                'owner' => 'Olivia Bennett',
                'team' => 'Product & Design',
                'department' => 'Product & Design',
                'category' => 'Product & Design',
                'model' => 'Direct hire',
                'model_key' => 'direct',
                'pipeline' => '1 nominated · Private',
                'pipeline_total' => 1,
                'pipeline_interview' => 0,
                'status' => 'Open',
                'status_key' => 'open',
                'publication_status' => 'published',
                'visibility' => 'private',
                'private_apply_mode' => 'nominated_only',
                'application_mode' => 'nominated_only',
                'openings_count' => 1,
                'deadline' => 'December 1, 2026',
                'nominations' => [
                    ['id' => 'nom-maya-chen', 'candidate_id' => 'maya-chen', 'name' => 'Maya Chen', 'email' => 'maya.chen@example.com', 'nominated_by' => 'Olivia Bennett', 'nominated_at' => 'October 19, 2026'],
                ],
                'skills' => [
                    ['label' => 'Product design', 'is_required' => true],
                    ['label' => 'Prototyping', 'is_required' => false],
                ],
                'stages_config' => [
                    ['type' => 'screening', 'label' => 'Screening', 'required' => true, 'order' => 1],
                    ['type' => 'interview', 'label' => 'Interview', 'required' => true, 'order' => 2],
                    ['type' => 'offer', 'label' => 'Offer', 'required' => false, 'order' => 3],
                ],
                'requirements_flags' => [
                    'cv_required' => true,
                    'ai_consent_required' => true,
                    'cover_note_required' => true,
                ],
                'employment' => 'Full-time',
                'work_model' => 'Hybrid · Two office days',
                'work_mode' => 'hybrid',
                'location' => 'Austin, TX',
                'salary_label' => '$130k–$155k USD',
                'salary_min' => '$130,000',
                'salary_max' => '$155,000',
                'location_short' => 'Austin hybrid',
                'posted_at' => 'October 19, 2026',
                'published_label' => 'Private · Nominated · Oct 19',
                'hiring_manager' => 'Daniel Brooks',
                'description' => 'Private nomination-only role. Approved members still need a nomination.',
                'responsibilities' => ['Deliver nominated product design initiatives.'],
                'requirements' => ['Approved membership plus active nomination.'],
                'benefits' => 'Internal Northstar package.',
                'process' => 'Nomination → Apply → Interview.',
                'summary' => 'Nomination-only private role.',
                'stages' => [
                    ['label' => 'Applied', 'key' => 'Applied', 'count' => 0],
                    ['label' => 'Screening', 'key' => 'Screening', 'count' => 0],
                    ['label' => 'Interview', 'key' => 'Interview', 'count' => 0],
                ],
                'publish_to_search' => false,
                'agency_submissions' => false,
                'accommodation_info' => true,
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
                'publication_status' => 'closed',
                'visibility' => 'public',
                'openings_count' => 1,
                'closed_reason_key' => 'openings_filled',
                'closed_reason' => 'Openings filled',
                'publish_to_search' => false,
                'agency_submissions' => false,
                'accommodation_info' => true,
                'last_edited' => 'Sep 30, 2026',
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
                'publication_status' => 'closed',
                'visibility' => 'public',
                'closed_reason_key' => 'manual_close',
                'closed_reason' => 'Cancelled — headcount reallocated',
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
    public static function jobWizard(?string $jobId = null): array
    {
        $isNew = $jobId === null || $jobId === '';
        $draft = $isNew ? [] : (self::job($jobId) ?? []);
        if (! $isNew && $draft === []) {
            $draft = [];
        }

        $resolvedId = $isNew ? 'new' : (string) ($draft['id'] ?? $jobId);
        $code = $isNew ? 'Draft' : (string) ($draft['code'] ?? 'Draft');
        $titleDefault = $isNew ? '' : (string) ($draft['title'] ?? '');

        return [
            'job_id' => $resolvedId,
            'is_new' => $isNew,
            'storage_key' => 'hb_company_job_draft_'.$resolvedId,
            'code' => $code,
            'title_default' => $titleDefault !== '' ? $titleDefault : 'New requisition',
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
            'categories' => [
                'Product & Design',
                'Engineering & Data',
                'People & Operations',
                'Marketing & Growth',
            ],
            'employment_types' => ['Full-time', 'Part-time', 'Contract'],
            'work_models' => [
                'Hybrid · Two office days',
                'Remote',
                'On-site',
            ],
            'owners' => ['Olivia Bennett', 'Jordan Lee', 'Daniel Brooks'],
            'hiring_managers' => ['Jordan Lee', 'Daniel Brooks', 'Olivia Bennett', 'Alex Morgan'],
            'skill_options' => [
                ['value' => 'Figma', 'label' => 'Figma'],
                ['value' => 'Design systems', 'label' => 'Design systems'],
                ['value' => 'User research', 'label' => 'User research'],
                ['value' => 'Prototyping', 'label' => 'Prototyping'],
                ['value' => 'Product design', 'label' => 'Product design'],
            ],
            'stage_type_options' => [
                ['value' => 'screening', 'label' => 'Screening'],
                ['value' => 'exam', 'label' => 'Examination'],
                ['value' => 'interview', 'label' => 'Interview'],
                ['value' => 'offer', 'label' => 'Offer'],
            ],
            'exam_options' => [
                ['value' => '', 'label' => 'No exam attached'],
                ['value' => 'exam-ns204-systems', 'label' => 'Design systems practical'],
                ['value' => 'exam-generic-screen', 'label' => 'General screening quiz'],
            ],
            'nominee_options' => [
                ['id' => 'maya-chen', 'name' => 'Maya Chen', 'email' => 'maya.chen@example.com'],
                ['id' => 'alex-morgan', 'name' => 'Alex Morgan', 'email' => 'alex.morgan@northstarlabs.example'],
                ['id' => 'jordan-lee', 'name' => 'Jordan Lee', 'email' => 'jordan.lee@northstarlabs.example'],
            ],
            'design_assumption' => 'Nomination list and stage builder follow shared Hirebridge design patterns.',
            'defaults' => $isNew
                ? [
                    'title' => '',
                    'department' => 'Customer Experience',
                    'category' => 'People & Operations',
                    'employment' => 'Full-time',
                    'location' => 'Austin, TX',
                    'work_model' => 'Hybrid · Two office days',
                    'salary_min' => '',
                    'salary_max' => '',
                    'openings_count' => '1',
                    'deadline' => '',
                    'about' => '',
                    'requirements' => '',
                    'benefits' => '',
                    'owner' => 'Olivia Bennett',
                    'hiring_manager' => 'Jordan Lee',
                    'visibility' => 'public',
                    'private_apply_mode' => 'all_employees',
                    'publish_to_search' => true,
                    'agency_submissions' => false,
                    'accommodation_info' => true,
                    'cv_required' => true,
                    'ai_consent_required' => true,
                    'cover_note_required' => false,
                    'skill_figma' => true,
                    'skill_figma_required' => true,
                    'skill_design_systems' => true,
                    'skill_design_systems_required' => true,
                    'skill_user_research' => false,
                    'skill_user_research_required' => false,
                    'skill_prototyping' => false,
                    'skill_prototyping_required' => false,
                    'skill_product_design' => false,
                    'skill_product_design_required' => false,
                    'stage_screening' => true,
                    'stage_exam' => false,
                    'stage_exam_id' => '',
                    'stage_interview' => true,
                    'stage_interview_rounds' => '1',
                    'stage_offer' => true,
                    'nominee_maya-chen' => false,
                    'nominee_alex-morgan' => false,
                    'nominee_jordan-lee' => false,
                ]
                : [
                    'title' => $draft['title'] ?? 'Customer Success Manager',
                    'department' => $draft['department'] ?? 'Customer Experience',
                    'category' => $draft['category'] ?? ($draft['department'] ?? 'Customer Experience'),
                    'employment' => $draft['employment'] ?? 'Full-time',
                    'location' => $draft['location'] ?? 'Austin, TX',
                    'work_model' => $draft['work_model'] ?? 'Hybrid · Two office days',
                    'salary_min' => ($draft['salary_min'] ?? '') !== '' ? $draft['salary_min'] : '$110,000',
                    'salary_max' => ($draft['salary_max'] ?? '') !== '' ? $draft['salary_max'] : '$135,000',
                    'openings_count' => (string) ($draft['openings_count'] ?? '1'),
                    'deadline' => $draft['deadline'] ?? '',
                    'about' => $draft['description'] ?? 'Turn operational challenges into lasting customer value. You’ll guide mid-market accounts through adoption, renewals, and product feedback loops with design and engineering partners.',
                    'requirements' => is_array($draft['requirements'] ?? null)
                        ? implode("\n", $draft['requirements'])
                        : "4+ years in B2B customer success\nExperience with SaaS onboarding and renewals\nClear written and verbal communication\nComfort collaborating with product teams",
                    'benefits' => $draft['benefits'] ?? 'Health coverage, flexible PTO, learning budget, hybrid Austin rhythm.',
                    'owner' => $draft['owner'] ?? 'Olivia Bennett',
                    'hiring_manager' => $draft['hiring_manager'] ?? 'Jordan Lee',
                    'visibility' => $draft['visibility'] ?? 'public',
                    'private_apply_mode' => $draft['private_apply_mode'] ?? 'all_employees',
                    'publish_to_search' => (bool) ($draft['publish_to_search'] ?? true),
                    'agency_submissions' => (bool) ($draft['agency_submissions'] ?? false),
                    'accommodation_info' => (bool) ($draft['accommodation_info'] ?? true),
                    'cv_required' => (bool) (($draft['requirements_flags']['cv_required'] ?? true)),
                    'ai_consent_required' => (bool) (($draft['requirements_flags']['ai_consent_required'] ?? true)),
                    'cover_note_required' => (bool) (($draft['requirements_flags']['cover_note_required'] ?? false)),
                    'skill_figma' => true,
                    'skill_figma_required' => true,
                    'skill_design_systems' => true,
                    'skill_design_systems_required' => true,
                    'skill_user_research' => false,
                    'skill_user_research_required' => false,
                    'skill_prototyping' => false,
                    'skill_prototyping_required' => false,
                    'skill_product_design' => false,
                    'skill_product_design_required' => false,
                    'stage_screening' => true,
                    'stage_exam' => ($draft['id'] ?? '') === 'ns-204',
                    'stage_exam_id' => ($draft['id'] ?? '') === 'ns-204' ? 'exam-ns204-systems' : '',
                    'stage_interview' => true,
                    'stage_interview_rounds' => '1',
                    'stage_offer' => true,
                    'nominee_maya-chen' => ($draft['private_apply_mode'] ?? '') === 'nominated_only',
                    'nominee_alex-morgan' => false,
                    'nominee_jordan-lee' => false,
                ],
            'checklist' => [
                ['key' => 'role', 'label' => 'Role and location', 'done' => ! $isNew],
                ['key' => 'salary', 'label' => 'Salary range', 'done' => ! $isNew && ($draft['salary_min'] ?? '') !== ''],
                ['key' => 'description', 'label' => 'Description and requirements', 'done' => ! $isNew],
                ['key' => 'visibility', 'label' => 'Visibility & eligibility', 'done' => ! $isNew],
                ['key' => 'stages', 'label' => 'Recruitment stages', 'done' => ! $isNew],
                ['key' => 'privacy', 'label' => 'Privacy & equal-opportunity text', 'done' => ! $isNew],
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
                    'href' => 'company.jobs.index',
                ],
                [
                    'label' => 'Active candidates',
                    'value' => '28',
                    'meta' => 'Across open requisitions',
                    'trend' => '+4 this week',
                    'trend_tone' => 'up',
                    'href' => 'company.applicants.index',
                    'href_params' => ['job' => 'ns-204'],
                ],
                [
                    'label' => 'Interviews scheduled',
                    'value' => '3',
                    'meta' => 'Next: Oct 22 · Maya Chen',
                    'trend' => 'This week',
                    'trend_tone' => 'neutral',
                    'href' => 'company.interviews.index',
                ],
                [
                    'label' => 'Hired this month',
                    'value' => '1',
                    'meta' => 'Grace Liu · NS-204',
                    'trend' => 'Offer stage: 1 draft',
                    'trend_tone' => 'up',
                    'href' => 'company.analytics.index',
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
                    'id' => 'ns-204',
                    'code' => 'NS-204',
                    'title' => 'Senior Product Designer',
                    'owner' => 'Olivia Bennett',
                    'team' => 'Product & Design',
                    'status' => 'Open',
                    'pipeline' => '12 total · 3 interview',
                ],
                [
                    'id' => 'ns-205',
                    'code' => 'NS-205',
                    'title' => 'Senior Software Engineer',
                    'owner' => 'Jordan Lee',
                    'team' => 'Engineering',
                    'status' => 'Open',
                    'pipeline' => '9 total · 3 interview',
                ],
                [
                    'id' => 'ns-206',
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
                    'id' => 'int-ns204-luis-team',
                    'when' => 'Wed · Oct 21',
                    'time' => '11:00 AM',
                    'candidate' => 'Luis Romero',
                    'role' => 'Team interview · NS-204',
                    'interviewer' => 'Alex Morgan',
                ],
                [
                    'id' => 'int-ns204-portfolio',
                    'when' => 'Thu · Oct 22',
                    'time' => '10:00–10:45 AM',
                    'candidate' => 'Maya Chen',
                    'role' => 'Portfolio review · NS-204',
                    'interviewer' => 'Alex Morgan',
                    'highlight' => true,
                ],
                [
                    'id' => 'int-ns204-aisha-portfolio',
                    'when' => 'Fri · Oct 23',
                    'time' => '11:00 AM',
                    'candidate' => 'Aisha Patel',
                    'role' => 'Portfolio · NS-204',
                    'interviewer' => 'Alex Morgan',
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
        $applicants = array_values(array_map(
            static function (array $row): array {
                $ai = AiScreeningPresentation::forApplicant($row['id'], $row);
                $row['ai_screening'] = $ai;
                $row['ai_pipeline_label'] = $ai['pipeline_label'];
                $row['ai_badge_variant'] = $ai['badge_variant'];

                return $row;
            },
            array_filter(
                self::applicants(),
                static fn (array $row): bool => ($row['job_id'] ?? '') === $jobId
            )
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

        $relatedInterview = collect(self::interviews())->firstWhere('applicant_id', $id);
        $relatedEvaluation = collect(self::evaluations())->firstWhere('applicant_id', $id);
        $relatedOffer = collect([
            self::offerDraft('of-2026-021'),
        ])->filter()->firstWhere('applicant_id', $id);

        if ($interview !== null && $relatedInterview !== null) {
            $interview['id'] = $relatedInterview['id'];
        }

        $aiScreening = AiScreeningPresentation::forApplicant($id, $base);

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
            'ai_screening' => $aiScreening,
            'interview' => $interview,
            'interview_id' => $relatedInterview['id'] ?? null,
            'evaluation_id' => $relatedEvaluation['id'] ?? null,
            'offer_id' => $relatedOffer['id'] ?? null,
            'assessment_id' => $isMaya || $id === 'ethan-cole' || $id === 'nora-kim' ? 'exam-ns204-systems' : null,
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

    /**
     * Company interview catalog (Batch 5D) — consistent with candidate NS-204 interviews.
     *
     * @return list<array<string, mixed>>
     */
    public static function interviews(): array
    {
        return [
            [
                'id' => 'int-ns204-luis-team',
                'applicant_id' => 'luis-romero',
                'applicant' => 'Luis Romero',
                'job_id' => 'ns-204',
                'job_code' => 'NS-204',
                'job_title' => 'Senior Product Designer',
                'title' => 'Team interview',
                'type' => 'Team interview',
                'type_key' => 'team',
                'status' => 'Confirmed',
                'status_key' => 'confirmed',
                'day_key' => '2026-10-21',
                'weekday' => 'Wed',
                'day_num' => '21',
                'when' => 'Wednesday, Oct 21, 2026',
                'time' => '11:00–11:45 AM Central',
                'time_short' => '11:00 AM',
                'duration' => '45 minutes',
                'timezone' => 'Central Time (UTC−05:00)',
                'interviewer' => 'Alex Morgan',
                'organizer' => 'Olivia Bennett',
                'format' => 'Video meeting',
                'department' => 'Product & Design',
                'calendar_label' => 'Luis Romero · Team interview',
                'checklist' => [
                    ['label' => 'Candidate invitation sent', 'done' => true],
                    ['label' => 'Interview brief attached', 'done' => false],
                ],
            ],
            [
                'id' => 'int-ns204-portfolio',
                'applicant_id' => 'maya-chen',
                'applicant' => 'Maya Chen',
                'job_id' => 'ns-204',
                'job_code' => 'NS-204',
                'job_title' => 'Senior Product Designer',
                'title' => 'Portfolio interview',
                'type' => 'Portfolio review',
                'type_key' => 'portfolio',
                'status' => 'Confirmed',
                'status_key' => 'confirmed',
                'day_key' => '2026-10-22',
                'weekday' => 'Thu',
                'day_num' => '22',
                'when' => 'Thursday, Oct 22, 2026',
                'time' => '10:00–10:45 AM Central',
                'time_short' => '10:00–10:45 AM',
                'duration' => '45 minutes',
                'timezone' => 'Central Time (UTC−05:00)',
                'interviewer' => 'Alex Morgan',
                'organizer' => 'Olivia Bennett',
                'format' => 'Video meeting',
                'department' => 'Product & Design',
                'calendar_label' => 'Maya Chen · Portfolio · NS-204',
                'selected' => true,
                'checklist' => [
                    ['label' => 'Candidate invitation sent', 'done' => true],
                    ['label' => 'Interview brief attached', 'done' => true],
                ],
            ],
            [
                'id' => 'int-ns204-aisha-portfolio',
                'applicant_id' => 'aisha-patel',
                'applicant' => 'Aisha Patel',
                'job_id' => 'ns-204',
                'job_code' => 'NS-204',
                'job_title' => 'Senior Product Designer',
                'title' => 'Portfolio review',
                'type' => 'Portfolio review',
                'type_key' => 'portfolio',
                'status' => 'Confirmed',
                'status_key' => 'confirmed',
                'day_key' => '2026-10-23',
                'weekday' => 'Fri',
                'day_num' => '23',
                'when' => 'Friday, Oct 23, 2026',
                'time' => '11:00–11:45 AM Central',
                'time_short' => '11:00 AM',
                'duration' => '45 minutes',
                'timezone' => 'Central Time (UTC−05:00)',
                // Frame 39–40 assign Alex Morgan as portfolio interviewer (Daniel Brooks remains hiring manager).
                'interviewer' => 'Alex Morgan',
                'organizer' => 'Olivia Bennett',
                'format' => 'Video meeting',
                'department' => 'Product & Design',
                'calendar_label' => 'Aisha Patel · Portfolio · NS-204',
                'checklist' => [
                    ['label' => 'Candidate invitation sent', 'done' => true],
                    ['label' => 'Interview brief attached', 'done' => false],
                ],
            ],
            [
                'id' => 'int-ns204-cancelled-sample',
                'applicant_id' => 'sam-okonkwo',
                'applicant' => 'Sam Okonkwo',
                'job_id' => 'ns-204',
                'job_code' => 'NS-204',
                'job_title' => 'Senior Product Designer',
                'title' => 'Portfolio interview',
                'type' => 'Portfolio review',
                'type_key' => 'portfolio',
                'status' => 'Cancelled',
                'status_key' => 'cancelled',
                'day_key' => '2026-10-22',
                'weekday' => 'Thu',
                'day_num' => '22',
                'when' => 'Thursday, Oct 22, 2026',
                'time' => 'Cancelled · sample',
                'time_short' => '—',
                'duration' => '45 minutes',
                'timezone' => 'Central Time (UTC−05:00)',
                'interviewer' => 'Alex Morgan',
                'organizer' => 'Olivia Bennett',
                'format' => 'Video meeting',
                'department' => 'Product & Design',
                'calendar_label' => 'Sam Okonkwo · Cancelled sample',
                'evaluation_status' => 'not_applicable',
                'checklist' => [
                    ['label' => 'Candidate invitation sent', 'done' => false],
                    ['label' => 'Interview brief attached', 'done' => false],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function interview(string $id): ?array
    {
        return collect(self::interviews())->firstWhere('id', $id);
    }

    /**
     * Figma Frame 33 — Team interview calendar.
     *
     * @return array<string, mixed>
     */
    public static function interviewCalendar(): array
    {
        $interviews = array_values(array_filter(
            self::interviews(),
            static fn (array $interview): bool => ($interview['status_key'] ?? '') !== 'cancelled'
        ));
        $byDay = [];
        foreach ($interviews as $interview) {
            $byDay[$interview['day_key']][] = $interview;
        }

        $mapInterviewSlots = static fn (array $dayInterviews): array => array_map(static fn (array $i): array => [
            'time' => $i['time_short'],
            'label' => $i['calendar_label'],
            'interview_id' => $i['id'],
            'available' => false,
        ], $dayInterviews);

        $weeks = [
            [
                'id' => '2026-10-12',
                'label' => 'October 12–16, 2026',
                'is_current' => false,
                'days' => [
                    ['key' => '2026-10-12', 'label' => 'Mon 12', 'slots' => [['time' => '10:00 AM', 'label' => 'Available', 'available' => true]]],
                    ['key' => '2026-10-13', 'label' => 'Tue 13', 'slots' => [['time' => '2:00 PM', 'label' => 'Available', 'available' => true]]],
                    ['key' => '2026-10-14', 'label' => 'Wed 14', 'slots' => []],
                    ['key' => '2026-10-15', 'label' => 'Thu 15', 'slots' => [['time' => '9:00 AM', 'label' => 'Available', 'available' => true]]],
                    ['key' => '2026-10-16', 'label' => 'Fri 16', 'slots' => []],
                ],
            ],
            [
                'id' => '2026-10-19',
                'label' => 'October 19–23, 2026',
                'is_current' => true,
                'days' => [
                    [
                        'key' => '2026-10-19',
                        'label' => 'Mon 19',
                        'slots' => [
                            ['time' => '9:00 AM', 'label' => 'Available', 'available' => true],
                            ['time' => '2:00 PM', 'label' => 'Available', 'available' => true],
                        ],
                    ],
                    [
                        'key' => '2026-10-20',
                        'label' => 'Tue 20',
                        'slots' => [
                            ['time' => '9:00 AM', 'label' => 'Available', 'available' => true],
                            ['time' => '2:00 PM', 'label' => 'Available', 'available' => true],
                        ],
                    ],
                    [
                        'key' => '2026-10-21',
                        'label' => 'Wed 21',
                        'highlight' => false,
                        'slots' => $mapInterviewSlots($byDay['2026-10-21'] ?? []),
                    ],
                    [
                        'key' => '2026-10-22',
                        'label' => 'Thu 22',
                        'highlight' => true,
                        'slots' => $mapInterviewSlots($byDay['2026-10-22'] ?? []),
                    ],
                    [
                        'key' => '2026-10-23',
                        'label' => 'Fri 23',
                        'slots' => $mapInterviewSlots($byDay['2026-10-23'] ?? []),
                    ],
                ],
            ],
            [
                'id' => '2026-10-26',
                'label' => 'October 26–30, 2026',
                'is_current' => false,
                'days' => [
                    ['key' => '2026-10-26', 'label' => 'Mon 26', 'slots' => [['time' => '10:00 AM', 'label' => 'Available', 'available' => true]]],
                    ['key' => '2026-10-27', 'label' => 'Tue 27', 'slots' => [['time' => '10:00 AM', 'label' => 'Available', 'available' => true]]],
                    ['key' => '2026-10-28', 'label' => 'Wed 28', 'slots' => [['time' => '3:00 PM', 'label' => 'Available', 'available' => true]]],
                    ['key' => '2026-10-29', 'label' => 'Thu 29', 'slots' => []],
                    ['key' => '2026-10-30', 'label' => 'Fri 30', 'slots' => []],
                ],
            ],
        ];

        $currentWeek = collect($weeks)->firstWhere('is_current', true) ?? $weeks[1];
        $selected = collect($interviews)->firstWhere('selected', true) ?? ($interviews[0] ?? null);

        return [
            'week_label' => $currentWeek['label'],
            'current_week_id' => $currentWeek['id'],
            'timezone' => 'Week view · Central Time (UTC−05:00)',
            'department_filter' => 'Product & Design',
            'departments' => ['Product & Design', 'Engineering', 'Product'],
            'jobs' => array_values(array_map(static fn (array $j): array => [
                'id' => $j['id'],
                'code' => $j['code'],
                'title' => $j['title'],
            ], array_filter(self::jobs(), static fn (array $j): bool => ($j['status_key'] ?? '') === 'open'))),
            'weeks' => $weeks,
            'days' => $currentWeek['days'],
            'interviews' => $interviews,
            'selected' => $selected,
            'schedule_base' => '/company/interviews/schedule',
            'availability' => [
                ['name' => 'Alex Morgan', 'windows' => 'Thu 22 · 9:00–11:30 AM, 1:00–3:00 PM'],
                ['name' => 'Daniel Brooks', 'windows' => 'Wed 21 · 10:00 AM–12:00 PM'],
                ['name' => 'Olivia Bennett', 'windows' => 'Weekdays · 9:00 AM–4:00 PM'],
            ],
            'availability_note' => 'Availability is in Central Time. Calendar conflicts are checked before confirmation.',
            'footer' => 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026',
        ];
    }

    /**
     * Frame 32 schedule form payload (PNG not located — fields from brief + Frame 33 continuity).
     *
     * @return array<string, mixed>
     */
    public static function interviewSchedule(?string $applicantId = null): array
    {
        $applicantId = $applicantId ?: 'maya-chen';
        $applicant = self::applicant($applicantId);
        if ($applicant === null) {
            $applicant = self::applicant('maya-chen');
            $applicantId = 'maya-chen';
        }

        return [
            'applicant_id' => $applicantId,
            'applicant' => $applicant['name'],
            'job_id' => $applicant['job_id'],
            'job_code' => $applicant['job_code'],
            'job_title' => $applicant['job_title'],
            'stage' => $applicant['stage'],
            'owner' => $applicant['owner'],
            'timezone' => 'Central Time (UTC−05:00)',
            'duration_default' => '45 minutes',
            'types' => [
                ['value' => 'portfolio', 'label' => 'Portfolio review'],
                ['value' => 'team', 'label' => 'Team interview'],
                ['value' => 'screening', 'label' => 'Recruiter screening'],
                ['value' => 'hiring_manager', 'label' => 'Hiring manager'],
            ],
            'interviewers' => [
                ['value' => 'alex-morgan', 'label' => 'Alex Morgan', 'meta' => 'Portfolio interviewer'],
                ['value' => 'daniel-brooks', 'label' => 'Daniel Brooks', 'meta' => 'Hiring manager'],
                ['value' => 'olivia-bennett', 'label' => 'Olivia Bennett', 'meta' => 'Recruiter / HR'],
            ],
            'dates' => [
                ['value' => '2026-10-22', 'label' => 'Thu, Oct 22'],
                ['value' => '2026-10-23', 'label' => 'Fri, Oct 23'],
                ['value' => '2026-10-27', 'label' => 'Mon, Oct 27'],
                ['value' => '2026-10-28', 'label' => 'Tue, Oct 28'],
            ],
            'slots' => InterviewBookingPresentation::enrichSlots([
                ['value' => '2026-10-22T10:00', 'label' => 'Thu, Oct 22 · 10:00 AM', 'meta' => '45 min · Central Time', 'date' => '2026-10-22'],
                ['value' => '2026-10-22T14:00', 'label' => 'Thu, Oct 22 · 2:00 PM', 'meta' => '45 min · Central Time', 'date' => '2026-10-22', 'conflict_on_confirm' => true],
                ['value' => '2026-10-23T09:00', 'label' => 'Fri, Oct 23 · 9:00 AM', 'meta' => '45 min · Central Time · already booked', 'date' => '2026-10-23', 'unavailable' => true],
                ['value' => '2026-10-23T11:30', 'label' => 'Fri, Oct 23 · 11:30 AM', 'meta' => '45 min · Central Time', 'date' => '2026-10-23'],
                ['value' => '2026-10-27T10:00', 'label' => 'Mon, Oct 27 · 10:00 AM', 'meta' => '45 min · Central Time · sample expired window', 'date' => '2026-10-27', 'expired' => true],
                ['value' => '2026-10-28T15:00', 'label' => 'Tue, Oct 28 · 3:00 PM', 'meta' => '45 min · Central Time', 'date' => '2026-10-28'],
            ]),
            'default_type' => 'portfolio',
            'default_interviewer' => 'alex-morgan',
            'default_date' => '2026-10-22',
            'default_slot' => '2026-10-22T10:00',
            'availability_authority' => __('company.interviews.availability_authority'),
            'instructions' => __('company.interviews.instructions_body'),
            'applicants' => array_values(array_map(static fn (array $a): array => [
                'id' => $a['id'],
                'name' => $a['name'],
                'job_code' => $a['job_code'],
                'stage' => $a['stage'],
            ], array_filter(self::applicants(), static fn (array $a): bool => in_array($a['stage_key'] ?? '', ['screening', 'interview', 'offer'], true)))),
            'footer' => 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026',
        ];
    }

    /**
     * Figma Frame 34 — Evaluation review (Ethan Cole).
     *
     * @return list<array<string, mixed>>
     */
    public static function evaluations(): array
    {
        return [
            [
                'id' => 'eval-ethan-cole',
                'applicant_id' => 'ethan-cole',
                'applicant' => 'Ethan Cole',
                'job_id' => 'ns-204',
                'job_code' => 'NS-204',
                'job_title' => 'Senior Product Designer',
                'interview_id' => 'int-ns204-ethan-final',
                'source' => 'Direct hire',
                'stage' => 'Offer stage',
                'subtitle' => 'NS-204 · Senior Product Designer · Final interviews completed October 16',
                'decision_owner' => 'Daniel Brooks',
                'feedback_complete' => '3 / 3',
                'evaluation_completion' => 'complete',
                'hr_review_status' => 'completed',
                'hr_review_label' => 'HR review completed · proceed to offer draft',
                'scale_legend' => '1 = Below · 2 = Developing · 3 = Meets · 4 = Strong · N/O = Not observed',
                'erd_contract_note' => 'Competency rows help structure reviewer feedback. Overall score and notes remain the primary review record in this preview.',
                'competencies' => [
                    [
                        'label' => 'Problem framing',
                        'alex' => ['score' => 4, 'label' => 'Strong', 'round' => 'Portfolio'],
                        'daniel' => ['score' => 3, 'label' => 'Meets', 'round' => 'Team'],
                        'summary' => 'Connected customer constraints to a clear opportunity.',
                    ],
                    [
                        'label' => 'Interaction craft',
                        'alex' => ['score' => 4, 'label' => 'Strong', 'round' => 'Portfolio'],
                        'daniel' => ['score' => 3, 'label' => 'Meets', 'round' => 'Team'],
                        'summary' => 'Demonstrated complex workflow and accessible states.',
                    ],
                    [
                        'label' => 'Systems thinking',
                        'alex' => ['score' => 3, 'label' => 'Meets', 'round' => 'Portfolio'],
                        'daniel' => ['score' => 4, 'label' => 'Strong', 'round' => 'Team'],
                        'summary' => 'Explained reuse, exceptions, and implementation trade-offs.',
                    ],
                    [
                        'label' => 'Collaboration',
                        'alex' => ['score' => 3, 'label' => 'Meets', 'round' => 'Portfolio'],
                        'daniel' => ['score' => 4, 'label' => 'Strong', 'round' => 'Team'],
                        'summary' => 'Specific examples of resolving product / engineering tension.',
                    ],
                ],
                'recommendations' => [
                    [
                        'name' => 'Alex Morgan',
                        'verdict' => 'Strong hire',
                        'meta' => 'Submitted Oct 16, 3:10 PM',
                        'state' => 'submitted',
                        'comment' => 'Strong portfolio depth. Clear rationale for prioritization and accessibility decisions. Would benefit from early pairing on domain complexity.',
                    ],
                    [
                        'name' => 'Daniel Brooks',
                        'verdict' => 'Hire',
                        'meta' => 'Submitted Oct 16, 4:20 PM',
                        'state' => 'submitted',
                        'comment' => 'Strong collaboration examples and realistic trade-offs. Recommend proceeding within the approved compensation range.',
                    ],
                    [
                        'name' => 'Olivia Bennett',
                        'verdict' => 'Proceed',
                        'meta' => 'Screening completed Oct 9',
                        'state' => 'submitted',
                        'comment' => null,
                    ],
                ],
                'decision' => [
                    'status' => 'Proceed to offer',
                    'status_key' => 'proceed',
                    'detail' => 'Recorded Oct 19 by Daniel Brooks. Offer is in draft and has not been sent. High scores do not auto-promote stages.',
                ],
                'evidence_note' => 'Evidence, not an automatic score. Ratings support structured discussion. The hiring manager records the final decision and its rationale.',
                'footer' => 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026',
            ],
            [
                'id' => 'eval-maya-chen-pending',
                'applicant_id' => 'maya-chen',
                'applicant' => 'Maya Chen',
                'job_id' => 'ns-204',
                'job_code' => 'NS-204',
                'job_title' => 'Senior Product Designer',
                'interview_id' => 'int-ns204-portfolio',
                'source' => 'Direct hire',
                'stage' => 'Interview stage',
                'subtitle' => 'NS-204 · Portfolio interview · Awaiting scorecards',
                'decision_owner' => 'Daniel Brooks',
                'feedback_complete' => '0 / 2',
                'evaluation_completion' => 'awaiting',
                'hr_review_status' => 'pending',
                'hr_review_label' => 'HR review pending — required evaluations incomplete',
                'scale_legend' => '1 = Below · 2 = Developing · 3 = Meets · 4 = Strong · N/O = Not observed',
                'erd_contract_note' => 'Competency rows help structure reviewer feedback while evaluations remain incomplete.',
                'competencies' => [],
                'recommendations' => [
                    [
                        'name' => 'Alex Morgan',
                        'verdict' => 'Draft in progress',
                        'meta' => 'Browser draft only · not submitted',
                        'state' => 'draft',
                        'comment' => 'Local scorecard draft exists on the interviewer device. Not a submitted evaluation.',
                    ],
                    [
                        'name' => 'Daniel Brooks',
                        'verdict' => 'Awaiting interview',
                        'meta' => 'Not started',
                        'state' => 'pending',
                        'comment' => null,
                    ],
                ],
                'decision' => [
                    'status' => 'Awaiting evaluations',
                    'status_key' => 'awaiting',
                    'detail' => 'No hiring-stage advance until required evaluations are submitted and HR reviews them.',
                ],
                'evidence_note' => 'Partial scorecards stay review-only. High draft ratings never auto-advance the candidate.',
                'footer' => 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026',
            ],
            [
                'id' => 'eval-conflict-sample',
                'applicant_id' => 'nora-kim',
                'applicant' => 'Nora Kim',
                'job_id' => 'ns-204',
                'job_code' => 'NS-204',
                'job_title' => 'Senior Product Designer',
                'interview_id' => 'int-ns204-nora-sample',
                'source' => 'Direct hire',
                'stage' => 'Interview stage',
                'subtitle' => 'NS-204 · Sample conflicting recommendations',
                'decision_owner' => 'Daniel Brooks',
                'feedback_complete' => '2 / 2',
                'evaluation_completion' => 'complete_conflict',
                'hr_review_status' => 'required',
                'hr_review_label' => 'HR review required — evaluator recommendations conflict',
                'scale_legend' => '1 = Below · 2 = Developing · 3 = Meets · 4 = Strong · N/O = Not observed',
                'erd_contract_note' => 'Sample conflict for HR review. Final decision remains human-controlled.',
                'competencies' => [],
                'recommendations' => [
                    [
                        'name' => 'Alex Morgan',
                        'verdict' => 'Strong hire',
                        'meta' => 'Submitted sample',
                        'state' => 'submitted',
                        'comment' => 'Recommend advance.',
                    ],
                    [
                        'name' => 'Daniel Brooks',
                        'verdict' => 'Hold / more review',
                        'meta' => 'Submitted sample',
                        'state' => 'submitted',
                        'comment' => 'Need another round before offer.',
                    ],
                ],
                'decision' => [
                    'status' => 'HR review required',
                    'status_key' => 'review',
                    'detail' => 'Conflicting recommendations. Do not auto-promote based on scores alone.',
                ],
                'evidence_note' => 'Conflicts require authorized human review.',
                'footer' => 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026',
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function evaluation(string $id): ?array
    {
        $byId = collect(self::evaluations())->firstWhere('id', $id);
        if ($byId !== null) {
            return $byId;
        }

        return collect(self::evaluations())->firstWhere('applicant_id', $id);
    }

    /**
     * Figma Frame 35 — Create offer (Ethan Cole · OF-2026-021).
     *
     * @return array<string, mixed>|null
     */
    public static function offerDraft(?string $id = null): ?array
    {
        $id = $id ?: 'of-2026-021';
        $drafts = [
            'of-2026-021' => [
                'id' => 'of-2026-021',
                'code' => 'OF-2026-021',
                'applicant_id' => 'ethan-cole',
                'applicant' => 'Ethan Cole',
                'evaluation_id' => 'eval-ethan-cole',
                'job_id' => 'ns-204',
                'job_code' => 'NS-204',
                'job_title' => 'Senior Product Designer',
                'company' => 'Northstar Labs',
                'department' => 'Product & Design',
                'status' => 'Draft · Pending compensation approval',
                'status_key' => 'draft',
                'approval_state' => 'pending_approval',
                'banner' => 'Daniel Brooks must approve compensation before this offer can be sent. Ethan\'s application is at Offer; Maya\'s remains at Interview. Company approval is distinct from candidate acceptance.',
                'salary' => '$160,000',
                'salary_numeric' => 160000,
                'currency' => 'USD',
                'currency_note' => 'Presentation uses USD for EN samples. Database locale defaults may differ — Product decision required before global currency changes.',
                'job_salary_min' => 140000,
                'job_salary_max' => 170000,
                'job_salary_band' => '$140,000–$170,000 USD',
                'salary_in_range' => true,
                'salary_range_hint' => 'Sample is within the NS-204 approved band ($140k–$170k USD).',
                'salary_benchmark_hint' => 'Suggested midpoint from sample salary_recommendations: $155,000 USD (advisory only).',
                'bonus' => '10% of annual base',
                'employment' => 'Full-time',
                'employment_options' => ['Full-time', 'Part-time', 'Contract'],
                'start_date' => 'November 16, 2026',
                'start_date_short' => 'Nov 16, 2026',
                'deadline' => 'October 28, 2026',
                'deadline_short' => 'Oct 28',
                'expires_at' => '2026-10-28T23:59:00-05:00',
                'work_model' => 'Austin · Hybrid',
                'equity' => 'Equity grant subject to board approval; terms described in the offer letter.',
                'template' => 'Northstar_US_Permanent_2026.docx',
                'templates' => [
                    'Northstar_US_Permanent_2026.docx',
                    'Northstar_US_Contract_2026.docx',
                ],
                'message' => 'Ethan, we\'re excited about the possibility of you joining Product & Design. Please review the attached terms and contact Olivia with any questions.',
                'approvals' => [
                    [
                        'label' => 'Compensation approval',
                        'owner' => 'Daniel Brooks',
                        'status' => 'Pending',
                        'status_key' => 'pending',
                    ],
                    [
                        'label' => 'Offer letter',
                        'owner' => 'Olivia Bennett',
                        'status' => 'Draft generated',
                        'status_key' => 'draft',
                    ],
                    [
                        'label' => 'Benefits summary',
                        'owner' => 'People team',
                        'status' => 'Attached',
                        'status_key' => 'attached',
                    ],
                ],
                'require_esignature' => true,
                'preview' => [
                    'heading' => 'Your offer from Northstar Labs',
                    'lines' => [
                        'Senior Product Designer',
                        '$160,000 annual base',
                        '10% target bonus',
                        'Austin hybrid · Full-time',
                        'Start: Nov 16, 2026',
                        'Response requested: Oct 28',
                    ],
                ],
                'send_note' => 'Sending is disabled until approval is recorded. Changes after approval require reapproval.',
                'footer' => 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026',
            ],
        ];

        return $drafts[$id] ?? collect($drafts)->firstWhere('applicant_id', $id) ?? collect($drafts)->firstWhere('code', strtoupper($id));
    }

    /**
     * Figma Frame 36 — Talent pool prospects.
     *
     * @return array<string, mixed>
     */
    public static function talentPool(): array
    {
        $prospects = [
            [
                'id' => 'riley-torres',
                'name' => 'Riley Torres',
                'location' => 'Austin',
                'headline' => 'Product Designer',
                'skills' => 'Systems · Figma · Future design leadership',
                'source' => 'Direct referral',
                'source_key' => 'direct',
                'relationship' => 'Open to opportunities',
                'relationship_key' => 'open',
                'consent' => 'Consent valid · Mar 2027',
                'owner' => 'Olivia',
                'action' => 'View profile',
                'action_key' => 'profile',
                'category' => 'product_design',
            ],
            [
                'id' => 'amara-okafor',
                'name' => 'Amara Okafor',
                'location' => 'Remote US',
                'headline' => 'UX Researcher',
                'skills' => 'Research · SaaS · Discovery specialist',
                'source' => 'Agency · Jamie Rivera',
                'source_key' => 'agency',
                'relationship' => 'Nurturing',
                'relationship_key' => 'nurturing',
                'consent' => 'Consent valid · Feb 2027',
                'owner' => 'Olivia',
                'action' => 'Contact',
                'action_key' => 'contact',
                'category' => 'product_design',
            ],
            [
                'id' => 'oliver-smith',
                'name' => 'Oliver Smith',
                'location' => 'Austin',
                'headline' => 'Software Engineer',
                'skills' => 'TypeScript · React · Frontend',
                'source' => 'Direct sourced',
                'source_key' => 'direct',
                'relationship' => 'Contact requested',
                'relationship_key' => 'requested',
                'consent' => 'Profile access limited',
                'owner' => 'Olivia',
                'action' => 'View request',
                'action_key' => 'request',
                'category' => 'engineering',
            ],
            [
                'id' => 'nina-santos',
                'name' => 'Nina Santos',
                'location' => 'Austin',
                'headline' => 'Product Manager',
                'skills' => 'Discovery · Strategy · B2B product',
                'source' => 'Agency · Jamie Rivera',
                'source_key' => 'agency',
                'relationship' => 'Available',
                'relationship_key' => 'available',
                'consent' => 'Consent valid · Apr 2027',
                'owner' => 'Daniel',
                'action' => 'View profile',
                'action_key' => 'profile',
                'category' => 'other',
            ],
            [
                'id' => 'theo-martin',
                'name' => 'Theo Martin',
                'location' => 'Remote US',
                'headline' => 'Design Engineer',
                'skills' => 'Figma · React · Design systems',
                'source' => 'Direct referral',
                'source_key' => 'direct',
                'relationship' => 'Nurturing',
                'relationship_key' => 'nurturing',
                'consent' => 'Consent valid · Jan 2027',
                'owner' => 'Olivia',
                'action' => 'Contact',
                'action_key' => 'contact',
                'category' => 'engineering',
            ],
            [
                'id' => 'priya-nair',
                'name' => 'Priya Nair',
                'location' => 'Austin',
                'headline' => 'Senior Product Designer',
                'skills' => 'Design systems · Prototyping · Mentorship',
                'source' => 'Direct referral',
                'source_key' => 'direct',
                'relationship' => 'Open to opportunities',
                'relationship_key' => 'open',
                'consent' => 'Consent valid · May 2027',
                'owner' => 'Olivia',
                'action' => 'View profile',
                'action_key' => 'profile',
                'category' => 'product_design',
            ],
            [
                'id' => 'chris-nguyen',
                'name' => 'Chris Nguyen',
                'location' => 'Remote US',
                'headline' => 'Backend Engineer',
                'skills' => 'PHP · Laravel · APIs',
                'source' => 'Direct sourced',
                'source_key' => 'direct',
                'relationship' => 'Available',
                'relationship_key' => 'available',
                'consent' => 'Consent valid · Jun 2027',
                'owner' => 'Jordan',
                'action' => 'View profile',
                'action_key' => 'profile',
                'category' => 'engineering',
            ],
            [
                'id' => 'samira-hassan',
                'name' => 'Samira Hassan',
                'location' => 'Austin',
                'headline' => 'Content Designer',
                'skills' => 'UX writing · Accessibility · Localization',
                'source' => 'Agency · Jamie Rivera',
                'source_key' => 'agency',
                'relationship' => 'Nurturing',
                'relationship_key' => 'nurturing',
                'consent' => 'Consent valid · Mar 2027',
                'owner' => 'Daniel',
                'action' => 'Contact',
                'action_key' => 'contact',
                'category' => 'product_design',
            ],
            [
                'id' => 'leo-park',
                'name' => 'Leo Park',
                'location' => 'Remote US',
                'headline' => 'Staff Engineer',
                'skills' => 'Architecture · Mentorship · Reliability',
                'source' => 'Direct referral',
                'source_key' => 'direct',
                'relationship' => 'Open to opportunities',
                'relationship_key' => 'open',
                'consent' => 'Consent valid · Jul 2027',
                'owner' => 'Jordan',
                'action' => 'View profile',
                'action_key' => 'profile',
                'category' => 'engineering',
            ],
            [
                'id' => 'mira-cohen',
                'name' => 'Mira Cohen',
                'location' => 'Austin',
                'headline' => 'People Operations Lead',
                'skills' => 'Onboarding · Programs · Process design',
                'source' => 'Direct sourced',
                'source_key' => 'direct',
                'relationship' => 'Available',
                'relationship_key' => 'available',
                'consent' => 'Consent valid · Aug 2027',
                'owner' => 'Olivia',
                'action' => 'View profile',
                'action_key' => 'profile',
                'category' => 'other',
            ],
        ];

        $counts = [
            'all' => count($prospects),
            'product_design' => 0,
            'engineering' => 0,
            'other' => 0,
        ];
        foreach ($prospects as $prospect) {
            $category = $prospect['category'] ?? 'other';
            if (! array_key_exists($category, $counts)) {
                $category = 'other';
            }
            $counts[$category]++;
        }

        return [
            'total' => $counts['all'],
            'page_size' => 5,
            'tabs' => [
                ['key' => 'all', 'label' => 'All prospects', 'count' => $counts['all']],
                ['key' => 'product_design', 'label' => 'Product & Design', 'count' => $counts['product_design']],
                ['key' => 'engineering', 'label' => 'Engineering', 'count' => $counts['engineering']],
                ['key' => 'other', 'label' => 'Other', 'count' => $counts['other']],
            ],
            'prospects' => $prospects,
            'consent_note' => 'Maya Chen has not opted into Northstar\'s talent pool, so she is not included here. Agency introductions retain their recruiter ownership and consent record.',
            'footer' => 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026',
        ];
    }

    /**
     * Figma Frame 37 — Team & permissions.
     *
     * @return array<string, mixed>
     */
    public static function team(): array
    {
        $members = [
            [
                'id' => 'olivia-bennett',
                'name' => 'Olivia Bennett',
                'email' => 'olivia@northstarlabs.example',
                'role' => 'Company Admin / HR',
                'role_key' => 'hr',
                'scope' => 'All Northstar requisitions',
                'status' => 'Active · Workspace owner',
                'status_key' => 'active',
                'membership' => 'owner',
                'action' => 'Manage',
                'action_key' => 'manage',
            ],
            [
                'id' => 'daniel-brooks',
                'name' => 'Daniel Brooks',
                'email' => 'daniel@northstarlabs.example',
                'role' => 'Hiring manager',
                'role_key' => 'hiring_manager',
                'scope' => 'Product & Design jobs',
                'status' => 'Active',
                'status_key' => 'active',
                'membership' => 'internal',
                'action' => 'Edit scope',
                'action_key' => 'edit_scope',
            ],
            [
                'id' => 'alex-morgan',
                'name' => 'Alex Morgan',
                'email' => 'alex@northstarlabs.example',
                'role' => 'Interviewer',
                'role_key' => 'interviewer',
                'scope' => 'Assigned interviews only',
                'status' => 'Active',
                'status_key' => 'active',
                'membership' => 'internal',
                'action' => 'Edit scope',
                'action_key' => 'edit_scope',
            ],
            [
                'id' => 'jordan-lee',
                'name' => 'Jordan Lee',
                'email' => 'jordan@northstarlabs.example',
                'role' => 'Hiring manager',
                'role_key' => 'hiring_manager',
                'scope' => 'Customer Experience jobs',
                'status' => 'Active',
                'status_key' => 'active',
                'membership' => 'internal',
                'action' => 'Edit scope',
                'action_key' => 'edit_scope',
            ],
            [
                'id' => 'jamie-rivera',
                'name' => 'Jamie Rivera',
                'email' => 'jamie@brightpath.agency',
                'role' => 'Agency recruiter',
                'role_key' => 'agency',
                'scope' => 'NS-204 / NS-205 submissions',
                'status' => 'Active · External',
                'status_key' => 'external',
                'membership' => 'external',
                'action' => 'Review access',
                'action_key' => 'review',
            ],
        ];

        return [
            'members' => $members,
            'invites' => [
                [
                    'id' => 'invite-taylor-reed',
                    'name' => 'Taylor Reed',
                    'email' => 'taylor@northstarlabs.example',
                    'role' => 'Interviewer',
                    'role_key' => 'interviewer',
                    'scope' => 'Assigned interviews only',
                    'invited' => 'Oct 19',
                    'expires' => 'Oct 26',
                    'status' => 'Pending',
                    'status_key' => 'pending',
                ],
            ],
            'roles' => [
                [
                    'key' => 'hr',
                    'title' => 'HR and company admins',
                    'body' => 'Manage jobs, applications, offers, and workspace membership.',
                ],
                [
                    'key' => 'hiring_manager',
                    'title' => 'Hiring managers',
                    'body' => 'Review assigned roles and candidates. Approve decisions within their scope.',
                ],
                [
                    'key' => 'interviewer_agency',
                    'title' => 'Interviewers and agency recruiters',
                    'body' => 'Interviewers see assigned briefs and scorecards. Agencies see assigned engagements and their own submissions, not private HR notes.',
                ],
            ],
            'invite_defaults' => [
                'email' => 'taylor@northstarlabs.example',
                'role' => 'Interviewer',
                'role_key' => 'interviewer',
                'scope' => 'Assigned interviews only',
                'send_email' => true,
            ],
            'role_options' => [
                ['value' => 'hr', 'label' => 'Company Admin / HR'],
                ['value' => 'hiring_manager', 'label' => 'Hiring manager'],
                ['value' => 'interviewer', 'label' => 'Interviewer'],
                ['value' => 'agency', 'label' => 'Agency recruiter'],
            ],
            'scope_options' => [
                'All Northstar requisitions',
                'Product & Design jobs',
                'Customer Experience jobs',
                'Assigned interviews only',
                'NS-204 / NS-205 submissions',
            ],
            'audit_body' => 'Removing access ends future workspace access but preserves authored feedback and audit history. Sensitive changes require confirmation.',
            'pending_invite_note' => 'Taylor Reed · Interviewer · Invited Oct 19 · Expires Oct 26',
            'join_request_count' => 3,
            'footer' => 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026',
        ];
    }

    /**
     * Employee join requests — workflow-derived (no dedicated Figma frame verified for Batch 5F).
     *
     * @return array<string, mixed>
     */
    public static function joinRequests(): array
    {
        return [
            'design_source' => 'workflow',
            'requests' => [
                [
                    'id' => 'req-samira-hassan',
                    'name' => 'Samira Hassan',
                    'email' => 'samira.hassan@example.com',
                    'requested_role' => 'Hiring manager',
                    'requested_role_key' => 'hiring_manager',
                    'department' => 'Product & Design',
                    'requested_at' => 'October 18, 2026',
                    'note' => 'Requested access after referral from Olivia Bennett for NS-204 hiring support.',
                    'status' => 'Pending',
                    'status_key' => 'pending',
                ],
                [
                    'id' => 'req-chris-nguyen',
                    'name' => 'Chris Nguyen',
                    'email' => 'chris.nguyen@example.com',
                    'requested_role' => 'Interviewer',
                    'requested_role_key' => 'interviewer',
                    'department' => 'Engineering',
                    'requested_at' => 'October 17, 2026',
                    'note' => 'Joined Northstar Engineering and asked to score portfolio rounds for NS-205.',
                    'status' => 'Pending',
                    'status_key' => 'pending',
                ],
                [
                    'id' => 'req-mira-cohen',
                    'name' => 'Mira Cohen',
                    'email' => 'mira.cohen@example.com',
                    'requested_role' => 'Company Admin / HR',
                    'requested_role_key' => 'hr',
                    'department' => 'People Operations',
                    'requested_at' => 'October 12, 2026',
                    'note' => 'Reviewed and declined earlier — retained for audit history in this preview.',
                    'status' => 'Declined',
                    'status_key' => 'declined',
                    'rejection_reason' => 'People Operations already has an assigned HR admin for this workspace.',
                    'reviewed_by' => 'Olivia Bennett',
                    'reviewed_at' => 'October 14, 2026',
                ],
                [
                    'id' => 'req-maya-chen',
                    'name' => 'Maya Chen',
                    'email' => 'maya.chen@example.com',
                    'requested_role' => 'Employee',
                    'requested_role_key' => 'employee',
                    'department' => 'Product & Design',
                    'requested_at' => 'October 19, 2026',
                    'note' => 'Optional employee membership request from candidate onboarding (workflow 2). Cross-links with candidate membership mock.',
                    'status' => 'Pending',
                    'status_key' => 'pending',
                    'candidate_id' => 'maya-chen',
                    'company_id' => 'org-ns-001',
                ],
            ],
            'assignable_roles' => [
                ['value' => 'hr', 'label' => 'HR'],
                ['value' => 'interviewer', 'label' => 'Interviewer'],
                ['value' => 'employee', 'label' => 'Employee'],
            ],
            'footer' => 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026',
            'preview_disclaimer' => 'Approve and decline actions are frontend previews only. No membership or role is persisted.',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function employee(?string $id): ?array
    {
        if ($id === null || $id === '') {
            return null;
        }

        return collect(self::team()['members'])->firstWhere('id', $id);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function joinRequest(?string $id): ?array
    {
        if ($id === null || $id === '') {
            return null;
        }

        return collect(self::joinRequests()['requests'])->firstWhere('id', $id);
    }

    /**
     * Figma Frame 38 — Hiring analytics (static mock snapshot aligned to open Northstar requisitions).
     *
     * @return array<string, mixed>
     */
    public static function analytics(): array
    {
        return [
            'metric_source' => 'static_snapshot',
            'as_of' => 'October 20, 2026',
            'subtitle' => 'Northstar Labs · Current cohort for open requisitions · As of October 20, 2026',
            'date_label' => 'Oct 1–20, 2026 · Cohort includes earlier active applicants',
            'date_options' => [
                'oct-1-20' => 'Oct 1–20, 2026 · Cohort includes earlier active applicants',
                'sep-1-30' => 'Sep 1–30, 2026 · Prior month cohort',
                'q3' => 'Jul 1–Sep 30, 2026 · Q3 snapshot',
            ],
            'kpis' => [
                [
                    'key' => 'records',
                    'label' => 'Candidate records',
                    'value' => '28',
                    'detail' => 'Across 3 open requisitions',
                ],
                [
                    'key' => 'interview_share',
                    'label' => 'Interview-stage share',
                    'value' => '29%',
                    'detail' => '8 / 28 currently at Interview',
                ],
                [
                    'key' => 'screening_age',
                    'label' => 'Median screening age',
                    'value' => '4 days',
                    'detail' => 'Current screening-stage records',
                ],
                [
                    'key' => 'hired',
                    'label' => 'Hired in cohort',
                    'value' => '1',
                    'detail' => 'Grace Liu · NS-204',
                ],
            ],
            'date_snapshots' => [
                'oct-1-20' => [
                    'subtitle' => 'Northstar Labs · Current cohort for open requisitions · As of October 20, 2026',
                    'kpis' => [
                        'records' => ['value' => '28', 'detail' => 'Across 3 open requisitions'],
                        'interview_share' => ['value' => '29%', 'detail' => '8 / 28 currently at Interview'],
                        'screening_age' => ['value' => '4 days', 'detail' => 'Current screening-stage records'],
                        'hired' => ['value' => '1', 'detail' => 'Grace Liu · NS-204'],
                    ],
                ],
                'sep-1-30' => [
                    'subtitle' => 'Northstar Labs · Prior month cohort · September 2026 snapshot',
                    'kpis' => [
                        'records' => ['value' => '22', 'detail' => 'Across 3 open requisitions'],
                        'interview_share' => ['value' => '23%', 'detail' => '5 / 22 at Interview in September'],
                        'screening_age' => ['value' => '5 days', 'detail' => 'September screening-stage records'],
                        'hired' => ['value' => '0', 'detail' => 'No hires closed in September'],
                    ],
                ],
                'q3' => [
                    'subtitle' => 'Northstar Labs · Q3 hiring snapshot · Jul–Sep 2026',
                    'kpis' => [
                        'records' => ['value' => '41', 'detail' => 'Across open and closed Q3 roles'],
                        'interview_share' => ['value' => '27%', 'detail' => '11 / 41 reached Interview in Q3'],
                        'screening_age' => ['value' => '6 days', 'detail' => 'Q3 screening-stage median'],
                        'hired' => ['value' => '2', 'detail' => 'Including earlier NS-204 hire'],
                    ],
                ],
            ],
            'stages' => [
                ['key' => 'applied', 'label' => 'Applied', 'count' => 8, 'tone' => 'blue'],
                ['key' => 'screening', 'label' => 'Screening', 'count' => 8, 'tone' => 'blue'],
                ['key' => 'interview', 'label' => 'Interview', 'count' => 8, 'tone' => 'blue'],
                ['key' => 'offer', 'label' => 'Offer', 'count' => 3, 'tone' => 'blue'],
                ['key' => 'hired', 'label' => 'Hired', 'count' => 1, 'tone' => 'success'],
            ],
            'stage_max' => 8,
            'stage_note' => 'Snapshot, not cumulative stage conversion',
            'time_in_stage' => [
                ['key' => 'applied', 'label' => 'Applied', 'median' => 2, 'records' => 8],
                ['key' => 'screening', 'label' => 'Screening', 'median' => 4, 'records' => 8],
                ['key' => 'interview', 'label' => 'Interview', 'median' => 5, 'records' => 8],
                ['key' => 'offer', 'label' => 'Offer', 'median' => 2, 'records' => 3],
            ],
            'time_note' => 'Stage age measures time since the latest stage entry, not total time to hire.',
            'sources' => [
                [
                    'key' => 'direct',
                    'label' => 'Direct hire',
                    'records' => 17,
                    'interview' => 5,
                    'offer' => 2,
                    'hired' => 0,
                ],
                [
                    'key' => 'agency',
                    'label' => 'Agency-managed',
                    'records' => 11,
                    'interview' => 3,
                    'offer' => 1,
                    'hired' => 1,
                ],
            ],
            'sources_total' => [
                'records' => 28,
                'interview' => 8,
                'offer' => 3,
                'hired' => 1,
            ],
            'sources_note' => 'Source is recorded at application entry. Small samples are descriptive and should not be treated as a ranking of candidate quality.',
            'requisitions' => [
                [
                    'id' => 'ns-204',
                    'label' => 'NS-204 · Design',
                    'applied' => 3,
                    'screening' => 3,
                    'interview' => 3,
                    'offer' => 2,
                    'hired' => 1,
                    'source_key' => 'mixed',
                ],
                [
                    'id' => 'ns-205',
                    'label' => 'NS-205 · Engineering',
                    'applied' => 3,
                    'screening' => 2,
                    'interview' => 3,
                    'offer' => 1,
                    'hired' => 0,
                    'source_key' => 'mixed',
                ],
                [
                    'id' => 'ns-206',
                    'label' => 'NS-206 · Product',
                    'applied' => 2,
                    'screening' => 3,
                    'interview' => 2,
                    'offer' => 0,
                    'hired' => 0,
                    'source_key' => 'direct',
                ],
            ],
            'footer' => 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026',
        ];
    }

    /**
     * Company assessments — workflow/ERD-derived (no dedicated Figma frame for Batch 5G).
     * Aligned with MockCandidateData::assessments() for NS-204 Design systems practical.
     *
     * @return array<string, mixed>
     */
    public static function assessments(): array
    {
        return [
            'design_source' => 'workflow',
            'items' => [
                [
                    'id' => 'exam-ns204-systems',
                    'title' => 'Design systems practical',
                    'job_id' => 'ns-204',
                    'job_code' => 'NS-204',
                    'job_title' => 'Senior Product Designer',
                    'status' => 'Active',
                    'status_key' => 'active',
                    'time_limit_min' => 45,
                    'attempt_limit' => 2,
                    'passing_score' => 70,
                    'questions_count' => 4,
                    'assigned_count' => 3,
                    'completed_count' => 1,
                    'updated' => 'Oct 18, 2026',
                    'owner' => 'Olivia Bennett',
                    'questions' => [
                        [
                            'id' => 'q1',
                            'type' => 'mc',
                            'prompt' => 'When introducing a new component to a shared design system, what should you validate first?',
                            'choices' => [
                                'Visual polish only',
                                'Usage patterns, accessibility, and token alignment',
                                'Marketing screenshots',
                                'Font licensing alone',
                            ],
                        ],
                        [
                            'id' => 'q2',
                            'type' => 'mc',
                            'prompt' => 'A product squad needs a temporary one-off pattern. Best next step?',
                            'choices' => [
                                'Add it to the core system immediately',
                                'Prototype locally, document constraints, then propose promotion criteria',
                                'Block the squad until a system owner rebuilds it',
                                'Copy from a competitor without review',
                            ],
                        ],
                        [
                            'id' => 'q3',
                            'type' => 'mc',
                            'prompt' => 'Which signal best indicates a workflow is ready for design-system extraction?',
                            'choices' => [
                                'It appeared once in a pitch deck',
                                'Repeated use across squads with stable interaction rules',
                                'An engineer prefers a custom CSS file',
                                'The brand team likes the color',
                            ],
                        ],
                        [
                            'id' => 'q4',
                            'type' => 'short',
                            'prompt' => 'In 2–3 sentences, describe how you would prioritize accessibility fixes versus new feature work on an operations SaaS surface.',
                            'choices' => [],
                        ],
                    ],
                    'assignees' => [
                        [
                            'id' => 'maya-chen',
                            'name' => 'Maya Chen',
                            'status' => 'Ready to start',
                            'status_key' => 'ready',
                            'score' => null,
                        ],
                        [
                            'id' => 'ethan-cole',
                            'name' => 'Ethan Cole',
                            'status' => 'Completed',
                            'status_key' => 'completed',
                            'score' => 82,
                        ],
                        [
                            'id' => 'nora-kim',
                            'name' => 'Nora Kim',
                            'status' => 'In progress',
                            'status_key' => 'in_progress',
                            'score' => null,
                        ],
                    ],
                    'proctoring_required' => true,
                    'question_source' => 'manual',
                    'approval_status' => 'approved',
                    'grading_methods' => ['automatic_mc', 'manual_short'],
                    'results' => [
                        [
                            'candidate' => 'Ethan Cole',
                            'candidate_id' => 'ethan-cole',
                            'attempt_id' => 'attempt-ns204-ethan-1',
                            'score' => 82,
                            'pass' => true,
                            'completed_at' => 'Oct 16, 2026',
                            'attempt' => '1 of 2',
                            'grading_status' => 'graded',
                            'grading_method' => 'automatic_mc + manual_short',
                            'proctoring_review_status' => 'completed',
                        ],
                        [
                            'candidate' => 'Maya Chen',
                            'candidate_id' => 'maya-chen',
                            'attempt_id' => 'attempt-ns204-maya-flagged',
                            'score' => null,
                            'pass' => null,
                            'completed_at' => 'Oct 18, 2026',
                            'attempt' => '1 of 2',
                            'grading_status' => 'held_for_review',
                            'grading_method' => 'pending_human',
                            'proctoring_review_status' => 'pending',
                        ],
                    ],
                    'proctoring_queue' => [
                        [
                            'id' => 'flag-ns204-1',
                            'attempt_id' => 'attempt-ns204-maya-flagged',
                            'candidate_id' => 'maya-chen',
                            'candidate' => 'Maya Chen',
                            'event_type' => 'attention_away',
                            'label' => 'Attention left the exam window',
                            'recorded_at' => 'Oct 18, 2026 · 3:12 PM',
                            'severity' => 'review',
                            'note' => 'Sample signal for human review. Not an automatic fail or cheating verdict.',
                            'review_status' => 'pending',
                        ],
                    ],
                ],
                [
                    'id' => 'exam-ns205-frontend',
                    'title' => 'Frontend reliability scenarios',
                    'job_id' => 'ns-205',
                    'job_code' => 'NS-205',
                    'job_title' => 'Senior Frontend Engineer',
                    'status' => 'Pending approval',
                    'status_key' => 'pending_approval',
                    'time_limit_min' => 40,
                    'attempt_limit' => 1,
                    'passing_score' => 75,
                    'questions_count' => 3,
                    'assigned_count' => 0,
                    'completed_count' => 0,
                    'updated' => 'Oct 19, 2026',
                    'owner' => 'Jordan Lee',
                    'proctoring_required' => false,
                    'question_source' => 'ai_assisted',
                    'approval_status' => 'pending_review',
                    'approval_note' => 'AI-assisted draft questions require reviewer approval before publishing.',
                    'grading_methods' => ['automatic_mc', 'manual_short'],
                    'proctoring_queue' => [],
                    'questions' => [
                        [
                            'id' => 'q1',
                            'type' => 'mc',
                            'prompt' => 'Which practice best reduces regressions in a shared component library?',
                            'choices' => [
                                'Ship without tests to move faster',
                                'Visual + interaction tests with clear owners',
                                'Only manual QA on release day',
                                'Disable TypeScript',
                            ],
                        ],
                        [
                            'id' => 'q2',
                            'type' => 'short',
                            'prompt' => 'Describe how you would triage a production performance regression affecting form submissions.',
                            'choices' => [],
                        ],
                        [
                            'id' => 'q3',
                            'type' => 'mc',
                            'prompt' => 'Best default for handling API timeouts in a hiring dashboard?',
                            'choices' => [
                                'Silent failure',
                                'Retry with backoff and clear user feedback',
                                'Infinite spinner',
                                'Hard refresh only',
                            ],
                        ],
                    ],
                    'assignees' => [],
                    'results' => [],
                ],
            ],
            'job_options' => [
                ['id' => 'ns-204', 'label' => 'NS-204 · Senior Product Designer'],
                ['id' => 'ns-205', 'label' => 'NS-205 · Senior Frontend Engineer'],
                ['id' => 'ns-206', 'label' => 'NS-206 · Product Manager'],
            ],
            'footer' => 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function assessment(?string $id): ?array
    {
        if ($id === null || $id === '') {
            return null;
        }

        return collect(self::assessments()['items'])->firstWhere('id', $id);
    }
}
