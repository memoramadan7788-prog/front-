<?php

namespace App\Support\Frontend;

/**
 * Super Admin frontend presentation data (Stage 7 · Batch 7A).
 * Aligns with MockCompanyData / Interviewer identities where relevant.
 * No Eloquent, auth, or persistence.
 */
class MockAdminData
{
    /**
     * Frame 44 — Platform operations overview.
     *
     * @return array<string, mixed>
     */
    public static function dashboard(): array
    {
        return [
            'admin_name' => 'Priya Shah',
            'snapshot_date' => 'October 20, 2026',
            'stats' => [
                [
                    'id' => 'orgs',
                    'value' => '12',
                    'meta' => '9 companies · 3 agencies',
                    'href' => 'admin.organizations.index',
                ],
                [
                    'id' => 'jobs',
                    'value' => '24',
                    'meta' => '16 direct-only · 8 agency-supported',
                    'href' => 'admin.jobs.index',
                ],
                [
                    'id' => 'engagements',
                    'value' => '4',
                    'meta' => 'Across 3 agency workspaces',
                    'href' => 'admin.agencies.index',
                ],
                [
                    'id' => 'review',
                    'value' => '5',
                    'meta' => '3 jobs · 2 organizations',
                    'href' => 'admin.jobs.index',
                    'query' => ['status' => 'pending'],
                ],
            ],
            'priorities' => [
                [
                    'id' => 'job_policy',
                    'title_key' => 'job_policy',
                    'object' => '3 jobs in queue',
                    'owner' => 'Priya Shah',
                    'next_key' => 'job_policy_next',
                    'href' => 'admin.jobs.index',
                    'query' => ['status' => 'pending'],
                ],
                [
                    'id' => 'company_verification',
                    'title_key' => 'company_verification',
                    'object' => 'Cedar Systems, Orbit Analytics',
                    'owner' => 'Priya Shah',
                    'next_key' => 'company_verification_next',
                    'href' => 'admin.organizations.index',
                    'query' => ['tab' => 'pending'],
                ],
                [
                    'id' => 'agency_delivery',
                    'title_key' => 'agency_delivery',
                    'object' => 'Northstar · Product Design',
                    'owner' => 'Jamie Rivera',
                    'next_key' => 'agency_delivery_next',
                    'href' => 'admin.agencies.index',
                    'query' => ['q' => 'ENG-104'],
                ],
                [
                    'id' => 'access_review',
                    'title_key' => 'access_review',
                    'object' => '1 suspended user',
                    'owner' => 'Priya Shah',
                    'next_key' => 'access_review_next',
                    'href' => 'admin.users.index',
                    'query' => ['tab' => 'suspended'],
                ],
            ],
            'org_health' => [
                [
                    'name' => 'Northstar Labs',
                    'type_key' => 'company',
                    'state_key' => 'active_verified',
                    'activity' => '3 open jobs · 28 candidate records',
                    'org_id' => 'org-ns-001',
                ],
                [
                    'name' => 'Hirebridge Talent',
                    'type_key' => 'agency',
                    'state_key' => 'active_verified',
                    'activity' => '4 active engagements',
                    'org_id' => 'org-ht-001',
                ],
                [
                    'name' => 'Atlas Health',
                    'type_key' => 'company',
                    'state_key' => 'active_verified',
                    'activity' => '2 open jobs',
                    'org_id' => 'org-ah-001',
                ],
                [
                    'name' => 'Cedar Systems',
                    'type_key' => 'company',
                    'state_key' => 'verification_pending',
                    'activity' => 'Draft jobs only',
                    'org_id' => 'org-cd-001',
                ],
            ],
            'recent_events' => [
                [
                    'time' => '9:42',
                    'title_key' => 'job_changes',
                    'detail' => 'Lumen Financial',
                ],
                [
                    'time' => '9:18',
                    'title_key' => 'team_access',
                    'detail' => 'Northstar Labs',
                ],
                [
                    'time' => '8:55',
                    'title_key' => 'engagement_assigned',
                    'detail' => 'Hirebridge Talent',
                ],
            ],
            'security' => [
                'status_key' => 'no_critical',
                'body_key' => 'security_body',
            ],
        ];
    }

