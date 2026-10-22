<?php

namespace Tests\Feature;

use App\Http\Middleware\SetFrontendLocale;
use App\Support\Frontend\InterviewBookingPresentation;
use App\Support\Frontend\MockCandidateData;
use App\Support\Frontend\MockCompanyData;
use App\Support\Frontend\MockInterviewerData;
use App\Support\Frontend\OfferLifecyclePresentation;
use Tests\TestCase;

class PhaseF9dInterviewOfferScreensTest extends TestCase
{
    public function test_interview_booking_twenty_four_hour_eligibility(): void
    {
        $outside = InterviewBookingPresentation::enrichInterview([
            'id' => 'int-ns204-portfolio',
            'status_key' => 'confirmed',
            'day_key' => '2026-10-22',
            'starts_time' => '10:00:00',
        ]);
        $this->assertTrue($outside['can_reschedule']);
        $this->assertTrue($outside['can_cancel']);
        $this->assertSame('outside_cutoff', $outside['reschedule_reason_key']);

        $inside = InterviewBookingPresentation::enrichInterview([
            'id' => 'int-ah312-near',
            'status_key' => 'confirmed',
            'day_key' => '2026-10-20',
            'starts_time' => '16:00:00',
        ]);
        $this->assertFalse($inside['can_reschedule']);
        $this->assertSame('inside_cutoff', $inside['reschedule_reason_key']);

        $completed = InterviewBookingPresentation::enrichInterview([
            'id' => 'int-done',
            'status_key' => 'completed',
            'day_key' => '2026-10-15',
            'starts_time' => '11:00:00',
        ]);
        $this->assertFalse($completed['can_reschedule']);
        $this->assertSame('completed', $completed['reschedule_reason_key']);

        $cancelled = InterviewBookingPresentation::enrichInterview([
            'id' => 'int-cancel',
            'status_key' => 'cancelled',
            'day_key' => '2026-10-18',
            'starts_time' => '13:00:00',
        ]);
        $this->assertFalse($cancelled['can_reschedule']);
        $this->assertSame('cancelled', $cancelled['reschedule_reason_key']);

        $forced = InterviewBookingPresentation::enrichInterview([
            'id' => 'int-ns204-portfolio',
            'status_key' => 'confirmed',
            'day_key' => '2026-10-22',
            'starts_time' => '10:00:00',
        ], 'blocked');
        $this->assertFalse($forced['can_reschedule']);
        $this->assertSame('inside_cutoff', $forced['reschedule_reason_key']);
    }

    public function test_schedule_slots_include_taken_expired_and_conflict_states(): void
    {
        $schedule = MockCompanyData::interviewSchedule('maya-chen');
        $states = collect($schedule['slots'])->pluck('state')->all();

        $this->assertContains(InterviewBookingPresentation::BOOKING_AVAILABLE, $states);
        $this->assertContains(InterviewBookingPresentation::BOOKING_TAKEN, $states);
        $this->assertContains(InterviewBookingPresentation::BOOKING_EXPIRED, $states);
        $this->assertContains(InterviewBookingPresentation::BOOKING_CONFLICT, $states);
        $this->assertSame('maya-chen', $schedule['applicant_id']);
        $this->assertSame('ns-204', $schedule['job_id']);
    }

    public function test_candidate_interviews_show_eligibility_and_cancelled_sample(): void
    {
        $this->get(route('candidate.interviews.index'))
            ->assertOk()
            ->assertSee('int-ns204-portfolio', false)
            ->assertSee('data-can-reschedule="1"', false)
            ->assertSee('Workflow 6', false)
            ->assertSee('presentation-only', false)
            ->assertSee('int-ms108-cancelled', false)
            ->assertSee('Sample cancelled interview', false);

        $this->get(route('candidate.interviews.show', 'int-ms108-cancelled'))
            ->assertOk()
            ->assertSee('data-interview-cancelled', false)
            ->assertSee('Cancelled', false);

        $this->get(route('candidate.interviews.index', ['interview' => 'int-ah312-near']))
            ->assertOk()
            ->assertSee('data-can-reschedule="0"', false)
            ->assertSee('data-reschedule-blocked', false)
            ->assertSee('24-hour', false);
    }

