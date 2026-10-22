<?php

namespace App\Support\Frontend;

/**
 * Frontend-only job visibility / eligibility presentation (F9B / WF3).
 * Not authorization. Server-side scoping remains mandatory.
 */
class JobAccessPresentation
{
    public const VISIBILITY_PUBLIC = 'public';

    public const VISIBILITY_PRIVATE = 'private';

    public const MODE_OPEN = 'open';

    public const MODE_ALL_EMPLOYEES = 'all_employees';

    public const MODE_NOMINATED_ONLY = 'nominated_only';

    /**
     * Candidate access demo context via query params.
     * `?membership=none|pending|approved` and `?nomination=yes|no`.
     *
     * @return array{membership_status: string, is_nominated: bool, is_employee_of_org: bool, organization_id: string|null, scenario: string}
     */
    public static function candidateContext(?string $membershipOverride = null, ?string $nominationOverride = null): array
    {
        $membership = $membershipOverride
            ?? (is_string(request()->query('membership')) ? request()->query('membership') : null)
            ?? 'none';

        if (! in_array($membership, ['none', 'pending', 'approved'], true)) {
            $membership = 'none';
        }

        $nomination = $nominationOverride
            ?? (is_string(request()->query('nomination')) ? request()->query('nomination') : null)
            ?? 'no';
        $isNominated = $nomination === 'yes';

        $isEmployee = $membership === 'approved';

        return [
            'membership_status' => $membership,
            'is_nominated' => $isNominated,
            'is_employee_of_org' => $isEmployee,
            'organization_id' => $isEmployee || $membership === 'pending' ? 'org-ns-001' : null,
            'scenario' => $membership.($isNominated ? '+nominated' : ''),
            'frontend_only' => true,
            'authority_note' => 'Eligibility below is a presentation mock. Server authorization is required.',
        ];
    }

    /**
     * Normalize job presentation fields onto a job array.
     *
     * @param  array<string, mixed>  $job
     * @return array<string, mixed>
     */
    public static function normalizeJob(array $job): array
    {
        $visibility = $job['visibility'] ?? self::VISIBILITY_PUBLIC;
        if (! in_array($visibility, [self::VISIBILITY_PUBLIC, self::VISIBILITY_PRIVATE], true)) {
            $visibility = self::VISIBILITY_PUBLIC;
        }

        $privateMode = $job['private_apply_mode'] ?? null;
        if ($visibility === self::VISIBILITY_PUBLIC) {
            $privateMode = null;
            $applicationMode = self::MODE_OPEN;
        } else {
            if (! in_array($privateMode, [self::MODE_ALL_EMPLOYEES, self::MODE_NOMINATED_ONLY], true)) {
                $privateMode = self::MODE_ALL_EMPLOYEES;
            }
            $applicationMode = $privateMode;
        }

        $status = $job['publication_status'] ?? match ($job['status_key'] ?? '') {
            'draft' => 'draft',
            'closed' => 'closed',
            default => 'published',
        };

        return array_merge($job, [
            'organization_id' => $job['organization_id'] ?? $job['company_id'] ?? 'org-ns-001',
            'visibility' => $visibility,
            'private_apply_mode' => $privateMode,
            'application_mode' => $applicationMode,
            'publication_status' => $status,
            'openings_count' => $job['openings_count'] ?? null,
            'deadline' => $job['deadline'] ?? null,
            'category' => $job['category'] ?? ($job['discipline'] ?? $job['department'] ?? null),
            'skills' => $job['skills'] ?? [],
            'stages_config' => $job['stages_config'] ?? [
                ['type' => 'screening', 'label' => 'Screening', 'required' => true, 'order' => 1],
                ['type' => 'interview', 'label' => 'Interview', 'required' => true, 'order' => 2],
                ['type' => 'offer', 'label' => 'Offer', 'required' => false, 'order' => 3],
            ],
            'nominations' => $job['nominations'] ?? [],
            'requirements_flags' => array_merge([
                'cv_required' => true,
                'ai_consent_required' => true,
                'cover_note_required' => false,
            ], $job['requirements_flags'] ?? []),
            'closed_reason_key' => $job['closed_reason_key'] ?? null,
            'closed_reason' => $job['closed_reason'] ?? null,
            'employees_cannot_apply_public' => (bool) ($job['employees_cannot_apply_public'] ?? true),
        ]);
    }

