<?php

namespace App\Support\Frontend;

/**
 * Isolated mock catalog for candidate workspace UI (Batch 4A).
 * Not Eloquent. Not live data. Frontend presentation only.
 */
class MockCandidateData
{
    /**
     * Figma 22 — Profile & resume presentation payload.
     *
     * @return array<string, mixed>
     */
    public static function profile(): array
    {
        $checklist = [
            ['label' => 'Resume uploaded', 'done' => true],
            ['label' => 'Work preferences saved', 'done' => true],
            ['label' => 'Add a featured portfolio case study', 'done' => false],
        ];

        return [
            'name' => 'Maya Chen',
            'initials' => 'MC',
            'email' => 'maya.chen@example.com',
            'headline' => 'Senior Product Designer',
            'location' => 'Austin, Texas',
            'location_short' => 'Austin, TX',
            'phone' => '+1 (512) 555-0142',
            'years_experience' => 8,
            'summary' => 'Product designer with 8 years’ experience shaping SaaS workflows, discovery, and interaction design. I partner with product and engineering to turn complex processes into clear, usable experiences.',
            'portfolio' => 'https://mayachen.design',
            'strength' => 85,
            'skills' => [
                ['label' => 'Figma', 'tone' => 'blue'],
                ['label' => 'Design systems', 'tone' => 'teal'],
                ['label' => 'User research', 'tone' => 'pending'],
                ['label' => 'Prototyping', 'tone' => 'success'],
            ],
            'resume' => [
                'filename' => 'Maya_Chen_Resume.pdf',
                'updated' => 'Oct 10, 2026',
                'size' => '248 KB',
            ],
            'checklist' => $checklist,
            'experience' => [
                [
                    'title' => 'Senior Product Designer',
                    'company' => 'Fieldwork',
                    'location' => 'Austin, TX',
                    'dates' => 'March 2022–Present',
                    'summary' => 'Leading end-to-end product design for collaborative operations tools — discovery, prototyping, and design-system evolution across multiple squads.',
                ],
                [
                    'title' => 'Product Designer',
                    'company' => 'Parcel Systems',
                    'location' => 'Austin, TX',
                    'dates' => 'June 2018–February 2022',
                    'summary' => 'Designed logistics and fulfillment workflows for operators and customers, with a focus on clarity, accessibility, and measurable outcomes.',
                ],
            ],
            'education' => [
                [
                    'degree' => 'BFA, Communication Design',
                    'school' => 'Texas State University',
                    'dates' => '2014–2018',
                ],
            ],
            'visibility' => [
                ['name' => 'employers_only', 'label' => 'Only employers I apply to can view my profile', 'checked' => true],
                ['name' => 'agency_access', 'label' => 'Allow agency recruiters to request access', 'checked' => true],
                ['name' => 'public_search', 'label' => 'Show my profile in public search', 'checked' => false],
            ],
            'visibility_note' => 'Your choices stay private. Employers only receive a snapshot of your profile when you apply or grant access.',
            'footer' => 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026',
        ];
    }

    /**
     * Figma 13 — Candidate dashboard presentation payload.
     *
     * @return array<string, mixed>
     */
    public static function dashboard(): array
    {
        $profile = self::profile();

        return [
            'greeting' => 'Good morning, Maya',
            'intro' => 'Here’s what’s next in your search. You’re making steady progress.',
            'stats' => [
                [
                    'label' => 'Active applications',
                    'value' => '4',
                    'meta' => '1 offer · 1 interview · 1 screening · 1 applied',
                ],
                [
                    'label' => 'Saved roles',
                    'value' => '3',
                    'meta' => '2 new updates this week',
                ],
                [
                    'label' => 'Profile strength',
                    'value' => $profile['strength'].'%',
                    'meta' => 'Add a case study to finish your profile',
                ],
            ],
            'interview' => [
                'title' => 'Portfolio interview',
                'company' => 'Northstar Labs',
                'role' => 'Senior Product Designer',
                'code' => 'NS-204',
                'status' => 'Confirmed · 45 minutes',
                'when' => 'Thursday, October 22',
                'time' => '10:00–10:45 AM · Central Time (UTC−05:00)',
                'interviewer' => 'Alex Morgan · Interviewer · Product Design',
                'format' => 'Video meeting · Link available 15 minutes before the interview.',
            ],
            'application' => [
                'company' => 'Northstar Labs',
                'title' => 'Senior Product Designer',
                'code' => 'NS-204',
                'stage' => 'Interview',
                'note' => 'Olivia Bennett confirmed your portfolio review. Prepare two projects that show your process and design decisions.',
                'pipeline' => [
                    ['label' => 'Applied', 'status' => 'completed'],
                    ['label' => 'Screening', 'status' => 'completed'],
                    ['label' => 'Interview', 'status' => 'current'],
                    ['label' => 'Offer', 'status' => 'pending'],
                    ['label' => 'Hired', 'status' => 'pending'],
                ],
            ],
            'recommended' => [
                'id' => 'ah-312',
                'title' => 'Senior UX Designer',
                'company' => 'Atlas Health',
                'code' => 'AH-312',
                'meta' => 'Hybrid · Austin, TX · Full-time · $135k–$160k',
                'source' => 'Agency-managed',
                'reason' => 'Matches your Austin hybrid preference and research experience.',
            ],
            'profile_progress' => [
                'percent' => $profile['strength'],
                'items' => $profile['checklist'],
            ],
            'next_actions' => [
                [
                    'title' => 'Review Meridian offer',
                    'meta' => 'Response due Oct 28 · Separate employer',
                ],
                [
                    'title' => 'Prepare your portfolio',
                    'meta' => 'Northstar interview · Oct 22, 10:00 AM',
                ],
                [
                    'title' => 'New message from Olivia',
                    'meta' => 'Interview agenda and meeting details',
                ],
            ],
            'footer' => 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026',
        ];
    }

