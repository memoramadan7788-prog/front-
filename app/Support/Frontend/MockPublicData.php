<?php

namespace App\Support\Frontend;

/**
 * Isolated mock catalog for public UI (Batch 3A).
 * Not Eloquent. Not live data. Frontend presentation only.
 */
class MockPublicData
{
    /**
     * Full mock catalog including private demo roles (not for public listing).
     *
     * @return list<array<string, mixed>>
     */
    public static function jobs(): array
    {
        return [
            [
                'id' => 'ns-204',
                'title' => 'Senior Product Designer',
                'company' => 'Northstar Labs',
                'company_slug' => 'northstar-labs',
                'organization_id' => 'org-ns-001',
                'code' => 'NS-204',
                'discipline' => 'Product & Design',
                'category' => 'Product & Design',
                'work_model' => 'Hybrid',
                'work_mode' => 'hybrid',
                'location' => 'Austin, TX',
                'employment' => 'Full-time',
                'salary_min' => 140000,
                'salary_max' => 170000,
                'salary_label' => '$140k–$170k',
                'source' => 'Direct hire',
                'posted' => 'Posted this week',
                'status' => 'Open',
                'publication_status' => 'published',
                'visibility' => 'public',
                'private_apply_mode' => null,
                'openings_count' => 2,
                'deadline' => 'November 30, 2026',
                'skills' => [
                    ['label' => 'Figma', 'is_required' => true],
                    ['label' => 'Design systems', 'is_required' => true],
                    ['label' => 'User research', 'is_required' => false],
                ],
                'requirements_flags' => [
                    'cv_required' => true,
                    'ai_consent_required' => true,
                    'cover_note_required' => false,
                ],
                'employees_cannot_apply_public' => true,
                'keyword' => 'product designer design systems research',
                'summary' => 'Design tools that make complex work feel simple',
                'description' => "Northstar Labs builds collaborative software for growing operations teams. We're looking for a senior product designer who can turn complex workflows into useful, accessible experiences.\n\nYou'll partner with product and engineering on the core workspace, own discovery through delivery, and help evolve a thoughtful design system.",
                'responsibilities' => [
                    'Lead end-to-end design for high-impact workflow experiences.',
                    'Translate customer research into clear product opportunities.',
                    'Prototype, test, and refine solutions with cross-functional partners.',
                    'Improve patterns, documentation, and accessibility in our design system.',
                    'Communicate trade-offs and support implementation quality.',
                ],
                'requirements' => [
                    '6+ years of product design experience, including complex SaaS products.',
                    'A portfolio showing strong interaction design and systems thinking.',
                    'Practical research and usability testing experience.',
                    'Confident collaboration with product managers and engineers.',
                    'Proficiency with Figma and accessible interface design.',
                ],
                'benefits' => 'Medical, dental, and vision coverage; flexible paid time off; learning budget; retirement plan; and a hybrid rhythm with two Austin office days each week. Details are shared during the recruiter conversation.',
                'process' => 'Application review → Recruiter conversation → Portfolio and team interviews → Decision and offer. We\'ll share preparation details before every interview.',
                'posted_at' => 'October 8, 2026',
                'reports_to' => 'Daniel Brooks',
                'hiring_contact' => 'Olivia Bennett',
                'hiring_manager' => 'Daniel Brooks',
            ],
            [
                'id' => 'ms-108',
                'title' => 'Lead Product Designer',
                'company' => 'Meridian Studio',
                'company_slug' => 'meridian-studio',
                'code' => 'MS-108',
                'discipline' => 'Product & Design',
                'work_model' => 'Remote',
                'location' => 'United States',
                'employment' => 'Full-time',
                'salary_min' => 155000,
                'salary_max' => 180000,
                'salary_label' => '$155k–$180k',
                'source' => 'Agency-managed',
                'posted' => 'Posted this week',
                'status' => 'Open',
                'keyword' => 'lead product designer brand visual',
                'summary' => 'Shape product craft for digital experiences across brands.',
                'description' => 'Meridian Studio partners with growth-stage companies on product and brand systems. This lead role owns discovery to delivery for flagship client workstreams.',
                'responsibilities' => [
                    'Lead design for multi-product client engagements.',
                    'Mentor designers and elevate critique quality.',
                    'Partner with strategy and engineering leads.',
                ],
                'requirements' => [
                    '8+ years product design experience.',
                    'Portfolio with systems and leadership evidence.',
                    'Comfortable with agency-managed hiring workflows.',
                ],
                'benefits' => 'Remote-first benefits package with learning stipend and flexible PTO.',
                'process' => 'Screening → Portfolio review → Panel interview → Offer.',
                'posted_at' => 'October 9, 2026',
                'reports_to' => 'Creative Director',
                'hiring_contact' => 'Agency partner',
                'hiring_manager' => 'Studio lead',
            ],
            [
                'id' => 'ah-312',
                'title' => 'Senior UX Designer',
                'company' => 'Atlas Health',
                'company_slug' => 'atlas-health',
                'code' => 'AH-312',
                'discipline' => 'Product & Design',
                'work_model' => 'Hybrid',
                'location' => 'Austin, TX',
                'employment' => 'Full-time',
                'salary_min' => 135000,
                'salary_max' => 160000,
                'salary_label' => '$135k–$160k',
                'source' => 'Agency-managed',
                'posted' => 'Posted this week',
                'status' => 'Open',
                'keyword' => 'ux designer research healthcare',
                'summary' => 'Improve clinical and operational workflows with research-led UX.',
                'description' => 'Atlas Health is hiring a senior UX designer to improve clinician and operations experiences across hybrid care products.',
                'responsibilities' => [
                    'Plan and run usability research.',
                    'Design accessible flows for clinical tools.',
                    'Partner with product and compliance stakeholders.',
                ],
                'requirements' => [
                    '5+ years UX experience in complex domains.',
                    'Strong research and prototyping skills.',
                ],
                'benefits' => 'Hybrid Austin schedule with comprehensive health benefits.',
                'process' => 'Recruiter screen → Design exercise → Team interviews.',
                'posted_at' => 'October 7, 2026',
                'reports_to' => 'Head of Design',
                'hiring_contact' => 'Agency partner',
                'hiring_manager' => 'Head of Design',
            ],
            [
                'id' => 'lf-087',
                'title' => 'Design Systems Designer',
                'company' => 'Lumen Financial',
                'company_slug' => 'lumen-financial',
                'code' => 'LF-087',
                'discipline' => 'Product & Design',
                'work_model' => 'Remote',
                'location' => 'United States',
                'employment' => 'Full-time',
                'salary_min' => 145000,
                'salary_max' => 175000,
                'salary_label' => '$145k–$175k',
                'source' => 'Direct hire',
                'posted' => 'Posted this week',
                'status' => 'Open',
                'keyword' => 'design systems designer tokens components',
                'summary' => 'Scale a multi-product design system for financial workflows.',
                'description' => 'Lumen Financial needs a design systems designer to evolve tokens, components, and documentation across product lines.',
                'responsibilities' => [
                    'Own component proposals and governance.',
                    'Partner with engineering on implementation quality.',
                    'Document usage guidance and accessibility notes.',
                ],
                'requirements' => [
                    'Experience shipping design systems at scale.',
                    'Strong Figma and documentation craft.',
                ],
                'benefits' => 'Remote US role with equity-eligible package.',
                'process' => 'Screening → Systems critique → Cross-functional panel.',
                'posted_at' => 'October 6, 2026',
                'reports_to' => 'Design Systems Lead',
                'hiring_contact' => 'Talent partner',
                'hiring_manager' => 'Design Systems Lead',
            ],
            [
                'id' => 'ns-205',
                'title' => 'Senior Software Engineer',
                'company' => 'Northstar Labs',
                'company_slug' => 'northstar-labs',
                'code' => 'NS-205',
                'discipline' => 'Engineering & Data',
                'work_model' => 'Hybrid',
                'location' => 'Austin, TX',
                'employment' => 'Full-time',
                'salary_min' => 160000,
                'salary_max' => 190000,
                'salary_label' => '$160k–$190k',
                'source' => 'Direct hire',
                'posted' => 'Posted this week',
                'status' => 'Open',
                'keyword' => 'software engineer backend laravel php',
                'summary' => 'Build reliable collaborative software for operations teams.',
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
                'posted_at' => 'October 5, 2026',
                'reports_to' => 'Engineering Manager',
                'hiring_contact' => 'Olivia Bennett',
                'hiring_manager' => 'Engineering Manager',
            ],
            [
                'id' => 'ns-206',
                'title' => 'Product Manager',
                'company' => 'Northstar Labs',
                'company_slug' => 'northstar-labs',
                'code' => 'NS-206',
                'discipline' => 'Product & Design',
                'work_model' => 'Hybrid',
                'location' => 'Austin, TX',
                'employment' => 'Full-time',
                'salary_min' => 145000,
                'salary_max' => 175000,
                'salary_label' => '$145k–$175k',
                'source' => 'Direct hire',
                'posted' => 'Posted this week',
                'status' => 'Open',
                'keyword' => 'product manager roadmap discovery',
                'summary' => 'Own discovery and delivery for core workspace outcomes.',
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
                'posted_at' => 'October 4, 2026',
                'reports_to' => 'Head of Product',
                'hiring_contact' => 'Olivia Bennett',
                'hiring_manager' => 'Head of Product',
            ],
            [
                'id' => 'gc-041',
                'title' => 'Customer Success Manager',
                'company' => 'Grove & Co.',
                'company_slug' => 'grove-and-co',
                'code' => 'GC-041',
                'discipline' => 'People & Operations',
                'work_model' => 'On-site',
                'location' => 'Austin, TX',
                'employment' => 'Full-time',
                'salary_min' => 95000,
                'salary_max' => 120000,
                'salary_label' => '$95k–$120k',
                'source' => 'Direct hire',
                'posted' => 'Posted this week',
                'status' => 'Open',
                'keyword' => 'customer success operations account',
                'summary' => 'Help customers adopt collaborative operating rhythms.',
                'description' => 'Grove & Co. seeks a customer success manager for on-site partnership with Austin accounts.',
                'responsibilities' => [
                    'Own a book of accounts.',
                    'Drive adoption and renewals.',
                ],
                'requirements' => [
                    '3+ years customer success experience.',
                    'Comfortable with on-site collaboration.',
                ],
                'benefits' => 'On-site Austin benefits with wellness stipend.',
                'process' => 'Screening → Role play → Offer.',
                'posted_at' => 'October 3, 2026',
                'reports_to' => 'CS Lead',
                'hiring_contact' => 'People Ops',
                'hiring_manager' => 'CS Lead',
            ],
            [
                'id' => 'lm-220',
                'title' => 'Lifecycle Marketing Manager',
                'company' => 'Lumen Financial',
                'company_slug' => 'lumen-financial',
                'code' => 'LM-220',
                'discipline' => 'Marketing & Growth',
                'work_model' => 'Remote',
                'location' => 'United States',
                'employment' => 'Contract',
                'salary_min' => 110000,
                'salary_max' => 130000,
                'salary_label' => '$110k–$130k',
                'source' => 'Agency-managed',
                'posted' => 'Posted this week',
                'status' => 'Open',
                'keyword' => 'marketing growth lifecycle email',
                'summary' => 'Own lifecycle programs that convert and retain customers.',
                'description' => 'Contract lifecycle marketing role supporting Lumen Financial growth programs.',
                'responsibilities' => [
                    'Plan lifecycle campaigns.',
                    'Measure funnel performance.',
                ],
                'requirements' => [
                    'Experience with lifecycle tooling and analytics.',
                ],
                'benefits' => 'Contract engagement via agency partner.',
                'process' => 'Agency screen → Hiring manager interview.',
                'posted_at' => 'October 2, 2026',
                'reports_to' => 'Growth Lead',
                'hiring_contact' => 'Agency partner',
                'hiring_manager' => 'Growth Lead',
            ],
            // Private demo records — excluded from publicCatalog(); used by candidate/company eligibility demos.
            [
                'id' => 'ns-208',
                'title' => 'Internal Design Systems Lead',
                'company' => 'Northstar Labs',
                'company_slug' => 'northstar-labs',
                'organization_id' => 'org-ns-001',
                'code' => 'NS-208',
                'discipline' => 'Product & Design',
                'category' => 'Product & Design',
                'work_model' => 'Hybrid',
                'work_mode' => 'hybrid',
                'location' => 'Austin, TX',
                'employment' => 'Full-time',
                'salary_min' => 150000,
                'salary_max' => 180000,
                'salary_label' => '$150k–$180k',
                'source' => 'Direct hire',
                'posted' => 'Private · Employees',
                'status' => 'Open',
                'publication_status' => 'published',
                'visibility' => 'private',
                'private_apply_mode' => 'all_employees',
                'openings_count' => 1,
                'deadline' => 'December 15, 2026',
                'skills' => [
                    ['label' => 'Design systems', 'is_required' => true],
                    ['label' => 'Figma', 'is_required' => true],
                ],
                'requirements_flags' => [
                    'cv_required' => true,
                    'ai_consent_required' => true,
                    'cover_note_required' => false,
                ],
                'keyword' => 'internal design systems lead northstar private',
                'summary' => 'Lead internal design systems for approved Northstar employees.',
                'description' => 'Private employee-eligible role for Northstar Labs members only. Not listed on the public job board.',
                'responsibilities' => [
                    'Own the internal design system roadmap.',
                    'Coach product teams on component adoption.',
                ],
                'requirements' => [
                    'Approved Northstar Labs employee membership.',
                    '5+ years design systems experience.',
                ],
                'benefits' => 'Internal mobility benefits for Northstar employees.',
                'process' => 'Internal screen → Portfolio → Offer.',
                'posted_at' => 'October 18, 2026',
                'reports_to' => 'Daniel Brooks',
                'hiring_contact' => 'Olivia Bennett',
                'hiring_manager' => 'Daniel Brooks',
            ],
            [
                'id' => 'ns-209',
                'title' => 'Nominated Product Designer',
                'company' => 'Northstar Labs',
                'company_slug' => 'northstar-labs',
                'organization_id' => 'org-ns-001',
                'code' => 'NS-209',
                'discipline' => 'Product & Design',
                'category' => 'Product & Design',
                'work_model' => 'Hybrid',
                'work_mode' => 'hybrid',
                'location' => 'Austin, TX',
                'employment' => 'Full-time',
                'salary_min' => 130000,
                'salary_max' => 155000,
                'salary_label' => '$130k–$155k',
                'source' => 'Direct hire',
                'posted' => 'Private · Nominated',
                'status' => 'Open',
                'publication_status' => 'published',
                'visibility' => 'private',
                'private_apply_mode' => 'nominated_only',
                'openings_count' => 1,
                'deadline' => 'December 1, 2026',
                'nominations' => [
                    ['id' => 'nom-maya-chen', 'candidate_id' => 'maya-chen', 'name' => 'Maya Chen', 'email' => 'maya.chen@example.com', 'nominated_by' => 'Olivia Bennett', 'nominated_at' => 'October 19, 2026'],
                ],
                'skills' => [
                    ['label' => 'Product design', 'is_required' => true],
                    ['label' => 'Prototyping', 'is_required' => false],
                ],
                'requirements_flags' => [
                    'cv_required' => true,
                    'ai_consent_required' => true,
                    'cover_note_required' => true,
                ],
                'keyword' => 'nominated product designer northstar private',
                'summary' => 'Nomination-only private role for selected Northstar employees.',
                'description' => 'Private nomination-only role. Approved members still need an explicit nomination to apply.',
                'responsibilities' => [
                    'Deliver nominated product design initiatives.',
                ],
                'requirements' => [
                    'Approved membership plus active nomination.',
                ],
                'benefits' => 'Internal Northstar package.',
                'process' => 'Nomination → Apply → Interview.',
                'posted_at' => 'October 19, 2026',
                'reports_to' => 'Daniel Brooks',
                'hiring_contact' => 'Olivia Bennett',
                'hiring_manager' => 'Daniel Brooks',
            ],
        ];
    }

