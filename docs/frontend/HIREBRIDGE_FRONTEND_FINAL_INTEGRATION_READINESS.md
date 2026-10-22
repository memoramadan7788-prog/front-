# Hirebridge — Frontend Final Integration Readiness

**Document type:** Standalone frontend alignment & Backend Integration readiness reference  
**Phase:** F9F (final frontend workflow-alignment batch)  
**Audience:** Backend Team, Product, QA, Frontend maintainers  
**Does not overwrite:** `HIREBRIDGE_FRONTEND_WORKFLOW_ERD_ALIGNMENT_AUDIT.md` or F9A–F9E completion reports  
**Date:** 2026-10-22  

**Legend**

| Label | Meaning |
|---|---|
| Verified in repository | Confirmed by inspecting code/tests in this workspace |
| Proposed pending Backend approval | Contract recommendation — not a live API |
| Source unavailable | PDF not present in workspace; used F8 audit + migrations/models + handoff |

---

## 1. Executive readiness verdict

### Frontend-to-Backend Integration Readiness

**Conditionally Ready for Backend Contract Review**

The Hirebridge Blade frontend is structurally aligned with the Workflow/ERD presentation surface documented in the F8 audit and delivered through F9A–F9F: identity/membership gates, job visibility/eligibility, AI screening presentation, assessments, interview cutoff/conflict, offers, Admin governance, actor-scoped notifications, and domain status dictionaries.

Conditions (do not block contract review; block “go-live”):

1. Product decisions **PQ-01** (Agency screen), **PQ-02** (Candidate Messages), **PQ-04** (exact 12 application statuses), **PQ-03/G28** (scorecard shape) remain open.
2. Trusted **native keyboard accessibility** remains **External QA Pending** (F8-G44).
3. All capability flags and “success” paths remain **presentation-only** until Backend implements authoritative workflows.

**Not production ready. Not Backend Integration complete.** Ready for Backend Team to review proposed contracts and storage migration inventory.

---

## 2. Current frontend baseline

| Item | Value (verified) |
|---|---|
| Stack | Laravel Blade · Tailwind CSS 4 · Vite |
| Application GET routes | **72** (`php artisan route:list --except-vendor`) |
| Automated tests | **258** pass · **0** fail · **0** error · **0** skip |
| `npm run build` | Pass (exit 0) |
| Locales | EN/AR · RTL via `hb_locale` cookie |
| Themes | Light / Dark / System (`hb_theme`) |
| Design system | Frame 51 |
| Data | Mock presentation providers under `app/Support/Frontend/` |
| Auth / persistence / APIs | Not implemented (GET presentation only) |

---

## 3. Official source references

| Source | Availability |
|---|---|
| `docs/frontend/HIREBRIDGE_FRONTEND_WORKFLOW_ERD_ALIGNMENT_AUDIT.md` | Available — authoritative F8 gap register (44 IDs) |
| `docs/frontend/HIREBRIDGE_F9E_COMPLETION_REPORT.md` | Available |
| `docs/backend/HIREBRIDGE_BACKEND_INTEGRATION_HANDOFF.md` | Available — compatibility context only; does not override workflows |
| `Recruitment_Platform_Full_ERD.pdf` | **Unavailable** in workspace |
| `Recruitment_Platform_Workflows.pdf` | **Unavailable** in workspace |
| Migrations / Eloquent models | Available — reference for field names/enums |
| Live routes, views, mocks, lang, JS | Available |

Where PDFs are missing, gap evidence follows the F8 audit register (which was produced against those documents) plus repository models/migrations.

---

## 4. Complete F8 gap closure matrix (all 44)

Statuses used: **Closed** · **Partially Closed** · **Open** · **Blocked** · **Backend-Owned** · **External QA Pending**