    /**
     * Default saved role IDs for Frame 15 (Figma shortlist).
     *
     * @return list<string>
     */
    public static function defaultSavedJobIds(): array
    {
        return ['ah-312', 'lf-087', 'gc-041'];
    }

    /**
     * Frame 14 — preference summary card.
     *
     * @return array<string, mixed>
     */
    public static function discoverPreferences(): array
    {
        return [
            'work_models' => [
                'Hybrid · Austin',
                'Remote · United States',
                'Full-time',
            ],
            'salary' => '$140k+ USD',
            'skills' => ['Design systems', 'User research', 'Figma'],
            'note' => 'Recommendations refresh when you update profile preferences.',
            'search_default' => 'Senior product design',
            'location_default' => 'austin_remote',
            'sort_default' => 'recommended',
            'recommended_count_label' => '12 recommended roles',
        ];
    }

    /**
     * Frame 15 — saved-search alert widget.
     *
     * @return array<string, mixed>
     */
    public static function savedSearchAlert(): array
    {
        return [
            'status' => 'Weekly email · Enabled',
            'lines' => [
                'Senior design · Austin hybrid + US remote',
                'Full-time · $140k+ · Product & Design',
            ],
            'updates_title' => 'Two roles have updates',
            'updates_body' => 'Atlas Health added interview details. Lumen Financial updated its salary range on October 19.',
        ];
    }

    /**
     * Candidate discover / saved catalog aligned to Figma 14–15.
     *
     * @return list<array<string, mixed>>
     */
    public static function jobs(): array
    {
        $saved = self::defaultSavedJobIds();

        $jobs = [
            [
                'id' => 'gc-041',
                'title' => 'Staff Product Designer',
                'company' => 'Grove & Co.',
                'company_slug' => 'grove-and-co',
                'code' => 'GC-041',
                'discipline' => 'Product & Design',
                'work_model' => 'Hybrid',
                'location' => 'Austin, TX',
                'location_short' => 'Austin',
                'employment' => 'Full-time',
                'salary_min' => 165000,
                'salary_max' => 195000,
                'salary_label' => '$165k–$195k',
                'source' => 'Direct hire',
                'experience_level' => 'Staff',
                'keyword' => 'staff product designer grove saas',
                'match_reason' => 'Why it fits: Austin hybrid, complex SaaS workflows, senior design ownership. Saved October 14; not yet applied.',
                'primary_cta' => 'review_apply',
                'primary_cta_label' => 'Review & apply →',
                'application_state' => null,
                'saved_on' => 'Oct 14',
                'saved_status' => 'Open',
                'saved_action' => 'review_role',
                'saved_action_label' => 'Review role',
                'recommended_rank' => 1,
                'posted_rank' => 10,
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
                'location_short' => 'Austin',
                'employment' => 'Full-time',
                'salary_min' => 135000,
                'salary_max' => 160000,
                'salary_label' => '$135k–$160k',
                'source' => 'Agency-managed',
                'experience_level' => 'Senior',
                'keyword' => 'senior ux designer atlas research',
                'match_reason' => 'Application in Screening — Matches your Austin hybrid preference and research experience.',
                'primary_cta' => 'view_application',
                'primary_cta_label' => 'View application →',
                'application_state' => 'screening',
                'saved_on' => 'Oct 18',
                'saved_status' => 'Applied',
                'saved_action' => 'view_application',
                'saved_action_label' => 'View application',
                'recommended_rank' => 2,
                'posted_rank' => 9,
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
                'location_short' => 'United States',
                'employment' => 'Full-time',
                'salary_min' => 145000,
                'salary_max' => 175000,
                'salary_label' => '$145k–$175k',
                'source' => 'Direct hire',
                'experience_level' => 'Senior',
                'keyword' => 'design systems designer lumen tokens',
                'match_reason' => 'Already Applied — Matches your systems experience, remote preference, and salary range.',
                'primary_cta' => 'view_application',
                'primary_cta_label' => 'View application →',
                'application_state' => 'applied',
                'saved_on' => 'Oct 17',
                'saved_status' => 'Applied',
                'saved_action' => 'view_application',
                'saved_action_label' => 'View application',
                'recommended_rank' => 3,
                'posted_rank' => 8,
            ],
            [
                'id' => 'ns-204',
                'title' => 'Senior Product Designer',
                'company' => 'Northstar Labs',
                'company_slug' => 'northstar-labs',
                'code' => 'NS-204',
                'discipline' => 'Product & Design',
                'work_model' => 'Hybrid',
                'location' => 'Austin, TX',
                'location_short' => 'Austin',
                'employment' => 'Full-time',
                'salary_min' => 140000,
                'salary_max' => 170000,
                'salary_label' => '$140k–$170k',
                'source' => 'Direct hire',
                'experience_level' => 'Senior',
                'keyword' => 'senior product designer northstar',
                'match_reason' => 'Matches your Austin hybrid preference and product design focus.',
                'primary_cta' => 'review_apply',
                'primary_cta_label' => 'Review & apply →',
                'application_state' => 'interview',
                'saved_on' => 'Oct 12',
                'saved_status' => 'Applied',
                'saved_action' => 'view_application',
                'saved_action_label' => 'View application',
                'recommended_rank' => 4,
                'posted_rank' => 7,
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
                'location_short' => 'United States',
                'employment' => 'Full-time',
                'salary_min' => 155000,
                'salary_max' => 180000,
                'salary_label' => '$155k–$180k',
                'source' => 'Agency-managed',
                'experience_level' => 'Lead',
                'keyword' => 'lead product designer meridian',
                'match_reason' => 'Aligned with lead product design and remote US preferences.',
                'primary_cta' => 'review_apply',
                'primary_cta_label' => 'Review & apply →',
                'application_state' => null,
                'saved_on' => 'Oct 10',
                'saved_status' => 'Open',
                'saved_action' => 'review_role',
                'saved_action_label' => 'Review role',
                'recommended_rank' => 5,
                'posted_rank' => 6,
            ],
        ];

        return array_map(function (array $job) use ($saved): array {
            return array_merge($job, [
                'saved' => in_array($job['id'], $saved, true),
            ]);
        }, $jobs);
    }

