# Hirebridge — Backend Requirements for Frontend Integration

**Audience:** Backend Team (primary), Frontend Integration Engineers, Product  
**Author role:** Senior Laravel Blade Frontend Integration Engineer  
**Date:** 2026-10-22  
**Purpose:** State exactly what the Frontend Team needs from the Backend Team to wire the **existing** Blade application — contracts, endpoints, payloads, errors, permissions, and test access.

**Out of scope for this document:** Designing or implementing backend systems, auditing migrations, backend roadmaps, or starting FE integration coding.

**Architecture constraint:** Preserve Laravel Blade layouts, named routes, Frame 51 Design System, EN/AR + RTL, and progressive enhancement. Prefer **session + CSRF form POSTs + redirects** over a separate SPA API unless Backend explicitly chooses otherwise and documents parity.

**Status legend used below**

| Status | Meaning |
|---|---|
| **Existing and verified** | Already works for FE today (e.g. locale cookie) |
| **Proposed — Backend confirmation required** | FE shape documented; Backend must confirm/freeze |
| **Missing** | FE needs it; Backend has not delivered a consumable contract |
| **Blocked by Product decision** | Contested domain — do not invent; confirm Product first |

---

## 1. Executive Summary

The Hirebridge frontend is a **presentation-complete** multi-role Blade app:

- **72** named GET routes (presentation only; **0** mutating routes today)
- Mock providers under `app/Support/Frontend/`
- Client drafts / read-state in `localStorage` (`resources/js/app.js` + Blade `data-*` keys)
- Proposed domain dictionaries in `FrontendStatusVocabulary` + `lang/*/status_vocab.php`

**Frontend needs Backend to deliver:**

1. Real **session authentication** and an authoritative **current user + membership + capability** payload on every authenticated page.  
2. **Server-rendered page data** (or documented partial JSON) matching the shapes already consumed from mocks.  
3. **Mutating form endpoints** for every actionable control (apply, withdraw, publish, override AI, book interview, submit scorecard, send/respond offer, approve org/membership, mark notification read, etc.).  
4. Shared **conventions** (IDs/public codes, statuses, datetime, money, validation errors, uploads, 403/419 behavior, flash messages).  
5. **Staging access** with seeded personas and sample Northstar-linked records.  

**Frontend does not need** Backend to redesign screens. Integration = replace `Mock*Data` / localStorage authority with Backend contracts while keeping views.

### Minimum to start Frontend Integration (wave 1)

| Must deliver before FE wiring begins | Why |
|---|---|
| Login / logout / register / email-verify contracts + CSRF session | Gate all workspaces |
| Current-user + role + org membership + capability flags | Replace `uiRole` chrome |
| Organization status + publish capability | Company gates |
| Job list/detail + eligibility flags | Candidate discover/apply |
| Application create/withdraw + status vocabulary freeze (**PQ-04**) | Core ATS loop |
| Staging URL + test users (Candidate, HR, Interviewer, Super Admin) | Manual + automated FE wiring |

### Blocked contracts (do not invent)

| Topic | FE impact | Status |
|---|---|---|
| Agency Admin (`/admin/agencies`) | Keep review-only or hide until Product decides | **Blocked by Product (PQ-01)** |
| Candidate Messages (`/candidate/messages`) | Keep non-authoritative or hide until Product decides | **Blocked by Product (PQ-02)** |
| Scorecard `competency_ratings` | UI collects grid; Backend must say persist/extend/ignore | **Blocked by Product (PQ-03)** |
| Exact 12 `application.status` keys | FE proposes list; Backend must freeze official enum | **Blocked by Product (PQ-04)** until confirmed |

---

## 2. Current Frontend Structure

| Layer | Location | Role today |
|---|---|---|
| Routes | `routes/web.php` | 72 GET presentation routes |
| Controllers | `app/Http/Controllers/{Public,Auth,Onboarding,Candidate,Company,Interviewer,Admin,Frontend}` | Pass mock arrays into views |
| Views | `resources/views/**` | Blade + Frame 51 components |
| JS | `resources/js/app.js` | Theme/locale, drafts, filters, mock success UX |
| Mocks | `MockPublicData`, `MockCandidateData`, `MockCompanyData`, `MockInterviewerData`, `MockAdminData`, `MockNotificationData` | Page payloads |
| Presentation helpers | `JobAccessPresentation`, `AiScreeningPresentation`, `InterviewBookingPresentation`, `OfferLifecyclePresentation`, `FrontendStatusVocabulary` | Labels/eligibility chrome |
| Locale | `GET /locale/{en\|ar}` + cookie `hb_locale` | **Existing and verified** |

