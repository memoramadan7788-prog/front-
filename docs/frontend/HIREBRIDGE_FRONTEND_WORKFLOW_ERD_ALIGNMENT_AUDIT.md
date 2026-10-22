# Hirebridge — Phase F8
# Frontend Workflow, ERD & Data Contract Alignment Audit

**Document type:** Read-only frontend alignment assessment (no implementation)  
**Phase:** F8 — Frontend Workflow & ERD Alignment Audit  
**Primary deliverable path:** `docs/frontend/HIREBRIDGE_FRONTEND_WORKFLOW_ERD_ALIGNMENT_AUDIT.md`  
**Audience:** Product, Design, Frontend, Backend, QA  
**Git revision (Verified):** `aca79424eed60e7cc7ae7622d083171438a9dfbe`  
**Audit date:** 2026-10-22  

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Verified Source Inventory](#2-verified-source-inventory)
3. [Workflow-to-Screen Coverage Matrix](#3-workflow-to-screen-coverage-matrix)
4. [ERD-to-Frontend Data Mapping](#4-erd-to-frontend-data-mapping)
5. [Critical Workflow Rule Coverage](#5-critical-workflow-rule-coverage)
6. [Cross-Role Record Consistency](#6-cross-role-record-consistency)
7. [Frontend UX State Inventory](#7-frontend-ux-state-inventory)
8. [Prioritized Gap Register](#8-prioritized-gap-register)
9. [Proposed F9 Implementation Batches](#9-proposed-f9-implementation-batches)
10. [Draft Frontend Data Contracts](#10-draft-frontend-data-contracts)
11. [Frontend vs Backend Responsibility](#11-frontend-vs-backend-responsibility)
12. [Product / Design Questions](#12-product--design-questions)
13. [Acceptance Criteria for F9](#13-acceptance-criteria-for-f9)
14. [Recommended Implementation Order](#14-recommended-implementation-order)
15. [F8 Readiness Verdict](#15-f8-readiness-verdict)
16. [Appendix A — Coverage Classification Key](#16-appendix-a--coverage-classification-key)
17. [Appendix B — Evidence Index](#17-appendix-b--evidence-index)

**Evidence labels:** Verified in repository · Verified in official PDF · Inferred · Proposed · Product decision

---

## 1. Executive Summary

### 1.1 Purpose

Determine whether the Conditionally Accepted Hirebridge Blade frontend fully represents the **official** recruitment workflows and ERD relationships **before** Backend Integration planning or F9 implementation.

### 1.2 Verdict (F8)

**Frontend is not yet workflow/ERD-aligned for Backend Integration.**

The UI covers many **presentation journeys** from Figma Frames 01–51, but several **documented product rules** from the official Workflows and ERD are missing, incomplete, or inconsistent in the frontend:

| Area | Assessment |
|---|---|
| Happy-path hiring story (discover → apply → interview → offer) | Largely present as presentation |
| Public vs private vs nominated job eligibility | **Major gaps** |
| Job wizard stage configuration (exams + interview rounds) | **Missing / incomplete** |
| AI CV match + reasoned override | **Missing as HR UI** |
| Assessment proctoring / attempt failure paths | **Incomplete** |
| Interview 24-hour cancel/reschedule rule | **Not represented** |
| Company approval gate before publish | **Not represented** |
| Admin agency module vs official ERD | **ERD has no agency entity** — product/scope conflict |
| Candidate Messages module vs official ERD | **ERD has notifications + chatbot, not ATS messaging** — product/scope conflict |
| Trusted keyboard a11y | Still pending (separate gate) |

### 1.3 Counts (this audit)

| Severity | Count |
|---|---|
| **P0** | **10** |
| **P1** | **20** |
| **P2** | **10** |
| **P3** | **4** |
| **Total registered gaps** | **44** |

### 1.4 Highest-impact frontend changes (before backend)

1. Job create/publish UI: visibility (public/private), `private_apply_mode`, nomination list, openings, deadline, exam + interview round configuration.  
2. Application eligibility presentation: employees cannot apply to own company’s public jobs; private/nominated eligibility states.  
3. AI screening review UI: score, matched/missing skills, failed≠rejected, override with reason.  
4. Withdrawal eligibility messaging (“only before review”) vs always-unavailable stub.  
5. Interview reschedule/cancel: surface **24h rule** (disable + explain when not allowed).  
6. Assessment: camera consent, attempt limit exhaustion, proctoring flag review (HR), no auto-fail from flags alone.  
7. Resolve **Agency Admin UI** and **Candidate Messages** against ERD (keep as design-only, hide, or get product approval as out-of-ERD extensions).  
8. Canonical mock ID/status contract across roles for NS-204 / Maya Chen / int-ns204-portfolio.

### 1.5 What this audit is not

- Not F9 implementation.  
- Not Backend Integration / handoff revision.  
- Not claiming keyboard accessibility Pass.

---

## 2. Verified Source Inventory

### 2.1 Source availability gate

| Source | Accessible path | Status |
|---|---|---|
| Official ERD PDF | `/home/ubuntu/.cursor/projects/home-ubuntu-projects-Recuituitment-Platform/attachments/207c0f17-089a-491a-bb8f-923717692782/Recruitment_Platform_Full_ERD.pdf` | **Accessible** (6 pages, image-based; inspected via rendered PNGs) |
| Official Workflows PDF | `…/attachments/…/Recruitment_Platform_Workflows.pdf` | **Accessible** (6 pages; inspected via rendered PNGs) |
| Duplicate copies | `Recruitment_Platform_Full_ERD_1.pdf`, `Recruitment_Platform_Workflows_1.pdf` | Same content (Verified by size/date) |
| Repository | `/home/ubuntu/projects/Recuituitment_Platform` | Accessible |
| Git revision | `aca79424eed60e7cc7ae7622d083171438a9dfbe` | Verified |
| Working tree | `?? docs/` only (pre-existing backend handoff + this frontend audit) | Verified via `git status` |
| Standalone ERD/workflow docs inside repo `docs/` | Not present as PDFs | N/A — attachments used |
| Backend handoff (supplementary) | `docs/backend/HIREBRIDGE_BACKEND_INTEGRATION_HANDOFF.md` | Present; **not** authoritative vs official PDFs |
| Frontend acceptance status | Conditionally Accepted; keyboard pending | Contextual |

**PDF text extraction:** `pdftotext` returned empty (scanned/diagram PDFs). Content verified by rendering pages with `pdftoppm` and inspecting each page image.

### 2.2 Official ERD sections inspected

| Page | Title (Verified on diagram) |
|---|---|
| ERD 1/6 | Identity, Roles and Company |
| ERD 2/6 | Candidate Profile |
| ERD 3/6 | Jobs, Applications and Offers |
| ERD 4/6 | Interviews and Scheduling |
| ERD 5/6 | Exams and Proctoring |
| ERD 6/6 | AI Review, Salary, Notifications and Governance |

### 2.3 Official Workflow sections inspected

| Page | Title (Verified on diagram) |
|---|---|
| WF 1/6 | End-to-end overview |
| WF 2/6 | Registration and onboarding (candidate, employee, company) |
| WF 3/6 | Posting a job and applying |
| WF 4/6 | AI screening, selection and offer |
| WF 5/6 | Exam, proctoring and grading |
| WF 6/6 | Interview scheduling and evaluation |

### 2.4 Frontend inventory (Verified)

- **71** named GET routes (`php artisan route:list --except-vendor`)  
- **196** PHPUnit tests passing (presentation suite)  
- Mock providers: `MockPublicData`, `MockCandidateData`, `MockCompanyData`, `MockInterviewerData`, `MockAdminData`  
- Controllers under `app/Http/Controllers/{Public,Auth,Onboarding,Candidate,Company,Interviewer,Admin}`  
- Client logic: `resources/js/app.js` + view-inline scripts  
- Locale: `lang/en`, `lang/ar`  

### 2.5 Schema alignment note (Verified)

Eloquent models/migrations largely mirror the official ERD entities (jobs visibility, nominations, cv_match_results, exam_attempts, interview_bookings, etc.). Frontend mocks and Blade journeys **do not yet expose many of these fields/states**. Schema existence ≠ UI coverage.

---

## 3. Workflow-to-Screen Coverage Matrix

Coverage key: see [Appendix A](#16-appendix-a--coverage-classification-key).

### 3.1 Workflow 1 — End-to-end overview

| Workflow | Step / Decision | Actor | Required UI | Existing Route/View | Coverage | Required Change | Priority |
|---|---|---|---|---|---|---|---|
| WF1 | Register + verify email | Candidate/Employee | Register + verification notice | `register`, `verification.notice` | Implemented but incomplete | Wire real verify later; show pending/verified states clearly | P1 |
| WF1 | Account type? | User | Candidate vs Company choice | `register` / onboarding split | Implemented and aligned | Keep; ensure exclusive paths | P2 |
| WF1 | Candidate profile (CV optional) | Candidate | Profile onboarding | `onboarding.candidate`, `candidate.profile` | Implemented but incomplete | Align mandatory phone/location (WF2) | P1 |
| WF1 | Employee asks to join company (optional) | Candidate | Join-request UI | Partial: company `employees.requests`; candidate request entry weak | Missing frontend state | Candidate “request to join” screen/step | P1 |
| WF1 | Register company + documents | Company | Company onboarding + doc upload | `onboarding.company` | Implemented but incomplete | Proof document field prominence; pending status | P1 |
| WF1 | Super Admin approves company | Admin | Org verify UI | `admin.organizations.*` | Implemented but incomplete | Explicit approve/reject reason states vs preview | P1 |
| WF1 | Admin approves/rejects employee | Company Admin | Join request queue | `company.employees.requests` | Implemented and aligned | Role assign on approve (HR/Interviewer/Employee) | P1 |
| WF1 | Active employees see private jobs | Employee | Private job visibility | Public `jobs.*` + candidate jobs mostly public catalog | Missing frontend state | Private job lists + eligibility banners | P0 |
| WF1 | HR posts job public/private | HR | Job wizard visibility | `company.jobs.create` | Implemented but inconsistent | Add visibility + nomination (WF3) | P0 |
| WF1 | Candidate applies (CV required) | Candidate | Apply wizard | `candidate.applications.apply` | Implemented but incomplete | Enforce CV required messaging; attempt_no | P1 |
| WF1 | AI CV match | System | Status/progress for HR+candidate | Not a dedicated UI | Missing frontend screen | Screening result panel | P0 |
| WF1 | HR review accept/override AI | HR | Review + override reason | Applicants show lacks AI override | Missing frontend screen | AI review decision UI | P0 |
| WF1 | Exam timed/proctored | Candidate/HR | Assessment take + review | `candidate.assessments.*`, `company.assessments.*` | Implemented but incomplete | Camera consent, flags, attempt fail | P1 |
| WF1 | Interview rounds booking+eval | Multi | Schedule + scorecard | Company/Interviewer/Candidate interview routes | Implemented but incomplete | 24h rule; shared feedback | P1 |
| WF1 | Company final decision | HR | Select/reject | Pipeline stages mock | Implemented but incomplete | Explicit decision action states | P2 |
| WF1 | Offer accept/reject/expire | Candidate/HR | Offer UI | `candidate.offers.*`, `company.offers.create` | Implemented but incomplete | Expire state; salary-in-range guidance | P1 |
| WF1 | Hired / Rejected notified | System | Terminal states + notifications | Partial badges + notifications mock | Implemented but incomplete | Consistent terminal banners | P2 |

### 3.2 Workflow 2 — Registration and onboarding

| Workflow | Step / Decision | Actor | Required UI | Existing | Coverage | Required Change | Priority |
|---|---|---|---|---|---|---|---|
| WF2 | Register name/email/password | User | Auth register | `register` | Implemented and aligned | — | — |
| WF2 | Verify email | System | Verification pending/success/fail | `verification.notice` | Implemented but incomplete | Failure/resend presentation | P2 |
| WF2 | Choose account type | User | Branch UI | Present | Implemented and aligned | — | — |
| WF2 | Candidate profile phone+location required | Candidate | Form fields | Onboarding candidate | Implemented but incomplete | Mark required per WF2 | P1 |
| WF2 | Optional experience/skills/CV/education/courses/social | Candidate | Optional sections | Profile/onboarding | Implemented and aligned | Ensure optional not blocking | P3 |
| WF2 | Works at a company? | Candidate | Decision UI | Weak/missing in onboarding | Missing frontend state | Explicit yes/no + company picker | P1 |
| WF2 | Pick company + job title + department | Candidate | Request form | Not clear on candidate side | Missing frontend screen | Candidate join-request form | P1 |
| WF2 | One company request at a time | System rule | Disable concurrent requests | Not shown | Missing frontend state | Banner + disabled CTA | P2 |
| WF2 | Notify company admin | System | Admin notification | Notifications mock | Implemented but incomplete | Join-request notification type | P2 |
| WF2 | Approve + assign role (HR/Interviewer/Employee) | Company Admin | Role select on approve | `employees.requests` | Implemented but incomplete | Ensure role choices match ERD/seeders | P1 |
| WF2 | Reject + reason + notify | Company Admin | Reason field | Partial | Implemented but incomplete | Required reason + candidate-visible outcome | P1 |
| WF2 | Company register + proof doc | Company | Doc upload | Onboarding company | Implemented but incomplete | `doc_path` UX | P1 |
| WF2 | Company pending → Super Admin review | Admin | Pending queue | Admin orgs pending tab | Implemented and aligned | — | — |
| WF2 | Approve → can publish jobs | Company | Unlocked publish | Publish not gated by approval in UI | Missing frontend state | Disable publish when company pending | P0 |
| WF2 | Reject/suspend + reason + notify owner | Admin | Rejection UI | Preview modals | Implemented but incomplete | Reason required presentation | P1 |

### 3.3 Workflow 3 — Posting a job and applying

| Workflow | Step / Decision | Actor | Required UI | Existing | Coverage | Required Change | Priority |
|---|---|---|---|---|---|---|---|
| WF3 | Create job draft (category, skills, salary, deadline, openings) | HR | Wizard fields | `company.jobs.create` has salary; category/skills/deadline/openings weak | Implemented but incomplete | Add missing fields | P0 |
| WF3 | Configure stages: exam(s) + interview rounds | HR | Stage builder | Not found in wizard | Missing frontend screen | Exam attach + round_no/type/duration | P0 |
| WF3 | Visibility public vs private | HR | Visibility control | Not in wizard grep | Missing frontend state | Radio/select visibility | P0 |
| WF3 | Private: all employees vs nominated only | HR | `private_apply_mode` | Schema yes; UI no | Missing frontend state | Mode selector | P0 |
| WF3 | Build nomination list | HR | Nominee picker | No nomination UI in views | Missing frontend screen | Nomination manager | P0 |
| WF3 | Publish public / private | HR | Publish CTA | Draft checklist exists | Implemented but incomplete | Publish preview must show visibility | P1 |
| WF3 | Regular candidates apply; **company employees cannot apply to own public jobs** | Candidate/Employee | Eligibility messaging | Not represented | Missing frontend state | Forbidden apply state | P0 |
| WF3 | Eligible employees apply to private | Employee | Private job apply path | Missing | Missing frontend state | Private catalog | P0 |
| WF3 | Apply: CV required, cover note, AI consent | Candidate | Apply form | Apply has resume/consent-ish; cover_note unclear | Implemented but incomplete | Explicit AI consent + cover_note | P1 |
| WF3 | Status history starts | System | Timeline | Application activity mock | Implemented and aligned | Keep shape compatible with history entity | P3 |
| WF3 | Withdraw only before review | Candidate | Conditional withdraw | Always “unavailable” stub (`app.js` withdraw preview) | Implemented but inconsistent | Show allowed vs blocked with rule | P1 |
| WF3 | Job closes: deadline / manual / openings filled | HR/System | Closed states | Closed tab exists | Implemented but incomplete | Openings-filled reason; re-apply messaging | P1 |
| WF3 | Re-apply only if re-published (new attempt) | Candidate | Attempt indicator | `attempt_no` not in FE mocks clearly | Missing frontend state | Show attempt + blocked re-apply | P1 |

### 3.4 Workflow 4 — AI screening, selection and offer

| Workflow | Step / Decision | Actor | Required UI | Existing | Coverage | Required Change | Priority |
|---|---|---|---|---|---|---|---|
| WF4 | Application submitted | Candidate | Confirmation | `applications.submitted` | Implemented and aligned | — | — |
| WF4 | AI CV match (score, skills) | System/HR | Match panel | No company AI match UI | Missing frontend screen | Display score/skills/summary/run_status | P0 |
| WF4 | AI failed/timeout ≠ rejection | HR | Failed state + manual review | Absent | Missing frontend state | Explicit “AI failed — review manually” | P0 |
| WF4 | Score threshold (e.g. >80%) | System/HR | Queue vs low-match flag | Absent | Missing frontend state | Flagged low match list | P1 |
| WF4 | HR shortlist / reject / override+reason | HR | Decision UI | Pipeline moves only (preview) | Implemented but incomplete | Override form bound to `ai_review_decisions` | P0 |
| WF4 | Run stages in order exam→interview | System | Stage progress | Partial timelines | Implemented but incomplete | Ordered stage checklist per job | P1 |
| WF4 | All stages passed? | HR | Gate to final decision | Implicit | Implemented but incomplete | Block offer if stages incomplete (presentation) | P2 |
| WF4 | Create offer (salary in job range; AI salary suggestion) | HR | Offer create | `company.offers.create` | Implemented but incomplete | Range validation UX + benchmark suggestion | P1 |
| WF4 | Candidate accept/reject/expire | Candidate | Offer response | Preview disabled responses | Implemented but incomplete | Expired state UI | P1 |
| WF4 | Openings filled → job closes | System | Auto-close messaging | Missing | Missing frontend state | Banner when openings filled | P2 |

### 3.5 Workflow 5 — Exam, proctoring and grading

| Workflow | Step / Decision | Actor | Required UI | Existing | Coverage | Required Change | Priority |
|---|---|---|---|---|---|---|---|
| WF5 | HR creates exam manual/AI questions | HR | Assessment create | `company.assessments.create` | Implemented but incomplete | Question types MCQ/written/voice/coding | P1 |
| WF5 | Reviewer approves AI questions | HR | Review gate | Not explicit | Missing frontend state | AI question approval queue | P1 |
| WF5 | Exam stage opens (stage_order) | System | Candidate unlock | Some “opens after screening” copy | Implemented but incomplete | Stage-order driven availability | P1 |
| WF5 | Notify candidate | System | Notification | Mock notifications | Implemented but incomplete | Assessment-assigned type | P2 |
| WF5 | Camera consent + identity check | Candidate | Consent UI | Consent checkbox on intro (not clearly camera) | Implemented but inconsistent | Camera consent + identity copy | P1 |
| WF5 | Attempt within limit (max 3) | Candidate | Attempt counter | Shown on intro/index | Implemented and aligned | Exhausted state screen | P1 |
| WF5 | Timed exam + autosave answers | Candidate | Take UI | `assessments.take` | Implemented but incomplete | Server-timer messaging (FE must not own authority) | P1 |
| WF5 | Proctoring flags with evidence (no auto-fail) | System/HR | Flag review | No HR proctoring review UI found | Missing frontend screen | Proctoring review queue | P1 |
| WF5 | Human decides on flags | HR | Decision UI | Missing | Missing frontend screen | decision + reviewed_at | P1 |
| WF5 | Auto-grade MCQ / AI+rubric others | System/HR | Results | `result` pages exist | Implemented but incomplete | Distinguish grading_method | P2 |
| WF5 | Pass → next stage / fail → attempts left | Candidate | Outcome states | Partial | Implemented but incomplete | Loop vs stage-failed terminal | P1 |

### 3.6 Workflow 6 — Interview scheduling and evaluation

| Workflow | Step / Decision | Actor | Required UI | Existing | Coverage | Required Change | Priority |
|---|---|---|---|---|---|---|---|
| WF6 | HR assigns interviewer for round | HR | Assign UI | Schedule form interviewer select | Implemented and aligned | Tie to `job_interview_rounds.round_no` | P1 |
| WF6 | Interviewer sets availability (+ calendar sync) | Interviewer | Availability UI | `interviewer` slots + localStorage prefs | Implemented but incomplete | Calendar sync is presentation-only | P2 |
| WF6 | Candidate picks free slot | Candidate | Slot picker | Candidate interview UI has slots | Implemented but incomplete | Exclusive lock messaging | P1 |
| WF6 | Booking locks slot (no double book) | System | Conflict error | Preview only | Backend-only rule + FE error state | Show conflict/unavailable | P1 |
| WF6 | Notify both sides | System | Notifications | Mock | Implemented but incomplete | Interview booked type | P2 |
| WF6 | Change/cancel? ≥24h before? | Candidate/HR | Reschedule/cancel with gate | Reschedule modal **preview**; no 24h rule | Missing frontend state | Disable + “Not allowed” when <24h | P0 |
| WF6 | Interview held onsite/online/custom | Interviewer | Live workspace | `interviewer.interviews.live` | Implemented and aligned | meeting_link/location display | P3 |
| WF6 | Evaluation score + internal notes | Interviewer | Scorecard | `interviewer.evaluations.create` | Implemented and aligned | Align to numeric_score + notes; competencies are FE extension | P2 |
| WF6 | Optional feedback shared with candidate | Interviewer/Candidate | Share toggle + candidate view | Unclear/incomplete | Missing frontend state | `feedback_shared_at` UX | P1 |
| WF6 | More rounds? | HR | Next round loop | Partial | Implemented but incomplete | Round progression UI | P1 |
| WF6 | Back to selection (WF4) | HR | Navigation | Pipeline links | Implemented and aligned | — | — |

---

## 4. ERD-to-Frontend Data Mapping

| ERD Entity | Relevant Frontend Screens | Existing Mock Fields | Required ERD Fields (user-facing subset) | Missing UI Data | Relationship / ID Risk | Recommended Change |
|---|---|---|---|---|---|---|
| users | Auth, all workspaces | name, email, role chrome | account_type, account_status, email_verified_at, locale, theme | account_status gates | Mock personas ≠ DB ids | FE-CONTRACT capability flags |
| roles / permissions / user_roles | Admin users, team | Role labels | scope platform\|company | Permission matrix not shown | Admin UI invents agency roles | Align to seeders; agency = decision |
| companies | Onboarding, admin orgs, company profile | name, status mock | status pending\|approved\|rejected\|suspended, doc_path, rejection_reason | Publish gate on status | Admin “org-ns-001” vs bigint | FE-DATA + FE-STATE |
| company_employees | employees requests | pending/approved | requested_job_title, department, role_id, rejection_reason | Candidate-side request | user_id unique (one company) | FE-WORKFLOW join request |
| company_social_links | company profile | partial | platform, url | Consistency | Low | FE-FORM |
| candidate_profiles | onboarding, profile | headline, location… | phone, location required; has_no_experience | Required markers | user_id 1:1 | FE-FORM |
| candidate_cvs | apply, profile | resume label | version, is_active, storage meta | Multi-CV version picker weak | cv_id required on apply | FE-FORM |
| candidate_experience/education/courses/social/skills | profile | presentational | level?, type soft\|tech | Skills type split | Low | FE-FORM optional |
| jobs | public/company jobs | title, work_model, salary labels | visibility, private_apply_mode, openings_count, deadline, category_id, work_mode enum | Visibility/nominations/openings | `ns-204` string vs id | **P0 FE-FORM/WORKFLOW** |
| job_skills | job create/detail | tags sometimes | is_required | Required vs nice-to-have | — | FE-FORM |
| job_nominations | — | none | employee_id, nominated_by | Entire UI missing | — | **P0 FE-WORKFLOW** |
| job_interview_rounds | — | schedule types ad hoc | round_no, interview_type, duration_min | Round config missing | — | **P0 FE-FORM** |
| job_exams / exams | assessments | time_limit, attempt_limit | stage_order, passing_score, attempt_limit≤3 | stage_order binding | — | FE-WORKFLOW |
| applications | apply, pipeline | stage_key labels | status (12 states), attempt_no, cv_id, consent_at, withdrawn_at | attempt_no, consent_at, full status set | `hb-ns204-mc-1012` | FE-CONTRACT statuses |
| application_status_history | activity timelines | mock events | from_status, to_status, reason, changed_by | reason on override | — | FE-STATE |
| offers | offers screens | salary, deadline | status sent\|accepted\|rejected\|expired, expires_at | expired UX; draft/approval extra in FE | — | FE-STATE (approval may be FE extension) |
| interviews | interview UIs | int-ns204-* | round_no, interview_type, meeting_link, location, status | round binding | Shared ID mostly OK for NS-204 | FE-DATA |
| interviewer_availability | interviewer slots | local prefs | start_at, end_at, availability_status | Server shape | — | FE-CONTRACT |
| interview_bookings | schedule | preview | booking_status, cancelled_by, UQ availability | 24h cancel UX | Double-book risk | FE-STATE |
| interview_evaluations | scorecard | competencies UI | numeric_score, internal_notes, candidate_feedback, feedback_shared_at | Share feedback | Competency grid ≠ ERD columns | FE-CONTRACT / PRODUCT |
| calendar_integrations | — | none | provider google\|outlook | Sync UI stub | — | FE-UX optional |
| questions/options/test_cases | assessment take/create | simplified | question_type enum, is_hidden tests | Voice/coding weak | — | FE-FORM |
| exam_attempts / answers / grading_results | take/result | local result keys | camera_consent_at, attempt_no, grading_method | Exhausted attempts; method | localStorage authority risk | FE-STATE + BE-ONLY timer |
| proctoring_events / reviews | — | none | event_type, evidence, decision | Entire HR review missing | — | **P1 FE-WORKFLOW** |
| cv_match_results | — | none | score, matched/missing skills, run_status | Entire HR panel missing | — | **P0 FE-WORKFLOW** |
| ai_review_decisions | — | none | decision accept\|override, reason | Missing | — | **P0 FE-FORM** |
| salary_records / recommendations | offer create? | weak | range suggestion | Benchmark suggestion UI | — | P2 FE-UX |
| notifications | notifications UI | title/body/unread | channel, type, related_*, delivery_status | related entity links weak | — | FE-CONTRACT |
| chatbot_* | — | none in product UI | audience candidate\|admin | Out of Figma acceptance | — | PRODUCT-DECISION |
| audit_logs | admin settings | mock audit rows | actor_id, action, entity_* | Mock ≠ ERD | Model exists; migration gap backend | FE-DATA later |

**Not in official ERD (but present in frontend):** Agency organizations module; Candidate↔Company ATS messaging threads; multi-competency scorecard structure; Frame “agency-managed” job source badges as first-class entity.

---

## 5. Critical Workflow Rule Coverage

| # | Documented rule | UI must show / validate / disable | Current FE | Classification | Gap ID |
|---|---|---|---|---|---|
| 1 | Public vs private eligibility | Distinct catalogs; eligibility banners | Mostly public marketplace | FE-WORKFLOW | F8-G01 |
| 2 | Nomination-only access | Nominee-only apply enabled | No nomination UI | FE-WORKFLOW | F8-G02 |
| 3 | CV required at application | Block continue without active CV | Resume UX present; ERD `cv_id` not explicit | FE-FORM | F8-G03 |
| 4 | Withdrawal only before review | Enable only pre-review; else explain | Always unavailable stub | FE-STATE | F8-G04 |
| 5 | Reapplication after republication | Show attempt; block otherwise | Missing | FE-STATE | F8-G05 |
| 6 | AI errors ≠ rejection | “AI failed” ≠ Rejected badge | Missing AI UI | FE-STATE | F8-G06 |
| 7 | Human override with reason | Override form + history | Missing | FE-FORM | F8-G07 |
| 8 | Sequential exam/interview stages | Ordered stage UI | Partial timelines | FE-WORKFLOW | F8-G08 |
| 9 | Exam attempt limits + server timer | Show limit; don’t trust client clock | Limit shown; timer authority local | FE-SECURITY / BE-ONLY | F8-G09 |
| 10 | Proctoring consent + human flag review | Camera consent; HR review queue | Weak consent; no flag review | FE-WORKFLOW | F8-G10 |
| 11 | Exclusive slot selection | Locked/unavailable slots | Preview schedule | FE-STATE | F8-G11 |
| 12 | 24-hour cancel/reschedule | Disable + Not allowed | Preview reschedule, no 24h | FE-STATE | F8-G12 |
| 13 | Private notes vs candidate feedback | Separate fields + share | Scorecard internal notes; share weak | FE-FORM | F8-G13 |
| 14 | Offer accept/reject/expire | Expired state | Partial | FE-STATE | F8-G14 |
| 15 | Job closes when openings filled | Closed reason | Closed tab only | FE-STATE | F8-G15 |
| 16 | Org approval before publishing | Disable publish if company pending | Not gated | FE-SECURITY | F8-G16 |
| 17 | Membership approval before private-job access | Hide/disable private jobs until approved | Not modeled | FE-SECURITY | F8-G17 |

**Do not implement these as authoritative client-side enforcement** — present/disable/explain only until backend exists.

---

## 6. Cross-Role Record Consistency

### 6.1 Canonical story currently used (Verified in mocks)

| Concept | Mock identifier (presentation) | Roles touching it |
|---|---|---|
| Job | `ns-204` / NS-204 Senior Product Designer | Public, Candidate, Company, Interviewer, Admin |
| Company | Northstar Labs / `org-ns-001` (admin) | Public, Company, Admin |
| Candidate | Maya Chen / `maya-chen` | Company applicants, Interviewer, Candidate apps |
| Application | `hb-ns204-mc-1012` | Candidate, Company |
| Interview | `int-ns204-portfolio` | Interviewer, Company, Candidate |
| Offer | `offer-ms108` (Meridian) vs NS-204 path | Split narratives |

### 6.2 Consistency findings

| Issue | Severity | Notes |
|---|---|---|
| Admin org id `org-ns-001` vs company mock without same key | P1 | Cross-role join risk |
| Offer story often Meridian MS-108 while primary job NS-204 | P2 | Confuses E2E demo |
| Application stage vocab differs (screening/interview/offer vs ERD 12 statuses) | P1 | Contract risk |
| Interviewer scorecard competencies vs ERD single numeric_score | P1 | Contract risk |
| Agency-managed source badges without ERD agency entity | P1 | Product/ERD conflict |
| Notifications reuse candidate mock content on company/admin chrome | P2 | Wrong actor content |

### 6.3 Proposed canonical mock contract (Proposed — do not apply in F8)

| Entity | Canonical mock id | Status seed | Actors |
|---|---|---|---|
| Company | `company_northstar` / display Northstar Labs | approved | all |
| Job | `job_ns204` code `NS-204` | published, public | all |
| Candidate user | `user_maya` | active, verified | candidate |
| Application | `app_ns204_maya` | shortlisted / interview | candidate+company |
| Interview round 1 | `iv_ns204_r1_portfolio` | scheduled | interviewer+candidate+hr |
| Evaluation | `eval_ns204_r1` | draft then submitted | interviewer |
| Offer (optional second path) | Keep MS-108 as **separate** labeled demo, not mixed into NS-204 | — | — |

---

## 7. Frontend UX State Inventory

For critical objects, required **user-visible** states (from workflows/ERD):

### 7.1 Company

| State | Need | FE today |
|---|---|---|
| pending | Banner: cannot publish | Weak |
| approved | Normal workspace | Assumed |
| rejected | Reason + next steps | Partial admin |
| suspended | Hard stop banner | Partial admin |

### 7.2 Job

| State | Need | FE today |
|---|---|---|
| draft | Wizard | Yes |
| published public/private | Badges + eligibility | Incomplete |
| closed (deadline/manual/filled) | Reason | Partial |
| archived | Optional | Weak |
| nomination-only | Explicit | Missing |

### 7.3 Application

| State | Need | FE today |
|---|---|---|
| submitted | Yes | Yes |
| AI scored / AI failed | Distinct | Missing |
| shortlisted / rejected | Yes | Partial pipeline |
| exam in progress / failed | Yes | Partial assessments |
| interview / offer / hired / withdrawn | Yes | Partial |
| withdraw blocked after review | Message | Wrong (always blocked) |
| re-apply blocked | Message | Missing |

### 7.4 Interview booking

| State | Need | FE today |
|---|---|---|
| slot open/booked | Yes | Partial |
| reschedule allowed (≥24h) | Enable | Preview always |
| reschedule not allowed (<24h) | Disable + explain | Missing |
| cancelled | Yes | Weak |

### 7.5 Exam attempt

| State | Need | FE today |
|---|---|---|
| not unlocked | Yes | Partial |
| consent required | Camera | Generic consent |
| in progress | Timer display | Yes (client) |
| submitted / graded pass/fail | Yes | Partial |
| no attempts left | Terminal | Missing dedicated |
| flagged pending human review | Informational | Missing |

### 7.6 Offer

| State | Need | FE today |
|---|---|---|
| sent | Yes | Yes |
| accepted / rejected | Yes | Preview |
| expired | Yes | Weak |

Generic HTTP states (401/403/404/409/429) remain mostly **BE-ONLY** until auth exists; FE should reserve empty/unauthorized patterns.

---

## 8. Prioritized Gap Register

| Gap ID | Module | Exact File / Route | Workflow / ERD Evidence | Existing Behavior | Missing Requirement | Severity | Classification | Recommended Resolution | Approval Needed |
|---|---|---|---|---|---|---|---|---|---|
| F8-G01 | Company Jobs | `company/jobs/wizard/create.blade.php` | WF3; ERD jobs.visibility | Draft wizard without visibility | Public/private control | P0 | FE-FORM | Add visibility field + review summary | Design if layout changes |
| F8-G02 | Company Jobs | same + new partial | WF3; ERD private_apply_mode, job_nominations | No nomination UI | Nominated-only mode + list builder | P0 | FE-WORKFLOW | New wizard step/panel | **Figma** |
| F8-G03 | Company Jobs | wizard | WF3 stage config; ERD job_exams, job_interview_rounds | No stage builder | Attach exams + define rounds | P0 | FE-WORKFLOW | Stage configuration UI | **Figma** |
| F8-G04 | Company Jobs | wizard | WF3 fields | Salary present; openings/deadline/skills/category weak | Complete draft fields | P0 | FE-FORM | Add openings_count, deadline, category, skills | Design |
| F8-G05 | Candidate/Public Jobs | `jobs.*`, `candidate/jobs.*` | WF1/3 private jobs | Public catalog only | Private eligible job views | P0 | FE-WORKFLOW | Eligibility-aware listings | Design |
| F8-G06 | Candidate Apply | apply routes | WF3 employees cannot apply own public jobs | No forbidden state | Block + explanation | P0 | FE-STATE | Unavailable apply state | Copy |
| F8-G07 | Company Applicants | applicants views | WF4; ERD cv_match_results | No AI panel | Score/skills/run_status | P0 | FE-WORKFLOW | AI match panel | **Figma** |
| F8-G08 | Company Applicants | applicants | WF4; ERD ai_review_decisions | No override+reason | Override decision UI | P0 | FE-FORM | Modal/form | **Figma** |
| F8-G09 | Company Onboarding/Jobs | publish CTAs | WF2 approved before publish | Publish always available in preview | Disable when company pending | P0 | FE-SECURITY | Capability flag presentation | Product |
| F8-G10 | Interviews | candidate/company reschedule UIs | WF6 24h rule | Preview reschedule | Not-allowed <24h | P0 | FE-STATE | Conditional disable + message | Copy |
| F8-G11 | Auth/Onboarding | onboarding candidate | WF2 works-at-company | Weak/missing branch | Join-request path | P1 | FE-WORKFLOW | Add step | Design |
| F8-G12 | Candidate | new/extend profile | WF2 join request | No candidate request form | Pick company + title + dept | P1 | FE-FORM | Form + pending state | Design |
| F8-G13 | Company Employees | `employees/requests` | WF2 assign role | Partial | HR/Interviewer/Employee assign | P1 | FE-FORM | Role select on approve | — |
| F8-G14 | Apply | `_apply-form` | WF3 CV+cover+AI consent; ERD | Resume/consent partial | cover_note + AI consent labeling | P1 | FE-FORM | Fields | — |
| F8-G15 | Applications | show + js withdraw | WF3 withdraw before review | Always unavailable | Conditional withdraw UX | P1 | FE-STATE | Rule-based presentation | Product copy |
| F8-G16 | Applications | mocks | ERD attempt_no; WF3 re-apply | Not shown | Attempt + blocked re-apply | P1 | FE-DATA | Mock + banners | — |
| F8-G17 | AI failed state | applicants | WF4 AI failed≠reject | Missing | Manual-review queue label | P1 | FE-STATE | Badge/filter | Design |
| F8-G18 | Assessments | intro/take | WF5 camera consent | Generic consent | Camera + identity copy | P1 | FE-FORM | Update consent | Copy |
| F8-G19 | Assessments | results/index | WF5 no attempts left | Partial | Terminal exhausted state | P1 | FE-STATE | Empty/error panel | — |
| F8-G20 | Company | assessments / new | WF5 proctoring review | Missing | Flag review UI | P1 | FE-WORKFLOW | Review queue | **Figma** |
| F8-G21 | Company | assessments create | WF5 AI question approve | Draft only | Approval gate for AI questions | P1 | FE-STATE | Review status | Design |
| F8-G22 | Interviewer/Candidate | evaluations / interviews | WF6 feedback share; ERD | Internal notes only | Optional share + candidate view | P1 | FE-FORM | Toggle + candidate panel | Design |
| F8-G23 | Offers | candidate/company offers | WF4 expire; ERD status | Weak expired | Expired presentation | P1 | FE-STATE | Badge + disabled actions | — |
| F8-G24 | Offers | create | WF4 salary in range + benchmark | Draft UI | Range check + suggestion | P1 | FE-UX | Hint from salary_recommendations | — |
| F8-G25 | Jobs | company jobs closed | WF3/4 openings filled | Closed tab | Filled reason | P1 | FE-STATE | Reason label | — |
| F8-G26 | Admin vs ERD | `admin/agencies` | ERD has no agency | Full agency UI | Scope conflict | P1 | PRODUCT-DECISION | Hide, mark experimental, or amend ERD | **Product** |
| F8-G27 | Candidate Messages | `candidate/messages` | ERD: notifications+chatbot only | Full messaging UI | Scope conflict | P1 | PRODUCT-DECISION | Keep as extension or demote | **Product** |
| F8-G28 | Scorecard shape | interviewer evaluations | ERD numeric_score | Competency grid | Contract mismatch | P1 | FE-CONTRACT | Map competencies→notes/score or extend ERD | Product/Tech |
| F8-G29 | Status vocabulary | mocks pipeline | ERD 12 application statuses | Short stage set | Enum alignment | P1 | FE-CONTRACT | Publish status dictionary | Tech |
| F8-G30 | Cross-role IDs | Mock*Data | Consistency | Mixed NS-204 / MS-108 / org-ns | Canonical contract | P1 | FE-DATA | Normalize mocks in F9E | — |
| F8-G31 | Notifications | dropdown all roles | ERD notifications | Candidate copy reused | Actor-appropriate types | P2 | FE-DATA | Role-specific mocks | — |
| F8-G32 | Calendar sync | interviewer availability | WF6 google/outlook | Not present | Optional integration UX | P2 | FE-UX | Stub “connect calendar” | Product |
| F8-G33 | Booking conflict | schedule | WF6 no double book | Preview confirm | Conflict error state | P2 | FE-STATE | Reserved error UI | — |
| F8-G34 | Email verification failure | auth | WF2 | Notice page only | Resend/failed states | P2 | FE-STATE | Expand auth states | — |
| F8-G35 | One join request at a time | candidate | WF2 | Missing | Disable + message | P2 | FE-STATE | Banner | — |
| F8-G36 | Job skills required flag | wizard | ERD job_skills.is_required | Flat skills | Required toggle | P2 | FE-FORM | Checkbox per skill | — |
| F8-G37 | Salary recommendation | offer/job | ERD salary_* | Missing | Suggestion chip | P2 | FE-UX | Read-only suggestion | — |
| F8-G38 | Chatbot | — | ERD chatbot_* | No product UI | In/out of scope | P2 | PRODUCT-DECISION | Defer | Product |
| F8-G39 | Audit mock realism | admin settings | ERD audit_logs | Presentation sample | Label as sample; align fields | P2 | FE-DATA | Field parity | — |
| F8-G40 | work_mode enum | jobs cards | ERD onsite\|remote\|hybrid | Hybrid/Remote/On-site labels | Naming map | P2 | FE-CONTRACT | Map display↔enum | — |
| F8-G41 | account_type enum | users | ERD candidate\|company | UI also admin via roles | Keep admin as role not account_type | P3 | FE-CONTRACT | Document | — |
| F8-G42 | has_no_experience | profile | ERD | May be missing toggle | Optional UX | P3 | FE-FORM | Toggle | Design |
| F8-G43 | localization of new states | lang/* | All WF | EN/AR gaps for new copy | Translate new states | P3 | FE-UX | F9F i18n | — |
| F8-G44 | Keyboard a11y | platform | Acceptance | Conditionally Accepted | Trusted keyboard QA | P3 | FE-UX | Separate sign-off | QA |

---

## 9. Proposed F9 Implementation Batches

> **Do not implement in F8.** Batches are planning only.

### F9A — Identity, registration, company approval, membership UI
- Screens: `register`, verification, `onboarding.candidate`, `onboarding.company`, `company.employees.requests`, candidate join-request (new), publish capability banners  
- Changes: WF2 gaps F8-G09, G11–G13, G35; company pending publish gate  
- Figma: join-request step; pending company banner  
- Mock deps: company status, membership status  
- Interactions: forms, role assign, disabled publish  
- Risks: onboarding localStorage contracts  
- AC: pending company cannot show publish as available; join-request path visible  

### F9B — Jobs, visibility, nominations, application eligibility
- Screens: job wizard, company jobs, public/candidate job lists, apply entry  
- Changes: F8-G01–G06, G04, G25, G36  
- Figma: **required** for nomination + stage builder  
- Mock deps: visibility, nominations, openings  
- Risks: wizard complexity; deep links  
- AC: can configure public/private/nominated; employees see correct eligibility  

### F9C — AI screening, pipeline states, assessments
- Screens: applicants show/pipeline, assessments candidate+company, new proctoring review  
- Changes: F8-G07–G08, G14–G21, G17  
- Figma: AI panel + proctoring review  
- Mock deps: cv_match_results, ai_review_decisions, proctoring_*  
- Risks: over-building AI as fake authority  
- AC: AI failed≠rejected; override requires reason field; attempt exhausted state  

### F9D — Interview scheduling, evaluation states, offers
- Screens: schedule, candidate reschedule, scorecard, offers  
- Changes: F8-G10, G22–G24, G28, G33  
- Figma: feedback share; 24h not-allowed  
- Mock deps: booking times relative to “now” for 24h demo  
- Risks: scorecard ERD mismatch  
- AC: <24h shows Not allowed; shared feedback optional path; expired offer state  

### F9E — Admin governance, scope conflicts, cross-role consistency
- Screens: admin orgs/users/jobs/agencies/settings; mock providers  
- Changes: F8-G26–G27, G29–G31, G30, G39  
- Figma: only if agency retained with new ERD  
- Product: agency + messages decisions **before** build  
- AC: canonical NS-204 record set consistent; out-of-ERD modules labeled or removed from primary nav  

### F9F — Shared contracts, UX, localization, regression
- Screens: all touched + EN/AR  
- Changes: F8-G40–G44 contracts/i18n; keyboard remains separate  
- AC: status dictionary published; EN/AR for new strings; no pointer regressions on 71 routes  

---

## 10. Draft Frontend Data Contracts

> **Proposed** until Backend Team approves. Prefer presentation DTOs over raw DB columns.

### 10.1 Conventions

- `id`: opaque string public id (e.g. `job_ns204`) **plus** optional numeric `db_id` later  
- `code`: human code (`NS-204`)  
- Timestamps: ISO-8601 UTC + `timezone` IANA for display  
- Money: `{amount, currency, period}`  
- Capabilities: `{can_apply, can_withdraw, can_publish, can_reschedule, …}` computed server-side later; mocks may approximate  

### 10.2 Enumerations (Proposed aligned to ERD/WF)

| Domain | Values |
|---|---|
| company.status | pending, approved, rejected, suspended |
| job.visibility | public, private |
| job.private_apply_mode | all_employees, nominated_only \| null |
| job.status | draft, published, closed, archived |
| job.work_mode | onsite, remote, hybrid |
| application.status | submitted, ai_review, ai_failed, shortlisted, rejected, exam, interview, offer, hired, withdrawn, expired, archived (**confirm exact 12 with backend**) |
| offer.status | sent, accepted, rejected, expired |
| booking / availability | open, booked, cancelled (+ booking_status) |
| cv_match.run_status | ok, failed, timeout |
| ai_decision | accept, override |
| exam attempt | within limit / exhausted |

### 10.3 Core DTO sketches (Proposed)

**AuthenticatedUser:** `{id, name, email, email_verified, account_type, account_status, locale, theme, memberships[], permissions[], capabilities{}}`

**Organization/Company:** `{id, name, status, rejection_reason?, can_publish}`

**Job:** `{id, code, company_id, title, visibility, private_apply_mode, status, openings_count, deadline, salary{}, work_mode, stages{exams[], rounds[]}, eligibility{can_view, can_apply, reason?}}`

**Application:** `{id, job_id, candidate_id, cv_id, attempt_no, status, consent_at, withdrawn_at?, ai_match?, timeline[]}`

**AssessmentAttempt:** `{id, exam_id, application_id, attempt_no, attempt_limit, camera_consent_at?, status, time_limit_min, server_ends_at?}`

**Interview:** `{id, application_id, round_no, type, status, meeting_link?, location?, booking?}`

**Scorecard/Evaluation:** `{id, interview_id, numeric_score, internal_notes, candidate_feedback?, feedback_shared_at?, status: draft|submitted}`

**Offer:** `{id, application_id, salary{}, start_date, status, expires_at?}`

**Notification:** `{id, type, title, body, channel, related{type,id}, read_at?}`

**AdminReview/Audit:** `{id, actor, action, entity_type, entity_id, created_at, meta?}`

Pagination: `{data[], meta{page, per_page, total}}`  
Empty/error: `{code, message, details?}`

Sensitive: never put internal_notes, proctoring evidence, or tokens in candidate-visible DTOs.

---

## 11. Frontend vs Backend Responsibility

| Concern | Frontend-owned (F9) | Backend-owned (later) |
|---|---|---|
| Screens for visibility/nomination/AI/proctoring/24h | Build presentation | Enforce rules |
| Disable publish when company pending | Show disabled + reason | Block API |
| Withdrawal affordance | Show allowed/blocked | Persist + authorize |
| AI failed badge | Display run_status | Produce results |
| Exam timer | Display server deadline | Authoritative timing |
| Slot lock / 24h | Show errors/disabled | Enforce booking |
| localStorage drafts | Migrate UX later | Persistence |
| Auth/session | Forms ready | Real auth |
| Agency/Messages scope | Nav labeling / removal | Only if product adds ERD |

**Forbidden:** treating JS validation as source of truth for eligibility, attempts, or scheduling.

---

## 12. Product / Design Questions

| ID | Question | Blocks | Owner |
|---|---|---|---|
| PQ-01 | Keep Admin Agency module though ERD has no agency entity? | F9E | Product |
| PQ-02 | Keep Candidate Messages as ATS messaging though ERD has chatbot/notifications only? | F9E | Product |
| PQ-03 | Is multi-competency scorecard an approved ERD extension? | F9D | Product/Tech |
| PQ-04 | Exact list of 12 application statuses? | F9C/F9F | Tech+Product |
| PQ-05 | AI threshold 80% — configurable or fixed display? | F9C | Product |
| PQ-06 | Who may cancel/reschedule within rules — candidate, HR, both? | F9D | Product |
| PQ-07 | Figma approval timeline for nomination + AI + proctoring screens? | F9B/C | Design |
| PQ-08 | Chatbot in v1 frontend scope? | Later | Product |
| PQ-09 | Salary benchmark visible to HR only? | F9D | Product |

---

## 13. Acceptance Criteria for F9

F9 is complete when:

1. All **P0** gaps closed or explicitly deferred with Product sign-off.  
2. WF3 visibility/nomination/eligibility demonstrable in browser with mocks.  
3. WF4 AI match + override reason demonstrable.  
4. WF6 24h not-allowed state demonstrable with relative mock timestamps.  
5. WF2 publish disabled for pending company demonstrable.  
6. Canonical mock IDs consistent across roles for NS-204 journey.  
7. EN/AR strings for new states present.  
8. No new fake “success” that implies server persistence.  
9. Regression: 71 routes HTTP OK; prior presentation tests updated as needed.  
10. Design-approved screens attached for net-new UI.  
11. Keyboard a11y remains tracked separately (not fake-passed).  

---

## 14. Recommended Implementation Order

1. **Product answers** PQ-01, PQ-02, PQ-03 (scope)  
2. **F9A** membership + company approval gates  
3. **F9B** jobs visibility/nominations/eligibility (**highest structural impact**)  
4. **F9C** AI + assessments/proctoring presentation  
5. **F9D** interview 24h + offers + feedback share  
6. **F9E** admin scope cleanup + mock canon  
7. **F9F** contracts, i18n, regression  
8. Then revisit Backend Integration planning against aligned FE contracts  

---

## 15. F8 Readiness Verdict

| Question | Answer |
|---|---|
| Are official PDFs inspected? | **Yes** (paths in §2) |
| Is frontend fully workflow/ERD aligned? | **No** |
| Ready to start Backend Integration implementation? | **No — complete F9 alignment first (per project owner decision)** |
| Ready to start F9A without Product input? | **Partially** — F9A/F9B can start after Design capacity; **PQ-01/02** needed before F9E |
| F8 documentation complete? | **Yes — this file** |

### Explicit verdict

**F8 COMPLETE — Frontend NOT READY for Backend Integration until prioritized P0/P1 alignment batches (F9) are executed and accepted.**

**STOP. Do not start F9A or Backend Integration without explicit approval.**

---

## 16. Appendix A — Coverage Classification Key

| Label | Meaning |
|---|---|
| Implemented and aligned | UI exists and matches documented step |
| Implemented but incomplete | UI exists; missing fields/states |
| Implemented but inconsistent | UI contradicts documented rule |
| Missing frontend state | Screen exists; state/branch missing |
| Missing frontend screen | No screen for required step |
| Backend-only rule | Must be enforced server-side; FE needs error/empty only |
| Requires Product decision | Scope conflict or unspecified |

---

## 17. Appendix B — Evidence Index

| Evidence | Location |
|---|---|
| ERD PDF | attachments `Recruitment_Platform_Full_ERD.pdf` (6 pages) |
| Workflows PDF | attachments `Recruitment_Platform_Workflows.pdf` (6 pages) |
| Routes | `routes/web.php` (71 GET) |
| Mocks | `app/Support/Frontend/Mock*.php` |
| Job wizard | `resources/views/company/jobs/wizard/create.blade.php` |
| Withdraw stub | `resources/js/app.js` (`data-withdraw-preview`) |
| Assessments consent | `resources/views/candidate/assessments/intro.blade.php` |
| Reschedule preview | `resources/views/candidate/interviews/_reschedule-modal-*.blade.php` |
| Employees requests | `resources/views/company/employees/requests.blade.php` |
| Schema mirrors ERD | `database/migrations/2026_10_20_100*.php` |
| Permissions incl. nominate/override_ai/proctoring.review | `database/seeders/PermissionSeeder.php` |
| Git HEAD | `aca79424eed60e7cc7ae7622d083171438a9dfbe` |

---

*End of Phase F8 Frontend Workflow & ERD Alignment Audit.*