    /**
     * Resolve a catalog job by id, or null.
     *
     * @return array<string, mixed>|null
     */
    public static function job(string $id): ?array
    {
        foreach (self::jobs() as $job) {
            if ($job['id'] === $id) {
                return $job;
            }
        }

        return null;
    }

    /**
     * Pipeline stage labels shared across application UIs.
     *
     * @return list<string>
     */
    public static function applicationPipelineLabels(): array
    {
        return ['Applied', 'Screening', 'Interview', 'Offer', 'Hired'];
    }

    /**
     * Build a progress pipeline with completed / current / pending statuses.
     *
     * @return list<array{label: string, status: string}>
     */
    public static function pipelineForStage(string $currentStage): array
    {
        $labels = self::applicationPipelineLabels();
        $currentIndex = array_search($currentStage, $labels, true);
        if ($currentIndex === false) {
            $currentIndex = 0;
        }

        return array_map(function (string $label, int $index) use ($currentIndex): array {
            $status = 'pending';
            if ($index < $currentIndex) {
                $status = 'completed';
            } elseif ($index === $currentIndex) {
                $status = 'current';
            }

            return ['label' => $label, 'status' => $status];
        }, $labels, array_keys($labels));
    }

    /**
     * Frame 16–19 — Candidate applications catalog (frontend mock only).
     *
     * @return list<array<string, mixed>>
     */
    public static function applications(): array
    {
        $profile = self::profile();

        return [
            [
                'id' => 'hb-ns204-mc-1012',
                'job_id' => 'ns-204',
                'reference' => 'HB-NS204-MC-1012',
                'title' => 'Senior Product Designer',
                'company' => 'Northstar Labs',
                'code' => 'NS-204',
                'source' => 'Direct hire',
                'stage' => 'Interview',
                'stage_key' => 'interview',
                'archived' => false,
                'featured' => true,
                'applied_on' => 'Oct 12',
                'applied_on_long' => 'October 12, 2026',
                'applied_at' => 'October 12, 2026, 9:24 AM Central',
                'last_activity' => 'Portfolio interview confirmed Oct 19',
                'last_activity_sort' => '2026-10-20',
                'next_step' => 'Portfolio interview · Oct 22, 10:00–10:45 AM Central · Alex Morgan',
                'discipline' => 'Product & Design',
                'work_model' => 'Hybrid',
                'location' => 'Austin, TX',
                'employment' => 'Full-time',
                'salary_label' => '$140k–$170k USD',
                'hiring_contact' => 'Olivia Bennett',
                'team' => 'Product & Design',
                'pipeline' => self::pipelineForStage('Interview'),
                'interview' => [
                    'title' => 'Portfolio interview',
                    'status' => 'Confirmed · 45 minutes',
                    'when' => 'Thursday, October 22',
                    'time' => '10:00–10:45 AM · Central Time (UTC−05:00)',
                    'interviewer' => 'Alex Morgan · Interviewer · Product Design',
                    'format' => 'Video meeting · Link available 15 minutes before the interview.',
                ],
                'activity' => [
                    ['when' => 'Oct 20 · 9:12 AM', 'text' => 'Olivia shared the portfolio interview agenda.'],
                    ['when' => 'Oct 19 · 2:30 PM', 'text' => 'Portfolio interview confirmed for October 22.'],
                    ['when' => 'Oct 15 · 11:00 AM', 'text' => 'Recruiter screening completed · Moved to Interview.'],
                    ['when' => 'Oct 12 · 9:24 AM', 'text' => 'Application and materials received.'],
                ],
                'contacts' => [
                    ['name' => 'Olivia Bennett', 'role' => 'Recruiter / HR · Primary contact'],
                    ['name' => 'Daniel Brooks', 'role' => 'Hiring manager'],
                ],
                'materials' => [
                    ['label' => $profile['resume']['filename'], 'meta' => $profile['resume']['size']],
                    ['label' => 'mayachen.design', 'meta' => 'Portfolio'],
                    ['label' => 'Screening answers', 'meta' => 'Oct 12'],
                ],
                'screening_summary' => 'Hybrid availability, motivation, salary expectations',
                'consent_summary' => 'Application processing approved · Talent pool opt-in not selected',
            ],
            [
                'id' => 'hb-ms108-mc-1019',
                'job_id' => 'ms-108',
                'reference' => 'HB-MS108-MC-1019',
                'title' => 'Lead Product Designer',
                'company' => 'Meridian Studio',
                'code' => 'MS-108',
                'source' => 'Agency-managed',
                'stage' => 'Offer',
                'stage_key' => 'offer',
                'archived' => false,
                'featured' => false,
                'applied_on' => 'Oct 8',
                'applied_on_long' => 'October 8, 2026',
                'applied_at' => 'October 8, 2026, 2:10 PM Central',
                'last_activity' => 'Offer received Oct 19',
                'last_activity_sort' => '2026-10-19',
                'next_step' => 'Review by Oct 28',
                'discipline' => 'Product & Design',
                'work_model' => 'Remote',
                'location' => 'United States',
                'employment' => 'Full-time',
                'salary_label' => '$155k–$180k USD',
                'hiring_contact' => 'Jamie Rivera',
                'team' => 'Product Design',
                'pipeline' => self::pipelineForStage('Offer'),
                'interview' => null,
                'activity' => [
                    ['when' => 'Oct 19 · 4:00 PM', 'text' => 'Offer documents shared for review.'],
                    ['when' => 'Oct 14 · 11:20 AM', 'text' => 'Final interview completed.'],
                    ['when' => 'Oct 8 · 2:10 PM', 'text' => 'Application received.'],
                ],
                'contacts' => [
                    ['name' => 'Jamie Rivera', 'role' => 'Assigned agency recruiter'],
                ],
                'materials' => [
                    ['label' => $profile['resume']['filename'], 'meta' => $profile['resume']['size']],
                    ['label' => 'mayachen.design', 'meta' => 'Portfolio'],
                ],
                'screening_summary' => 'Remote preference, leadership experience, salary expectations',
                'consent_summary' => 'Application processing approved',
            ],
            [
                'id' => 'hb-ah312-mc-1018',
                'job_id' => 'ah-312',
                'reference' => 'HB-AH312-MC-1018',
                'title' => 'Senior UX Designer',
                'company' => 'Atlas Health',
                'code' => 'AH-312',
                'source' => 'Agency-managed',
                'stage' => 'Screening',
                'stage_key' => 'screening',
                'archived' => false,
                'featured' => false,
                'applied_on' => 'Oct 18',
                'applied_on_long' => 'October 18, 2026',
                'applied_at' => 'October 18, 2026, 3:45 PM Central',
                'last_activity' => 'Materials reviewed Oct 18',
                'last_activity_sort' => '2026-10-18',
                'next_step' => 'Recruiter will contact you',
                'discipline' => 'Product & Design',
                'work_model' => 'Hybrid',
                'location' => 'Austin, TX',
                'employment' => 'Full-time',
                'salary_label' => '$135k–$160k USD',
                'hiring_contact' => 'Sam Wilson',
                'team' => 'UX Research',
                'pipeline' => self::pipelineForStage('Screening'),
                'interview' => null,
                'activity' => [
                    ['when' => 'Oct 18 · 4:10 PM', 'text' => 'Materials reviewed by agency recruiter.'],
                    ['when' => 'Oct 18 · 3:45 PM', 'text' => 'Application and materials received.'],
                ],
                'contacts' => [
                    ['name' => 'Sam Wilson', 'role' => 'Agency recruiter'],
                ],
                'materials' => [
                    ['label' => $profile['resume']['filename'], 'meta' => $profile['resume']['size']],
                    ['label' => 'mayachen.design', 'meta' => 'Portfolio'],
                    ['label' => 'Screening answers', 'meta' => 'Oct 18'],
                ],
                'screening_summary' => 'Hybrid availability, research focus, salary expectations',
                'consent_summary' => 'Application processing approved',
            ],
            [
                'id' => 'hb-lf087-mc-1017',
                'job_id' => 'lf-087',
                'reference' => 'HB-LF087-MC-1017',
                'title' => 'Design Systems Designer',
                'company' => 'Lumen Financial',
                'code' => 'LF-087',
                'source' => 'Direct hire',
                'stage' => 'Applied',
                'stage_key' => 'applied',
                'archived' => false,
                'featured' => false,
                'applied_on' => 'Oct 17',
                'applied_on_long' => 'October 17, 2026',
                'applied_at' => 'October 17, 2026, 11:05 AM Central',
                'last_activity' => 'Submitted Oct 17',
                'last_activity_sort' => '2026-10-17',
                'next_step' => 'Awaiting application review',
                'discipline' => 'Product & Design',
                'work_model' => 'Remote',
                'location' => 'United States',
                'employment' => 'Full-time',
                'salary_label' => '$145k–$175k USD',
                'hiring_contact' => 'Priya Nair',
                'team' => 'Design Systems',
                'pipeline' => self::pipelineForStage('Applied'),
                'interview' => null,
                'activity' => [
                    ['when' => 'Oct 17 · 11:05 AM', 'text' => 'Application and materials received.'],
                ],
                'contacts' => [
                    ['name' => 'Priya Nair', 'role' => 'Hiring manager'],
                ],
                'materials' => [
                    ['label' => $profile['resume']['filename'], 'meta' => $profile['resume']['size']],
                    ['label' => 'mayachen.design', 'meta' => 'Portfolio'],
                ],
                'screening_summary' => 'Systems experience, remote preference, salary expectations',
                'consent_summary' => 'Application processing approved',
            ],
            [
                'id' => 'hb-gc039-mc-1009',
                'job_id' => 'gc-039',
                'reference' => 'HB-GC039-MC-1009',
                'title' => 'Product Designer',
                'company' => 'Grove & Co.',
                'code' => 'GC-039',
                'source' => 'Direct hire',
                'stage' => 'Withdrawn',
                'stage_key' => 'withdrawn',
                'archived' => true,
                'featured' => false,
                'applied_on' => 'Oct 2',
                'applied_on_long' => 'October 2, 2026',
                'applied_at' => 'October 2, 2026, 10:00 AM Central',
                'last_activity' => 'Withdrawn Oct 9',
                'last_activity_sort' => '2026-10-09',
                'next_step' => 'No active next steps',
                'archived_note' => 'Withdrawn by you on October 9 · Location no longer matched your preferences. No active next steps.',
                'discipline' => 'Product & Design',
                'work_model' => 'On-site',
                'location' => 'Dallas, TX',
                'employment' => 'Full-time',
                'salary_label' => '$120k–$145k USD',
                'hiring_contact' => 'Taylor Kim',
                'team' => 'Product',
                'pipeline' => self::pipelineForStage('Applied'),
                'interview' => null,
                'activity' => [
                    ['when' => 'Oct 9 · 9:00 AM', 'text' => 'Application withdrawn by candidate.'],
                    ['when' => 'Oct 2 · 10:00 AM', 'text' => 'Application and materials received.'],
                ],
                'contacts' => [
                    ['name' => 'Taylor Kim', 'role' => 'Recruiter'],
                ],
                'materials' => [
                    ['label' => $profile['resume']['filename'], 'meta' => $profile['resume']['size']],
                ],
                'screening_summary' => '—',
                'consent_summary' => 'Application processing approved',
            ],
        ];
    }

