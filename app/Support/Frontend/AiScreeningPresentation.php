<?php

namespace App\Support\Frontend;


class AiScreeningPresentation
{
    public const RUN_PENDING = 'pending';

    public const RUN_COMPLETED = 'completed';

    public const RUN_FAILED = 'failed';

    public const RUN_UNAVAILABLE = 'unavailable';

    public const HR_NONE = 'none';

    public const HR_REQUIRED = 'required';

    public const HR_IN_PROGRESS = 'in_progress';

    public const HR_COMPLETED = 'completed';

    /**
     * Sample threshold from WF4 illustration — advisory flag only, never auto-reject.
     */
    public const ADVISORY_MATCH_THRESHOLD = 80.0;

    /**
     * @return array<string, mixed>
     */
    public static function forApplicant(string $applicantId, array $base = []): array
    {
        $samples = self::samples();
        $screening = $samples[$applicantId] ?? self::defaultPending($base);

        return self::normalize($screening, $base);
    }

    /**
     * @param  array<string, mixed>  $screening
     * @param  array<string, mixed>  $base
     * @return array<string, mixed>
     */
    public static function normalize(array $screening, array $base = []): array
    {
        $runStatus = $screening['run_status'] ?? self::RUN_PENDING;
        if (! in_array($runStatus, [self::RUN_PENDING, self::RUN_COMPLETED, self::RUN_FAILED, self::RUN_UNAVAILABLE], true)) {
            $runStatus = self::RUN_PENDING;
        }

        $score = isset($screening['score']) ? (float) $screening['score'] : null;
        $threshold = (float) ($screening['advisory_threshold'] ?? self::ADVISORY_MATCH_THRESHOLD);
        $band = null;
        if ($runStatus === self::RUN_COMPLETED && $score !== null) {
            $band = $score >= $threshold ? 'high' : 'low';
        }

        $hrStatus = $screening['hr_review_status'] ?? self::HR_NONE;
        if ($runStatus === self::RUN_FAILED && $hrStatus === self::HR_NONE) {
            $hrStatus = self::HR_REQUIRED;
        }
        if ($band === 'low' && $hrStatus === self::HR_NONE) {
            $hrStatus = self::HR_REQUIRED;
        }

        $recommendation = $screening['recommendation'] ?? null;
        if ($recommendation === null && $band === 'high') {
            $recommendation = 'advance_review';
        }
        if ($recommendation === null && $band === 'low') {
            $recommendation = 'manual_review';
        }

        return [
            'application_id' => $screening['application_id'] ?? ($base['application_id'] ?? null),
            'candidate_id' => $screening['candidate_id'] ?? ($base['id'] ?? null),
            'job_id' => $screening['job_id'] ?? ($base['job_id'] ?? null),
            'score' => $score,
            'score_label' => $score !== null ? number_format($score, 0).'%' : null,
            'summary' => $screening['summary'] ?? null,
            'matched_skills' => $screening['matched_skills'] ?? [],
            'missing_skills' => $screening['missing_skills'] ?? [],
            'run_status' => $runStatus,
            'match_band' => $band,
            'advisory_threshold' => $threshold,
            'below_threshold' => $band === 'low',
            'recommendation' => $recommendation,
            'recommendation_label' => self::recommendationLabel($recommendation),
            'hr_review_status' => $hrStatus,
            'reviewer_name' => $screening['reviewer_name'] ?? null,
            'override_reason' => $screening['override_reason'] ?? null,
            'human_decision' => $screening['human_decision'] ?? null,
            'human_decision_label' => self::decisionLabel($screening['human_decision'] ?? null),
            'model_version' => $screening['model_version'] ?? 'sample-cv-match-v0',
            'is_sample' => true,
            'advisory_note' => __('company.applicants.ai.advisory_note'),
            'not_rejection_note' => __('company.applicants.ai.not_rejection_note'),
            'badge_label' => self::badgeLabel($runStatus, $band, $hrStatus),
            'badge_variant' => self::badgeVariant($runStatus, $band, $hrStatus),
            'pipeline_label' => self::pipelineLabel($runStatus, $band, $hrStatus),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function samples(): array
    {
        return [
            // Scenario A — high match; HR still reviewing / completed sample
            'maya-chen' => [
                'application_id' => 'hb-ns204-mc-1012',
                'candidate_id' => 'maya-chen',
                'job_id' => 'ns-204',
                'score' => 92,
                'summary' => 'Sample match: strong alignment on design systems, research, and hybrid SaaS workflow experience.',
                'matched_skills' => ['Design systems', 'User research', 'Figma', 'SaaS workflows'],
                'missing_skills' => ['Enterprise sales tooling'],
                'run_status' => self::RUN_COMPLETED,
                'recommendation' => 'advance_review',
                'hr_review_status' => self::HR_COMPLETED,
                'reviewer_name' => 'Olivia Bennett',
                'human_decision' => 'accept',
                'override_reason' => null,
            ],
            // Scenario B — low match; not rejected; manual review required
            'ethan-cole' => [
                'application_id' => 'hb-ns204-ec-1008',
                'candidate_id' => 'ethan-cole',
                'job_id' => 'ns-204',
                'score' => 54,
                'summary' => 'Sample match: partial systems experience; gaps vs senior product-design brief.',
                'matched_skills' => ['Systems', 'SaaS'],
                'missing_skills' => ['Portfolio depth', 'Facilitated research', 'Hybrid product ownership'],
                'run_status' => self::RUN_COMPLETED,
                'recommendation' => 'manual_review',
                'hr_review_status' => self::HR_REQUIRED,
                'reviewer_name' => null,
                'human_decision' => null,
                'override_reason' => null,
            ],
            // Scenario C — AI failed; not a candidate rejection
            'noah-williams' => [
                'application_id' => 'hb-ns204-nw-1015',
                'candidate_id' => 'noah-williams',
                'job_id' => 'ns-204',
                'score' => null,
                'summary' => 'Sample run: CV match service timed out before a score was produced.',
                'matched_skills' => [],
                'missing_skills' => [],
                'run_status' => self::RUN_FAILED,
                'recommendation' => null,
                'hr_review_status' => self::HR_REQUIRED,
                'reviewer_name' => null,
                'human_decision' => null,
                'override_reason' => null,
            ],
            // Scenario A pending
            'sofia-nguyen' => [
                'application_id' => 'hb-ns204-sn-1020',
                'candidate_id' => 'sofia-nguyen',
                'job_id' => 'ns-204',
                'score' => null,
                'summary' => 'Sample run: match is queued after application materials were received.',
                'matched_skills' => [],
                'missing_skills' => [],
                'run_status' => self::RUN_PENDING,
                'hr_review_status' => self::HR_NONE,
            ],
            // Scenario D — override completed (labeled sample)
            'ella-davis' => [
                'application_id' => 'hb-ns204-ed-1014',
                'candidate_id' => 'ella-davis',
                'job_id' => 'ns-204',
                'score' => 61,
                'summary' => 'Sample match: below advisory threshold; HR recorded an override after portfolio review.',
                'matched_skills' => ['Figma', 'Systems'],
                'missing_skills' => ['Leadership facilitation'],
                'run_status' => self::RUN_COMPLETED,
                'recommendation' => 'manual_review',
                'hr_review_status' => self::HR_COMPLETED,
                'reviewer_name' => 'Olivia Bennett',
                'human_decision' => 'override_advance',
                'override_reason' => 'Sample override: portfolio demonstrated systems leadership not reflected in the automated skill list.',
            ],
            'marcus-reed' => [
                'application_id' => 'hb-ns204-mr-1017',
                'candidate_id' => 'marcus-reed',
                'job_id' => 'ns-204',
                'score' => null,
                'summary' => 'Sample: AI screening unavailable for this agency submission in the preview.',
                'matched_skills' => [],
                'missing_skills' => [],
                'run_status' => self::RUN_UNAVAILABLE,
                'hr_review_status' => self::HR_REQUIRED,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $base
     * @return array<string, mixed>
     */
    private static function defaultPending(array $base): array
    {
        return [
            'application_id' => $base['application_id'] ?? null,
            'candidate_id' => $base['id'] ?? null,
            'job_id' => $base['job_id'] ?? null,
            'score' => null,
            'summary' => 'Sample: AI screening has not produced a result for this preview record.',
            'matched_skills' => [],
            'missing_skills' => [],
            'run_status' => self::RUN_PENDING,
            'hr_review_status' => self::HR_NONE,
        ];
    }

    private static function recommendationLabel(?string $key): ?string
    {
        return match ($key) {
            'advance_review' => __('company.applicants.ai.rec_advance'),
            'manual_review' => __('company.applicants.ai.rec_manual'),
            default => null,
        };
    }

    private static function decisionLabel(?string $key): ?string
    {
        return match ($key) {
            'accept' => __('company.applicants.ai.decision_accept'),
            'override_advance' => __('company.applicants.ai.decision_override_advance'),
            'override_hold' => __('company.applicants.ai.decision_override_hold'),
            default => null,
        };
    }

    private static function badgeLabel(string $run, ?string $band, string $hr): string
    {
        if ($run === self::RUN_FAILED) {
            return __('company.applicants.ai.badge_failed');
        }
        if ($run === self::RUN_UNAVAILABLE) {
            return __('company.applicants.ai.badge_unavailable');
        }
        if ($run === self::RUN_PENDING) {
            return __('company.applicants.ai.badge_pending');
        }
        if ($band === 'low') {
            return __('company.applicants.ai.badge_low');
        }
        if ($hr === self::HR_REQUIRED) {
            return __('company.applicants.ai.badge_review');
        }

        return __('company.applicants.ai.badge_high');
    }

    private static function badgeVariant(string $run, ?string $band, string $hr): string
    {
        if ($run === self::RUN_FAILED || $run === self::RUN_UNAVAILABLE) {
            return 'pending';
        }
        if ($run === self::RUN_PENDING || $hr === self::HR_REQUIRED) {
            return 'info';
        }
        if ($band === 'low') {
            return 'pending';
        }

        return 'success';
    }

    private static function pipelineLabel(string $run, ?string $band, string $hr): string
    {
        if ($run === self::RUN_FAILED) {
            return __('company.applicants.ai.pipeline_failed');
        }
        if ($run === self::RUN_PENDING) {
            return __('company.applicants.ai.pipeline_pending');
        }
        if ($band === 'low') {
            return __('company.applicants.ai.pipeline_low');
        }
        if ($band === 'high') {
            return __('company.applicants.ai.pipeline_high');
        }

        return __('company.applicants.ai.pipeline_pending');
    }
}