| Gap ID | Original Requirement | Priority | F9 Phase | Code Evidence | Test Evidence | Current Status | Remaining Action |
|---|---|---|---|---|---|---|---|
| F8-G01 | Job visibility public/private | P0 | F9B | Job wizard access step | PhaseF9b | **Closed** | — |
| F8-G02 | Nominated-only + list | P0 | F9B | Nominations panel | PhaseF9b | **Closed** | Design polish optional |
| F8-G03 | Stage builder exams/rounds | P0 | F9B | Stages panel | PhaseF9b | **Closed** | Figma refinement optional |
| F8-G04 | openings/deadline/skills/category | P0 | F9B | Wizard fields | PhaseF9b | **Closed** | — |
| F8-G05 | Private eligible listings | P0 | F9B | JobAccessPresentation | PhaseF9b | **Closed** | — |
| F8-G06 | Employee cannot apply public own job | P0 | F9B | Apply gate states | PhaseF9b | **Closed** | — |
| F8-G07 | AI match panel | P0 | F9C | ai-screening-panel | PhaseF9c | **Closed** | — |
| F8-G08 | Override + reason | P0 | F9C | ai-override-modal | PhaseF9c | **Closed** | — |
| F8-G09 | Pending org publish gate | P0 | F9A | org_status capabilities | PhaseF9a | **Closed** | — |
| F8-G10 | Interview <24h not allowed | P0 | F9D | InterviewBookingPresentation | PhaseF9d | **Closed** | — |
| F8-G11 | Candidate onboarding join path | P1 | F9A | onboarding + membership CTA | PhaseF9a | **Closed** | — |
| F8-G12 | Candidate membership form | P1 | F9A | candidate/membership | PhaseF9a | **Closed** | — |
| F8-G13 | Join request role assign | P1 | F9A | employees/requests | PhaseF9a | **Closed** | — |
| F8-G14 | Cover note + AI consent | P1 | F9C | apply form | PhaseF9c | **Closed** | — |
| F8-G15 | Conditional withdraw | P1 | F9C | application show rules | PhaseF9c | **Partially Closed** | Product copy for edge cases |
| F8-G16 | attempt_no / re-apply | P1 | F9C | mocks + banners | PhaseF9c | **Closed** | — |
| F8-G17 | AI failed ≠ reject | P1 | F9C | ai_failed presentation | PhaseF9c | **Closed** | — |
| F8-G18 | Camera/identity consent | P1 | F9C | assessment intro | PhaseF9c | **Closed** | — |
| F8-G19 | Attempts exhausted | P1 | F9C | assessment results | PhaseF9c | **Closed** | — |
| F8-G20 | Proctoring review queue | P1 | F9C | company assessments | PhaseF9c | **Closed** | Figma polish optional |
| F8-G21 | AI question approval gate | P1 | F9C | assessment create | PhaseF9c | **Closed** | — |
| F8-G22 | Optional feedback share | P1 | F9D | scorecard toggle | PhaseF9d | **Closed** | — |
| F8-G23 | Expired offer state | P1 | F9D | OfferLifecyclePresentation | PhaseF9d | **Closed** | — |
| F8-G24 | Salary range + suggestion | P1 | F9D | offer create hints | PhaseF9d | **Closed** | — |
| F8-G25 | Closed job filled reason | P1 | F9B | company jobs closed | PhaseF9b | **Closed** | — |
| F8-G26 | Agency UI vs no ERD agency | P1 | F9E | Review-only banner (honest copy; IDs removed from UI in F9F) | PhaseF9e/f | **Blocked** | **PQ-01** Product: keep/hide/amend ERD |
| F8-G27 | Candidate Messages vs chatbot | P1 | F9E | Messages kept + delivery disclaimer (no gap IDs in F9F) | PhaseF9e/f | **Blocked** | **PQ-02** Product scope |
| F8-G28 | Scorecard vs numeric_score | P1 | F9D | Competency UI + honest note | PhaseF9d | **Blocked** | Product/Tech: map or extend ERD |
| F8-G29 | Status vocabulary | P1 | F9E/F9F | FrontendStatusVocabulary + 12 application keys | PhaseF9e/f | **Partially Closed** | Backend confirm exact enum (**PQ-04**) |
| F8-G30 | Cross-role IDs | P1 | F9E/F9F | org-ns-001 / ns-204 / maya-chen / int-ns204-portfolio | PhaseF9f | **Closed** | Residual MS-108 samples intentional |
| F8-G31 | Actor notifications | P2 | F9E | MockNotificationData | PhaseF9e | **Closed** | Delivery = Backend |
| F8-G32 | Calendar sync stub | P2 | — | Not productized | — | **Blocked** | Product optional UX |
| F8-G33 | Booking conflict UI | P2 | F9D | schedule conflict presentation | PhaseF9d | **Closed** | — |
| F8-G34 | Email verify failed/resent | P2 | F9A | verification.notice states | PhaseF9a | **Closed** | — |
| F8-G35 | One join request at a time | P2 | F9A | membership pending banner | PhaseF9a | **Closed** | — |
| F8-G36 | Skill required toggle | P2 | F9B | wizard skills required | PhaseF9b | **Closed** | — |
| F8-G37 | Salary recommendation chip | P2 | F9D | offer/job suggestion | PhaseF9d | **Closed** | — |
| F8-G38 | Chatbot product UI | P2 | — | No ATS chatbot UI | — | **Blocked** | Product defer |
| F8-G39 | Audit sample realism | P2 | F9E | is_sample + honest label | PhaseF9e | **Closed** | Live audit = Backend |
| F8-G40 | work_mode enum map | P2 | F9F | workModeKey / workModeLabels | PhaseF9f | **Closed** | — |
| F8-G41 | account_type vs admin role | P3 | F9E/F9F | accountTypeLabels excludes admin | PhaseF9f | **Closed** | Documented in contracts |
| F8-G42 | has_no_experience toggle | P3 | — | Not added | — | **Blocked** | Design optional |
| F8-G43 | i18n for new states | P3 | F9A–F9F | EN/AR status_vocab + screens | PhaseF9f | **Closed** | Ongoing as Backend adds copy |
| F8-G44 | Trusted keyboard a11y | P3 | — | Synthetic not accepted | — | **External QA Pending** | Human sign-off checklist |