**Canonical demo IDs FE already deep-links:** `org-ns-001`, `ns-204`, `maya-chen`, `int-ns204-portfolio` (Alex Morgan interviewer). Backend staging should preserve or map these.

---

## 3. Backend Deliverables Checklist

### A. Authentication

| Deliverable | Status | Frontend need |
|---|---|---|
| `POST` login (email/password) → session cookie | Missing | Wire `auth/login.blade.php` |
| `POST` logout | Missing | Nav logout |
| `POST` register (account_type candidate\|company) | Missing | `auth/register` |
| Email verification notice + resend + signed verify link | Missing | `auth/verify-email` |
| Forgot / reset password | Missing | Auth password screens |
| Current authenticated user on authenticated layouts | Missing | Replace mock actor chrome |
| Roles: platform `super_admin`; company `company_admin`\|`hr`\|`interviewer`\|`employee` | Proposed | Sidebar + capability gates |
| Organization membership status | Proposed | Banners + publish/apply gates |
| Capability/permission flags (server-owned) | Missing | Never trust FE query flags |
| Session expiry + `419` CSRF recovery guidance | Missing | Forms / fetch |
| CSRF token in Blade (`@csrf`) accepted on POSTs | Missing | All mutations |

### B. Data responses (page load)

Backend must supply the fields each screen already reads from mocks (see §5). Prefer **controller → view array** compatible with current Blade keys to minimize FE churn.

### C. User actions

Every button/modal that today fakes success needs a documented method/URL/fields/errors (see §6).

### D. Shared conventions

Document and freeze: public codes vs internal IDs; official status enums; ISO-8601+TZ datetimes; currency; pagination/filter query params; Laravel validation error bag / JSON 422 shape; upload/download; 401/403/404/419/429; redirect + flash keys (`success`, `error`, `status`).

### E. Testing access

Staging base URL, seeded users per role, sample org/job/application/interview/offer, CSRF/session instructions, optional OpenAPI/Postman for any JSON endpoints. **No production secrets.**

---

## 4. Screen-by-Screen Integration Matrix

> **Screens covered:** 69 product screens + locale + 3 non-production previews = **72** routes. Previews need no Backend.  
> **Backend Contract Required** names are **Proposed** request IDs — **Missing** until Backend publishes them. None are implemented today.

### 4.1 Public

| Frontend Screen | Existing Route/View | Data Needed from Backend | Action Required | Backend Contract Required | Frontend Integration Point |
|---|---|---|---|---|---|
| Landing | `home` → `public/landing` | Featured public jobs (title, company, location, work_mode, public_code) | None | `GET` public featured jobs | `HomeController` / replace `MockPublicData::jobs` |
| Public jobs index | `jobs.index` → `public/jobs/index` | Paginated public catalog; filters | Filter query | `GET /jobs` data | `Public\JobController@index` |
| Public job show | `jobs.show` → `public/jobs/show` | Job detail; company summary; apply CTA eligibility if auth | Deep link by public_code | `GET /jobs/{code}` | `Public\JobController@show` |
| Public company | `companies.show` → `public/companies/show` | Company profile + open public roles | — | `GET /companies/{code}` | `Public\CompanyController` |
| About | `about` | Static/CMS optional | — | None required | Content only |
| Contact | `contact` → `public/contact` | None | Submit contact | `POST /contact` | Contact form |
| Locale switch | `locale.switch` | — | Set locale cookie | **Existing and verified** | `LocaleController` |

### 4.2 Auth

| Frontend Screen | Existing Route/View | Data Needed from Backend | Action Required | Backend Contract Required | Frontend Integration Point |
|---|---|---|---|---|---|
| Login | `login` → `auth/login` | Guest only; redirect if authed | Login | `POST /login` | Login form |
| Register | `register` → `auth/register` | account_type options | Register | `POST /register` | Register form |
| Verify email | `verification.notice` | verification state (sent/failed/resent) | Resend | `POST` verification notification; signed verify GET | `VerifyEmailController` + notice UI |
| Forgot password | `password.request` | — | Request reset link | `POST /forgot-password` | Form |
| Reset password | `password.reset` | Valid reset token | Reset | `POST /reset-password` | Form |

### 4.3 Onboarding

| Frontend Screen | Existing Route/View | Data Needed from Backend | Action Required | Backend Contract Required | Frontend Integration Point |
|---|---|---|---|---|---|
| Candidate onboarding | `onboarding.candidate` | Auth user; draft profile fields | Save steps / complete | `POST` candidate profile onboarding | Replace `hb_onboarding_candidate_v1` |
| Company register | `onboarding.company` | Auth user; company draft fields; doc upload | Submit for review | `POST` company registration + file | Replace `hb_onboarding_company_v1` |
| Company status | `onboarding.company.status` | org status, rejection_reason, capabilities | — | `GET` organization status for owner | `MockCompanyData::organizationContext` |

