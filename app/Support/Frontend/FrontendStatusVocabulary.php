<?php

namespace App\Support\Frontend;

/**
 * Frontend presentation status labels and proposed domain dictionaries.
 *
 * Separates database/workflow keys from display labels.
 * Status sets are domain-specific — do not collapse into one universal enum.
 *
 * Proposed pending Backend Team approval (Phase F9F contracts).
 */
class FrontendStatusVocabulary
{
    /**
     * Organization verification / lifecycle (WF2 / companies).
     *
     * @return array<string, array{label: string, variant: string}>
     */
    public static function organization(): array
    {
        return [
            'pending' => [
                'label' => __('status_vocab.organization.pending'),
                'variant' => 'pending',
            ],
            'approved' => [
                'label' => __('status_vocab.organization.approved'),
                'variant' => 'success',
            ],
            'verified_active' => [
                'label' => __('status_vocab.organization.approved'),
                'variant' => 'success',
            ],
            'rejected' => [
                'label' => __('status_vocab.organization.rejected'),
                'variant' => 'error',
            ],
            'suspended' => [
                'label' => __('status_vocab.organization.suspended'),
                'variant' => 'error',
            ],
            'archived' => [
                'label' => __('status_vocab.organization.archived'),
                'variant' => 'neutral',
            ],
        ];
    }

    /**
     * Company membership / join request (WF2).
     *
     * @return array<string, array{label: string, variant: string}>
     */
    public static function membership(): array
    {
        return [
            'none' => [
                'label' => __('status_vocab.membership.none'),
                'variant' => 'neutral',
            ],
            'pending' => [
                'label' => __('status_vocab.membership.pending'),
                'variant' => 'pending',
            ],
            'approved' => [
                'label' => __('status_vocab.membership.approved'),
                'variant' => 'success',
            ],
            'rejected' => [
                'label' => __('status_vocab.membership.rejected'),
                'variant' => 'error',
            ],
        ];
    }

    /**
     * User account state (users table + Admin directory).
     *
     * @return array<string, array{label: string, variant: string}>
     */
    public static function account(): array
    {
        return [
            'active' => [
                'label' => __('status_vocab.account.active'),
                'variant' => 'success',
            ],
            'invited' => [
                'label' => __('status_vocab.account.invited'),
                'variant' => 'pending',
            ],
            'suspended' => [
                'label' => __('status_vocab.account.suspended'),
                'variant' => 'error',
            ],
        ];
    }

    /**
     * Admin job moderation presentation states (may differ from company job publication status).
     *
     * @return array<string, array{label: string, variant: string, note: string}>
     */
    public static function jobModeration(): array
    {
        return [
            'pending' => [
                'label' => __('status_vocab.job_moderation.pending'),
                'variant' => 'pending',
                'note' => __('status_vocab.job_moderation.pending_note'),
            ],
            'changes_requested' => [
                'label' => __('status_vocab.job_moderation.changes_requested'),
                'variant' => 'info',
                'note' => __('status_vocab.job_moderation.changes_note'),
            ],
            'approved' => [
                'label' => __('status_vocab.job_moderation.approved'),
                'variant' => 'success',
                'note' => __('status_vocab.job_moderation.approved_note'),
            ],
            'rejected' => [
                'label' => __('status_vocab.job_moderation.rejected'),
                'variant' => 'error',
                'note' => __('status_vocab.job_moderation.rejected_note'),
            ],
        ];
    }

    /**
     * Proposed application.status dictionary (12 keys from F8 audit / handoff).
     * Confirm exact set with Backend before persistence.
     *
     * @return array<string, array{label: string, variant: string}>
     */
    public static function application(): array
    {
        return [
            'submitted' => [
                'label' => __('status_vocab.application.submitted'),
                'variant' => 'info',
            ],
            'ai_review' => [
                'label' => __('status_vocab.application.ai_review'),
                'variant' => 'pending',
            ],
            'ai_failed' => [
                'label' => __('status_vocab.application.ai_failed'),
                'variant' => 'warning',
            ],
            'shortlisted' => [
                'label' => __('status_vocab.application.shortlisted'),
                'variant' => 'success',
            ],
            'rejected' => [
                'label' => __('status_vocab.application.rejected'),
                'variant' => 'error',
            ],
            'exam' => [
                'label' => __('status_vocab.application.exam'),
                'variant' => 'pending',
            ],
            'interview' => [
                'label' => __('status_vocab.application.interview'),
                'variant' => 'pending',
            ],
            'offer' => [
                'label' => __('status_vocab.application.offer'),
                'variant' => 'info',
            ],
            'hired' => [
                'label' => __('status_vocab.application.hired'),
                'variant' => 'success',
            ],
            'withdrawn' => [
                'label' => __('status_vocab.application.withdrawn'),
                'variant' => 'neutral',
            ],
            'expired' => [
                'label' => __('status_vocab.application.expired'),
                'variant' => 'neutral',
            ],
            'archived' => [
                'label' => __('status_vocab.application.archived'),
                'variant' => 'neutral',
            ],
        ];
    }