### Totals by status

| Status | Count |
|---|---|
| Closed | **34** |
| Partially Closed | **2** (G15, G29) |
| Blocked (Product/Design/Tech) | **6** (G26, G27, G28, G32, G38, G42) |
| External QA Pending | **1** (G44) |
| Open (FE must still implement) | **0** |
| Backend-Owned (no FE work now) | **0** as exclusive status — delivery/persistence for closed FE gaps is Backend |

**Frontend-owned blockers remaining:** **0** mandatory FE gaps.  
**Product/Design decisions remaining:** **6** blocked items (+ PQ-04 confirmation on G29).

---

## 5. F9A–F9F delivery summary

| Phase | Focus | Outcome |
|---|---|---|
| F9A | Identity, org approval, membership | Publish gates; join requests; membership drafts |
| F9B | Job visibility, nominations, eligibility | Private/nomination apply gates; wizard stages |
| F9C | AI screening, assessments | AI panel/override; consent; attempts; proctoring |
| F9D | Interviews, scorecards, offers | 24h cutoff; conflict; share toggle; expiry |
| F9E | Admin governance, notifications | Org states; agency scope; actor notifications |
| F9F | Contracts, leakage, readiness | Status dictionaries; UI leakage cleanup; this document |

---

## 6. Canonical mock entity map

| Entity | Canonical ID | Cross-role consumers |
|---|---|---|
| Organization | `org-ns-001` (Northstar Labs) | Admin, Company, Candidate membership, Notifications |
| Owner | Olivia Bennett | Admin org, Company HR |
| Job | `ns-204` (NS-204 Senior Product Designer) | Public, Candidate, Company, Admin moderation sample `job-ns-204` |
| Candidate | `maya-chen` | Applications, interviews, membership `req-maya-chen`, notifications |
| Interview | `int-ns204-portfolio` | Candidate, Company, Interviewer |
| Interviewer | Alex Morgan (`usr-alex`) | Admin users, Interviewer workspace |
| Related offer sample | Ethan Cole / `eval-ethan-cole` / offer drafts | Company evaluations/offers (distinct from Maya interview stage) |
| Secondary public/agency samples | `ms-108`, Meridian, Hirebridge Talent engagements | Intentionally retained for multi-company demos |

Stable IDs were **not** renamed in F9F; governance mismatches were already corrected in F9E.