    /**
     * Resolve an application by id, or null.
     *
     * @return array<string, mixed>|null
     */
    public static function application(string $id): ?array
    {
        foreach (self::applications() as $application) {
            if ($application['id'] === $id || $application['job_id'] === $id || $application['code'] === $id) {
                return $application;
            }
        }

        return null;
    }

    /**
     * Frame 16 — Apply flow payload for a job (frontend mock only).
     *
     * @return array<string, mixed>
     */
    public static function applyFlow(?string $jobId = null): array
    {
        $profile = self::profile();
        $jobId = $jobId ?: 'ns-204';
        $job = self::job($jobId) ?? self::job('ns-204');

        return [
            'job' => $job,
            'profile' => [
                'name' => $profile['name'],
                'email' => $profile['email'],
                'portfolio' => $profile['portfolio'],
                'resume' => $profile['resume'],
                'headline' => $profile['headline'],
                'location_short' => $profile['location_short'],
            ],
            'journey_banner' => 'Journey-state example: Earlier journey state · October 12, 2026. This application form precedes Maya’s later interview-stage application.',
            'steps' => [
                ['label' => 'Your profile'],
                ['label' => 'Screening questions'],
                ['label' => 'Review & submit'],
            ],
            'current_step' => 2,
            'hybrid_options' => [
                'Yes · Two office days per week',
                'Yes · Three office days per week',
                'Flexible · Discuss with hiring team',
                'No · Remote only',
            ],
            'defaults' => [
                'hybrid' => 'Yes · Two office days per week',
                'interest' => 'I’m drawn to operational tools and SaaS products where design systems and discovery shape complex workflows. Northstar’s product focus and hybrid Austin setup match how I like to collaborate.',
                'salary' => '$155,000 – $165,000',
                'consent' => true,
                'talent_pool' => false,
            ],
            'role_summary' => [
                'source' => $job['source'] ?? 'Direct hire',
                'title' => $job['title'] ?? 'Senior Product Designer',
                'company' => $job['company'] ?? 'Northstar Labs',
                'code' => $job['code'] ?? 'NS-204',
                'meta' => ($job['location_short'] ?? 'Austin').' '.strtolower($job['work_model'] ?? 'hybrid').' · '.($job['employment'] ?? 'Full-time'),
                'salary' => ($job['salary_label'] ?? '$140k–$170k').' USD',
                'hiring_contact' => 'Olivia Bennett',
                'team' => $job['discipline'] ?? 'Product & Design',
            ],
            'before_submit' => 'Your resume and portfolio will be shared with the authorized Northstar hiring team for this role. Other applications and private preferences stay confidential.',
            'footer' => $profile['footer'],
        ];
    }