### 4.4 Candidate

| Frontend Screen | Existing Route/View | Data Needed from Backend | Action Required | Backend Contract Required | Frontend Integration Point |
|---|---|---|---|---|---|
| Dashboard | `candidate.dashboard` | Summary counts, next actions, recent apps | — | Candidate dashboard DTO | `MockCandidateData::dashboard` |
| Profile | `candidate.profile` | Profile, CVs, skills, membership summary | Save profile; upload CV | `GET/PUT` profile; `POST` CV | Profile + `hb_candidate_profile_draft_v1` |
| Membership | `candidate.membership` | Membership state; searchable orgs | Submit join request | `POST` membership request | Membership form / LS draft |
| Jobs discover | `candidate.jobs.index` | Eligible job cards + `JobAccessPresentation` flags | Save job; open apply | `GET` candidate jobs; `POST` saved jobs | `JobController` + LS saved |
| Saved jobs | `candidate.jobs.saved` | Saved job list | Unsave; alert prefs | Saved jobs CRUD | Saved view |
| Applications index | `candidate.applications.index` | Applications + status labels | Filter | `GET` applications | Applications list |
| Apply | `candidate.applications.apply` | Job + eligibility + CV list | Submit apply (cover, consent) | `POST` apply | Apply form / draft key |
| Submitted | `candidate.applications.submitted` | Confirmation payload | — | Apply response redirect data | Submitted view |
| Application show | `candidate.applications.show` | Application detail; withdraw eligibility | Withdraw | `POST` withdraw | Show + withdraw CTA |
| Assessments index/intro/take/result | `candidate.assessments.*` | Assignments, attempts_remaining, questions, result | Consent; start; submit answers | Assessment attempt APIs | Assessment views + LS result |
| Interviews index/show | `candidate.interviews.*` | Interviews; slots; `can_reschedule` | Book; reschedule; cancel | Booking APIs + 24h rules | Interview views / prep LS optional |
| Offers index/show | `candidate.offers.*` | Offers + expiry | Accept / reject | Offer respond | Offer views |
| Preferences | `candidate.preferences` | Prefs | Save prefs | `PUT` preferences | Prefs form / LS |
| Notifications | `candidate.notifications.index` | Actor-scoped notifications | Mark read / mark all | Notifications APIs | Notifications + dropdown |
| Messages | `candidate.messages.index` | Threads (if approved) | Send message | **Blocked PQ-02** | Messages UI — wait Product |

### 4.5 Company / HR

| Frontend Screen | Existing Route/View | Data Needed from Backend | Action Required | Backend Contract Required | Frontend Integration Point |
|---|---|---|---|---|---|
| Dashboard | `company.dashboard` | KPIs; org banner context | — | Company dashboard + org context | `MockCompanyData::dashboard` |
| Profile | `company.profile` | Company profile | Save profile; logo | `PUT` company profile | Profile / LS draft |
| Jobs index | `company.jobs.index` | Company jobs + statuses | Close/archive actions | Jobs list + mutations | Jobs index |
| Job wizard | `company.jobs.create` | Draft job; categories; skills; stages; nominations | Save draft; publish | Job draft/publish (+ org approved gate) | Wizard / LS draft |
| Job show | `company.jobs.show` | Job detail; pipeline shortcut | Publish/close | Job mutations | Job show |
| Applicants pipeline | `company.applicants.index` | Pipeline by job/stage | Stage move (if server-owned) | Pipeline DTO + status change | Pipeline |
| Applicant show | `company.applicants.show` | Applicant; AI panel; notes | AI override; save note; advance | AI override; notes; status | Applicant show / note LS |
| Interviews calendar | `company.interviews.index` | Calendar events | — | Interview list | Calendar |
| Schedule interview | `company.interviews.schedule` | Applicant; interviewers; slots | Create interview / invite | Interview create | Schedule |
| Interview show | `company.interviews.show` | Interview detail | Cancel/reschedule (HR) | Interview mutations | Show |
| Evaluation show | `company.evaluations.show` | Evaluation (no private leak beyond role) | — | Evaluation read | Show |
| Offer create | `company.offers.create` | Application; salary range; benchmark hint | Save draft; send offer | Offer draft/send | Offer create / LS |
| Talent pool | `company.talent-pool.index` | Pool candidates | Shortlist / invite | Talent pool APIs | Talent pool / LS |
| Employees | `company.employees.index` | Team members | Invite/remove (if in scope) | Team list | Employees |
| Join requests | `company.employees.requests` | Pending requests | Approve+role / reject | Membership review | Requests |
| Employee show | `company.employees.show` | Member detail | Role change | Employee update | Show |
| Analytics | `company.analytics.index` | Aggregates | Export optional | Analytics DTO | Analytics |
| Assessments index/create/show/results | `company.assessments.*` | Exams; AI Q approval; attempts; proctoring | Create; approve Qs; review flags | Assessment + proctoring review | Assessment views / draft LS |