---

## 7. Frontend-facing data contracts (proposed)

> **Proposed pending Backend Team approval.** These describe presentation resources the UI already consumes from mocks. They are **not** implemented server responses.

### 7.1 Authenticated user

| Field | Required | Notes |
|---|---|---|
| `id` | yes | Stable user id |
| `account_type` | yes | `candidate` \| `company` only |
| `platform_role` | no | e.g. `super_admin` — **not** account_type |
| `display_name`, `email` | yes | |
| `locale`, `theme` | client | May sync later |
| Capabilities | backend | Never trust FE query flags |

### 7.2 Role & company membership

| Field | Required |
|---|---|
| `organization_id` | when member/pending |
| `membership_status` | `none\|pending\|approved\|rejected` |
| `company_role` | when approved (`hr`, `interviewer`, `employee`, …) |
| `requested_role` | when pending |

### 7.3 Organization

| Field | Required |
|---|---|
| `id` | yes (`org-ns-001`) |
| `status` | `pending\|approved\|rejected\|suspended` (+ archived directory) |
| `owner_user_id` | yes |
| `rejection_reason` / `suspension_reason` | when applicable |
| `capabilities.can_publish_jobs` | backend-owned |

### 7.4 Job

| Field | Required |
|---|---|
| `id` | yes (`ns-204`) |
| `organization_id` | yes |
| `visibility` | public/private |
| `private_apply_mode` | when private |
| `work_mode` | `onsite\|remote\|hybrid` |
| `status` | company publication status (distinct from Admin moderation) |
| `openings_count`, `deadline`, skills | as configured |

### 7.5 Application

| Field | Required |
|---|---|
| `id` | yes |
| `job_id`, `candidate_id`, `organization_id` | yes |
| `status` | proposed 12-key set §8 |
| `attempt_no` | yes |
| `cover_note`, `consent_at` | apply |
| Client-only | local draft keys |

### 7.6 AI screening result

| Field | Required |
|---|---|
| `application_id` | yes |
| `run_status` | success/failed/pending |
| `score`, skill matches | when success |
| `human_override` + `reason` | when overridden |
| Sensitive | treat as HR-only |

### 7.7 Assessment & attempt

| Field | Required |
|---|---|
| `assessment_id`, `application_id` | yes |
| `attempts_remaining` | yes |
| `consent` camera/identity | before take |
| `proctoring_flags` | company review |

### 7.8 Interview & booking

| Field | Required |
|---|---|
| `id` | yes (`int-ns204-portfolio`) |
| `job_id`, `applicant_id`, `interviewer_ids` | yes |
| `starts_at` ISO + timezone | yes |
| `status` | proposed/confirmed/completed/cancelled/no_show |
| `can_reschedule` / `reschedule_reason_key` | eligibility flags |
| Client-only | prep checklist drafts |

### 7.9 Interviewer evaluation

| Field | Required |
|---|---|
| `interview_id`, `evaluator_id` | yes |
| `numeric_score`, `internal_notes` | yes (ERD-aligned) |
| `competency_ratings` | **optional / contested (G28)** |
| `share_with_candidate` | boolean |
| `candidate_feedback` | optional |

### 7.10 Offer

| Field | Required |
|---|---|
| `id`, `application_id`, `job_id` | yes |
| `status` | draft/pending_approval/sent/accepted/rejected/expired/withdrawn |
| `compensation` + currency | yes |
| `expires_at` | when sent |
| Sensitive | compensation — role-gated |

### 7.11 Notification

| Field | Required |
|---|---|
| `id`, `actor_role`, `event` | yes |
| `title`, `body` | localized |
| `unread` | yes |
| Deep link ids | org/job/interview/candidate as applicable |
| Backend-owned | delivery, read-state |

### 7.12 Admin governance / audit

| Field | Required |
|---|---|
| `id`, `actor_id`, `action`, `target_type`, `target_id` | yes |
| `result` | success/blocked |
| `is_sample` | until live ingestion |
| Writes | Backend + MFA + audit append-only |

**Money:** major currency code + minor units or decimal string — confirm with Backend.  
**Dates:** ISO-8601 with timezone; UI currently uses Central labels in mocks.