    /**
     * @param  array<string, mixed>  $job
     * @param  array<string, mixed>  $context
     * @return array{can_view: bool, can_apply: bool, reason: string|null, reason_key: string|null, membership_path: bool, nomination_required: bool}
     */
    public static function eligibility(array $job, array $context): array
    {
        $job = self::normalizeJob($job);
        $orgId = $job['organization_id'];
        $visibility = $job['visibility'];
        $mode = $job['application_mode'];
        $membership = $context['membership_status'] ?? 'none';
        $isEmployee = (bool) ($context['is_employee_of_org'] ?? false)
            && ($context['organization_id'] ?? null) === $orgId;
        $isPendingMember = $membership === 'pending'
            && ($context['organization_id'] ?? null) === $orgId;
        $isNominated = (bool) ($context['is_nominated'] ?? false);

        if (($job['publication_status'] ?? '') === 'closed') {
            return self::result(false, false, 'job_closed', __('jobs.eligibility.reasons.job_closed'));
        }

        if (($job['publication_status'] ?? '') === 'draft') {
            return self::result(false, false, 'job_draft', __('jobs.eligibility.reasons.job_draft'));
        }

        // Public jobs
        if ($visibility === self::VISIBILITY_PUBLIC) {
            if ($isEmployee && ($job['employees_cannot_apply_public'] ?? true)) {
                return self::result(
                    true,
                    false,
                    'employee_public_blocked',
                    __('jobs.eligibility.reasons.employee_public_blocked'),
                    membershipPath: false,
                );
            }

            return self::result(true, true, null, null);
        }

        // Private · all employees
        if ($mode === self::MODE_ALL_EMPLOYEES) {
            if ($isEmployee) {
                return self::result(true, true, null, null);
            }
            if ($isPendingMember) {
                return self::result(
                    false,
                    false,
                    'membership_pending',
                    __('jobs.eligibility.reasons.membership_pending'),
                    membershipPath: true,
                );
            }

            return self::result(
                false,
                false,
                'membership_required',
                __('jobs.eligibility.reasons.membership_required'),
                membershipPath: true,
            );
        }

        // Private · nominated only
        if ($mode === self::MODE_NOMINATED_ONLY) {
            if (! $isEmployee && ! $isPendingMember) {
                return self::result(
                    false,
                    false,
                    'membership_required',
                    __('jobs.eligibility.reasons.nomination_membership_required'),
                    membershipPath: true,
                    nominationRequired: true,
                );
            }
            if ($isPendingMember) {
                return self::result(
                    false,
                    false,
                    'membership_pending',
                    __('jobs.eligibility.reasons.nomination_membership_pending'),
                    membershipPath: true,
                    nominationRequired: true,
                );
            }
            if (! $isNominated) {
                return self::result(
                    true,
                    false,
                    'nomination_required',
                    __('jobs.eligibility.reasons.nomination_required'),
                    nominationRequired: true,
                );
            }

            return self::result(true, true, null, null, nominationRequired: true);
        }

        return self::result(false, false, 'unknown', __('jobs.eligibility.reasons.unknown'));
    }

    /**
     * @return array{can_view: bool, can_apply: bool, reason: string|null, reason_key: string|null, membership_path: bool, nomination_required: bool}
     */
    private static function result(
        bool $canView,
        bool $canApply,
        ?string $reasonKey,
        ?string $reason,
        bool $membershipPath = false,
        bool $nominationRequired = false,
    ): array {
        return [
            'can_view' => $canView,
            'can_apply' => $canApply,
            'reason_key' => $reasonKey,
            'reason' => $reason,
            'membership_path' => $membershipPath,
            'nomination_required' => $nominationRequired,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $jobs
     * @return list<array<string, mixed>>
     */
    public static function publicCatalog(array $jobs): array
    {
        return array_values(array_filter(
            array_map(fn (array $job): array => self::normalizeJob($job), $jobs),
            fn (array $job): bool => ($job['visibility'] ?? self::VISIBILITY_PUBLIC) === self::VISIBILITY_PUBLIC
                && ($job['publication_status'] ?? 'published') === 'published'
        ));
    }

    /**
     * Enrich candidate-facing jobs with eligibility for the current demo context.
     *
     * @param  list<array<string, mixed>>  $jobs
     * @param  array<string, mixed>  $context
     * @return list<array<string, mixed>>
     */
    public static function enrichCandidateJobs(array $jobs, array $context): array
    {
        return array_values(array_map(function (array $job) use ($context): array {
            $normalized = self::normalizeJob($job);
            $eligibility = self::eligibility($normalized, $context);
            $normalized['eligibility'] = $eligibility;

            // Private jobs the candidate cannot view are listed as restricted cards (not full detail).
            if (! $eligibility['can_view'] && ($normalized['visibility'] ?? '') === self::VISIBILITY_PRIVATE) {
                $normalized['restricted'] = true;
                $normalized['match_reason'] = $eligibility['reason'];
                $normalized['primary_cta'] = $eligibility['membership_path'] ? 'membership' : 'unavailable';
                $normalized['primary_cta_label'] = $eligibility['membership_path']
                    ? __('jobs.eligibility.open_membership')
                    : __('jobs.eligibility.unavailable');
            }

            if ($eligibility['can_view'] && ! $eligibility['can_apply']) {
                $normalized['primary_cta'] = $eligibility['nomination_required'] && ($eligibility['reason_key'] ?? '') === 'nomination_required'
                    ? 'nomination_blocked'
                    : (($eligibility['reason_key'] ?? '') === 'employee_public_blocked' ? 'employee_blocked' : 'unavailable');
                $normalized['primary_cta_label'] = match ($normalized['primary_cta']) {
                    'nomination_blocked' => __('jobs.eligibility.reasons.nomination_required'),
                    'employee_blocked' => __('jobs.eligibility.reasons.employee_public_blocked'),
                    default => __('jobs.eligibility.unavailable'),
                };
            }

            return $normalized;
        }, $jobs));
    }
}