    /**
     * Pipeline board stage labels used in Company UI (presentation grouping, not full application.status).
     *
     * @return array<string, string>
     */
    public static function applicationStageLabels(): array
    {
        return [
            'applied' => __('status_vocab.application.applied'),
            'screening' => __('status_vocab.application.screening'),
            'assessment' => __('status_vocab.application.assessment'),
            'interview' => __('status_vocab.application.interview'),
            'offer' => __('status_vocab.application.offer'),
            'hired' => __('status_vocab.application.hired'),
            'rejected' => __('status_vocab.application.rejected'),
            'withdrawn' => __('status_vocab.application.withdrawn'),
        ];
    }

    /**
     * Offer lifecycle display statuses.
     *
     * @return array<string, array{label: string, variant: string}>
     */
    public static function offer(): array
    {
        return [
            'draft' => [
                'label' => __('status_vocab.offer.draft'),
                'variant' => 'neutral',
            ],
            'pending_approval' => [
                'label' => __('status_vocab.offer.pending_approval'),
                'variant' => 'pending',
            ],
            'sent' => [
                'label' => __('status_vocab.offer.sent'),
                'variant' => 'info',
            ],
            'accepted' => [
                'label' => __('status_vocab.offer.accepted'),
                'variant' => 'success',
            ],
            'rejected' => [
                'label' => __('status_vocab.offer.rejected'),
                'variant' => 'error',
            ],
            'expired' => [
                'label' => __('status_vocab.offer.expired'),
                'variant' => 'neutral',
            ],
            'withdrawn' => [
                'label' => __('status_vocab.offer.withdrawn'),
                'variant' => 'neutral',
            ],
        ];
    }

    /**
     * Interview booking / session statuses.
     *
     * @return array<string, array{label: string, variant: string}>
     */
    public static function interview(): array
    {
        return [
            'proposed' => [
                'label' => __('status_vocab.interview.proposed'),
                'variant' => 'pending',
            ],
            'confirmed' => [
                'label' => __('status_vocab.interview.confirmed'),
                'variant' => 'success',
            ],
            'completed' => [
                'label' => __('status_vocab.interview.completed'),
                'variant' => 'neutral',
            ],
            'cancelled' => [
                'label' => __('status_vocab.interview.cancelled'),
                'variant' => 'error',
            ],
            'no_show' => [
                'label' => __('status_vocab.interview.no_show'),
                'variant' => 'warning',
            ],
        ];
    }

    /**
     * ERD work_mode enum ↔ display labels (onsite|remote|hybrid).
     *
     * @return array<string, string>
     */
    public static function workModeLabels(): array
    {
        return [
            'onsite' => __('status_vocab.work_mode.onsite'),
            'on-site' => __('status_vocab.work_mode.onsite'),
            'On-site' => __('status_vocab.work_mode.onsite'),
            'remote' => __('status_vocab.work_mode.remote'),
            'Remote' => __('status_vocab.work_mode.remote'),
            'hybrid' => __('status_vocab.work_mode.hybrid'),
            'Hybrid' => __('status_vocab.work_mode.hybrid'),
        ];
    }

    /**
     * Normalize a display or storage work-mode value to ERD-style key.
     */
    public static function workModeKey(string $value): string
    {
        $normalized = strtolower(trim(str_replace(['_', ' '], '-', $value)));

        return match (true) {
            str_contains($normalized, 'remote') && ! str_contains($normalized, 'hybrid') => 'remote',
            str_contains($normalized, 'onsite') || str_contains($normalized, 'on-site') => 'onsite',
            default => 'hybrid',
        };
    }

    /**
     * Account type enum (ERD: candidate|company). Platform Super Admin is a role, not account_type.
     *
     * @return array<string, string>
     */
    public static function accountTypeLabels(): array
    {
        return [
            'candidate' => __('status_vocab.account_type.candidate'),
            'company' => __('status_vocab.account_type.company'),
        ];
    }

    /**
     * @param  array<string, array{label: string, variant?: string}>  $map
     * @return array{label: string, variant: string}
     */
    public static function resolve(array $map, string $key, string $fallbackLabel = ''): array
    {
        if (isset($map[$key])) {
            return [
                'label' => $map[$key]['label'],
                'variant' => $map[$key]['variant'] ?? 'neutral',
            ];
        }

        return [
            'label' => $fallbackLabel !== '' ? $fallbackLabel : $key,
            'variant' => 'neutral',
        ];
    }
}