### 4.6 Interviewer

| Frontend Screen | Existing Route/View | Data Needed from Backend | Action Required | Backend Contract Required | Frontend Integration Point |
|---|---|---|---|---|---|
| Dashboard | `interviewer.dashboard` | Assigned interviews; checklist keys | — | Interviewer dashboard | Dashboard |
| Schedule / slots | `interviewer.schedule.index` | Availability windows | Save availability | Availability CRUD | Schedule / LS prefs |
| Prepare | `interviewer.interviews.prepare` | Packet; candidate summary | — | Preparation DTO | Prepare |
| Live | `interviewer.interviews.live` | Live session meta; notes key | Save live notes (optional) | Live session + notes | Live |
| Evaluations index | `interviewer.evaluations.index` | Pending/past scorecards | — | Evaluations list | Index |
| Scorecard | `interviewer.evaluations.create` | Interview; competencies UI | Submit scorecard | Evaluation submit (**PQ-03**) | Scorecard / LS |

### 4.7 Admin

| Frontend Screen | Existing Route/View | Data Needed from Backend | Action Required | Backend Contract Required | Frontend Integration Point |
|---|---|---|---|---|---|
| Dashboard | `admin.dashboard` | Platform KPIs | — | Admin dashboard | Dashboard |
| Organizations index/show | `admin.organizations.*` | Orgs; docs; status | Approve / reject / suspend | Org moderation | Org views |
| Users | `admin.users.index` | User directory | Suspend/invite optional | Users list (+ mutations if in scope) | Users |
| Job moderation | `admin.jobs.index` | Moderation queue | Approve / request changes / reject | **Confirm if real vs jobs.status** | Jobs moderation |
| Agencies | `admin.agencies.index` | — | — | **Blocked PQ-01** | Agencies — wait Product |
| Settings / audit samples | `admin.settings.index` | Settings; audit events | Save settings (if any); view audit | Settings + audit list | Settings |

### 4.8 Non-production

| Frontend Screen | Route | Backend need |
|---|---|---|
| Layout/component previews | `_frontend/*` | None |

---

## 5. Data Contracts Required

All shapes below are **Proposed — Backend confirmation required** (aligned to F9F §7 / current mocks). Backend may rename keys if it publishes a mapping table FE can apply once.

### 5.1 Current user (every authenticated layout)

```
id, display_name, email, account_type(candidate|company),
platform_roles[], membership{organization_id, organization_public_code, status, company_role},
capabilities{...booleans}, locale?, theme?
```

### 5.2 Organization

```
id | public_code, name, status(pending|approved|rejected|suspended|archived),
owner_user_id, rejection_reason?, suspension_reason?,
capabilities.can_publish_jobs
```

### 5.3 Job

```
id | public_code, organization_id, title, description, location,
work_mode(onsite|remote|hybrid), employment_type,
salary_min?, salary_max?, currency, salary_period,
experience_years?, department?, openings_count, deadline?,
visibility(public|private), private_apply_mode?,
status(draft|published|closed|archived),
skills[{id,name,is_required}], stages/exams/rounds as configured,
eligibility{can_view, can_apply, reason_key?}  // candidate views
```

### 5.4 Application

```
id, job_id|job_public_code, candidate_id, organization_id,
status /* official 12 — PQ-04 */, attempt_no,
cover_note?, consent_at, applied_at, withdrawn_at?,
can_withdraw, pipeline_stage_label?
```

**Proposed status keys (awaiting freeze):**  
`submitted`, `ai_review`, `ai_failed`, `shortlisted`, `rejected`, `exam`, `interview`, `offer`, `hired`, `withdrawn`, `expired`, `archived`

### 5.5 AI screening

```
application_id, run_status(pending|success|failed),
score?, matched_skills[], missing_skills[],
human_override{decision, reason, reviewer_name, at}?
```

`ai_failed` ≠ rejection.

### 5.6 Assessment / attempt