    /**
     * Public discovery catalog — private jobs excluded (presentation filter only).
     *
     * @return list<array<string, mixed>>
     */
    public static function publicCatalog(): array
    {
        return JobAccessPresentation::publicCatalog(self::jobs());
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function job(string $id): ?array
    {
        foreach (self::jobs() as $job) {
            if ($job['id'] === $id) {
                return JobAccessPresentation::normalizeJob($job);
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function company(string $slug): ?array
    {
        $companies = [
            'northstar-labs' => [
                'slug' => 'northstar-labs',
                'name' => 'Northstar Labs',
                'tagline' => 'Helping operations teams do their best work, together.',
                'industry' => 'Software & collaboration',
                'location' => 'Austin, TX · Hybrid & remote teams',
                'website' => 'northstarlabs.example',
                'overview' => "Northstar Labs creates collaborative software for operations teams. We bring research, design, and engineering together to turn fragmented processes into clear, useful workflows.\n\nOur team values thoughtful craft, practical experimentation, and respectful feedback. We work in small cross-functional groups with room to own meaningful problems.",
                'how_we_work' => [
                    ['title' => 'Thoughtful collaboration', 'body' => 'Clear decisions, shared context, and time for focused work.'],
                    ['title' => 'Room to grow', 'body' => 'Learning support, regular feedback, and ownership of outcomes.'],
                ],
                'benefits' => 'Benefits include health coverage, flexible paid time off, a learning budget, and retirement support. Hybrid roles spend two days a week in our Austin office.',
                'team' => [
                    ['name' => 'Olivia Bennett', 'role' => 'Recruiter / HR · Hiring contact'],
                    ['name' => 'Daniel Brooks', 'role' => 'Hiring manager · Product & Design'],
                ],
                'locations' => [
                    ['title' => 'Austin headquarters', 'body' => "Downtown Austin, Texas\nHybrid roles: two office days / week"],
                    ['title' => 'Remote collaboration', 'body' => 'US-based teammates collaborate across Central and Eastern time zones. See each role for eligibility.'],
                ],
            ],
        ];

        return $companies[$slug] ?? null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function jobsForCompany(string $slug): array
    {
        return array_values(array_filter(
            self::jobs(),
            fn (array $job): bool => ($job['company_slug'] ?? '') === $slug
        ));
    }
}