    /**
     * Frame 17 — Frontend confirmation preview payload (not a real submission).
     *
     * @return array<string, mixed>
     */
    public static function submittedPreview(?string $jobId = null): array
    {
        $profile = self::profile();
        $jobId = $jobId ?: 'ns-204';
        $job = self::job($jobId) ?? self::job('ns-204');
        $existing = self::application($jobId);
        $code = $job['code'] ?? 'NS-204';
        $reference = $existing['reference'] ?? ('HB-'.strtoupper(str_replace('-', '', $jobId)).'-MC-PREVIEW');
        $company = $job['company'] ?? 'Northstar Labs';
        $title = $job['title'] ?? 'Senior Product Designer';
        $isNorthstarDemo = ($job['id'] ?? '') === 'ns-204';

        return [
            'job' => $job,
            'reference' => $reference,
            'title' => $title,
            'code' => $code,
            'company' => $company,
            'discipline' => $job['discipline'] ?? 'Product & Design',
            'submitted_at' => 'October 12, 2026, 9:24 AM Central',
            'journey_banner' => $isNorthstarDemo
                ? 'Journey-state example. Submission-stage example · This is the earlier confirmation for the same application shown later at Interview. It is not a second submission.'
                : 'Frontend confirmation preview for '.$company.' · '.$code.'. This is a demonstration state only.',
            'preview_notice' => 'Frontend confirmation preview only — no application was sent to a server. Reference and timestamps are mock demonstration data.',
            'email' => $profile['email'],
            'pipeline' => self::pipelineForStage('Applied'),
            'summary' => [
                'profile' => $profile['name'].' · '.$profile['location_short'].' · '.$profile['headline'],
                'resume' => $profile['resume']['filename'].' · '.$profile['resume']['size'],
                'portfolio' => 'mayachen.design',
                'screening' => 'Hybrid availability, motivation, salary expectations',
                'consent' => 'Application processing approved · Talent pool opt-in not selected',
            ],
            'next_steps' => [
                [
                    'number' => '01',
                    'title' => 'Application review',
                    'body' => ($existing['hiring_contact'] ?? 'The hiring team').' will review your materials against the '.$title.' brief.',
                ],
                [
                    'number' => '02',
                    'title' => 'Recruiter conversation',
                    'body' => 'If aligned, '.$company.' may arrange a screening call to discuss fit and timeline.',
                ],
            ],
            'application_id' => $existing['id'] ?? null,
            'footer' => $profile['footer'],
        ];
    }

