<?php

namespace App\Support\Frontend;

use Illuminate\Support\Facades\Route;

/**
 * Actor-scoped notification samples (F9E / F8-G31).
 * Presentation only — no delivery bus or server read-state.
 */
class MockNotificationData
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function forActor(string $actor): array
    {
        return match ($actor) {
            'company' => self::company(),
            'interviewer' => self::interviewer(),
            'admin' => self::admin(),
            default => self::candidate(),
        };
    }

    public static function storageKey(string $actor): string
    {
        return match ($actor) {
            'company' => 'hb_company_notifications_v1',
            'interviewer' => 'hb_interviewer_notifications_v1',
            'admin' => 'hb_admin_notifications_v1',
            default => 'hb_candidate_notifications_v1',
        };
    }

    public static function viewAllRoute(string $actor): ?string
    {
        return $actor === 'candidate' && Route::has('candidate.notifications.index')
            ? 'candidate.notifications.index'
            : null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function candidate(): array
    {
        return [
            [
                'id' => 'n-cand-interview',
                'event' => 'interview_scheduled',
                'actor' => 'candidate',
                'title' => __('notifications.candidate.interview_title'),
                'body' => __('notifications.candidate.interview_body'),
                'time' => __('notifications.candidate.interview_time'),
                'unread' => true,
                'category' => 'interviews',
                'organization_id' => 'org-ns-001',
                'job_id' => 'ns-204',
                'href' => route('candidate.interviews.index'),
            ],
            [
                'id' => 'n-cand-message',
                'event' => 'hiring_message',
                'actor' => 'candidate',
                'title' => __('notifications.candidate.message_title'),
                'body' => __('notifications.candidate.message_body'),
                'time' => __('notifications.candidate.message_time'),
                'unread' => true,
                'category' => 'messages',
                'organization_id' => 'org-ns-001',
                'job_id' => 'ns-204',
                'href' => route('candidate.messages.index', ['thread' => 'msg-olivia-ns204']),
            ],
            [
                'id' => 'n-cand-membership',
                'event' => 'membership_status',
                'actor' => 'candidate',
                'title' => __('notifications.candidate.membership_title'),
                'body' => __('notifications.candidate.membership_body'),
                'time' => __('notifications.candidate.membership_time'),
                'unread' => false,
                'category' => 'membership',
                'organization_id' => 'org-ns-001',
                'href' => route('candidate.membership'),
            ],
            [
                'id' => 'n-cand-assessment',
                'event' => 'assessment_invited',
                'actor' => 'candidate',
                'title' => __('notifications.candidate.assessment_title'),
                'body' => __('notifications.candidate.assessment_body'),
                'time' => __('notifications.candidate.assessment_time'),
                'unread' => false,
                'category' => 'assessments',
                'organization_id' => 'org-ns-001',
                'job_id' => 'ns-204',
                'href' => route('candidate.assessments.index'),
            ],
            [
                'id' => 'n-cand-offer',
                'event' => 'offer_available',
                'actor' => 'candidate',
                'title' => __('notifications.candidate.offer_title'),
                'body' => __('notifications.candidate.offer_body'),
                'time' => __('notifications.candidate.offer_time'),
                'unread' => false,
                'category' => 'offers',
                'job_id' => 'ms-108',
                'href' => route('candidate.offers.show', 'offer-ms108'),
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function company(): array
    {
        return [
            [
                'id' => 'n-co-membership',
                'event' => 'membership_request',
                'actor' => 'company',
                'title' => __('notifications.company.membership_title'),
                'body' => __('notifications.company.membership_body'),
                'time' => __('notifications.company.membership_time'),
                'unread' => true,
                'category' => 'membership',
                'organization_id' => 'org-ns-001',
                'candidate_id' => 'maya-chen',
                'href' => route('company.employees.requests'),
            ],
            [
                'id' => 'n-co-eval',
                'event' => 'evaluation_ready',
                'actor' => 'company',
                'title' => __('notifications.company.eval_title'),
                'body' => __('notifications.company.eval_body'),
                'time' => __('notifications.company.eval_time'),
                'unread' => true,
                'category' => 'evaluations',
                'organization_id' => 'org-ns-001',
                'job_id' => 'ns-204',
                'candidate_id' => 'ethan-cole',
                // Link applicant context — avoid embedding eval-ethan-cole on unrelated applicant pages via the shared topbar.
                'href' => route('company.applicants.show', 'ethan-cole'),
            ],
            [
                'id' => 'n-co-interview',
                'event' => 'interview_upcoming',
                'actor' => 'company',
                'title' => __('notifications.company.interview_title'),
                'body' => __('notifications.company.interview_body'),
                'time' => __('notifications.company.interview_time'),
                'unread' => false,
                'category' => 'interviews',
                'organization_id' => 'org-ns-001',
                'job_id' => 'ns-204',
                'interview_id' => 'int-ns204-portfolio',
                'href' => route('company.interviews.index'),
            ],
            [
                'id' => 'n-co-org',
                'event' => 'organization_status',
                'actor' => 'company',
                'title' => __('notifications.company.org_title'),
                'body' => __('notifications.company.org_body'),
                'time' => __('notifications.company.org_time'),
                'unread' => false,
                'category' => 'organization',
                'organization_id' => 'org-ns-001',
                'href' => route('company.dashboard'),
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function interviewer(): array
    {
        return [
            [
                'id' => 'n-iv-assigned',
                'event' => 'interview_assigned',
                'actor' => 'interviewer',
                'title' => __('notifications.interviewer.assigned_title'),
                'body' => __('notifications.interviewer.assigned_body'),
                'time' => __('notifications.interviewer.assigned_time'),
                'unread' => true,
                'category' => 'interviews',
                'organization_id' => 'org-ns-001',
                'job_id' => 'ns-204',
                'interview_id' => 'int-ns204-portfolio',
                'candidate_id' => 'maya-chen',
                'href' => route('interviewer.schedule.index'),
            ],
            [
                'id' => 'n-iv-scorecard',
                'event' => 'evaluation_requested',
                'actor' => 'interviewer',
                'title' => __('notifications.interviewer.scorecard_title'),
                'body' => __('notifications.interviewer.scorecard_body'),
                'time' => __('notifications.interviewer.scorecard_time'),
                'unread' => true,
                'category' => 'evaluations',
                'job_id' => 'ns-205',
                'href' => route('interviewer.evaluations.index'),
            ],
            [
                'id' => 'n-iv-reschedule',
                'event' => 'interview_reschedule',
                'actor' => 'interviewer',
                'title' => __('notifications.interviewer.reschedule_title'),
                'body' => __('notifications.interviewer.reschedule_body'),
                'time' => __('notifications.interviewer.reschedule_time'),
                'unread' => false,
                'category' => 'interviews',
                'organization_id' => 'org-ns-001',
                'href' => route('interviewer.dashboard'),
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function admin(): array
    {
        return [
            [
                'id' => 'n-ad-org-pending',
                'event' => 'organization_pending',
                'actor' => 'admin',
                'title' => __('notifications.admin.org_pending_title'),
                'body' => __('notifications.admin.org_pending_body'),
                'time' => __('notifications.admin.org_pending_time'),
                'unread' => true,
                'category' => 'organizations',
                'organization_id' => 'org-cd-001',
                'href' => route('admin.organizations.index', ['status' => 'pending']),
            ],
            [
                'id' => 'n-ad-job-mod',
                'event' => 'job_moderation',
                'actor' => 'admin',
                'title' => __('notifications.admin.job_mod_title'),
                'body' => __('notifications.admin.job_mod_body'),
                'time' => __('notifications.admin.job_mod_time'),
                'unread' => true,
                'category' => 'moderation',
                'organization_id' => 'org-lf-001',
                'href' => route('admin.jobs.index', ['status' => 'pending']),
            ],
            [
                'id' => 'n-ad-suspend',
                'event' => 'organization_suspended',
                'actor' => 'admin',
                'title' => __('notifications.admin.suspend_title'),
                'body' => __('notifications.admin.suspend_body'),
                'time' => __('notifications.admin.suspend_time'),
                'unread' => false,
                'category' => 'organizations',
                'organization_id' => 'org-pr-001',
                'href' => route('admin.organizations.show', 'org-pr-001'),
            ],
        ];
    }
}
