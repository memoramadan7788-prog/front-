<?php

namespace App\Support\Frontend;

/**
 * Interviewer workspace presentation data (Stage 6 · Batches 6A–6B).
 * Aligned with MockCompanyData interviews assigned to Alex Morgan.
 */
class MockInterviewerData
{
    public static function interviewerName(): string
    {
        return 'Alex Morgan';
    }

    /**
     * Interviews assigned to the current interviewer persona.
     *
     * @return list<array<string, mixed>>
     */
    public static function assignedInterviews(): array
    {
        return array_values(array_filter(
            MockCompanyData::interviews(),
            static fn (array $interview): bool => ($interview['interviewer'] ?? '') === self::interviewerName()
        ));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function interview(string $id): ?array
    {
        foreach (self::assignedInterviews() as $interview) {
            if (($interview['id'] ?? '') === $id) {
                return self::enrichInterview($interview);
            }
        }

        foreach (self::pastScorecardInterviews() as $interview) {
            if (($interview['id'] ?? '') === $id) {
                return $interview;
            }
        }

        return null;
    }

    /**
     * Past interviews that still need scorecard work (not in Company calendar feed).
     *
     * @return list<array<string, mixed>>
     */
    public static function pastScorecardInterviews(): array
    {
        return [
            [
                'id' => 'scorecard-owen-ns205',
                'applicant' => 'Owen Clarke',
                'applicant_id' => 'owen-clarke',
                'job_id' => 'ns-205',
                'job_code' => 'NS-205',
                'job_title' => 'Senior Software Engineer',
                'title' => 'Collaboration interview',
                'type' => 'Collaboration',
                'type_key' => 'collaboration',
                'status' => 'Completed',
                'status_key' => 'completed',
                'day_key' => '2026-10-18',
                'when' => 'Saturday, Oct 18, 2026',
                'time' => '2:00–2:45 PM Central',
                'time_short' => '2:00 PM',
                'duration' => '45 minutes',
                'timezone' => 'Central Time (UTC−05:00)',
                'interviewer' => self::interviewerName(),
                'organizer' => 'Olivia Bennett',
                'format' => 'Video meeting',
                'preparation' => 'Draft scorecard saved',
                'location' => 'Video',
                'is_past' => true,
                'scorecard_overdue' => true,
            ],
        ];
    }

    /**
     * Frame 39 — Interviewer dashboard.
     *
     * @return array<string, mixed>
     */
    public static function dashboard(): array
    {
        $assigned = array_map([self::class, 'enrichInterview'], self::assignedInterviews());
        $maya = collect($assigned)->firstWhere('id', 'int-ns204-portfolio');
        $aisha = collect($assigned)->firstWhere('id', 'int-ns204-aisha-portfolio');
        // Cancelled/reassigned samples stay out of active upcoming sessions (F9D).
        $thisWeek = array_values(array_filter(
            $assigned,
            static fn (array $row): bool => in_array($row['id'] ?? '', ['int-ns204-portfolio', 'int-ns204-aisha-portfolio'], true)
                && ($row['status_key'] ?? '') !== 'cancelled'
        ));

        // Session-readiness checklist (logistics) — distinct from Preparation "role checklist".
        $checklist = [
            ['id' => 'resume', 'label' => __('interviewer.dashboard.checklist_resume'), 'done' => true],
            ['id' => 'portfolio', 'label' => __('interviewer.dashboard.checklist_portfolio'), 'done' => false],
            ['id' => 'rubric', 'label' => __('interviewer.dashboard.checklist_rubric'), 'done' => false],
            ['id' => 'camera', 'label' => __('interviewer.dashboard.checklist_camera'), 'done' => false],
        ];

        $overdue = [
            'id' => 'scorecard-owen-ns205',
            'candidate' => 'Owen Clarke',
            'job_code' => 'NS-205',
            'job_title' => 'Senior Software Engineer',
            'type' => 'Collaboration interview',
            'completed_on' => 'Oct 18',
            'due' => 'Oct 19',
            'overdue_label' => __('interviewer.dashboard.overdue_badge'),
            'body' => __('interviewer.dashboard.overdue_body'),
            'status_key' => 'overdue',
            'scorecard_route' => 'interviewer.evaluations.create',
        ];

        return [
            'greeting' => __('interviewer.dashboard.greeting'),
            'intro' => __('interviewer.dashboard.intro'),
            'footer' => 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026',
            'stats' => [
                [
                    'id' => 'upcoming',
                    'label' => __('interviewer.dashboard.stat_upcoming'),
                    'value' => (string) count($thisWeek),
                    'meta' => __('interviewer.dashboard.stat_upcoming_meta'),
                    'href' => 'interviewer.schedule.index',
                ],
                [
                    'id' => 'prep',
                    'label' => __('interviewer.dashboard.stat_prep'),
                    'value' => (string) collect($checklist)->where('done', false)->count(),
                    'meta' => __('interviewer.dashboard.stat_prep_meta'),
                    'href' => null,
                    'anchor' => 'prep-checklist',
                ],
                [
                    'id' => 'feedback',
                    'label' => __('interviewer.dashboard.stat_feedback'),
                    'value' => '1',
                    'meta' => __('interviewer.dashboard.stat_feedback_meta'),
                    'href' => 'interviewer.evaluations.index',
                ],
            ],
            'next_interview' => $maya ? [
                'id' => $maya['id'],
                'status' => __('interviewer.dashboard.next_status'),
                'title' => __('interviewer.dashboard.next_title'),
                'role_line' => __('interviewer.dashboard.next_role'),
                'when_line' => __('interviewer.dashboard.next_when'),
                'focus' => __('interviewer.dashboard.next_focus'),
                'applicant_id' => $maya['applicant_id'],
                'job_id' => $maya['job_id'],
            ] : null,
            'session_checklist_interview_id' => 'int-ns204-portfolio',
            'session_checklist_key' => 'hb_iv_int-ns204-portfolio_session_v1',
            'session_checklist_legacy_keys' => [
                'hb_iv_int-ns204-portfolio_dash_v1',
                'hb_interviewer_prep_checklist_v1',
            ],
            'this_week' => array_map(static function (array $row): array {
                $isMaya = ($row['id'] ?? '') === 'int-ns204-portfolio';

                return [
                    'id' => $row['id'],
                    'candidate' => $row['applicant'],
                    'interview' => $isMaya
                        ? __('interviewer.dashboard.week_maya_interview')
                        : __('interviewer.dashboard.week_aisha_interview'),
                    'when' => $isMaya
                        ? __('interviewer.dashboard.week_maya_when')
                        : __('interviewer.dashboard.week_aisha_when'),
                    'action_key' => $isMaya ? 'prepare' : 'review_brief',
                    'day_key' => $row['day_key'],
                ];
            }, $thisWeek),
            'checklist' => $checklist,
            'availability' => [
                'windows' => __('interviewer.dashboard.availability_windows'),
                'note' => __('interviewer.dashboard.availability_note'),
            ],
            'overdue' => $overdue,
            'assigned_count' => count($assigned),
        ];
    }

    /**
     * Frame 40 — Interviewer schedule.
     *
     * @return array<string, mixed>
     */
    public static function schedule(): array
    {
        $assigned = array_map([self::class, 'enrichInterview'], self::assignedInterviews());
        $upcoming = array_values(array_filter(
            $assigned,
            static fn (array $row): bool => ($row['day_key'] ?? '') >= '2026-10-20'
        ));
        $past = self::pastScorecardInterviews();

        $weeks = self::scheduleWeeks($assigned);
        $currentWeek = collect($weeks)->firstWhere('is_current', true) ?? $weeks[1];
        $selected = collect($assigned)->firstWhere('id', 'int-ns204-portfolio')
            ?? ($assigned[0] ?? null);

        return [
            'interviewer' => self::interviewerName(),
            'subtitle_meta' => 'Assigned interviews only · Central Time (UTC−05:00)',
            'timezone' => 'Week view · Central Time (UTC−05:00)',
            'week_label' => $currentWeek['label'],
            'current_week_id' => $currentWeek['id'],
            'weeks' => $weeks,
            'days' => $currentWeek['days'],
            'interviews' => $assigned,
            'upcoming' => $upcoming,
            'past' => $past,
            'selected' => $selected,
            'detail_rows' => array_values(array_filter(
                $assigned,
                static fn (array $row): bool => in_array($row['id'] ?? '', ['int-ns204-portfolio', 'int-ns204-aisha-portfolio'], true)
            )),
            'meeting_access' => [
                'title' => 'Meeting access & changes',
                'body' => 'Meeting links appear 15 minutes before the scheduled start. If your availability changes, contact Olivia Bennett so the candidate receives one coordinated update.',
            ],
            'availability_local' => [
                ['day' => 'Thu', 'windows' => '9:00–11:30 AM, 1:00–3:00 PM', 'selected' => true],
                ['day' => 'Fri', 'windows' => '10:00 AM–2:00 PM', 'selected' => true],
            ],
            'footer' => 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026',
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $assigned
     * @return list<array<string, mixed>>
     */
    private static function scheduleWeeks(array $assigned): array
    {
        $byDay = [];
        foreach ($assigned as $interview) {
            $byDay[$interview['day_key']][] = $interview;
        }

        $mapSlots = static function (array $dayInterviews, array $availability = []): array {
            $slots = [];
            foreach ($availability as $slot) {
                $slots[] = [
                    'time' => $slot,
                    'label' => 'Available',
                    'available' => true,
                    'interview_id' => null,
                ];
            }
            foreach ($dayInterviews as $interview) {
                $slots[] = [
                    'time' => $interview['time_short'],
                    'label' => $interview['applicant'],
                    'sub' => ($interview['type_key'] === 'portfolio' ? 'Portfolio' : $interview['title']).' · '.$interview['job_code'],
                    'available' => false,
                    'interview_id' => $interview['id'],
                    'candidate' => $interview['applicant'],
                    'type' => $interview['type_key'] === 'portfolio' ? 'Portfolio' : $interview['title'],
                    'job_code' => $interview['job_code'],
                ];
            }

            return $slots;
        };

        return [
            [
                'id' => '2026-10-12',
                'label' => 'October 12–16, 2026',
                'is_current' => false,
                'days' => [
                    ['key' => '2026-10-12', 'label' => 'Mon 12', 'slots' => $mapSlots([], ['10:00 AM'])],
                    ['key' => '2026-10-13', 'label' => 'Tue 13', 'slots' => $mapSlots([], ['2:00 PM'])],
                    ['key' => '2026-10-14', 'label' => 'Wed 14', 'slots' => []],
                    ['key' => '2026-10-15', 'label' => 'Thu 15', 'slots' => $mapSlots([], ['9:00 AM'])],
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
                        'slots' => $mapSlots([], ['9:00 AM', '2:00 PM']),
                    ],
                    [
                        'key' => '2026-10-20',
                        'label' => 'Tue 20',
                        'slots' => $mapSlots([], ['9:00 AM', '2:00 PM']),
                    ],
                    [
                        'key' => '2026-10-21',
                        'label' => 'Wed 21',
                        'slots' => $mapSlots($byDay['2026-10-21'] ?? [], []),
                    ],
                    [
                        'key' => '2026-10-22',
                        'label' => 'Thu 22',
                        'highlight' => true,
                        'slots' => $mapSlots($byDay['2026-10-22'] ?? [], ['9:00 AM', '2:00 PM']),
                    ],
                    [
                        'key' => '2026-10-23',
                        'label' => 'Fri 23',
                        'slots' => $mapSlots($byDay['2026-10-23'] ?? [], ['9:00 AM', '2:00 PM']),
                    ],
                ],
            ],
            [
                'id' => '2026-10-26',
                'label' => 'October 26–30, 2026',
                'is_current' => false,
                'days' => [
                    ['key' => '2026-10-26', 'label' => 'Mon 26', 'slots' => $mapSlots([], ['10:00 AM'])],
                    ['key' => '2026-10-27', 'label' => 'Tue 27', 'slots' => $mapSlots([], ['10:00 AM'])],
                    ['key' => '2026-10-28', 'label' => 'Wed 28', 'slots' => $mapSlots([], ['3:00 PM'])],
                    ['key' => '2026-10-29', 'label' => 'Thu 29', 'slots' => []],
                    ['key' => '2026-10-30', 'label' => 'Fri 30', 'slots' => []],
                ],
            ],
        ];
    }

    /**
     * Frame 41 — Interview preparation payload.
     *
     * @return array<string, mixed>|null
     */
    public static function preparation(string $id): ?array
    {
        $interview = self::interview($id);
        if ($interview === null) {
            return null;
        }

        $applicant = MockCompanyData::applicant($interview['applicant_id'] ?? '') ?? [
            'id' => $interview['applicant_id'] ?? '',
            'name' => $interview['applicant'] ?? '',
            'headline' => $interview['job_title'] ?? '',
            'job_title' => $interview['job_title'] ?? '',
            'location' => null,
            'email' => null,
            'portfolio' => null,
            'experience' => [],
            'years_experience' => null,
        ];
        $isMaya = ($interview['id'] ?? '') === 'int-ns204-portfolio';
        $isLuis = ($interview['id'] ?? '') === 'int-ns204-luis-team';

        $brief = $isMaya
            ? __('interviewer.prepare.brief_maya')
            : ($isLuis
                ? __('interviewer.prepare.brief_luis')
                : __('interviewer.prepare.brief_aisha'));

        $permitted = $isMaya
            ? __('interviewer.prepare.permitted_maya')
            : __('interviewer.prepare.permitted_generic');

        $competencies = [
            [
                'id' => 'problem_framing',
                'label' => __('interviewer.prepare.comp_problem'),
                'question' => __('interviewer.prepare.q_problem'),
            ],
            [
                'id' => 'interaction_craft',
                'label' => __('interviewer.prepare.comp_interaction'),
                'question' => __('interviewer.prepare.q_interaction'),
            ],
            [
                'id' => 'systems_thinking',
                'label' => __('interviewer.prepare.comp_systems'),
                'question' => __('interviewer.prepare.q_systems'),
            ],
            [
                'id' => 'collaboration',
                'label' => __('interviewer.prepare.comp_collaboration'),
                'question' => __('interviewer.prepare.q_collaboration'),
            ],
        ];

        $agenda = [
            ['id' => 'intro', 'range' => '00–05', 'label' => __('interviewer.prepare.agenda_intro')],
            ['id' => 'portfolio', 'range' => '05–25', 'label' => __('interviewer.prepare.agenda_portfolio')],
            ['id' => 'competency', 'range' => '25–35', 'label' => __('interviewer.prepare.agenda_competency')],
            ['id' => 'candidate_q', 'range' => '35–42', 'label' => __('interviewer.prepare.agenda_candidate')],
            ['id' => 'wrap', 'range' => '42–45', 'label' => __('interviewer.prepare.agenda_wrap')],
        ];

        $roleChecklist = [
            ['id' => 'role_requirements', 'label' => __('interviewer.prepare.check_role'), 'done' => true],
            ['id' => 'candidate_materials', 'label' => __('interviewer.prepare.check_materials'), 'done' => false],
            ['id' => 'follow_up', 'label' => __('interviewer.prepare.check_followup'), 'done' => false],
        ];

        return [
            'interview' => $interview,
            'applicant' => $applicant,
            'title' => __('interviewer.prepare.title', ['name' => $interview['applicant']]),
            'meta' => $interview['title'].' · '.$interview['job_code'].' · '.($isMaya
                ? __('interviewer.prepare.meta_maya')
                : ($isLuis ? __('interviewer.prepare.meta_luis') : __('interviewer.prepare.meta_aisha'))),
            'brief' => $brief,
            'permitted_context' => $permitted,
            'portfolio_url' => $applicant['portfolio'] ?? null,
            'portfolio_label' => $applicant['portfolio_label'] ?? null,
            'has_resume' => true,
            'competencies' => $competencies,
            'agenda' => $agenda,
            'role' => [
                'label' => __('interviewer.prepare.role_label'),
                'body' => __('interviewer.prepare.role_body'),
            ],
            'role_checklist' => $roleChecklist,
            // Session readiness mirrors Dashboard only for Maya's next interview (Frame 39).
            'session_checklist' => $isMaya
                ? [
                    ['id' => 'resume', 'label' => __('interviewer.dashboard.checklist_resume'), 'done' => true],
                    ['id' => 'portfolio', 'label' => __('interviewer.dashboard.checklist_portfolio'), 'done' => false],
                    ['id' => 'rubric', 'label' => __('interviewer.dashboard.checklist_rubric'), 'done' => false],
                    ['id' => 'camera', 'label' => __('interviewer.dashboard.checklist_camera'), 'done' => false],
                ]
                : [],
            'scoped_note' => __('interviewer.prepare.scoped_note'),
            'coordination' => [
                'organizer' => $interview['organizer'] ?? 'Olivia Bennett',
                'hiring_manager' => 'Daniel Brooks',
                'video_access' => __('interviewer.prepare.video_access'),
            ],
            'footer' => 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026',
            'storage_prefix' => 'hb_iv_'.$interview['id'],
            'session_checklist_key' => $isMaya ? 'hb_iv_'.$interview['id'].'_session_v1' : null,
            'session_checklist_legacy_keys' => $isMaya
                ? ['hb_iv_int-ns204-portfolio_dash_v1', 'hb_interviewer_prep_checklist_v1']
                : [],
        ];
    }

    /**
     * Frame 42 — Live interview workspace payload.
     *
     * @return array<string, mixed>|null
     */
    public static function live(string $id): ?array
    {
        $prep = self::preparation($id);
        if ($prep === null) {
            return null;
        }

        $interview = $prep['interview'];

        return [
            'interview' => $interview,
            'applicant' => $prep['applicant'],
            'title' => $interview['title'].' · '.$interview['applicant'],
            'meta' => $interview['job_code'].' · '.self::interviewerName().' · '.($interview['when'] ?? '').' · '.($interview['time'] ?? ''),
            'agenda' => [
                ['id' => 'intro', 'range' => '00–05', 'label' => __('interviewer.live.agenda_intro'), 'state' => 'done'],
                ['id' => 'portfolio', 'range' => '05–25', 'label' => __('interviewer.live.agenda_portfolio'), 'state' => 'current'],
                ['id' => 'competency', 'range' => '25–35', 'label' => __('interviewer.live.agenda_competency'), 'state' => 'todo'],
                ['id' => 'candidate_q', 'range' => '35–42', 'label' => __('interviewer.live.agenda_candidate'), 'state' => 'todo'],
                ['id' => 'wrap', 'range' => '42–45', 'label' => __('interviewer.live.agenda_wrap'), 'state' => 'todo'],
            ],
            'question_bank' => [
                [
                    'id' => 'problem_framing',
                    'label' => __('interviewer.prepare.comp_problem'),
                    'question' => __('interviewer.live.q_problem'),
                ],
                [
                    'id' => 'systems_thinking',
                    'label' => __('interviewer.prepare.comp_systems'),
                    'question' => __('interviewer.live.q_systems'),
                ],
                [
                    'id' => 'collaboration',
                    'label' => __('interviewer.prepare.comp_collaboration'),
                    'question' => __('interviewer.live.q_collaboration'),
                ],
            ],
            'note_tabs' => [
                [
                    'id' => 'portfolio',
                    'label' => __('interviewer.live.tab_portfolio'),
                    'fields' => [
                        ['id' => 'context', 'label' => __('interviewer.live.field_context'), 'placeholder' => __('interviewer.live.ph_context')],
                        ['id' => 'evidence', 'label' => __('interviewer.live.field_evidence'), 'placeholder' => __('interviewer.live.ph_evidence')],
                        ['id' => 'followup', 'label' => __('interviewer.live.field_followup'), 'placeholder' => __('interviewer.live.ph_followup')],
                    ],
                ],
                [
                    'id' => 'competencies',
                    'label' => __('interviewer.live.tab_competencies'),
                    'fields' => [
                        ['id' => 'problem', 'label' => __('interviewer.prepare.comp_problem'), 'placeholder' => __('interviewer.live.ph_comp')],
                        ['id' => 'systems', 'label' => __('interviewer.prepare.comp_systems'), 'placeholder' => __('interviewer.live.ph_comp')],
                        ['id' => 'collab', 'label' => __('interviewer.prepare.comp_collaboration'), 'placeholder' => __('interviewer.live.ph_comp')],
                    ],
                ],
                [
                    'id' => 'candidate_questions',
                    'label' => __('interviewer.live.tab_candidate'),
                    'fields' => [
                        ['id' => 'asked', 'label' => __('interviewer.live.field_asked'), 'placeholder' => __('interviewer.live.ph_asked')],
                        ['id' => 'themes', 'label' => __('interviewer.live.field_themes'), 'placeholder' => __('interviewer.live.ph_themes')],
                    ],
                ],
            ],
            'seed_notes' => ($interview['id'] ?? '') === 'int-ns204-portfolio'
                ? [
                    'portfolio' => [
                        'context' => __('interviewer.live.seed_context'),
                        'evidence' => __('interviewer.live.seed_evidence'),
                        'followup' => __('interviewer.live.seed_followup'),
                    ],
                ]
                : [],
            'notes_footnote' => __('interviewer.live.notes_footnote'),
            'storage_prefix' => 'hb_iv_'.$interview['id'],
            'prepare_url' => route('interviewer.interviews.prepare', $interview['id']),
            'scorecard_url' => route('interviewer.evaluations.create', $interview['id']),
            'footer' => 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026',
        ];
    }

    /**
     * Frame 43 — Scorecards queue.
     *
     * @return array<string, mixed>
     */
    public static function evaluationsIndex(): array
    {
        $rows = [];
        foreach (self::pastScorecardInterviews() as $past) {
            $rows[] = [
                'id' => $past['id'],
                'applicant' => $past['applicant'],
                'job_code' => $past['job_code'],
                'job_title' => $past['job_title'],
                'title' => $past['title'],
                'when' => $past['when'],
                'status_key' => 'overdue',
                'status_label' => __('interviewer.evaluations.status_overdue'),
            ];
        }

        foreach (self::assignedInterviews() as $interview) {
            $enriched = self::enrichInterview($interview);
            $statusKey = match ($enriched['id'] ?? '') {
                'int-ns204-portfolio' => 'draft',
                'int-ns204-luis-team' => 'ready',
                default => 'upcoming',
            };
            $rows[] = [
                'id' => $enriched['id'],
                'applicant' => $enriched['applicant'],
                'job_code' => $enriched['job_code'],
                'job_title' => $enriched['job_title'],
                'title' => $enriched['title'],
                'when' => ($enriched['when'] ?? '').' · '.($enriched['time_short'] ?? $enriched['time'] ?? ''),
                'status_key' => $statusKey,
                'status_label' => match ($statusKey) {
                    'draft' => __('interviewer.evaluations.status_draft'),
                    'ready' => __('interviewer.evaluations.status_ready'),
                    default => __('interviewer.evaluations.status_upcoming'),
                },
            ];
        }

        return [
            'title' => __('interviewer.evaluations.index_title'),
            'intro' => __('interviewer.evaluations.index_intro'),
            'rows' => $rows,
            'footer' => 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026',
        ];
    }

    /**
     * Frame 43 — Interview scorecard form for one interview.
     *
     * @return array<string, mixed>|null
     */
    public static function scorecard(string $id): ?array
    {
        $interview = self::interview($id);
        if ($interview === null) {
            return null;
        }

        $isMaya = ($interview['id'] ?? '') === 'int-ns204-portfolio';
        $isOwen = ($interview['id'] ?? '') === 'scorecard-owen-ns205';

        $ratingOptions = [
            ['value' => '1', 'label' => __('interviewer.evaluations.rating_1')],
            ['value' => '2', 'label' => __('interviewer.evaluations.rating_2')],
            ['value' => '3', 'label' => __('interviewer.evaluations.rating_3')],
            ['value' => '4', 'label' => __('interviewer.evaluations.rating_4')],
            ['value' => 'no', 'label' => __('interviewer.evaluations.rating_no')],
        ];

        $competencies = [
            [
                'id' => 'problem_framing',
                'label' => __('interviewer.prepare.comp_problem'),
                'seed_rating' => $isMaya ? '4' : ($isOwen ? '3' : ''),
                'seed_evidence' => $isMaya
                    ? __('interviewer.evaluations.seed_evidence_problem')
                    : ($isOwen ? __('interviewer.evaluations.seed_evidence_owen_problem') : ''),
            ],
            [
                'id' => 'interaction_craft',
                'label' => __('interviewer.prepare.comp_interaction'),
                'seed_rating' => $isMaya ? '3' : '',
                'seed_evidence' => $isMaya ? __('interviewer.evaluations.seed_evidence_interaction') : '',
            ],
            [
                'id' => 'systems_thinking',
                'label' => __('interviewer.prepare.comp_systems'),
                'seed_rating' => $isMaya ? '3' : '',
                'seed_evidence' => $isMaya ? __('interviewer.evaluations.seed_evidence_systems') : '',
            ],
            [
                'id' => 'collaboration',
                'label' => __('interviewer.prepare.comp_collaboration'),
                'seed_rating' => $isMaya ? '3' : ($isOwen ? '4' : ''),
                'seed_evidence' => $isMaya
                    ? __('interviewer.evaluations.seed_evidence_collab')
                    : ($isOwen ? __('interviewer.evaluations.seed_evidence_owen_collab') : ''),
            ],
        ];

        $recommendations = [
            ['value' => 'strong_hire', 'label' => __('interviewer.evaluations.rec_strong_hire')],
            ['value' => 'hire', 'label' => __('interviewer.evaluations.rec_hire')],
            ['value' => 'lean_hire', 'label' => __('interviewer.evaluations.rec_lean_hire')],
            ['value' => 'no_hire', 'label' => __('interviewer.evaluations.rec_no_hire')],
            ['value' => 'hold', 'label' => __('interviewer.evaluations.rec_hold')],
        ];

        return [
            'interview' => $interview,
            'title' => __('interviewer.evaluations.title', ['name' => $interview['applicant']]),
            'meta' => $interview['title'].' · '.$interview['job_code'].' · '.self::interviewerName().' · '.__('interviewer.evaluations.draft_saved_meta'),
            'scale_legend' => __('interviewer.evaluations.scale_legend'),
            'competencies' => $competencies,
            'rating_options' => $ratingOptions,
            'recommendations' => $recommendations,
            'seed' => [
                'strengths' => $isMaya ? __('interviewer.evaluations.seed_strengths') : '',
                'concerns' => $isMaya ? __('interviewer.evaluations.seed_concerns') : '',
                'recommendation' => $isMaya ? 'hire' : '',
                'attested' => false,
            ],
            'shared_with' => __('interviewer.evaluations.shared_with', [
                'organizer' => $interview['organizer'] ?? 'Olivia Bennett',
                'manager' => 'Daniel Brooks',
            ]),
            'visibility_note' => __('interviewer.evaluations.visibility_note', [
                'name' => $interview['applicant'],
            ]),
            'tip_title' => __('interviewer.evaluations.tip_title'),
            'tip_body' => __('interviewer.evaluations.tip_body'),
            'storage_key' => 'hb_iv_'.$interview['id'].'_scorecard_v1',
            'live_url' => (! empty($interview['is_past']))
                ? null
                : route('interviewer.interviews.live', $interview['id']),
            'prepare_url' => (! empty($interview['is_past']))
                ? null
                : route('interviewer.interviews.prepare', $interview['id']),
            'index_url' => route('interviewer.evaluations.index'),
            'footer' => 'Hirebridge · Private workspace · All times Central unless noted · October 20, 2026',
            // Company Evaluation Review (Ethan) remains a separate documented mock — this draft is device-local only.
            'company_eval_note' => __('interviewer.evaluations.company_eval_note'),
        ];
    }

    /**
     * @param  array<string, mixed>  $interview
     * @return array<string, mixed>
     */
    private static function enrichInterview(array $interview): array
    {
        $id = $interview['id'] ?? '';
        $statusKey = $interview['status_key'] ?? 'confirmed';
        $prep = match ($id) {
            'int-ns204-portfolio' => 'Resume reviewed · Portfolio review pending',
            'int-ns204-aisha-portfolio' => 'Brief ready',
            'int-ns204-luis-team' => 'Team brief pending',
            'int-ns204-cancelled-sample' => 'Cancelled — preparation closed',
            default => 'Preparation not started',
        };
        $evaluationStatus = $interview['evaluation_status'] ?? match ($id) {
            'int-ns204-portfolio' => 'draft',
            'int-ns204-luis-team' => 'pending',
            'int-ns204-cancelled-sample' => 'not_applicable',
            'scorecard-owen-ns205' => 'draft',
            default => 'not_started',
        };
        $isCancelled = $statusKey === 'cancelled';

        return array_merge($interview, [
            'preparation' => $prep,
            'location' => ($interview['format'] ?? 'Video meeting') === 'Video meeting' ? 'Video' : ($interview['format'] ?? 'Video'),
            'is_past' => $isCancelled || ($interview['is_past'] ?? false),
            'is_active_upcoming' => ! $isCancelled && $statusKey !== 'completed',
            'evaluation_status' => $evaluationStatus,
            'evaluation_status_label' => match ($evaluationStatus) {
                'draft' => 'Scorecard draft · not submitted',
                'submitted' => 'Evaluation submitted',
                'pending' => 'Evaluation pending',
                'not_applicable' => 'Evaluation not applicable',
                default => 'Evaluation not started',
            },
            'prepare_route' => $isCancelled ? null : 'interviewer.interviews.prepare',
            'live_route' => $isCancelled ? null : 'interviewer.interviews.live',
            'scorecard_route' => $isCancelled ? null : 'interviewer.evaluations.create',
        ]);
    }
}