    /**
     * Frame 45 — Organizations directory.
     *
     * @return array<string, mixed>
     */
    public static function organizationsIndex(): array
    {
        $organizations = self::organizations();

        $counts = [
            'active' => 0,
            'pending' => 0,
            'suspended' => 0,
            'archived' => 0,
        ];
        foreach ($organizations as $org) {
            $tab = $org['tab'] ?? 'active';
            if (isset($counts[$tab])) {
                $counts[$tab]++;
            }
        }

        return [
            'page_size' => 7,
            'default_tab' => 'active',
            'tabs' => [
                ['key' => 'active', 'count' => $counts['active']],
                ['key' => 'pending', 'count' => $counts['pending']],
                ['key' => 'suspended', 'count' => $counts['suspended']],
                ['key' => 'archived', 'count' => $counts['archived']],
            ],
            'type_filters' => [
                ['key' => 'all', 'label_key' => 'type_all'],
                ['key' => 'company', 'label_key' => 'type_companies'],
                ['key' => 'agency', 'label_key' => 'type_agencies'],
            ],
            'plan_filters' => [
                ['key' => 'all', 'label_key' => 'plan_all'],
                ['key' => 'business', 'label_key' => 'plan_business'],
                ['key' => 'growth', 'label_key' => 'plan_growth'],
                ['key' => 'agency_partner', 'label_key' => 'plan_agency'],
            ],
            'organizations' => $organizations,
            'lifecycle' => [
                'pending_names' => 'Cedar Systems and Orbit Analytics',
                'suspended_names' => 'Pinecrest Recruiting',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function organizations(): array
    {
        return [
            self::orgRow('org-ns-001', 'Northstar Labs', 'company', 'business', 'verified_active', 'Olivia Bennett', 'northstarlabs.example', 3, 'jobs', 'active'),
            self::orgRow('org-ms-001', 'Meridian Studio', 'company', 'growth', 'verified_active', 'Jamie Rivera', 'meridianstudio.example', 2, 'jobs', 'active'),
            self::orgRow('org-ah-001', 'Atlas Health', 'company', 'business', 'verified_active', 'Sam Wilson', 'atlashealth.example', 2, 'jobs', 'active'),
            self::orgRow('org-lf-001', 'Lumen Financial', 'company', 'growth', 'verified_active', 'Casey Tran', 'lumenfinancial.example', 3, 'jobs', 'active'),
            self::orgRow('org-ht-001', 'Hirebridge Talent', 'agency', 'agency_partner', 'verified_active', 'Jamie Rivera', 'hirebridgetalent.example', 8, 'roles', 'active'),
            self::orgRow('org-pc-001', 'Peoplecraft', 'agency', 'agency_partner', 'verified_active', 'Erin James', 'peoplecraft.example', 3, 'roles', 'active'),
            self::orgRow('org-gc-001', 'Grove & Co.', 'company', 'growth', 'verified_active', 'Robin Ellis', 'groveandco.example', 2, 'jobs', 'active'),
            self::orgRow('org-fw-001', 'Fieldwork Systems', 'company', 'business', 'verified_active', 'Morgan Price', 'fieldwork.example', 1, 'jobs', 'active'),
            self::orgRow('org-ps-001', 'Parcel Systems', 'company', 'growth', 'verified_active', 'Taylor Nguyen', 'parcelsystems.example', 1, 'jobs', 'active'),
            self::orgRow('org-rb-001', 'Riverbend Ops', 'company', 'business', 'verified_active', 'Chris Alvarez', 'riverbend.example', 2, 'jobs', 'active'),
            self::orgRow('org-qc-001', 'Quorum Consulting', 'agency', 'agency_partner', 'verified_active', 'Avery Cole', 'quorum.example', 2, 'roles', 'active'),
            self::orgRow('org-bl-001', 'Brightline Media', 'company', 'growth', 'verified_active', 'Dana Ortiz', 'brightline.example', 1, 'jobs', 'active'),
            self::orgRow('org-cd-001', 'Cedar Systems', 'company', 'growth', 'pending', 'Riley Quinn', 'cedarsystems.example', 0, 'jobs', 'pending'),
            self::orgRow('org-oa-001', 'Orbit Analytics', 'company', 'business', 'pending', 'Pat Okonkwo', 'orbitanalytics.example', 0, 'jobs', 'pending'),
            self::orgRow('org-pr-001', 'Pinecrest Recruiting', 'agency', 'agency_partner', 'suspended', 'Support desk', 'pinecrest.example', 0, 'roles', 'suspended'),
            self::orgRow('org-lp-001', 'Legacy Partners', 'company', 'growth', 'archived', 'Archive owner', 'legacypartners.example', 0, 'jobs', 'archived'),
            self::orgRow('org-ss-001', 'Summit Staffing', 'agency', 'agency_partner', 'archived', 'Archive owner', 'summitstaffing.example', 0, 'roles', 'archived'),
        ];
    }

    /**
     * Directory row for an organization id.
     *
     * @return array<string, mixed>|null
     */
    public static function organization(string $id): ?array
    {
        foreach (self::organizations() as $org) {
            if (($org['id'] ?? '') === $id) {
                return $org;
            }
        }

        return null;
    }

    /**
     * Frame 46 — Organization workspace detail.
     * Northstar Labs has full profile data; others use a limited coherent profile.
     *
     * @return array<string, mixed>|null
     */
    public static function organizationShow(string $id): ?array
    {
        $base = self::organization($id);
        if ($base === null) {
            return null;
        }

        $detail = self::organizationDetailProfiles()[$id] ?? self::limitedOrganizationProfile($base);

        return array_merge($base, $detail, [
            'show_url' => route('admin.organizations.show', $id),
            'index_url' => route('admin.organizations.index'),
        ]);
    }

    /**
     * Frame 47 — Users & access directory.
     * Counts reflect this mock set only (not a fabricated 96-account population).
     *
     * @return array<string, mixed>
     */
    public static function usersIndex(): array
    {
        $users = self::users();
        $counts = [
            'all' => count($users),
            'active' => 0,
            'invited' => 0,
            'suspended' => 0,
        ];
        foreach ($users as $user) {
            $state = $user['state'] ?? 'active';
            if (isset($counts[$state])) {
                $counts[$state]++;
            }
        }

        $roles = [];
        $organizations = [];
        foreach ($users as $user) {
            $roles[$user['role_key']] = true;
            $organizations[$user['organization']] = true;
        }

        return [
            'page_size' => 8,
            'default_tab' => 'all',
            'tabs' => [
                ['key' => 'all', 'count' => $counts['all']],
                ['key' => 'active', 'count' => $counts['active']],
                ['key' => 'invited', 'count' => $counts['invited']],
                ['key' => 'suspended', 'count' => $counts['suspended']],
            ],
            'role_filters' => array_keys($roles),
            'organization_filters' => array_keys($organizations),
            'users' => $users,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function users(): array
    {
        return [
            self::userRow('usr-maya', 'Maya Chen', 'maya.chen@example.com', 'Personal workspace', null, 'candidate', 'active', 'Today 9:26 AM', 'view_account'),
            self::userRow('usr-olivia', 'Olivia Bennett', 'olivia.bennett@northstarlabs.example', 'Northstar Labs', 'org-ns-001', 'company_admin', 'active', 'Today 9:48 AM', 'view_access'),
            self::userRow('usr-daniel', 'Daniel Brooks', 'daniel.brooks@northstarlabs.example', 'Northstar Labs', 'org-ns-001', 'hiring_manager', 'active', 'Yesterday', 'view_access'),
            self::userRow('usr-alex', 'Alex Morgan', 'alex.morgan@northstarlabs.example', 'Northstar Labs', 'org-ns-001', 'interviewer', 'active', 'Today 8:30 AM', 'view_access'),
            self::userRow('usr-jamie', 'Jamie Rivera', 'jamie.rivera@hirebridgetalent.example', 'Hirebridge Talent', 'org-ht-001', 'agency_recruiter', 'active', 'Today 8:55 AM', 'view_access'),
            self::userRow('usr-priya', 'Priya Shah', 'priya.shah@hirebridge.example', 'Hirebridge platform', null, 'super_admin', 'active', 'Current session', 'view_own'),
            self::userRow('usr-taylor', 'Taylor Reed', 'taylor.reed@northstarlabs.example', 'Northstar Labs', 'org-ns-001', 'interviewer', 'invited', 'Oct 19', 'invite_actions'),
            self::userRow('usr-morgan', 'Morgan Price', 'morgan.price@pinecrest.example', 'Pinecrest Recruiting', 'org-pr-001', 'agency_admin', 'suspended', 'Oct 18', 'support_case'),
            self::userRow('usr-jordan', 'Jordan Lee', 'jordan.lee@northstarlabs.example', 'Northstar Labs', 'org-ns-001', 'hiring_manager', 'active', 'Oct 20', 'view_access'),
            self::userRow('usr-casey', 'Casey Tran', 'casey.tran@lumenfinancial.example', 'Lumen Financial', 'org-lf-001', 'company_admin', 'active', 'Oct 19', 'view_access'),
            self::userRow('usr-sam', 'Sam Wilson', 'sam.wilson@atlashealth.example', 'Atlas Health', 'org-ah-001', 'hiring_manager', 'active', 'Oct 18', 'view_access'),
            self::userRow('usr-erin', 'Erin James', 'erin.james@peoplecraft.example', 'Peoplecraft', 'org-pc-001', 'agency_recruiter', 'invited', 'Oct 17', 'invite_actions'),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function organizationDetailProfiles(): array
    {
        return [
            'org-ns-001' => [
                'code' => 'ORG-NS-001',
                'workspace_label_key' => 'company_workspace',
                'created_on' => 'September 28, 2026',
                'lifecycle_key' => 'active',
                'verified_badge_key' => 'business_verified',
                'detail_level' => 'full',
                'profile' => [
                    'industry_location' => 'Software & collaboration · Austin, Texas',
                    'hiring_models' => 'Direct hiring + agency support',
                    'subscription' => 'Business · Monthly · Renews November 1',
                    'verification' => 'Business domain and owner reviewed October 1',
                    'usage' => '3 open jobs · 28 candidate records · 5 active members',
                    'agency_relationship' => 'Hirebridge Talent · NS-204 and NS-205',
                ],
                'subscription_tab' => [
                    'plan' => 'Business',
                    'billing' => 'Monthly',
                    'renews' => 'November 1, 2026',
                    'seats' => '5 active members',
                    'notes' => 'Agency support enabled for Hirebridge Talent engagements.',
                ],
                'members' => [
                    ['name' => 'Olivia Bennett', 'role' => 'Company Admin / HR', 'scope' => 'All Northstar'],
                    ['name' => 'Daniel Brooks', 'role' => 'Hiring manager', 'scope' => 'Product & Design'],
                    ['name' => 'Alex Morgan', 'role' => 'Interviewer', 'scope' => 'Assigned interviews'],
                    ['name' => 'Jordan Lee', 'role' => 'Hiring manager', 'scope' => 'Customer Experience'],
                    ['name' => 'Jamie Rivera', 'role' => 'Agency recruiter', 'scope' => 'Assigned submissions only'],
                ],
                'usage_tab' => [
                    'open_jobs' => '3',
                    'candidates' => '28',
                    'members' => '5',
                    'interviews_week' => '2 upcoming',
                    'scorecards_pending' => '1 overdue (Owen Clarke · NS-205)',
                ],
                'suspend_reasons' => [
                    'policy_review' => 'Policy or terms review',
                    'verification_failed' => 'Verification failed',
                    'support_case' => 'Active support case',
                    'billing_hold' => 'Billing hold',
                ],
            ],
            'org-ht-001' => [
                'code' => 'ORG-HT-001',
                'workspace_label_key' => 'agency_workspace',
                'created_on' => 'August 12, 2026',
                'lifecycle_key' => 'active',
                'verified_badge_key' => 'business_verified',
                'detail_level' => 'full',
                'profile' => [
                    'industry_location' => 'Recruitment services · Remote-first',
                    'hiring_models' => 'Agency-managed submissions',
                    'subscription' => 'Agency partner · Monthly · Renews November 1',
                    'verification' => 'Agency partner agreement on file',
                    'usage' => '4 active engagements · 8 assigned roles',
                    'agency_relationship' => 'Client workspaces include Northstar Labs',
                ],
                'subscription_tab' => [
                    'plan' => 'Agency partner',
                    'billing' => 'Monthly',
                    'renews' => 'November 1, 2026',
                    'seats' => 'Partner recruiters on assigned engagements',
                    'notes' => 'Client interview and offer decisions remain with company workspaces.',
                ],
                'members' => [
                    ['name' => 'Jamie Rivera', 'role' => 'Agency recruiter', 'scope' => 'NS-204 / NS-205'],
                    ['name' => 'Erin James', 'role' => 'Agency recruiter', 'scope' => 'Assigned submissions'],
                ],
                'usage_tab' => [
                    'open_jobs' => '8 assigned roles',
                    'candidates' => 'Consented submissions only',
                    'members' => '2 active recruiters',
                    'interviews_week' => 'Coordination with Northstar',
                    'scorecards_pending' => 'None for this agency workspace',
                ],
                'suspend_reasons' => [
                    'policy_review' => 'Policy or terms review',
                    'verification_failed' => 'Verification failed',
                    'support_case' => 'Active support case',
                    'billing_hold' => 'Billing hold',
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $base
     * @return array<string, mixed>
     */
    private static function limitedOrganizationProfile(array $base): array
    {
        $code = strtoupper(str_replace('org-', 'ORG-', (string) $base['id']));
        $isAgency = ($base['type'] ?? '') === 'agency';
        $verified = ($base['verification'] ?? '') === 'verified_active';
        $pending = ($base['verification'] ?? '') === 'pending';
        $suspended = ($base['verification'] ?? '') === 'suspended';

        $lifecycle = match (true) {
            $suspended => 'suspended',
            ($base['tab'] ?? '') === 'archived' => 'archived',
            $pending => 'pending',
            default => 'active',
        };

        return [
            'code' => $code,
            'workspace_label_key' => $isAgency ? 'agency_workspace' : 'company_workspace',
            'created_on' => '—',
            'lifecycle_key' => $lifecycle,
            'verified_badge_key' => $verified ? 'business_verified' : ($pending ? 'verification_pending' : 'not_verified'),
            'detail_level' => 'limited',
            'profile' => [
                'industry_location' => 'Limited Admin overview · Details expand in later integration',
                'hiring_models' => $isAgency ? 'Agency-managed submissions' : 'Direct hiring',
                'subscription' => match ($base['plan'] ?? '') {
                    'growth' => 'Growth',
                    'agency_partner' => 'Agency partner',
                    default => 'Business',
                },
                'verification' => match ($base['verification'] ?? '') {
                    'pending' => 'Verification pending · business details under review',
                    'suspended' => 'Workspace suspended · support review required',
                    'archived' => 'Archived workspace · read-only historical record',
                    default => 'Verified · Active',
                },
                'usage' => ($base['action_kind'] ?? '') === 'roles'
                    ? (($base['active_count'] ?? 0).' assigned roles')
                    : (($base['active_count'] ?? 0).' open jobs'),
                'agency_relationship' => $isAgency ? 'Agency partner workspace' : 'No agency relationship on file in this overview',
            ],
            'subscription_tab' => [
                'plan' => match ($base['plan'] ?? '') {
                    'growth' => 'Growth',
                    'agency_partner' => 'Agency partner',
                    default => 'Business',
                },
                'billing' => 'Not expanded in this overview',
                'renews' => '—',
                'seats' => '—',
                'notes' => 'Full subscription history opens after backend billing integration.',
            ],
            'members' => [
                [
                    'name' => (string) $base['owner'],
                    'role' => $isAgency ? 'Agency contact' : 'Workspace owner',
                    'scope' => (string) $base['domain'],
                ],
            ],
            'usage_tab' => [
                'open_jobs' => (string) ($base['active_count'] ?? 0),
                'candidates' => 'Not expanded',
                'members' => '1 listed contact',
                'interviews_week' => '—',
                'scorecards_pending' => '—',
            ],
            'suspend_reasons' => [
                'policy_review' => 'Policy or terms review',
                'verification_failed' => 'Verification failed',
                'support_case' => 'Active support case',
                'billing_hold' => 'Billing hold',
            ],
        ];
    }

    /**
     * Frame 48 — Job moderation queue.
     * Navigable set matches the three pending policy reviews in the design.
     *
     * @return array<string, mixed>
     */
    public static function jobsModerationIndex(): array
    {
        $jobs = self::moderationJobs();
        $pending = count(array_filter($jobs, static fn (array $j): bool => ($j['status'] ?? '') === 'pending'));

        return [
            'default_status' => 'pending',
            'default_job_id' => $jobs[0]['id'] ?? null,
            'status_filters' => [
                ['key' => 'pending', 'count' => $pending],
                ['key' => 'changes_requested', 'count' => 0],
                ['key' => 'approved', 'count' => 0],
                ['key' => 'all', 'count' => count($jobs)],
            ],
            'reason_filters' => [
                'all',
                'salary',
                'verification',
                'employment_type',
            ],
            'jobs' => $jobs,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function moderationJobs(): array
    {
        return [
            [
                'id' => 'job-lf-091',
                'code' => 'LF-091',
                'title' => 'Product Analyst',
                'organization' => 'Lumen Financial',
                'org_id' => 'org-lf-001',
                'location' => 'Austin hybrid',
                'employment_type' => 'Full-time',
                'salary_label' => 'Not provided',
                'status' => 'pending',
                'reason_key' => 'salary',
                'submitted_at' => 'Oct 20 · 9:30 AM',
                'submitted_by' => 'Casey Tran',
                'policy_title_key' => 'salary_transparency',
                'policy_body' => 'The role requires an annual base salary range before publication. The description references “competitive pay” without a range.',
                'review_note_default' => 'Please add the annual base salary minimum and maximum in USD. Bonus or equity details should be listed separately.',
                'search' => strtolower('LF-091 Product Analyst Lumen Financial Casey Tran salary'),
                'sort_time' => '2026-10-20T09:30:00',
            ],
            [
                'id' => 'job-or-014',
                'code' => 'OR-014',
                'title' => 'Account Executive',
                'organization' => 'Orbit Analytics',
                'org_id' => 'org-oa-001',
                'location' => 'Remote',
                'employment_type' => 'Full-time',
                'salary_label' => 'Range on file',
                'status' => 'pending',
                'reason_key' => 'verification',
                'submitted_at' => 'Oct 19 · 4:18 PM',
                'submitted_by' => 'Pat Okonkwo',
                'policy_title_key' => 'employer_verification',
                'policy_body' => 'Orbit Analytics is still pending business verification. Listings cannot go live until the organization verification review is complete.',
                'review_note_default' => 'Publication remains paused until business verification is approved. Confirm domain ownership and legal entity details with the organization owner.',
                'search' => strtolower('OR-014 Account Executive Orbit Analytics Pat Okonkwo verification'),
                'sort_time' => '2026-10-19T16:18:00',
            ],
            [
                'id' => 'job-cd-008',
                'code' => 'CD-008',
                'title' => 'UX Designer',
                'organization' => 'Cedar Systems',
                'org_id' => 'org-cd-001',
                'location' => 'Hybrid',
                'employment_type' => 'Unclear',
                'salary_label' => 'Range on file',
                'status' => 'pending',
                'reason_key' => 'employment_type',
                'submitted_at' => 'Oct 19 · 2:40 PM',
                'submitted_by' => 'Riley Quinn',
                'policy_title_key' => 'employment_type',
                'policy_body' => 'The listing does not clearly state employment type (full-time, contract, or intern). Candidates need a concrete engagement type before publication.',
                'review_note_default' => 'Please clarify employment type and expected weekly hours before resubmitting for publication review.',
                'search' => strtolower('CD-008 UX Designer Cedar Systems Riley Quinn employment'),
                'sort_time' => '2026-10-19T14:40:00',
            ],
        ];
    }

    /**
     * Frame 49 — Agency operations.
     * Navigable engagements match the design list (not a fabricated larger population).
     *
     * @return array<string, mixed>
     */
    public static function agenciesIndex(): array
    {
        $engagements = self::agencyEngagements();
        $active = count(array_filter($engagements, static fn (array $e): bool => ($e['status'] ?? '') === 'active'));

        $agencies = [];
        foreach ($engagements as $engagement) {
            $agencies[$engagement['agency']] = true;
        }

        return [
            'default_status' => 'active',
            'default_engagement_id' => $engagements[0]['id'] ?? null,
            // Snapshot cards: submission/coordination counts; engagement list is the navigable set below.
            'stats' => [
                'active_engagements' => (string) $active,
                'active_meta' => '3 agency workspaces',
                'pending_review' => '4',
                'pending_meta' => '2 submissions for Northstar',
                'needs_coordination' => '1',
                'coordination_meta' => 'Interview availability outstanding',
            ],
            'status_filters' => [
                ['key' => 'active', 'count' => $active],
                ['key' => 'all', 'count' => count($engagements)],
            ],
            'agency_filters' => array_keys($agencies),
            'engagements' => $engagements,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function agencyEngagements(): array
    {
        return [
            [
                'id' => 'eng-104',
                'code' => 'ENG-104',
                'client' => 'Northstar Labs',
                'client_org_id' => 'org-ns-001',
                'job_code' => 'NS-204',
                'job_title' => 'Senior Product Designer',
                'model' => 'Contingent search',
                'agency' => 'Hirebridge Talent',
                'agency_org_id' => 'org-ht-001',
                'recruiter' => 'Jamie Rivera',
                'client_owner' => 'Olivia Bennett',
                'submissions_label' => '5 total · 1 hired',
                'service_status' => 'Active · Weekly review Oct 23',
                'status' => 'active',
                'pending_client_review' => true,
                'needs_coordination' => false,
                'scope' => 'Scope: senior product design search, qualification, and interview coordination. Northstar retains interview and offer decisions.',
                'status_log' => [
                    'Brief approved Oct 7',
                    'Weekly review: Fridays',
                    'Terms: Agreed proposal on file',
                    'Next: Review interview feedback',
                ],
                'candidates' => [
                    ['name' => 'Marcus Reed', 'stage' => 'Applied', 'next' => 'Consent recorded · Await client review'],
                    ['name' => 'Ella Davis', 'stage' => 'Screening', 'next' => 'Consent recorded · Recruiter follow-up'],
                    ['name' => 'Luis Romero', 'stage' => 'Interview', 'next' => 'Consent recorded · Oct 21 team interview'],
                    ['name' => 'Nora Kim', 'stage' => 'Offer', 'next' => 'Consent recorded · Offer coordination'],
                    ['name' => 'Grace Liu', 'stage' => 'Hired', 'next' => 'Consent recorded · Start-date support'],
                ],
                'search' => strtolower('ENG-104 Northstar Labs NS-204 Senior Product Designer Jamie Rivera Hirebridge Talent'),
                'sort_code' => 'ENG-104',
            ],
            [
                'id' => 'eng-105',
                'code' => 'ENG-105',
                'client' => 'Northstar Labs',
                'client_org_id' => 'org-ns-001',
                'job_code' => 'NS-205',
                'job_title' => 'Senior Software Engineer',
                'model' => 'Retained search',
                'agency' => 'Hirebridge Talent',
                'agency_org_id' => 'org-ht-001',
                'recruiter' => 'Jamie Rivera',
                'client_owner' => 'Olivia Bennett',
                'submissions_label' => '6 total · 2 awaiting review',
                'service_status' => 'Active · Client review needed',
                'status' => 'active',
                'pending_client_review' => true,
                'needs_coordination' => false,
                'scope' => 'Scope: senior engineering search with retained search terms. Northstar retains interview and offer decisions.',
                'status_log' => [
                    'Brief approved Oct 5',
                    'Client review needed for 2 submissions',
                    'Terms: Retained proposal on file',
                    'Next: Client shortlist feedback',
                ],
                'candidates' => [
                    ['name' => 'Owen Clarke', 'stage' => 'Interview', 'next' => 'Consent recorded · Scorecard overdue'],
                    ['name' => 'Priya Nair', 'stage' => 'Screening', 'next' => 'Consent recorded · Await client review'],
                    ['name' => 'Tom Alvarez', 'stage' => 'Applied', 'next' => 'Consent recorded · Recruiter follow-up'],
                ],
                'search' => strtolower('ENG-105 Northstar Labs NS-205 Senior Software Engineer Jamie Rivera Hirebridge Talent'),
                'sort_code' => 'ENG-105',
            ],
            [
                'id' => 'eng-106',
                'code' => 'ENG-106',
                'client' => 'Atlas Health',
                'client_org_id' => 'org-ah-001',
                'job_code' => 'AH-312',
                'job_title' => 'Senior UX Designer',
                'model' => 'Contingent search',
                'agency' => 'Peoplecraft',
                'agency_org_id' => 'org-pc-001',
                'recruiter' => 'Erin James',
                'client_owner' => 'Sam Wilson',
                'submissions_label' => '4 total · 1 screening',
                'service_status' => 'Active · Search in progress',
                'status' => 'active',
                'pending_client_review' => false,
                'needs_coordination' => true,
                'scope' => 'Scope: senior UX search for Atlas Health product teams. Client retains interview and offer decisions.',
                'status_log' => [
                    'Brief approved Oct 10',
                    'Interview availability outstanding',
                    'Terms: Contingent proposal on file',
                    'Next: Confirm interview windows',
                ],
                'candidates' => [
                    ['name' => 'Avery Cole', 'stage' => 'Screening', 'next' => 'Consent recorded · Schedule interview'],
                    ['name' => 'Jordan Blake', 'stage' => 'Applied', 'next' => 'Consent recorded · Recruiter screen'],
                ],
                'search' => strtolower('ENG-106 Atlas Health AH-312 Senior UX Designer Erin James Peoplecraft'),
                'sort_code' => 'ENG-106',
            ],
            [
                'id' => 'eng-107',
                'code' => 'ENG-107',
                'client' => 'Meridian Studio',
                'client_org_id' => 'org-ms-001',
                'job_code' => 'MS-108',
                'job_title' => 'Lead Product Designer',
                'model' => 'Retained search',
                'agency' => 'Hirebridge Talent',
                'agency_org_id' => 'org-ht-001',
                'recruiter' => 'Jamie Rivera',
                'client_owner' => 'Jamie Rivera',
                'submissions_label' => '3 total · 1 offer',
                'service_status' => 'Offer coordination',
                'status' => 'active',
                'pending_client_review' => true,
                'needs_coordination' => false,
                'scope' => 'Scope: lead product design retained search. Meridian retains final offer decisions.',
                'status_log' => [
                    'Brief approved Sep 28',
                    'Offer coordination in progress',
                    'Terms: Retained proposal on file',
                    'Next: Confirm offer package',
                ],
                'candidates' => [
                    ['name' => 'Riley Quinn', 'stage' => 'Offer', 'next' => 'Consent recorded · Offer coordination'],
                    ['name' => 'Sam Ortiz', 'stage' => 'Interview', 'next' => 'Consent recorded · Final loop'],
                ],
                'search' => strtolower('ENG-107 Meridian Studio MS-108 Lead Product Designer Jamie Rivera Hirebridge Talent'),
                'sort_code' => 'ENG-107',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function userRow(
        string $id,
        string $name,
        string $email,
        string $organization,
        ?string $orgId,
        string $roleKey,
        string $state,
        string $lastActive,
        string $actionKey,
    ): array {
        return [
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'organization' => $organization,
            'org_id' => $orgId,
            'role_key' => $roleKey,
            'state' => $state,
            'last_active' => $lastActive,
            'action_key' => $actionKey,
            'search' => strtolower($name.' '.$email.' '.$organization.' '.$roleKey),
        ];
    }

    /**
     * Frame 50 — Platform settings & audit.
     *
     * @return array<string, mixed>
     */
    public static function settingsIndex(): array
    {
        $events = self::auditEvents();

        return [
            'default_tab' => 'audit',
            'page_size' => 8,
            'default_range' => 'oct19_20',
            'default_result' => 'all',
            'default_sort' => 'newest',
            'security_card' => [
                'status_key' => 'enforced',
                'bullets' => [
                    'mfa_required',
                    'session_expiry',
                    'retention',
                    'data_requests',
                ],
            ],
            'permissions_card' => [
                'bullets' => [
                    'super_admin_accounts',
                    'email_connected',
                    'calendar_connected',
                    'video_connected',
                ],
                'last_sync' => 'Today 9:00 AM',
            ],
            'security_policies' => [
                [
                    'id' => 'mfa',
                    'title_key' => 'policy_mfa_title',
                    'body_key' => 'policy_mfa_body',
                    'state_key' => 'enforced',
                ],
                [
                    'id' => 'session',
                    'title_key' => 'policy_session_title',
                    'body_key' => 'policy_session_body',
                    'state_key' => 'enforced',
                ],
                [
                    'id' => 'retention',
                    'title_key' => 'policy_retention_title',
                    'body_key' => 'policy_retention_body',
                    'state_key' => 'documented',
                ],
                [
                    'id' => 'privacy',
                    'title_key' => 'policy_privacy_title',
                    'body_key' => 'policy_privacy_body',
                    'state_key' => 'routed',
                ],
            ],
            'roles' => [
                [
                    'id' => 'super_admin',
                    'name' => 'Super Admin',
                    'accounts' => 2,
                    'scopes' => ['Platform operations', 'Job moderation', 'Agency oversight', 'Audit review'],
                    'notes_key' => 'role_super_admin_notes',
                ],
                [
                    'id' => 'company_admin',
                    'name' => 'Company admin',
                    'accounts' => 4,
                    'scopes' => ['Company workspace', 'Jobs & pipeline', 'Offers', 'Team access'],
                    'notes_key' => 'role_company_admin_notes',
                ],
                [
                    'id' => 'agency_admin',
                    'name' => 'Agency admin',
                    'accounts' => 2,
                    'scopes' => ['Agency workspace', 'Client engagements', 'Consented submissions'],
                    'notes_key' => 'role_agency_admin_notes',
                ],
                [
                    'id' => 'interviewer',
                    'name' => 'Interviewer',
                    'accounts' => 3,
                    'scopes' => ['Assigned interviews', 'Scorecards', 'Preparation notes'],
                    'notes_key' => 'role_interviewer_notes',
                ],
            ],
            'integrations' => [
                [
                    'id' => 'email',
                    'name' => 'Email delivery',
                    'provider' => 'Platform mail',
                    'status' => 'connected',
                    'capability_key' => 'integration_email_cap',
                    'last_sync' => 'Today 9:00 AM',
                ],
                [
                    'id' => 'calendar',
                    'name' => 'Calendar sync',
                    'provider' => 'Workspace calendar',
                    'status' => 'connected',
                    'capability_key' => 'integration_calendar_cap',
                    'last_sync' => 'Today 8:55 AM',
                ],
                [
                    'id' => 'video',
                    'name' => 'Video meetings',
                    'provider' => 'Meeting links',
                    'status' => 'connected',
                    'capability_key' => 'integration_video_cap',
                    'last_sync' => 'Today 8:30 AM',
                ],
                [
                    'id' => 'hris',
                    'name' => 'HRIS export',
                    'provider' => 'Not configured',
                    'status' => 'unavailable',
                    'capability_key' => 'integration_hris_cap',
                    'last_sync' => '—',
                ],
            ],
            'events' => $events,
        ];
    }

    /**
     * Presentation audit history only — not live platform telemetry.
     *
     * @return list<array<string, mixed>>
     */
    public static function auditEvents(): array
    {
        return [
            self::auditRow(
                'aud-001',
                '2026-10-20T09:42:00-05:00',
                'Oct 20 · 9:42 AM',
                'oct19_20',
                'Priya Shah',
                'usr-priya',
                'moderation',
                'Requested job changes',
                'LF-091 · Lumen Financial',
                'job',
                'LF-091',
                'success',
                'Prepared employer-facing salary transparency correction for LF-091.',
            ),
            self::auditRow(
                'aud-002',
                '2026-10-20T09:18:00-05:00',
                'Oct 20 · 9:18 AM',
                'oct19_20',
                'Olivia Bennett',
                'usr-olivia',
                'access',
                'Updated access scope',
                'Alex Morgan · Northstar',
                'user',
                'usr-alex',
                'success',
                'Adjusted interviewer scope for Alex Morgan within Northstar Labs.',
            ),
            self::auditRow(
                'aud-003',
                '2026-10-20T08:55:00-05:00',
                'Oct 20 · 8:55 AM',
                'oct19_20',
                'Priya Shah',
                'usr-priya',
                'agency',
                'Assigned agency recruiter',
                'ENG-105 · Jamie Rivera',
                'engagement',
                'ENG-105',
                'success',
                'Assigned Jamie Rivera (Hirebridge Talent) to ENG-105 / NS-205.',
            ),
            self::auditRow(
                'aud-004',
                '2026-10-20T08:30:00-05:00',
                'Oct 20 · 8:30 AM',
                'oct19_20',
                'Alex Morgan',
                'usr-alex',
                'integrations',
                'Connected calendar',
                'Northstar · Personal calendar',
                'organization',
                'ORG-NS-001',
                'success',
                'Linked personal calendar for interview scheduling at Northstar Labs.',
            ),
            self::auditRow(
                'aud-005',
                '2026-10-19T16:22:00-05:00',
                'Oct 19 · 4:22 PM',
                'oct19_20',
                'Priya Shah',
                'usr-priya',
                'identity',
                'Reviewed company identity',
                'Northstar · ORG-NS-001',
                'organization',
                'ORG-NS-001',
                'success',
                'Completed identity review for Northstar Labs (ORG-NS-001).',
            ),
            self::auditRow(
                'aud-006',
                '2026-10-19T15:10:00-05:00',
                'Oct 19 · 3:10 PM',
                'oct19_20',
                'Olivia Bennett',
                'usr-olivia',
                'offers',
                'Created draft offer',
                'Ethan Cole · OF-2026-021',
                'offer',
                'OF-2026-021',
                'success',
                'Drafted offer OF-2026-021 for Ethan Cole at Northstar Labs.',
            ),
            self::auditRow(
                'aud-007',
                '2026-10-19T14:30:00-05:00',
                'Oct 19 · 2:30 PM',
                'oct19_20',
                'Olivia Bennett',
                'usr-olivia',
                'interviews',
                'Confirmed interview',
                'Maya Chen · NS-204',
                'interview',
                'NS-204',
                'success',
                'Confirmed interview slot for Maya Chen on NS-204 Senior Product Designer.',
            ),
            self::auditRow(
                'aud-008',
                '2026-10-19T13:05:00-05:00',
                'Oct 19 · 1:05 PM',
                'oct19_20',
                'Morgan Price',
                'usr-morgan',
                'security',
                'Attempted workspace access',
                'Pinecrest Recruiting',
                'organization',
                'ORG-PC-001',
                'blocked',
                'Blocked workspace access attempt for suspended organization Pinecrest Recruiting.',
            ),
            self::auditRow(
                'aud-009',
                '2026-10-19T11:40:00-05:00',
                'Oct 19 · 11:40 AM',
                'oct19_20',
                'Priya Shah',
                'usr-priya',
                'moderation',
                'Flagged employment type',
                'CD-008 · Cedar Systems',
                'job',
                'CD-008',
                'success',
                'Queued CD-008 UX Designer for employment-type clarification.',
            ),
            self::auditRow(
                'aud-010',
                '2026-10-19T10:15:00-05:00',
                'Oct 19 · 10:15 AM',
                'oct19_20',
                'Jamie Rivera',
                'usr-jamie',
                'agency',
                'Submitted consented candidate',
                'ENG-104 · Marcus Reed',
                'engagement',
                'ENG-104',
                'success',
                'Recorded consented submission for Marcus Reed on ENG-104 / NS-204.',
            ),
            self::auditRow(
                'aud-011',
                '2026-10-18T16:50:00-05:00',
                'Oct 18 · 4:50 PM',
                'earlier',
                'Priya Shah',
                'usr-priya',
                'access',
                'Reviewed suspended account',
                'Support case · Users & access',
                'user',
                'usr-support',
                'success',
                'Opened suspended-user support case from the Users & access queue.',
            ),
            self::auditRow(
                'aud-012',
                '2026-10-18T14:20:00-05:00',
                'Oct 18 · 2:20 PM',
                'earlier',
                'Olivia Bennett',
                'usr-olivia',
                'interviews',
                'Rescheduled interview',
                'Luis Romero · NS-204',
                'interview',
                'NS-204',
                'success',
                'Moved Luis Romero interview on NS-204 after client availability update.',
            ),
            self::auditRow(
                'aud-013',
                '2026-10-18T11:05:00-05:00',
                'Oct 18 · 11:05 AM',
                'earlier',
                'Priya Shah',
                'usr-priya',
                'identity',
                'Verification follow-up',
                'Orbit Analytics · ORG-OR-001',
                'organization',
                'ORG-OR-001',
                'success',
                'Requested additional verification details for Orbit Analytics.',
            ),
            self::auditRow(
                'aud-014',
                '2026-10-17T15:30:00-05:00',
                'Oct 17 · 3:30 PM',
                'earlier',
                'Alex Morgan',
                'usr-alex',
                'integrations',
                'Connected video meetings',
                'Northstar · Meeting links',
                'organization',
                'ORG-NS-001',
                'success',
                'Enabled meeting-link generation for Northstar interview panels.',
            ),
            self::auditRow(
                'aud-015',
                '2026-10-17T09:12:00-05:00',
                'Oct 17 · 9:12 AM',
                'earlier',
                'Morgan Price',
                'usr-morgan',
                'security',
                'Attempted privileged export',
                'Restricted settings',
                'settings',
                'restricted',
                'blocked',
                'Blocked privileged data-export attempt without documented reason.',
            ),
            self::auditRow(
                'aud-016',
                '2026-10-16T13:45:00-05:00',
                'Oct 16 · 1:45 PM',
                'earlier',
                'Jamie Rivera',
                'usr-jamie',
                'agency',
                'Updated engagement brief',
                'ENG-107 · Meridian Studio',
                'engagement',
                'ENG-107',
                'success',
                'Updated weekly-review notes for ENG-107 Meridian Studio engagement.',
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function auditRow(
        string $id,
        string $sortTime,
        string $timeLabel,
        string $range,
        string $actor,
        string $actorId,
        string $type,
        string $action,
        string $object,
        string $targetType,
        string $targetId,
        string $result,
        string $description,
    ): array {
        return [
            'id' => $id,
            'sort_time' => $sortTime,
            'time' => $timeLabel,
            'range' => $range,
            'actor' => $actor,
            'actor_id' => $actorId,
            'type' => $type,
            'action' => $action,
            'object' => $object,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'result' => $result,
            'description' => $description,
            'search' => strtolower($actor.' '.$action.' '.$object.' '.$targetId.' '.$type.' '.$result),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function orgRow(
        string $id,
        string $name,
        string $type,
        string $plan,
        string $verification,
        string $owner,
        string $domain,
        int $activeCount,
        string $actionKind,
        string $tab,
    ): array {
        return [
            'id' => $id,
            'code' => strtoupper(str_replace('org-', 'ORG-', $id)),
            'name' => $name,
            'type' => $type,
            'plan' => $plan,
            'verification' => $verification,
            'owner' => $owner,
            'domain' => $domain,
            'active_count' => $activeCount,
            'action_kind' => $actionKind,
            'tab' => $tab,
            'search' => strtolower($name.' '.$domain.' '.$owner),
        ];
    }
}