    /**
     * Frame 20 — Candidate interviews workspace (frontend mock only).
     *
     * @return array<string, mixed>
     */
    public static function interviews(): array
    {
        $upcoming = [
            [
                'id' => 'int-ns204-portfolio',
                'application_id' => 'hb-ns204-mc-1012',
                'job_id' => 'ns-204',
                'title' => 'Portfolio interview',
                'company' => 'Northstar Labs',
                'role' => 'Senior Product Designer',
                'code' => 'NS-204',
                'status' => 'Confirmed · 45 minutes',
                'status_key' => 'confirmed',
                'when' => 'Thursday, October 22',
                'time' => '10:00–10:45 AM · Central Time (UTC−05:00)',
                'time_short' => '10:00–10:45',
                'day_key' => '2026-10-22',
                'weekday' => 'Thu',
                'day_num' => '22',
                'interviewer' => 'Alex Morgan · Interviewer · Product Design',
                'interviewer_short' => 'Alex Morgan',
                'format' => 'Video meeting · Link available 15 minutes before the interview.',
                'type' => 'Remote',
                'duration' => '45 minutes',
                'calendar_label' => 'Maya Chen · Portfolio · NS-204',
                'scope' => 'upcoming',
                'prep' => [
                    ['label' => 'Review the role description', 'done' => true],
                    ['label' => 'Choose two portfolio projects', 'done' => false],
                    ['label' => 'Test camera and microphone', 'done' => false],
                    ['label' => 'Prepare your questions', 'done' => false],
                ],
                'guide_note' => 'Frontend checklist only — interview guide opening is a presentation preview.',
            ],
        ];

        $past = [
            [
                'id' => 'int-ns204-screening',
                'application_id' => 'hb-ns204-mc-1012',
                'job_id' => 'ns-204',
                'title' => 'Recruiter screening',
                'company' => 'Northstar Labs',
                'role' => 'Senior Product Designer',
                'code' => 'NS-204',
                'status' => 'Completed',
                'status_key' => 'completed',
                'when' => 'October 15',
                'time' => '11:00–11:30 AM Central · Olivia Bennett · Completed',
                'time_short' => '11:00–11:30',
                'day_key' => '2026-10-15',
                'interviewer' => 'Olivia Bennett',
                'format' => 'Video meeting',
                'type' => 'Remote',
                'duration' => '30 minutes',
                'scope' => 'past',
                'note' => 'Next stage: portfolio interview. No action required for the completed screening.',
                'prep' => [],
            ],
        ];

        return [
            'week_label' => 'October 19–23, 2026',
            'timezone' => 'Week view · Central Time (UTC−05:00)',
            'days' => [
                ['key' => '2026-10-19', 'label' => 'Mon 19', 'slots' => [['time' => '9:00 AM', 'label' => 'Available'], ['time' => '2:00 PM', 'label' => 'Available']]],
                ['key' => '2026-10-20', 'label' => 'Tue 20', 'slots' => [['time' => '9:00 AM', 'label' => 'Available'], ['time' => '2:00 PM', 'label' => 'Available']]],
                ['key' => '2026-10-21', 'label' => 'Wed 21', 'slots' => [['time' => '9:00 AM', 'label' => 'Available'], ['time' => '2:00 PM', 'label' => 'Available']]],
                ['key' => '2026-10-22', 'label' => 'Thu 22', 'highlight' => true, 'slots' => [['time' => '10:00–10:45', 'label' => 'Maya Chen · Portfolio · NS-204', 'interview_id' => 'int-ns204-portfolio']]],
                ['key' => '2026-10-23', 'label' => 'Fri 23', 'slots' => [['time' => '9:00 AM', 'label' => 'Available'], ['time' => '2:00 PM', 'label' => 'Available']]],
            ],
            'upcoming' => $upcoming,
            'past' => $past,
            'selected_id' => 'int-ns204-portfolio',
            'footer' => self::profile()['footer'],
        ];
    }

    /**
     * Resolve a mock interview by id.
     *
     * @return array<string, mixed>|null
     */
    public static function interview(string $id): ?array
    {
        $data = self::interviews();
        foreach (array_merge($data['upcoming'], $data['past']) as $interview) {
            if ($interview['id'] === $id) {
                return $interview;
            }
        }

        return null;
    }

    /**
     * Frame 21 — Candidate messages inbox (frontend mock only).
     *
     * @return array<string, mixed>
     */
    public static function messages(): array
    {
        return [
            'threads' => [
                [
                    'id' => 'msg-olivia-ns204',
                    'contact' => 'Olivia Bennett',
                    'company_short' => 'Northstar',
                    'company' => 'Northstar Labs',
                    'role' => 'Senior Product Designer',
                    'code' => 'NS-204',
                    'application_id' => 'hb-ns204-mc-1012',
                    'time' => '9:12 AM',
                    'unread' => 1,
                    'selected' => true,
                    'snippet' => 'Portfolio interview details',
                    'stage' => 'Interview',
                    'source' => 'Direct hire',
                    'event' => 'Oct 22 · 10:00–10:45 AM Central · Alex Morgan',
                    'date_divider' => 'Tuesday, October 20, 2026 · Central Time',
                    'messages' => [
                        [
                            'id' => 'm1',
                            'from' => 'them',
                            'author' => 'Olivia Bennett',
                            'role' => 'Recruiter / HR',
                            'time' => '9:12 AM',
                            'body' => 'Hi Maya — confirming your portfolio interview for October 22, 10:00–10:45 AM Central with Alex Morgan. Please prepare two projects that show problem framing and design decisions. I’ve attached a short guide.',
                            'attachment' => [
                                'name' => 'Northstar_Portfolio_Guide.pdf',
                                'size' => '184 KB',
                            ],
                        ],
                        [
                            'id' => 'm2',
                            'from' => 'you',
                            'author' => 'Maya Chen',
                            'role' => 'You',
                            'time' => '9:26 AM',
                            'body' => 'Thanks, Olivia. The time works well. I’ll prepare a workflow redesign and a systems case study that highlight framing and decisions.',
                            'status' => 'Delivered · 9:26 AM',
                        ],
                    ],
                ],
                [
                    'id' => 'msg-jamie-ms108',
                    'contact' => 'Jamie Rivera',
                    'company_short' => 'Meridian',
                    'company' => 'Meridian Studio',
                    'role' => 'Lead Product Designer',
                    'code' => 'MS-108',
                    'application_id' => 'hb-ms108-mc-1019',
                    'time' => 'Yesterday',
                    'unread' => 0,
                    'selected' => false,
                    'snippet' => 'Your offer documents are ready',
                    'stage' => 'Offer',
                    'source' => 'Agency-managed',
                    'event' => 'Review offer by Oct 28',
                    'date_divider' => 'Monday, October 19, 2026 · Central Time',
                    'messages' => [
                        [
                            'id' => 'm1',
                            'from' => 'them',
                            'author' => 'Jamie Rivera',
                            'role' => 'Agency recruiter',
                            'time' => '4:05 PM',
                            'body' => 'Maya — your Meridian offer documents are ready for review. Reply here if you have questions before October 28.',
                            'attachment' => null,
                        ],
                    ],
                ],
                [
                    'id' => 'msg-sam-ah312',
                    'contact' => 'Sam Wilson',
                    'company_short' => 'Atlas Health',
                    'company' => 'Atlas Health',
                    'role' => 'Senior UX Designer',
                    'code' => 'AH-312',
                    'application_id' => 'hb-ah312-mc-1018',
                    'time' => 'Oct 18',
                    'unread' => 0,
                    'selected' => false,
                    'snippet' => 'Thanks for your application',
                    'stage' => 'Screening',
                    'source' => 'Agency-managed',
                    'event' => 'Recruiter will contact you',
                    'date_divider' => 'Saturday, October 18, 2026 · Central Time',
                    'messages' => [
                        [
                            'id' => 'm1',
                            'from' => 'them',
                            'author' => 'Sam Wilson',
                            'role' => 'Agency recruiter',
                            'time' => '3:50 PM',
                            'body' => 'Thanks for your application to Atlas Health. We’ve reviewed your materials and will be in touch about next steps.',
                            'attachment' => null,
                        ],
                    ],
                ],
            ],
            'footer' => self::profile()['footer'],
        ];
    }

