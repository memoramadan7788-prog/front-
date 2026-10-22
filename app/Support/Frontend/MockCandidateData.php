<?php

namespace App\Support\Frontend;

/**
 * Isolated mock catalog for candidate workspace UI (Batch 4A).
 * Not Eloquent. Not live data. Frontend presentation only.
 */
class MockCandidateData
{
    /**
     * Optional employee membership request (Workflow 2 / F9A).
     * Demo via `?membership=none|draft|pending|approved|rejected`.
     *
     * @return array<string, mixed>
     */
    public static function membership(?string $demoState = null): array
    {
        $state = $demoState
            ?? (is_string(request()->query('membership')) ? request()->query('membership') : null)
            ?? 'none';

        if (! in_array($state, ['none', 'draft', 'pending', 'approved', 'rejected'], true)) {
            $state = 'none';
        }

        $companies = [
            [
                'id' => 'org-ns-001',
                'name' => 'Northstar Labs',
                'domain' => 'northstarlabs.example',
            ],
            [
                'id' => 'org-ms-001',
                'name' => 'Meridian Studio',
                'domain' => 'meridian.example',
            ],
            [
                'id' => 'org-ah-001',
                'name' => 'Atlas Health',
                'domain' => 'atlashealth.example',
            ],
        ];

        $request = match ($state) {
            'pending' => [
                'id' => 'req-maya-chen',
                'company_id' => 'org-ns-001',
                'company_name' => 'Northstar Labs',
                'job_title' => 'Product Designer',
                'department' => 'Product & Design',
                'status' => 'pending',
                'status_label' => 'Pending company review',
                'submitted_at' => 'October 19, 2026',
                'rejection_reason' => null,
                'can_submit_another' => false,
            ],
            'approved' => [
                'id' => 'req-maya-chen-approved',
                'company_id' => 'org-ns-001',
                'company_name' => 'Northstar Labs',
                'job_title' => 'Product Designer',
                'department' => 'Product & Design',
                'status' => 'approved',
                'status_label' => 'Approved · Employee',
                'submitted_at' => 'October 10, 2026',
                'reviewed_at' => 'October 12, 2026',
                'assigned_role' => 'Employee',
                'rejection_reason' => null,
                'can_submit_another' => false,
            ],
            'rejected' => [
                'id' => 'req-maya-cohen-style',
                'company_id' => 'org-ns-001',
                'company_name' => 'Northstar Labs',
                'job_title' => 'Hiring coordinator',
                'department' => 'People Operations',
                'status' => 'rejected',
                'status_label' => 'Rejected',
                'submitted_at' => 'October 12, 2026',
                'reviewed_at' => 'October 14, 2026',
                'rejection_reason' => 'People Operations already has an assigned HR admin for this workspace.',
                'can_submit_another' => true,
            ],
            default => null,
        };

        return [
            'design_source' => 'workflow_2',
            'design_note' => 'No dedicated Figma membership frame was verified for F9A. UI uses Frame 51 patterns.',
            'candidate' => [
                'id' => 'maya-chen',
                'name' => 'Maya Chen',
                'email' => 'maya.chen@example.com',
            ],
            'demo_state' => $state,
            'companies' => $companies,
            'request' => $request,
            'storage_key' => 'hb_candidate_membership_request_v1',
            'frontend_only' => true,
        ];
    }

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
                    'href' => 'candidate.applications.index',
                ],
                [
                    'label' => 'Saved roles',
                    'value' => '3',
                    'meta' => '2 new updates this week',
                    'href' => 'candidate.jobs.saved',
                    'count_attr' => 'data-saved-count',
                ],
                [
                    'label' => 'Profile strength',
                    'value' => $profile['strength'].'%',
                    'meta' => 'Add a case study to finish your profile',
                    'href' => 'candidate.profile',
                ],
            ],
            'interview' => [
                'id' => 'int-ns204-portfolio',
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
                'organization_id' => 'org-ms-001',
                'visibility' => 'public',
                'publication_status' => 'published',
            ],
            [
                'id' => 'ns-208',
                'title' => 'Internal Design Systems Lead',
                'company' => 'Northstar Labs',
                'company_slug' => 'northstar-labs',
                'organization_id' => 'org-ns-001',
                'code' => 'NS-208',
                'discipline' => 'Product & Design',
                'work_model' => 'Hybrid',
                'location' => 'Austin, TX',
                'location_short' => 'Austin',
                'employment' => 'Full-time',
                'salary_min' => 150000,
                'salary_max' => 180000,
                'salary_label' => '$150k–$180k',
                'source' => 'Direct hire',
                'experience_level' => 'Lead',
                'keyword' => 'internal design systems lead private employee',
                'match_reason' => 'Private employee-eligible role at Northstar Labs.',
                'primary_cta' => 'review_apply',
                'primary_cta_label' => 'Review & apply →',
                'application_state' => null,
                'saved_on' => null,
                'saved_status' => 'Private',
                'saved_action' => 'review_role',
                'saved_action_label' => 'Review role',
                'recommended_rank' => 6,
                'posted_rank' => 5,
                'visibility' => 'private',
                'private_apply_mode' => 'all_employees',
                'publication_status' => 'published',
                'openings_count' => 1,
                'deadline' => 'December 15, 2026',
                'requirements_flags' => [
                    'cv_required' => true,
                    'ai_consent_required' => true,
                    'cover_note_required' => false,
                ],
            ],
            [
                'id' => 'ns-209',
                'title' => 'Nominated Product Designer',
                'company' => 'Northstar Labs',
                'company_slug' => 'northstar-labs',
                'organization_id' => 'org-ns-001',
                'code' => 'NS-209',
                'discipline' => 'Product & Design',
                'work_model' => 'Hybrid',
                'location' => 'Austin, TX',
                'location_short' => 'Austin',
                'employment' => 'Full-time',
                'salary_min' => 130000,
                'salary_max' => 155000,
                'salary_label' => '$130k–$155k',
                'source' => 'Direct hire',
                'experience_level' => 'Senior',
                'keyword' => 'nominated product designer private',
                'match_reason' => 'Private nomination-only role at Northstar Labs.',
                'primary_cta' => 'review_apply',
                'primary_cta_label' => 'Review & apply →',
                'application_state' => null,
                'saved_on' => null,
                'saved_status' => 'Private',
                'saved_action' => 'review_role',
                'saved_action_label' => 'Review role',
                'recommended_rank' => 7,
                'posted_rank' => 4,
                'visibility' => 'private',
                'private_apply_mode' => 'nominated_only',
                'publication_status' => 'published',
                'openings_count' => 1,
                'deadline' => 'December 1, 2026',
                'nominations' => [
                    ['candidate_id' => 'maya-chen', 'name' => 'Maya Chen'],
                ],
                'requirements_flags' => [
                    'cv_required' => true,
                    'ai_consent_required' => true,
                    'cover_note_required' => true,
                ],
            ],
        ];

        // Ensure public NS-204 carries presentation contract fields.
        $jobs = array_map(function (array $job): array {
            if (($job['id'] ?? '') === 'ns-204') {
                $job['organization_id'] = 'org-ns-001';
                $job['visibility'] = 'public';
                $job['publication_status'] = 'published';
                $job['employees_cannot_apply_public'] = true;
                $job['requirements_flags'] = [
                    'cv_required' => true,
                    'ai_consent_required' => true,
                    'cover_note_required' => false,
                ];
            }

            return $job;
        }, $jobs);

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
                'message_thread_id' => 'msg-olivia-ns204',
                'interview_id' => 'int-ns204-portfolio',
                'assessment_id' => 'exam-ns204-systems',
                'assessment_attempt_id' => 'attempt-ns204-maya-1',
                'assessment_status' => 'Ready to start',
                'assessment_status_key' => 'ready',
                'assessment_attempts_used' => 1,
                'assessment_attempt_limit' => 2,
                'assessment_proctoring_review' => 'pending',
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
                'attempt_no' => 1,
                'attempt_limit' => 1,
                'can_reapply' => false,
                'reapply_reason' => 'An active application already exists for this role.',
                'can_withdraw' => false,
                'withdraw_reason' => 'Withdrawal is unavailable after the hiring team has started review.',
                'withdraw_status' => 'review_started',
            ],
            [
                'id' => 'hb-ms108-mc-1019',
                'job_id' => 'ms-108',
                'message_thread_id' => 'msg-jamie-ms108',
                'offer_id' => 'offer-ms108',
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
                'message_thread_id' => 'msg-sam-ah312',
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
                'attempt_no' => 1,
                'attempt_limit' => 2,
                'can_reapply' => false,
                'reapply_reason' => 'An active application already exists for this role.',
                'can_withdraw' => true,
                'withdraw_reason' => null,
                'withdraw_status' => 'before_review',
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
        $job = self::job($jobId);

        if ($job === null) {
            $public = MockPublicData::job($jobId);
            if (is_array($public)) {
                $location = (string) ($public['location'] ?? '');
                $job = array_merge($public, [
                    'location_short' => trim(explode(',', $location)[0] ?: $location),
                ]);
            }
        }

        $job = $job ?? self::job('ns-204');
        $job = JobAccessPresentation::normalizeJob($job);
        $context = JobAccessPresentation::candidateContext();
        $eligibility = JobAccessPresentation::eligibility($job, $context);
        $job['eligibility'] = $eligibility;

        $company = $job['company'] ?? 'Northstar Labs';
        $locationShort = $job['location_short'] ?? 'Austin';
        $flags = $job['requirements_flags'] ?? [
            'cv_required' => true,
            'ai_consent_required' => true,
            'cover_note_required' => false,
        ];

        return [
            'job' => $job,
            'access_context' => $context,
            'eligibility' => $eligibility,
            'can_proceed' => (bool) ($eligibility['can_apply'] ?? false),
            'profile' => [
                'name' => $profile['name'],
                'email' => $profile['email'],
                'portfolio' => $profile['portfolio'],
                'resume' => $profile['resume'],
                'headline' => $profile['headline'],
                'location_short' => $profile['location_short'],
            ],
            'journey_banner' => 'Complete the steps below to finish applying to '.$company.'. This is a presentation preview — nothing is submitted yet.',
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
                'interest' => 'I’m drawn to operational tools and SaaS products where design systems and discovery shape complex workflows. '.$company.'’s product focus and hybrid setup match how I like to collaborate.',
                'salary' => '$155,000 – $165,000',
                'cover_note' => '',
                'consent' => false,
                'ai_consent' => false,
                'talent_pool' => false,
                'resume_attached' => true,
            ],
            'requirements_flags' => $flags,
            'role_summary' => [
                'source' => $job['source'] ?? 'Direct hire',
                'title' => $job['title'] ?? 'Senior Product Designer',
                'company' => $company,
                'code' => $job['code'] ?? 'NS-204',
                'meta' => $locationShort.' '.strtolower($job['work_model'] ?? 'hybrid').' · '.($job['employment'] ?? 'Full-time'),
                'salary' => ($job['salary_label'] ?? '$140k–$170k').' USD',
                'hiring_contact' => $job['hiring_contact'] ?? 'Olivia Bennett',
                'team' => $job['discipline'] ?? 'Product & Design',
                'visibility' => $job['visibility'] ?? 'public',
                'application_mode' => $job['application_mode'] ?? 'open',
            ],
            'before_submit' => 'Your resume and portfolio will be shared with the authorized '.$company.' hiring team for this role. Other applications and private preferences stay confidential.',
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
                ? 'Your Northstar Labs application is in progress. Next steps will appear here as the hiring team reviews your materials.'
                : 'Your application to '.$company.' ('.$code.') is in progress. Next steps will appear here as the hiring team reviews your materials.',
            'preview_notice' => 'Keep this confirmation for your records. Use the application reference if you need to follow up with the hiring team.',
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
            InterviewBookingPresentation::enrichInterview([
                'id' => 'int-ns204-portfolio',
                'application_id' => 'hb-ns204-mc-1012',
                'job_id' => 'ns-204',
                'organization_id' => 'org-ns-001',
                'candidate_id' => 'maya-chen',
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
                'starts_time' => '10:00:00',
                'weekday' => 'Thu',
                'day_num' => '22',
                'interviewer' => 'Alex Morgan · Interviewer · Product Design',
                'interviewer_short' => 'Alex Morgan',
                'interviewer_id' => 'alex-morgan',
                'format' => 'Video meeting · Link available 15 minutes before the interview.',
                'type' => 'Remote',
                'duration' => '45 minutes',
                'round_no' => 2,
                'calendar_label' => 'Maya Chen · Portfolio · NS-204',
                'scope' => 'upcoming',
                'evaluation_status' => 'pending',
                'prep' => [
                    ['label' => 'Review the role description', 'done' => true],
                    ['label' => 'Choose two portfolio projects', 'done' => false],
                    ['label' => 'Test camera and microphone', 'done' => false],
                    ['label' => 'Prepare your questions', 'done' => false],
                ],
                'guide_note' => 'Checklist progress is saved in this browser.',
            ]),
            InterviewBookingPresentation::enrichInterview([
                'id' => 'int-ah312-near',
                'application_id' => 'hb-ah312-mc-1018',
                'job_id' => 'ah-312',
                'organization_id' => 'org-ah-001',
                'candidate_id' => 'maya-chen',
                'title' => 'Screening follow-up',
                'company' => 'Atlas Health',
                'role' => 'Senior UX Designer',
                'code' => 'AH-312',
                'status' => 'Confirmed · within 24h cutoff',
                'status_key' => 'confirmed',
                'when' => 'Tuesday, October 20',
                'time' => '4:00–4:30 PM · Central Time (UTC−05:00)',
                'time_short' => '4:00–4:30',
                'day_key' => '2026-10-20',
                'starts_time' => '16:00:00',
                'weekday' => 'Tue',
                'day_num' => '20',
                'interviewer' => 'Priya Shah · Interviewer',
                'interviewer_short' => 'Priya Shah',
                'interviewer_id' => 'priya-shah',
                'format' => 'Video meeting',
                'type' => 'Remote',
                'duration' => '30 minutes',
                'round_no' => 1,
                'calendar_label' => 'Maya Chen · Screening · AH-312',
                'scope' => 'upcoming',
                'evaluation_status' => 'not_started',
                'prep' => [],
                'guide_note' => 'Reschedule is unavailable inside the 24-hour cutoff in this preview.',
            ]),
        ];

        $past = [
            InterviewBookingPresentation::enrichInterview([
                'id' => 'int-ns204-screening',
                'application_id' => 'hb-ns204-mc-1012',
                'job_id' => 'ns-204',
                'organization_id' => 'org-ns-001',
                'candidate_id' => 'maya-chen',
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
                'starts_time' => '11:00:00',
                'interviewer' => 'Olivia Bennett',
                'interviewer_id' => 'olivia-bennett',
                'format' => 'Video meeting',
                'type' => 'Remote',
                'duration' => '30 minutes',
                'round_no' => 1,
                'scope' => 'past',
                'evaluation_status' => 'submitted',
                'note' => 'Next stage: portfolio interview. No action required for the completed screening.',
                'prep' => [],
            ]),
            InterviewBookingPresentation::enrichInterview([
                'id' => 'int-ms108-cancelled',
                'application_id' => 'hb-ms108-mc-1019',
                'job_id' => 'ms-108',
                'candidate_id' => 'maya-chen',
                'title' => 'Team panel',
                'company' => 'Meridian Studio',
                'role' => 'Lead Product Designer',
                'code' => 'MS-108',
                'status' => 'Cancelled',
                'status_key' => 'cancelled',
                'when' => 'October 18',
                'time' => 'Cancelled by hiring team · sample',
                'time_short' => '—',
                'day_key' => '2026-10-18',
                'starts_time' => '13:00:00',
                'interviewer' => 'Jamie Rivera',
                'format' => 'Video meeting',
                'type' => 'Remote',
                'duration' => '45 minutes',
                'round_no' => 2,
                'scope' => 'past',
                'evaluation_status' => 'not_applicable',
                'note' => 'Sample cancelled interview. It is not an active upcoming session.',
                'prep' => [],
            ]),
        ];

        return [
            'week_label' => 'October 19–23, 2026',
            'timezone' => 'Week view · Central Time (UTC−05:00)',
            'demo_now' => InterviewBookingPresentation::DEMO_NOW,
            'days' => [
                ['key' => '2026-10-19', 'label' => 'Mon 19', 'slots' => [['time' => '9:00 AM', 'label' => 'Available'], ['time' => '2:00 PM', 'label' => 'Available']]],
                ['key' => '2026-10-20', 'label' => 'Tue 20', 'highlight' => true, 'slots' => [['time' => '4:00–4:30', 'label' => 'Maya Chen · Screening · AH-312', 'interview_id' => 'int-ah312-near'], ['time' => '9:00 AM', 'label' => 'Available']]],
                ['key' => '2026-10-21', 'label' => 'Wed 21', 'slots' => [['time' => '9:00 AM', 'label' => 'Available'], ['time' => '2:00 PM', 'label' => 'Available']]],
                ['key' => '2026-10-22', 'label' => 'Thu 22', 'highlight' => true, 'slots' => [['time' => '10:00–10:45', 'label' => 'Maya Chen · Portfolio · NS-204', 'interview_id' => 'int-ns204-portfolio']]],
                ['key' => '2026-10-23', 'label' => 'Fri 23', 'slots' => [['time' => '9:00 AM', 'label' => 'Available'], ['time' => '2:00 PM', 'label' => 'Available']]],
            ],
            'upcoming' => $upcoming,
            'past' => $past,
            'selected_id' => request()->query('interview') === 'int-ah312-near' ? 'int-ah312-near' : 'int-ns204-portfolio',
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
        $consentDeclined = request()->query('consent') === 'declined';
        $exhaustedDemo = request()->query('attempts') === 'exhausted';

        return [
            [
                'id' => 'exam-ns204-systems',
                'application_id' => 'hb-ns204-mc-1012',
                'job_id' => 'ns-204',
                'attempt_id' => 'attempt-ns204-maya-1',
                'title' => 'Design systems practical',
                'company' => 'Northstar Labs',
                'role' => 'Senior Product Designer',
                'code' => 'NS-204',
                'status' => $exhaustedDemo ? 'Attempts exhausted' : ($consentDeclined ? 'Consent required' : 'Ready to start'),
                'status_key' => $exhaustedDemo ? 'exhausted' : ($consentDeclined ? 'consent_required' : 'ready'),
                'lifecycle' => $exhaustedDemo ? 'unavailable_expired' : 'published_assigned',
                'time_limit_min' => 45,
                'attempt_limit' => 2,
                'attempt_no' => $exhaustedDemo ? 2 : 1,
                'attempts_remaining' => $exhaustedDemo ? 0 : 1,
                'can_start' => ! $exhaustedDemo && ! $consentDeclined,
                'can_resume' => false,
                'retake_available' => ! $exhaustedDemo,
                'passing_score' => 70,
                'questions_count' => 4,
                'due' => 'Complete before Oct 22 interview',
                'deadline' => 'October 22, 2026, 9:00 AM Central',
                'timer_authority_note' => 'Browser timer is presentation-only. Server-controlled timing is required for production exams.',
                'proctoring_required' => true,
                'camera_consent_required' => true,
                'identity_verification' => 'required',
                'proctoring_consent_state' => $consentDeclined ? 'declined' : 'pending',
                'grading' => [
                    'methods' => ['automatic_mc', 'manual_short'],
                    'preliminary' => true,
                    'publication_status' => 'candidate_preview',
                ],
                'instructions' => [
                    'You will answer four short questions about design systems and workflow prioritization.',
                    'The timer starts when you begin. Leaving the page does not pause the timer.',
                    'Stay in a quiet space with a stable connection for the full attempt.',
                    'This assessment may require camera consent for proctoring review. No camera is accessed in this frontend preview.',
                    'Submit when you are finished to review your estimated score.',
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
                'attempt_id' => null,
                'title' => 'UX research scenario',
                'company' => 'Atlas Health',
                'role' => 'Senior UX Designer',
                'code' => 'AH-312',
                'status' => 'Scheduled',
                'status_key' => 'scheduled',
                'lifecycle' => 'not_started',
                'time_limit_min' => 30,
                'attempt_limit' => 1,
                'attempt_no' => 0,
                'attempts_remaining' => 1,
                'can_start' => false,
                'can_resume' => false,
                'retake_available' => false,
                'passing_score' => 75,
                'questions_count' => 3,
                'due' => 'Opens after screening call',
                'deadline' => null,
                'timer_authority_note' => 'Browser timer is presentation-only. Server-controlled timing is required for production exams.',
                'proctoring_required' => false,
                'camera_consent_required' => false,
                'identity_verification' => 'not_required',
                'proctoring_consent_state' => 'not_required',
                'grading' => [
                    'methods' => ['automatic_mc', 'manual_short'],
                    'preliminary' => true,
                    'publication_status' => 'not_published',
                ],
                'instructions' => [
                    'This assessment unlocks after your recruiter screening.',
                    'Start remains unavailable until your screening is complete.',
                ],
                'questions' => [],
            ],
            [
                'id' => 'exam-ns204-flagged',
                'application_id' => 'hb-ns204-mc-1012',
                'job_id' => 'ns-204',
                'attempt_id' => 'attempt-ns204-maya-flagged',
                'title' => 'Design systems practical · Prior attempt',
                'company' => 'Northstar Labs',
                'role' => 'Senior Product Designer',
                'code' => 'NS-204',
                'status' => 'Flagged for review',
                'status_key' => 'flagged',
                'lifecycle' => 'flagged_proctoring',
                'time_limit_min' => 45,
                'attempt_limit' => 2,
                'attempt_no' => 1,
                'attempts_remaining' => 1,
                'can_start' => false,
                'can_resume' => false,
                'retake_available' => true,
                'passing_score' => 70,
                'questions_count' => 4,
                'due' => 'Awaiting human proctoring review',
                'deadline' => 'October 20, 2026',
                'timer_authority_note' => 'Browser timer is presentation-only.',
                'proctoring_required' => true,
                'camera_consent_required' => true,
                'identity_verification' => 'completed',
                'proctoring_consent_state' => 'accepted',
                'proctoring_flags' => [
                    [
                        'id' => 'flag-ns204-1',
                        'event_type' => 'attention_away',
                        'label' => 'Attention left the exam window',
                        'recorded_at' => 'Oct 18, 2026 · 3:12 PM',
                        'severity' => 'review',
                        'note' => 'Sample signal for human review. Not an automatic fail or cheating verdict.',
                    ],
                ],
                'proctoring_review_status' => 'pending',
                'grading' => [
                    'methods' => ['automatic_mc', 'manual_short'],
                    'preliminary' => true,
                    'score' => null,
                    'publication_status' => 'held_for_review',
                ],
                'instructions' => [
                    'A prior attempt was flagged for human proctoring review.',
                    'Flags are signals for authorized reviewers. They are not automatic failures.',
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
                ['name' => 'job_digest', 'label' => __('candidate.preferences.notif_job_digest'), 'checked' => true],
                ['name' => 'app_status', 'label' => __('candidate.preferences.notif_app_status'), 'checked' => true],
                ['name' => 'interview_reminders', 'label' => __('candidate.preferences.notif_interview_reminders'), 'checked' => true],
                ['name' => 'hiring_messages', 'label' => __('candidate.preferences.notif_hiring_messages'), 'checked' => true],
                ['name' => 'marketing', 'label' => __('candidate.preferences.notif_marketing'), 'checked' => false],
            ],
            'privacy' => [
                ['name' => 'share_on_apply', 'label' => __('candidate.preferences.privacy_share_on_apply'), 'checked' => true],
                ['name' => 'recruiter_requests', 'label' => __('candidate.preferences.privacy_recruiter_requests'), 'checked' => true],
                ['name' => 'salary_public', 'label' => __('candidate.preferences.privacy_salary_public'), 'checked' => false],
            ],
            'privacy_note' => __('candidate.preferences.privacy_note'),
            'saved_search' => [
                'title' => __('candidate.preferences.saved_search_title'),
                'schedule' => __('candidate.preferences.saved_search_schedule'),
                'email' => $profile['email'],
            ],
            'saved_banner' => __('candidate.preferences.saved'),
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
        $active = OfferLifecyclePresentation::enrich([
            'id' => 'offer-ms108',
            'application_id' => 'hb-ms108-mc-1019',
            'job_id' => 'ms-108',
            'candidate_id' => 'maya-chen',
            'company' => 'Meridian Studio',
            'title' => 'Lead Product Designer',
            'code' => 'MS-108',
            'received_on' => 'October 19, 2026',
            'deadline' => 'October 28, 2026',
            'expires_at' => '2026-10-28T23:59:00-05:00',
            'status' => 'Awaiting your response',
            'status_key' => OfferLifecyclePresentation::AWAITING,
            'source' => 'Agency-managed',
            'headline' => 'A new chapter at Meridian',
            'meta' => 'Remote · United States · Full-time',
            'start_date' => 'November 16, 2026',
            'currency' => 'USD',
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
                'status' => 'Not started',
                'steps' => [
                    'Confirm and sign your offer',
                    'Complete company checks',
                    'Share onboarding details',
                    'Meet your Meridian team',
                ],
                'note' => 'Onboarding only starts after your confirmed acceptance.',
            ],
            'footer' => self::profile()['footer'],
        ]);

        $expired = OfferLifecyclePresentation::enrich([
            'id' => 'offer-ms107-expired',
            'application_id' => 'hb-ms107-mc-1001',
            'job_id' => 'ms-107',
            'candidate_id' => 'maya-chen',
            'company' => 'Meridian Studio',
            'title' => 'Product Designer',
            'code' => 'MS-107',
            'received_on' => 'September 20, 2026',
            'deadline' => 'October 5, 2026',
            'expires_at' => '2026-10-05T23:59:00-05:00',
            'status' => 'Expired',
            'status_key' => OfferLifecyclePresentation::EXPIRED,
            'source' => 'Agency-managed',
            'headline' => 'Previous Meridian offer (sample)',
            'meta' => 'Remote · United States · Full-time',
            'start_date' => 'October 20, 2026',
            'currency' => 'USD',
            'banner' => 'This sample offer expired on October 5. Accept and decline stay unavailable.',
            'compensation' => [
                ['label' => 'Base salary', 'value' => '$140,000 USD'],
            ],
            'compensation_note' => 'Expired sample — no response can be submitted.',
            'documents' => [],
            'contact' => [
                'name' => 'Jamie Rivera',
                'role' => 'Assigned agency recruiter',
                'note' => 'Contact Jamie if you need a new offer draft.',
            ],
            'after_accept' => [
                'status' => 'Not available',
                'steps' => [],
                'note' => 'Expired offers cannot start onboarding.',
            ],
            'footer' => self::profile()['footer'],
        ]);

        $accepted = OfferLifecyclePresentation::enrich([
            'id' => 'offer-sample-accepted',
            'application_id' => 'hb-sample-accepted',
            'job_id' => 'sample-job',
            'candidate_id' => 'maya-chen',
            'company' => 'Sample Co',
            'title' => 'Sample accepted offer',
            'code' => 'SAMPLE-A',
            'received_on' => 'September 1, 2026',
            'deadline' => 'September 10, 2026',
            'status' => 'Accepted (sample record)',
            'status_key' => OfferLifecyclePresentation::ACCEPTED,
            'source' => 'Direct hire',
            'headline' => 'Labeled sample — already accepted',
            'meta' => 'Presentation sample only',
            'start_date' => 'October 1, 2026',
            'currency' => 'USD',
            'banner' => 'Labeled sample record. No new decision can be previewed.',
            'compensation' => [
                ['label' => 'Base salary', 'value' => '$150,000 USD'],
            ],
            'compensation_note' => 'Sample accepted offer.',
            'documents' => [],
            'contact' => [
                'name' => 'Sample Recruiter',
                'role' => 'HR',
                'note' => 'Sample contact.',
            ],
            'after_accept' => [
                'status' => 'Sample complete',
                'steps' => ['Acceptance recorded in sample data'],
                'note' => 'Presentation only.',
            ],
            'footer' => self::profile()['footer'],
        ]);

        return [$active, $expired, $accepted];
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
        return MockNotificationData::forActor('candidate');
    }

    /**
     * Localized label for a stored work-model value (values stay English for filters/storage).
     */
    public static function workModelLabel(string $value): string
    {
        return match ($value) {
            'Hybrid' => __('common.work_models.hybrid'),
            'Remote' => __('common.work_models.remote'),
            'On-site' => __('common.work_models.on_site'),
            'Hybrid + Remote' => __('common.work_models.hybrid_remote'),
            'Hybrid only' => __('common.work_models.hybrid_only'),
            'Remote only' => __('common.work_models.remote_only'),
            default => $value,
        };
    }

    /**
     * Localized label for a stored employment-type value.
     */
    public static function employmentLabel(string $value): string
    {
        return match ($value) {
            'Full-time' => __('common.employment.full_time'),
            'Contract' => __('common.employment.contract'),
            'Part-time' => __('common.employment.part_time'),
            default => $value,
        };
    }
}