    public function test_company_schedule_conflict_and_authority_presentation(): void
    {
        $this->get(route('company.interviews.schedule', ['applicant' => 'maya-chen']))
            ->assertOk()
            ->assertSee('data-schedule-authority', false)
            ->assertSee('data-slot-conflict="1"', false)
            ->assertSee('Already taken', false)
            ->assertSee('Expired window', false)
            ->assertSee('Conflict risk on confirm', false)
            ->assertSee('Maya Chen', false)
            ->assertSee('NS-204', false)
            ->assertDontSee('Invitation sent successfully', false);
    }

    public function test_company_calendar_excludes_cancelled_from_active_slots(): void
    {
        $calendar = MockCompanyData::interviewCalendar();
        $labels = collect($calendar['days'] ?? [])
            ->flatMap(static fn (array $day): array => $day['slots'] ?? [])
            ->pluck('interview_id')
            ->filter()
            ->all();

        $this->assertNotContains('int-ns204-cancelled-sample', $labels);
        $this->assertContains('int-ns204-portfolio', $labels);

        $this->get(route('company.interviews.index'))
            ->assertOk()
            ->assertSee('int-ns204-portfolio', false)
            ->assertDontSee('Sam Okonkwo · Cancelled sample', false);
    }

    public function test_interviewer_lifecycle_shares_canonical_ids(): void
    {
        $dashboard = MockInterviewerData::dashboard();
        $thisWeekIds = collect($dashboard['this_week'] ?? $dashboard['upcoming'] ?? [])
            ->pluck('id')
            ->all();
        if ($thisWeekIds === [] && isset($dashboard['interviews'])) {
            $thisWeekIds = collect($dashboard['interviews'])->pluck('id')->all();
        }

        $maya = MockInterviewerData::interview('int-ns204-portfolio');
        $this->assertNotNull($maya);
        $this->assertSame('maya-chen', $maya['applicant_id']);
        $this->assertSame('ns-204', $maya['job_id']);
        $this->assertSame('draft', $maya['evaluation_status']);
        $this->assertTrue($maya['is_active_upcoming']);

        $cancelled = MockInterviewerData::interview('int-ns204-cancelled-sample');
        $this->assertNotNull($cancelled);
        $this->assertFalse($cancelled['is_active_upcoming']);
        $this->assertNull($cancelled['live_route']);
        $this->assertNotContains('int-ns204-cancelled-sample', $thisWeekIds);

        $this->get(route('interviewer.dashboard'))
            ->assertOk()
            ->assertSee('hb_iv_int-ns204-portfolio_session_v1', false)
            ->assertSee('Maya Chen', false);

        $this->get(route('interviewer.interviews.prepare', 'int-ns204-portfolio'))
            ->assertOk()
            ->assertSee('Maya Chen', false)
            ->assertSee('NS-204', false);

        $this->get(route('interviewer.interviews.live', 'int-ns204-portfolio'))
            ->assertOk()
            ->assertSee('Maya Chen', false);

        $this->get(route('interviewer.evaluations.create', 'int-ns204-portfolio'))
            ->assertOk()
            ->assertSee('data-feedback-share-toggle', false)
            ->assertSee('Draft · Not submitted', false)
            ->assertSee('Competency ratings help structure feedback', false)
            ->assertDontSee('F8-G28', false)
            ->assertSee('device only', false);
    }

    public function test_company_evaluation_review_states(): void
    {
        $this->get(route('company.evaluations.show', 'eval-ethan-cole'))
            ->assertOk()
            ->assertSee('data-evaluation-completion="complete"', false)
            ->assertSee('High scores do not auto-promote', false);

        $this->get(route('company.evaluations.show', 'eval-maya-chen-pending'))
            ->assertOk()
            ->assertSee('data-evaluation-completion="awaiting"', false)
            ->assertSee('data-recommendation-state="draft"', false)
            ->assertSee('Browser draft only', false)
            ->assertSee('Awaiting evaluations', false);

        $this->get(route('company.evaluations.show', 'eval-conflict-sample'))
            ->assertOk()
            ->assertSee('data-evaluation-completion="complete_conflict"', false)
            ->assertSee('Conflicting recommendations', false)
            ->assertSee('HR review required', false);
    }