    /**
     * Workflow/ERD-derived candidate assessments (no dedicated Figma frame).
     * Screens map to existing exam stubs: overview, intro, take, result.
     *
     * @return list<array<string, mixed>>
     */
    public static function assessments(): array
    {
        return [
            [
                'id' => 'exam-ns204-systems',
                'application_id' => 'hb-ns204-mc-1012',
                'job_id' => 'ns-204',
                'title' => 'Design systems practical',
                'company' => 'Northstar Labs',
                'role' => 'Senior Product Designer',
                'code' => 'NS-204',
                'status' => 'Ready to start',
                'status_key' => 'ready',
                'time_limit_min' => 45,
                'attempt_limit' => 2,
                'attempt_no' => 1,
                'passing_score' => 70,
                'questions_count' => 4,
                'due' => 'Complete before Oct 22 interview',
                'instructions' => [
                    'You will answer four short questions about design systems and workflow prioritization.',
                    'Timer starts when you begin. Leaving the page does not pause the frontend timer preview.',
                    'Camera consent is recorded as a local UI acknowledgment only — no webcam capture runs in this preview.',
                    'Submit opens a results preview. Nothing is graded or stored on a server.',
                ],
                'questions' => [
                    [
                        'id' => 'q1',
                        'prompt' => 'When introducing a new component to a shared design system, what should you validate first?',
                        'choices' => [
                            'Visual polish only',
                            'Usage patterns, accessibility, and token alignment',
                            'Marketing screenshots',
                            'Font licensing alone',
                        ],
                        'answer' => 1,
                    ],
                    [
                        'id' => 'q2',
                        'prompt' => 'A product squad needs a temporary one-off pattern. Best next step?',
                        'choices' => [
                            'Add it to the core system immediately',
                            'Prototype locally, document constraints, then propose promotion criteria',
                            'Block the squad until a system owner rebuilds it',
                            'Copy from a competitor without review',
                        ],
                        'answer' => 1,
                    ],
                    [
                        'id' => 'q3',
                        'prompt' => 'Which signal best indicates a workflow is ready for design-system extraction?',
                        'choices' => [
                            'It appeared once in a pitch deck',
                            'Repeated use across squads with stable interaction rules',
                            'An engineer prefers a custom CSS file',
                            'The brand team likes the color',
                        ],
                        'answer' => 1,
                    ],
                    [
                        'id' => 'q4',
                        'type' => 'short',
                        'prompt' => 'In 2–3 sentences, describe how you would prioritize accessibility fixes versus new feature work on an operations SaaS surface.',
                        'choices' => [],
                        'answer' => null,
                    ],
                ],
            ],
            [
                'id' => 'exam-ah312-research',
                'application_id' => 'hb-ah312-mc-1018',
                'job_id' => 'ah-312',
                'title' => 'UX research scenario',
                'company' => 'Atlas Health',
                'role' => 'Senior UX Designer',
                'code' => 'AH-312',
                'status' => 'Scheduled',
                'status_key' => 'scheduled',
                'time_limit_min' => 30,
                'attempt_limit' => 1,
                'attempt_no' => 0,
                'passing_score' => 75,
                'questions_count' => 3,
                'due' => 'Opens after screening call',
                'instructions' => [
                    'This assessment unlocks after your recruiter screening.',
                    'Frontend preview lists it as scheduled — start remains disabled until the ready state.',
                ],
                'questions' => [],
            ],
        ];
    }

    /**
     * Resolve a mock assessment by id.
     *
     * @return array<string, mixed>|null
     */
    public static function assessment(string $id): ?array
    {
        foreach (self::assessments() as $assessment) {
            if ($assessment['id'] === $id) {
                return $assessment;
            }
        }

        return null;
    }