```
assessment_id, application_id, title, attempts_remaining, max_attempts,
consent{camera, identity}, questions[], time_limit_seconds,
proctoring_flags[], result{score?, passed?}
```

### 5.7 Interview / booking

```
id | public_code, job_id, applicant_id, interviewer_ids[],
starts_at (ISO-8601 + TZ), ends_at, status,
meeting_link?, location?,
can_reschedule, can_cancel, reschedule_reason_key?,
available_slots[]
```

### 5.8 Evaluation / scorecard

```
interview_id, evaluator_id, numeric_score, internal_notes,
candidate_feedback?, share_with_candidate, feedback_shared_at?,
competency_ratings?  /* only if PQ-03 approves */
```

### 5.9 Offer

```
id, application_id, job_id, status,
salary_amount, currency, salary_period, start_date,
sent_at?, expires_at?, responded_at?,
benchmark_hint?, in_range?
```

Confirm whether `draft` / `pending_approval` / `withdrawn` are official or FE-only until send.

### 5.10 Notification

```
id, actor_role, event, title, body, unread, read_at?,
related{type, id|public_code}, deep_link_path?
```

### 5.11 Admin org / user / audit

```
Organization review DTO; User directory DTO;
Audit{id, actor_id, action, target_type, target_id, result, at}
```

### 5.12 Messages / Agency

**Blocked by Product** — no FE contract freeze requested until PQ-01 / PQ-02.

---

## 6. Action / Endpoint Contracts Required

> All are **Missing** (not implemented). Names are proposed for Backend to confirm. Prefer web form POST + redirect unless noted.