---

## 8. Domain status dictionaries

Domain enums remain **separate** (do not unify).

**Application (proposed 12):**  
`submitted`, `ai_review`, `ai_failed`, `shortlisted`, `rejected`, `exam`, `interview`, `offer`, `hired`, `withdrawn`, `expired`, `archived`  
*(Confirm with Backend — PQ-04.)*

**Organization:** `pending`, `approved`/`verified_active`, `rejected`, `suspended`, `archived`

**Membership:** `none`, `pending`, `approved`, `rejected`

**Account:** `active`, `invited`, `suspended`

**Offer / Interview / Job moderation / work_mode:** see `FrontendStatusVocabulary` + `lang/*/status_vocab.php`

Company pipeline **stage** labels (`applied`, `screening`, …) are UI groupings, not the full application.status set.

---

## 9. Browser storage migration inventory

| Key | Current Owner | Stored Data | Sensitive? | Current Behavior | Future Backend Resource | Migration Recommendation |
|---|---|---|---|---|---|---|
| `hb_theme` | All | theme preference | No | localStorage | user preferences | Keep client-first; optional sync |
| `hb_locale` | All | locale (+ cookie) | No | cookie `hb_locale` + localStorage | user preferences | Cookie authoritative for SSR |
| `hb_candidate_saved_jobs_v2` | Candidate | job ids | Low | local list | saved_jobs | Migrate on login |
| `hb_candidate_message_draft_{thread}` | Candidate | draft text | Possibly PII | local draft | messages (if approved) | Discard or migrate per PQ-02 |
| `hb_candidate_profile_draft_v1` | Candidate | profile draft | PII | local | profile | Replace with server draft |
| `hb_candidate_apply_draft_{job}` | Candidate | apply form | PII | local | applications draft | Server-side draft |
| `hb_candidate_preferences_v1` | Candidate | prefs | Low | local | candidate_preferences | Sync |
| `hb_candidate_membership_request_v1` | Candidate | join request draft | PII | local preview | membership_requests | Authoritative server |
| `hb_candidate_notifications_v1` (+ role variants) | Per actor | unread map | Low | local read-state | notifications | Server read-state |
| `hb_assessment_result_{id}` | Candidate | result preview | Medium | local | exam_attempts | Server only |
| `hb_onboarding_candidate_v1` / `hb_onboarding_company_v1` | Onboarding | wizard progress | PII | local | onboarding sessions | Server |
| `hb_company_job_draft_*` | Company | job wizard | Company-sensitive | local | jobs draft | Server |
| `hb_company_profile_draft_v1` | Company | profile | Company-sensitive | local | companies | Server |
| `hb_assessment_draft` | Company | assessment builder | Medium | local | job_exams | Server |
| `hb_offer_draft_{id}` | Company | offer draft | High (comp) | local | offers | Server + ACL |
| `hb_applicant_note_{id}` | Company | HR notes | High | local | application notes | Server + ACL |
| `hb_talent_shortlist` | Company | shortlist ids | Medium | local | talent_pool | Server |
| `hb_interview_prep_{id}` | Candidate | prep checklist | Low | local | optional | Keep client or sync |
| `hb_iv_*_session_v1` / scorecard keys | Interviewer | prep/scorecard drafts | High (confidential) | local | interview_evaluations | Server; never leave device long-term |
| `hb_interviewer_availability_prefs_v1` | Interviewer | availability UI | Low | local | interviewer_availability | Server + calendar later |

**Do not migrate data in F9F.** Treat workflow-authoritative keys as Backend-owned after integration.

---

## 10. Workflow-to-screen matrix (summary)

| Workflow | Primary screens | FE alignment |
|---|---|---|
| WF1 Discover/Apply | Public jobs, Candidate apply | Aligned (presentation) |
| WF2 Identity/Org/Membership | Onboarding, membership, Admin orgs, join requests | Aligned; Admin writes Backend |
| WF3 Jobs visibility | Company wizard, eligibility | Aligned |
| WF4 Pipeline/AI/Offers | Applicants, AI panel, offers | Aligned presentation |
| WF5 Assessments | Candidate + Company assessments | Aligned presentation |
| WF6 Interviews | Schedule, prepare, live, scorecard | Aligned presentation; G28 blocked |
| Admin ops | Orgs, users, jobs, agencies, audit | Aligned presentation; G26 blocked |