    /**
     * Frame 23 — Job preferences & alerts (frontend mock only).
     *
     * @return array<string, mixed>
     */
    public static function preferences(): array
    {
        $profile = self::profile();

        return [
            'titles' => 'Senior Product Designer · Lead Product Designer',
            'location' => 'Austin, TX + Remote US',
            'employment' => 'Full-time',
            'employment_options' => ['Full-time', 'Contract', 'Part-time'],
            'salary' => '$140,000 / year',
            'work_model' => 'Hybrid + Remote',
            'work_model_options' => ['Hybrid + Remote', 'Hybrid only', 'Remote only', 'On-site'],
            'include_agency' => true,
            'willing_relocate' => false,
            'notifications' => [
                ['name' => 'job_digest', 'label' => 'Weekly personalized job digest', 'checked' => true],
                ['name' => 'app_status', 'label' => 'Application status updates · Email and in-app', 'checked' => true],
                ['name' => 'interview_reminders', 'label' => 'Interview reminders · 24 hours and 1 hour before', 'checked' => true],
                ['name' => 'hiring_messages', 'label' => 'New hiring-team messages · Email and in-app', 'checked' => true],
                ['name' => 'marketing', 'label' => 'Product news and marketing', 'checked' => false],
            ],
            'privacy' => [
                ['name' => 'share_on_apply', 'label' => 'Share profile only when I apply or approve access', 'checked' => true],
                ['name' => 'recruiter_requests', 'label' => 'Allow recruiter contact requests', 'checked' => true],
                ['name' => 'salary_public', 'label' => 'Share salary preferences on public profile', 'checked' => false],
            ],
            'privacy_note' => 'Your preferences are used to suggest relevant roles. Other employers cannot see where you have applied.',
            'saved_search' => [
                'title' => 'Senior design · Austin + Remote',
                'schedule' => 'Weekly · Mondays at 9:00 AM Central',
                'email' => $profile['email'],
            ],
            'saved_banner' => 'Preferences saved. Updated October 20 at 8:45 AM. New recommendations use these preferences.',
            'footer' => $profile['footer'],
        ];
    }

    /**
     * Frame 24 — Candidate offers catalog (frontend mock only).
     *
     * @return list<array<string, mixed>>
     */
    public static function offers(): array
    {
        return [
            [
                'id' => 'offer-ms108',
                'application_id' => 'hb-ms108-mc-1019',
                'job_id' => 'ms-108',
                'company' => 'Meridian Studio',
                'title' => 'Lead Product Designer',
                'code' => 'MS-108',
                'received_on' => 'October 19, 2026',
                'deadline' => 'October 28',
                'status' => 'Awaiting your response',
                'status_key' => 'awaiting',
                'source' => 'Agency-managed',
                'headline' => 'A new chapter at Meridian',
                'meta' => 'Remote · United States · Full-time',
                'start_date' => 'November 16, 2026',
                'banner' => 'Review your offer by October 28. This is a separate Meridian Studio application. Your Northstar Labs application remains at Interview.',
                'compensation' => [
                    ['label' => 'Base salary', 'value' => '$165,000 USD'],
                    ['label' => 'Target annual bonus', 'value' => '10% of base · subject to plan terms'],
                    ['label' => 'Equity', 'value' => 'Grant details in the offer letter'],
                    ['label' => 'Benefits', 'value' => 'Medical, dental, vision, retirement, learning budget'],
                ],
                'compensation_note' => 'The signed offer letter governs all terms. Bonus and equity are not guaranteed cash compensation.',
                'documents' => [
                    [
                        'name' => 'Meridian_Offer_Maya_Chen.pdf',
                        'status' => 'Ready for review',
                        'action' => 'Open document',
                    ],
                    [
                        'name' => 'Benefits_Overview_2026.pdf',
                        'status' => 'Reference',
                        'action' => 'Download',
                    ],
                ],
                'contact' => [
                    'name' => 'Jamie Rivera',
                    'role' => 'Assigned agency recruiter',
                    'note' => 'Questions about compensation, timing, or terms? Send a message before the response deadline.',
                ],
                'after_accept' => [
                    'status' => 'Preview · Not started',
                    'steps' => [
                        'Confirm and sign your offer',
                        'Complete company checks',
                        'Share onboarding details',
                        'Meet your Meridian team',
                    ],
                    'note' => 'Onboarding only starts after your confirmed acceptance.',
                ],
                'footer' => self::profile()['footer'],
            ],
        ];
    }

    /**
     * Resolve a mock offer by id, application id, or job id.
     *
     * @return array<string, mixed>|null
     */
    public static function offer(string $id): ?array
    {
        foreach (self::offers() as $offer) {
            if ($offer['id'] === $id || $offer['application_id'] === $id || $offer['job_id'] === $id || $offer['code'] === $id) {
                return $offer;
            }
        }

        return null;
    }

    /**
     * Candidate notifications (Stage 2 dropdown + Batch 4E list).
     *
     * @return list<array<string, mixed>>
     */
    public static function notifications(): array
    {
        return [
            [
                'id' => 'n-interview',
                'title' => 'Portfolio interview confirmed',
                'body' => 'Northstar Labs · Oct 22, 10:00 AM',
                'time' => '2h ago',
                'unread' => true,
                'category' => 'interviews',
                'href' => '/candidate/interviews',
            ],
            [
                'id' => 'n-message',
                'title' => 'New message from Olivia Bennett',
                'body' => 'Interview agenda and meeting details',
                'time' => 'Yesterday',
                'unread' => true,
                'category' => 'messages',
                'href' => '/candidate/messages',
            ],
            [
                'id' => 'n-offer',
                'title' => 'Offer response reminder',
                'body' => 'Meridian Studio · due Oct 28',
                'time' => '2d ago',
                'unread' => false,
                'category' => 'offers',
                'href' => '/candidate/offers/offer-ms108',
            ],
            [
                'id' => 'n-assessment',
                'title' => 'Assessment ready',
                'body' => 'Northstar Labs · Design systems practical',
                'time' => '3d ago',
                'unread' => false,
                'category' => 'assessments',
                'href' => '/candidate/assessments',
            ],
        ];
    }
}