| ID | Method | Proposed URL | Purpose | Required fields | Success | Errors | Permission | FE consumer |
|---|---|---|---|---|---|---|---|---|
| AUTH-LOGIN | POST | `/login` | Session login | email, password, remember? | Redirect role home | 422, 429 | Guest | Login |
| AUTH-LOGOUT | POST | `/logout` | End session | _token | Redirect `/` | 419 | Auth | Nav |
| AUTH-REGISTER | POST | `/register` | Create user | name, email, password, account_type | Redirect verify | 422 | Guest | Register |
| AUTH-VERIFY-RESEND | POST | `/email/verification-notification` | Resend | — | Flash | 429 | Auth unverified | Verify |
| AUTH-PASSWORD | POST | `/forgot-password`, `/reset-password` | Reset flow | email / token+password | Flash/redirect | 422 | Guest | Password screens |
| CONTACT | POST | `/contact` | Contact form | name, email, message | Flash | 422, 429 | Public | Contact |
| ONB-CAND | POST | `/onboarding/candidate` | Save profile | profile fields | Redirect dashboard/membership | 422 | Candidate | Onboarding |
| ONB-CO | POST | `/onboarding/company` | Register company | company fields, doc | Redirect status | 422 | Company owner | Onboarding |
| MEM-REQ | POST | `/candidate/membership` | Join request | company_id, title?, dept? | Redirect | 409 pending exists | Candidate | Membership |
| MEM-APPROVE | POST | `/company/employees/requests/{id}/approve` | Approve+role | role | Redirect | 403, 422 | Company admin/HR | Requests |
| MEM-REJECT | POST | `/company/employees/requests/{id}/reject` | Reject | reason? | Redirect | 403 | Company admin/HR | Requests |
| JOB-DRAFT | POST/PUT | `/company/jobs`, `/company/jobs/{id}` | Save draft | wizard fields | Redirect wizard/show | 403 org pending, 422 | HR+ | Wizard |
| JOB-PUBLISH | POST | `/company/jobs/{id}/publish` | Publish | — | Redirect | 403, 422 stages | HR+ | Wizard/show |
| JOB-CLOSE | POST | `/company/jobs/{id}/close` | Close | reason? | Redirect | 403 | HR+ | Jobs |
| JOB-NOM | POST | `/company/jobs/{id}/nominations` | Nominate | employee_ids[] | Back | 422 | HR+ | Wizard |
| JOB-SAVE | POST/DELETE | `/candidate/jobs/{code}/saved` | Save/unsave | — | 204/back | 403 | Candidate | Discover/saved |
| APP-APPLY | POST | `/candidate/jobs/{code}/apply` | Apply | cv_id, cover_note?, consent | Redirect submitted | 403 eligibility, 409 attempt | Candidate | Apply |
| APP-WITHDRAW | POST | `/candidate/applications/{id}/withdraw` | Withdraw | — | Redirect | 409 not allowed | Owner | Application show |
| APP-STATUS | POST | `/company/applicants/{id}/status` | Advance/reject | status, reason? | Redirect | 422 illegal transition | HR+ | Pipeline/show |
| AI-OVERRIDE | POST | `/company/applicants/{id}/ai-override` | Override AI | decision, reason | Redirect | 422 reason required | HR+ | Override modal |
| NOTE-SAVE | PUT | `/company/applicants/{id}/notes` | HR note | body | 204/back | 403 | HR+ | Applicant notes |
| ASSESS-CREATE | POST | `/company/assessments` | Create exam | fields, questions | Redirect | 422 | HR+ | Create |
| ASSESS-APPROVE-Q | POST | `/company/assessments/{id}/approve-questions` | Approve AI Qs | — | Redirect | 409 | HR+ | Show |
| ASSESS-START | POST | `/candidate/assessments/{id}/start` | Start attempt | camera_consent, identity_consent | Redirect take | 409 exhausted | Candidate | Intro |
| ASSESS-SUBMIT | POST | `/candidate/assessments/{id}/submit` | Submit | answers[] | Redirect result | 409 timer | Candidate | Take |
| PROCTOR-REVIEW | POST | `/company/assessments/reviews/{id}` | Human review | decision, notes | Redirect | 403 | HR/proctor | Results |
| IV-CREATE | POST | `/company/interviews` | Schedule | application_id, round, interviewers, slots | Redirect | 422 | HR+ | Schedule |
| IV-BOOK | POST | `/candidate/interviews/{id}/book` | Book | availability_id | Redirect | 409 conflict | Candidate | Slots |
| IV-RESCHED | POST | `/candidate/interviews/{id}/reschedule` | Reschedule | availability_id | Redirect | 403 &lt;24h | Candidate | Modal |
| IV-CANCEL | POST | `/candidate/interviews/{id}/cancel` | Cancel | reason? | Redirect | 403 &lt;24h | Candidate | Detail |
| AVAIL-SAVE | PUT | `/interviewer/availability` | Save windows | slots[] | Back | 422 | Interviewer | Schedule |
| EVAL-SUBMIT | POST | `/interviewer/evaluations` | Scorecard | interview_id, numeric_score, internal_notes, feedback?, share, competencies? | Redirect | 422; **PQ-03** | Assigned interviewer | Scorecard |
| OFFER-UPSERT | POST | `/company/offers` | Draft/send | application_id, compensation, expires_at?, send? | Redirect | 422 range | HR+ | Offer create |
| OFFER-RESPOND | POST | `/candidate/offers/{id}/respond` | Accept/reject | decision | Redirect | 409 expired | Candidate | Offer show |
| NOTIF-READ | POST | `/notifications/{id}/read` | Mark read | — | 204 | 403 | Recipient | Dropdown/list |
| NOTIF-READ-ALL | POST | `/notifications/read-all` | Mark all | — | 204 | — | Recipient | Dropdown |
| ORG-APPROVE | POST | `/admin/organizations/{id}/approve` | Approve org | notes? | Redirect | 403 | Super Admin | Org show |
| ORG-REJECT | POST | `/admin/organizations/{id}/reject` | Reject | reason | Redirect | 422 | Super Admin | Org show |
| ORG-SUSPEND | POST | `/admin/organizations/{id}/suspend` | Suspend | reason | Redirect | 403 | Super Admin | Org show |
| ADMIN-JOB-MOD | POST | `/admin/jobs/{id}/moderate` | Moderation | status, note | Redirect | iff Product confirms queue | Super Admin | Admin jobs |
| PREFS-SAVE | PUT | `/candidate/preferences` | Save prefs | prefs | Back | 422 | Candidate | Preferences |
| PROFILE-SAVE | PUT | `/candidate/profile` | Save profile | profile | Back | 422 | Candidate | Profile |
| CV-UPLOAD | POST | `/candidate/cvs` | Upload CV | file | JSON/redirect | 422 mime/size | Candidate | Profile/apply |
| CO-PROFILE | PUT | `/company/profile` | Save company | fields, logo? | Back | 422 | Company admin | Profile |

**Contract count requested (action IDs above):** **42** mutating contracts + **page GET data contracts** for each of the **69** product screens (read models may be fulfilled by Blade controllers without separate JSON).

---

## 7. Authentication and Permissions

### 7.1 What FE needs on each authenticated request

| Need | Detail |
|---|---|
| Session cookie | Standard Laravel web guard |
| CSRF | `@csrf` / `X-XSRF-TOKEN` for any XHR |
| Actor context | Current user DTO (§5.1) shared to layouts |
| Authorization failures | `403` page or redirect + flash; never silent success |
| Unauthenticated | `401`/`302` to `login` with `intended` URL |

### 7.2 Permission flags FE will display (Backend must compute)