---

## 11. Cross-role journey evidence

| Journey | Route/Actor | Action | Verification Method | Expected | Observed | Pass/Fail/Not Executed |
|---|---|---|---|---|---|---|
| A Company approval | onboarding status + company dashboard pending | HTTP | HTTP 200 + PhaseF9a | Pending gates publish | Pending banner / disabled create | **Pass** (HTTP/tests) |
| B Membership | membership → join requests | HTTP + tests | Pending Maya | Pending; not employee | **Pass** |
| C Public apply | `/jobs/ns-204` → apply | HTTP | Apply reachable | 200 | **Pass** (HTTP) |
| D Private/nomination | PhaseF9b tests | Automated | Eligibility gates | Assertions green | **Pass** |
| E AI screening | applicants/maya-chen | HTTP + PhaseF9c | Panel + override preview | 200 / tests | **Pass** |
| F Assessments | candidate assessments | HTTP + PhaseF9c | Consent/attempts | 200 / tests | **Pass** |
| G Interviews | prepare int-ns204-portfolio | HTTP + PhaseF9d | Cutoff/canonical ids | 200 / tests | **Pass** |
| H Offers | candidate offers + company create | HTTP + PhaseF9d | Expiry/review-only | tests green | **Pass** |
| I Admin governance | org-ns-001, agencies, jobs | HTTP + PhaseF9e + prior pointer | Preview-only | 200; no gap-id leak | **Pass** |
| J Notifications/Messages | actor dropdowns + messages | HTTP + PhaseF9e/f | Actor copy; local messages | Actor-scoped; honest disclaimer | **Pass** |

Pointer browser for F9F org/job modals was previously executed in F9E; F9F re-verified leakage and routes via HTTP + suite. Full viewport/theme matrix: **Not Executed** as exhaustive visual suite.

---

## 12. Navigation audit

Verified (tests/routes):

- Public job → Candidate apply entry for `ns-204`
- Company Job → Applicants pipeline links
- Company Evaluation deep links remain applicant-scoped (notification hrefs avoid contaminating unrelated applicant pages)
- Interviewer Schedule → Preparation / Live / Scorecard for `int-ns204-portfolio`
- Admin Organizations → show
- Notification actor deep links to existing named routes
- Membership / eligibility CTAs
- **No** `company/messages` route

No confirmed wrong-identity deep links remaining after F9E/F9F notification href discipline.

---

## 13. UI content leakage findings (F9F)

**Removed / rewritten from production-facing copy:**

- Gap IDs (`F8-G26`, `F8-G27`, `F8-G28`, `F8-G39`, …)
- “Experimental ERD / prototype queue / Backend contract mismatch” developer phrasing
- Agency & Messages banners rewritten to honest product language without internal IDs

**Retained as necessary honesty:**

- “Not available yet” / “preview” / “draft saved on this device” / “no booking created”
- Sample audit labeling

**Developer-only comments** in Blade (`{{-- F9C --}}`) and PHPDoc remain in source — not rendered.

**Showcase** (`frontend/components`) may mention Frame 51 — design-system preview surface.

---

## 14. EN/AR/RTL/theme/responsive results

| Combination | Executed? | Result |
|---|---|---|
| EN Admin/Candidate key screens | Yes (tests/HTTP) | Pass |
| AR Admin agencies + messages + status labels | Yes (PhaseF9f) | Pass |
| RTL dir via locale middleware | Yes when `hb_locale=ar` | Pass (structural) |
| Light/Dark/System controls present | Yes (prior browser + markup) | Pass presence |
| 390 / 768 / 1440 exhaustive | **Not Executed** | Manual QA recommended |
| Full theme × locale × viewport matrix | **Not Executed** | Do not claim complete |

---

## 15. Accessibility sign-off status