    public function test_offer_lifecycle_candidate_and_company(): void
    {
        $active = OfferLifecyclePresentation::enrich(['status_key' => OfferLifecyclePresentation::AWAITING]);
        $this->assertTrue($active['can_accept']);

        $expired = OfferLifecyclePresentation::enrich(['status_key' => OfferLifecyclePresentation::EXPIRED]);
        $this->assertFalse($expired['can_accept']);
        $this->assertNotNull($expired['response_blocked_reason']);

        $this->get(route('candidate.offers.show', 'offer-ms108'))
            ->assertOk()
            ->assertSee('data-can-respond="1"', false)
            ->assertSee('Meridian Studio', false)
            ->assertSee('USD', false)
            ->assertSee('No hiring record is created', false);

        $this->get(route('candidate.offers.show', 'offer-ms107-expired'))
            ->assertOk()
            ->assertSee('data-offer-expired', false)
            ->assertSee('data-can-respond="0"', false)
            ->assertSee('data-offer-accept-blocked', false)
            ->assertSee('Expired offers cannot be accepted', false);

        $this->get(route('candidate.offers.show', 'offer-sample-accepted'))
            ->assertOk()
            ->assertSee('data-can-respond="0"', false)
            ->assertSee('already accepted', false);

        $this->get(route('company.offers.create', ['applicant' => 'ethan-cole']))
            ->assertOk()
            ->assertSee('data-salary-band', false)
            ->assertSee('data-salary-benchmark', false)
            ->assertSee('$140,000–$170,000 USD', false)
            ->assertSee('must approve compensation', false)
            ->assertSee('Company approval is distinct from candidate acceptance', false)
            ->assertDontSee('Offer sent successfully', false);
    }

    public function test_cross_role_canonical_ns204_identity(): void
    {
        $candidateInterview = collect(MockCandidateData::interviews()['upcoming'])
            ->firstWhere('id', 'int-ns204-portfolio');
        $companyInterview = MockCompanyData::interview('int-ns204-portfolio');
        $interviewerInterview = MockInterviewerData::interview('int-ns204-portfolio');

        $this->assertSame('maya-chen', $candidateInterview['candidate_id']);
        $this->assertSame('ns-204', $candidateInterview['job_id']);
        $this->assertSame('org-ns-001', $candidateInterview['organization_id']);
        $this->assertSame('maya-chen', $companyInterview['applicant_id']);
        $this->assertSame('ns-204', $companyInterview['job_id']);
        $this->assertSame('Alex Morgan', $companyInterview['interviewer']);
        $this->assertSame('maya-chen', $interviewerInterview['applicant_id']);
        $this->assertSame('ns-204', $interviewerInterview['job_id']);
    }

    public function test_arabic_locale_renders_scheduling_and_offer_copy(): void
    {
        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('candidate.interviews.index'))
            ->assertOk()
            ->assertSee('إعادة الجدولة والإلغاء', false)
            ->assertSee('للعرض فقط', false);

        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('company.interviews.schedule', ['applicant' => 'maya-chen']))
            ->assertOk()
            ->assertSee('محجوز مسبقًا', false)
            ->assertSee('خطر تعارض عند التأكيد', false);

        $this->withUnencryptedCookie(SetFrontendLocale::COOKIE, 'ar')
            ->get(route('candidate.offers.show', 'offer-ms107-expired'))
            ->assertOk()
            ->assertSee('انتهى العرض', false);
    }

    public function test_f9a_f9c_regression_smoke(): void
    {
        $this->get(route('company.dashboard', ['org_status' => 'pending']))
            ->assertOk()
            ->assertSee('Organization pending Super Admin review', false);

        $this->get(route('jobs.show', 'ns-204'))
            ->assertOk()
            ->assertSee('NS-204', false);

        $this->get(route('company.applicants.show', 'noah-williams'))
            ->assertOk()
            ->assertSee('data-ai-failed', false)
            ->assertSee('not a candidate rejection', false);
    }
}