Examples FE already gates in chrome: `can_publish_jobs`, `can_apply`, `can_withdraw`, `can_reschedule`, `can_override_ai`, `can_manage_offers`, `can_approve_membership`, `can_review_proctoring`, `can_moderate_organizations`.

**FE will not treat query-string role switches as authority.**

### 7.3 Role → workspace mapping (expected)

| Role | Primary workspace routes |
|---|---|
| Candidate | `/candidate/*` |
| Company admin / HR | `/company/*` |
| Interviewer | `/interviewer/*` (+ limited company reads) |
| Employee | Limited; membership + nominated private jobs |
| Super Admin | `/admin/*` |

---

## 8. Mock Data and localStorage Replacement

| Current Mock/LocalStorage | Frontend Consumer | Backend Data or Action Needed | Expected Contract | Integration Priority |
|---|---|---|---|---|
| `MockPublicData::*` | Public jobs/company/home | Public catalog queries | GET jobs/companies | P0 |
| `MockCandidateData::dashboard/profile/...` | Candidate screens | Candidate DTOs | GET resources | P0 |
| `MockCompanyData::*` | Company screens | Company-scoped DTOs | GET resources | P0 |
| `MockInterviewerData::*` | Interviewer screens | Assignment DTOs | GET resources | P1 |
| `MockAdminData::*` | Admin screens | Admin DTOs | GET resources | P1 |
| `MockNotificationData::forActor` | Dropdown + lists | Notifications | GET + read POST | P1 |
| Presentation helpers | Eligibility/AI/booking/offer labels | Server flags + same label keys | Enrich DTOs | P0 |
| `hb_theme` / `hb_locale` | All | Optional user pref sync | **Stay local** (locale cookie already works) | — |
| `hb_interview_prep_*` | Candidate prep | Optional | **May stay local** | P3 |
| `hb_candidate_saved_jobs_v2` | Saved jobs | saved_jobs | Persist | P1 |
| `hb_candidate_preferences_v1` | Preferences | preferences | Persist | P1 |
| `hb_candidate_profile_draft_v1` | Profile | profile | Persist; clear LS | P0 |
| `hb_candidate_apply_draft_{job}` | Apply | optional draft / discard after submit | Persist or ephemeral | P0 |
| `hb_candidate_membership_request_v1` | Membership | membership request | Persist | P0 |
| `hb_onboarding_*` | Onboarding | onboarding/profile/company | Persist | P0 |
| `hb_candidate_notifications*` | Read-state | notifications.read_at | Persist | P1 |
| `hb_assessment_result_*` | Results preview | exam_attempts | **Server only** | P1 |
| `hb_company_job_draft_*` | Wizard | jobs draft | Persist | P0 |
| `hb_company_profile_draft_v1` | Company profile | companies | Persist | P1 |
| `hb_assessment_draft` | Assessment create | exams | Persist | P1 |
| `hb_offer_draft_*` | Offer create | offers draft | **Persist + ACL** | P0 |
| `hb_applicant_note_*` | Applicant notes | notes | **Persist + ACL** | P0 |
| `hb_talent_shortlist` | Talent pool | talent shortlist | Persist | P2 |
| `hb_iv_*` / scorecard storage keys | Interviewer | evaluations | **Persist; clear device** | P0 |
| `hb_interviewer_availability_prefs_v1` | Schedule | availability | Persist | P1 |
| `hb_candidate_message_draft_*` | Messages | — | **Blocked PQ-02** / discard | — |

---

## 9. Error, Validation and Upload Conventions

| Topic | Frontend expectation |
|---|---|
| Validation | Laravel `$errors` bag on redirect; or JSON `{ message, errors:{field:[...]}}` with `422` |
| Auth | `302` → login; session regenerate on login |
| Forbidden | `403` with safe message; no data leak |
| Not found | `404` for unknown public_code/id |
| CSRF | `419` → reload + flash; document recovery |
| Rate limit | `429` + Retry-After when possible |
| Flash | `success`, `error`, `status`, `warning` keys FE can toast |
| Datetime | ISO-8601 with offset; FE formats per locale |
| Currency | ISO code + decimal string or minor units — **pick one and document** |
| Pagination | `page`, `per_page`; response meta `total`, `last_page` |
| Filters | Document query params per index (q, status, work_mode, …) |
| Uploads | Multipart; max size; allowed MIME; virus scan async OK if status returned |
| Downloads | Authorized route or short-lived signed URL for CV/docs |
| Idempotency | Document for apply/book/offer-respond (duplicate submit) |