| Item | Status |
|---|---|
| Semantic controls, labels, modals (code review) | Generally present |
| Disabled-state explanations for gates | Present on org pending / interview cutoff |
| **Trusted native keyboard testing** | **External QA Pending (F8-G44)** |
| Synthetic automation keyboard | **Not accepted** as sign-off |

### Manual sign-off appendix (24-item checklist reference)

Use the existing frontend accessibility acceptance checklist (24 items). Sign-off requires a human tester with:

1. Keyboard-only traversal of Candidate, Company, Interviewer, Admin shells  
2. Skip link, focus visible, modal focus trap/restore  
3. Form error association  
4. Disclosure/dropdowns  
5. Mobile drawer  
6. EN + AR passes  

Until signed, platform a11y status remains **Conditionally Accepted**.

---

## 16. Design / Product approvals pending

| ID | Decision |
|---|---|
| PQ-01 | Agency Admin screen keep / hide / ERD amend |
| PQ-02 | Candidate Messages product scope vs chatbot/notifications |
| PQ-03 / G28 | Competency scorecard vs ERD numeric_score |
| PQ-04 | Exact application.status 12-list |
| PQ-32/38/42 | Calendar stub, chatbot UI, has_no_experience |
| Design | Optional Figma polish for workflow-derived panels |

---

## 17. Backend-owned dependencies

- Authentication, RBAC, tenancy  
- Persistence for all workflow transitions  
- Notification/email/push delivery  
- Real AI scoring & assessment proctoring  
- Interview booking conflicts as server truth  
- Offer acceptance / hiring  
- Admin moderation & audit ingestion  
- File/CV storage  
- Calendar OAuth  

Frontend capability flags must be replaced by server-evaluated policies.

---

## 18. Recommended Backend Handoff corrections

Relative to `docs/backend/HIREBRIDGE_BACKEND_INTEGRATION_HANDOFF.md`:

1. Treat **F9F contracts + status dictionaries** as the FE consumption contract source of truth for review.  
2. Do not invent agency schema without **PQ-01**.  
3. Do not implement Company Messages without **PQ-02**.  
4. Scorecard persistence must resolve **G28** before locking API shape.  
5. Confirm application.status enum (**PQ-04**) before migrating mocks.  
6. Plan localStorage migration using §9 inventory; prioritize PII and compensation drafts.  
7. Admin audit events are samples until append-only ingestion exists.

---

## 19. Remaining blockers and risks

| Risk | Severity | Mitigation |
|---|---|---|
| Open Product scope (agency/messages/scorecard) | High for schema | Resolve PQs before coding those domains |
| Keyboard a11y unsigned | Medium for release | External QA |
| Mock → real auth gap | High | Never ship mocks as auth |
| Dual status vocab (pipeline vs application.status) | Medium | Backend mapping table |
| Client drafts with PII | High | Early server drafts + purge guidance |

---

## 20. Final recommendations

1. **Proceed to Backend Contract Review** using this document + F8 audit + backend handoff.  
2. Resolve Product PQs in a short decision workshop before schema freeze for contested domains.  
3. Schedule trusted keyboard QA in parallel with Backend Phase 0.  
4. Keep Frontend presentation GET surface stable; integrate behind feature flags/services without inventing write UX that implies persistence.  
5. **Do not start Backend Integration coding until contracts for identity, org, job, application, and interview are approved.**

---

## Appendix A — F9F code changes (summary)

- Rewrote production copy removing gap IDs / ERD developer leakage (Admin agency/jobs/audit, Candidate messages, Interviewer/Company evaluation notes, Company wizard notes).  
- Expanded `FrontendStatusVocabulary` (application 12, offer, interview, work_mode, account_type).  
- Added `tests/Feature/PhaseF9fFinalReadinessTest.php`.  
- Updated PhaseF9e/F9d assertions for honest copy.

## Appendix B — Source limitation

`Recruitment_Platform_Full_ERD.pdf` and `Recruitment_Platform_Workflows.pdf` were **not found** in the workspace at F9F time. Alignment claims rely on the committed F8 audit register, migrations/models, and prior phase reports.

---

**STOP:** Frontend F9F complete. Await explicit approval before any Backend Architecture or Integration phase.