Loading states: FE can use disabled buttons + spinners; Backend should avoid multi-minute sync AI on request thread (return `ai_review` / pending and notify).

---

## 10. Staging and Test Data Requirements

Provide to Frontend (no production secrets):

| Item | Need |
|---|---|
| Staging base URL | HTTPS app URL |
| CSRF/session instructions | How to log in via browser; cookie names |
| Test users | Candidate, Company Admin/HR, Interviewer, Employee, Super Admin (emails + passwords for staging only) |
| Sample organization | Prefer `org-ns-001` Northstar Labs (`approved` + `pending` variants) |
| Sample jobs | Public + private + nominated-only; include `ns-204` or mapping |
| Sample candidate | `maya-chen` or mapped id with CV |
| Sample application chain | submitted → ai_review → shortlisted |
| Sample interview | `int-ns204-portfolio` with bookable slots |
| Sample offer | sent + expiring soon |
| AI failed fixture | Application in `ai_failed` for override UI |
| Assessment fixture | attempts_remaining 0 and &gt;0 |
| OpenAPI/Postman | Optional; required if any JSON-only endpoints |

---

## 11. Frontend Integration Sequence

| Wave | FE connects | Backend must have delivered first |
|---|---|---|
| **1** | Auth screens + layout actor | AUTH-* ; current user DTO; CSRF session; staging users |
| **2** | Onboarding, profiles, membership, org banners | Org status; profile/company write; MEM-*; capabilities |
| **3** | Public + candidate jobs; eligibility; saved jobs | Job GETs; eligibility flags; JOB-SAVE; publish gate data |
| **4** | Apply / applications / company pipeline / AI override | APP-*; status enum freeze (**PQ-04**); AI-OVERRIDE; notes |
| **5** | Assessments + interviews + availability | ASSESS-*; IV-*; AVAIL-*; 24h rules |
| **6** | Evaluations + offers | EVAL-SUBMIT (**PQ-03**); OFFER-* |
| **7** | Notifications (+ messaging **only if PQ-02 approved**) | NOTIF-*; messaging contract or FE hide |
| **8** | Admin orgs/users/audit/jobs (+ agencies **only if PQ-01**) | ORG-*; audit GET; moderation decision |

FE will keep mocks behind a temporary flag only if Backend agrees; default plan is replace controller data sources screen-by-screen per wave.

---

## 12. Outstanding Backend Questions

1. Confirm **session Blade forms** as the primary integration style (vs JSON API).  
2. Freeze **PQ-04** official application statuses (accept FE 12 or publish alternate + mapping).  
3. **PQ-03:** Will `competency_ratings` be stored? If no, may FE keep them UI-only and only POST `numeric_score` + notes?  
4. **PQ-02:** Persist, hide, or leave Candidate Messages as non-authoritative preview?  
5. **PQ-01:** Hide Admin Agencies or deliver a contract?  
6. Are Admin job moderation states real, or should FE remove that queue?  
7. Official offer statuses: include `draft` / `pending_approval` / `withdrawn`?  
8. Public resource identifiers: numeric id, `public_code`, or both in URLs?  
9. Currency representation standard?  
10. Notification deep-link format (path vs named route + params)?  
11. File upload limits and storage URL pattern for CVs/company docs?  
12. Exact capability flag list for layouts?

---

## 13. Final Backend Team Handoff Checklist

Backend Team, please deliver:

- [ ] Written confirmation of auth style (session+CSRF)  
- [ ] Current-user + capabilities contract sample (JSON or PHP array dump)  
- [ ] Official status dictionaries (especially **application.status**)  
- [ ] List of mutating routes with request/response/error tables (§6)  
- [ ] Page-level read DTOs for waves 1–4 at minimum  
- [ ] Upload/download rules  
- [ ] Validation/flash/error conventions  
- [ ] Staging URL + seeded personas + sample linked records  
- [ ] Decisions or timelines for **PQ-01…PQ-04**  
- [ ] Explicit “ready for FE wave N” notice per sequence step  

### Summary counts (this request)

| Metric | Count |
|---|---|
| Product screens covered | **69** (+ 1 locale + 3 preview routes) |
| Mutating action contracts requested | **42** |
| Major read DTO families requested | **12** (§5) |
| localStorage keys/patterns called out | **20+** |
| Blocked by Product (do not invent) | **PQ-01, PQ-02, PQ-03, PQ-04** |

---

**Document path:** `docs/backend/HIREBRIDGE_BACKEND_REQUIREMENTS_FOR_FRONTEND_INTEGRATION.md`  

**No application code was modified. Integration not started.**

**STOP — awaiting explicit approval before any Frontend wiring or Backend implementation.**
