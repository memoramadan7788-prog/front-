# Hirebridge — Backend Integration Requirements & Technical Handoff

**Document type:** Implementation-ready backend requirements and handoff specification  
**Classification:** Documentation only (no implementation performed by this deliverable)  
**Primary audience:** Backend Team Lead, Backend Engineers, Frontend Engineers, QA, DevOps, Product, Security  
**Repository path:** `docs/backend/HIREBRIDGE_BACKEND_INTEGRATION_HANDOFF.md`

---

## Table of Contents

1. [Document Control & Scope](#1-document-control--scope)
2. [Executive Summary](#2-executive-summary)
3. [Verified Repository Inventory](#3-verified-repository-inventory)
4. [Current Architecture](#4-current-architecture)
5. [Recommended Target Architecture](#5-recommended-target-architecture)
6. [Backend Capability Gap Matrix](#6-backend-capability-gap-matrix)
7. [Authentication, Roles & Permissions](#7-authentication-roles--permissions)
8. [Organization Tenancy & Data Ownership](#8-organization-tenancy--data-ownership)
9. [Database & ERD Gap Analysis](#9-database--erd-gap-analysis)
10. [Backend API / Server Contracts](#10-backend-api--server-contracts)
11. [Domain Workflows & State Machines](#11-domain-workflows--state-machines)
12. [Frontend-to-Backend Integration Map](#12-frontend-to-backend-integration-map)
13. [Browser Storage & Draft Migration](#13-browser-storage--draft-migration)
14. [Document/File Storage](#14-documentfile-storage)
15. [Messaging, Notifications & Queues](#15-messaging-notifications--queues)
16. [Interviews, Scheduling & Time Zones](#16-interviews-scheduling--time-zones)
17. [Assessments, Evaluations & Offers](#17-assessments-evaluations--offers)
18. [Admin, Moderation & Audit](#18-admin-moderation--audit)
19. [Security & Privacy](#19-security--privacy)
20. [Error Handling & Localization](#20-error-handling--localization)
21. [Backend Testing Strategy](#21-backend-testing-strategy)
22. [Infrastructure & DevOps](#22-infrastructure--devops)
23. [Phased Backend Roadmap](#23-phased-backend-roadmap)
24. [Team Ownership & RACI](#24-team-ownership--raci)
25. [Detailed Implementation Backlog](#25-detailed-implementation-backlog)
26. [Open Product/Architecture Decisions](#26-open-productarchitecture-decisions)
27. [Backend Team Handoff Checklist](#27-backend-team-handoff-checklist)
28. [Integration Definition of Done](#28-integration-definition-of-done)
29. [Risks, Assumptions & Dependencies](#29-risks-assumptions--dependencies)
30. [Final Recommendations](#30-final-recommendations)
31. [Appendix: Verified Route Inventory](#31-appendix-verified-route-inventory)
32. [Appendix: LocalStorage Contract Inventory](#32-appendix-localstorage-contract-inventory)
33. [Appendix: Proposed Endpoint Catalog](#33-appendix-proposed-endpoint-catalog)
34. [Appendix: Repository Evidence References](#34-appendix-repository-evidence-references)

**Legend used throughout**

| Label | Meaning |
|---|---|
| **Verified in repository** | Confirmed by inspecting source files in this workspace |
| **Specified in available project documents** | Supported by accessible in-repo docs (limited — see gaps) |
| **Inferred requirement** | Architectural inference justified by UI/workflows; needs confirmation |
| **Proposed implementation** | Recommendation for Backend Team; **not implemented** |
| **Unresolved / requires product decision** | Cannot be finalized from evidence |

---

## 1. Document Control & Scope

### 1.1 Purpose

This document tells the Backend Team what must be designed and implemented to turn the completed Hirebridge **frontend presentation** into an **operational recruitment platform** with real identity, authorization, persistence, workflows, integrations, and observability.

### 1.2 Scope (in)

- Gap analysis of current Blade/JS frontend versus required backend capabilities
- Inventory of existing Eloquent models, migrations, seeders, and mock providers
- Proposed hybrid server architecture preserving Blade UI
- AuthZ/AuthN, tenancy, API/form contracts, workflows, storage migration, security, testing, roadmap, backlog

### 1.3 Scope (out)

- Implementing any backend code, migrations, APIs, policies, or services
- Changing frontend behavior
- Declaring frontend keyboard accessibility as signed off (separate QA gate; status remains Conditionally Accepted)
- Inventing unavailable external ERD/workflow PDF contents

### 1.4 Evidence discipline

External “Platform Workflows” and “Recruitment Platform ERD” attachments were **not found** as standalone documents under the repository root, `docs/`, or common filenames (`*erd*`, `*workflow*`, `*.pdf` requirement packs).  

**Verified substitute for ERD:** `database/migrations/*`, `app/Models/*`, `database/seeders/{Role,Permission,Demo}Seeder.php`.  

**Verified substitute for workflows:** Blade screens + `resources/js/app.js` presentation behaviors + controller docblocks stating “frontend presentation only”.

### 1.5 Inspection snapshot (Verified)

| Item | Value | Evidence |
|---|---|---|
| PHP | 8.3.6 (CLI) | `php -v` |
| Laravel | Framework **13.37.0** (`laravel/framework` ^13.17) | `composer.json`, `php artisan --version` |
| Application GET routes | **71** (`--except-vendor`) | `php artisan route:list` |
| PHPUnit | **196** tests passed | `php artisan test --compact` |
| Frontend stack | Blade, Tailwind CSS 4, Vite 8, JS in `resources/js/app.js` | `package.json`, views |
| API routes file | **Not registered**; `routes/api.php` **missing** | `bootstrap/app.php` registers only `web` + `console` + `/up` |
| Auth middleware on workspace routes | **Absent** (presentation GET only) | `routes/web.php` comments |
| Policies directory | **Missing** | `app/Policies` does not exist |
| Mock providers | 5 classes under `app/Support/Frontend/` | File inventory |
| Controllers using mocks | **34** controller files reference Mock*Data | ripgrep |
| Default DB | `sqlite` via env default | `config/database.php` |
| Locale cookie | `hb_locale` via `SetFrontendLocale` | `app/Http/Middleware/SetFrontendLocale.php` |
| Frontend acceptance | Conditionally Accepted; trusted keyboard QA pending | Prior acceptance reports (product status; not re-verified here as keyboard) |

**Discrepancy note:** Prior reports said “Laravel 13 / PHP 8.3” and ~71 routes / ~196 tests — **confirmed accurate** against this workspace on 2026-10-22.

### 1.6 Absolute constraint for this deliverable

This task creates **only** this Markdown file. No application source, migrations, configs, or tests were modified as part of producing this document.

---

## 2. Executive Summary

### 2.1 Frontend maturity (high)

**Verified:** Hirebridge ships a broad multi-role Blade UI covering Public, Auth screens, Onboarding wizards, Candidate, Company/HR, Interviewer, and Super Admin modules; EN/AR + RTL; Light/Dark/System themes; Frame 51 shared components; 71 named GET routes; 196 presentation-oriented automated tests.

### 2.2 Backend maturity (partial schema, minimal runtime integration)

**Verified:** A large Eloquent model set (**45** models) and **49** migrations exist for core hiring domains (users, companies, jobs, applications, interviews, exams, offers, notifications scaffolding, AI/proctoring-related tables). Role/permission **seed data** exists.  

**Also verified:** Controllers almost universally render views from `Mock*Data` arrays. Auth routes are GET-only with explicit comments that credentials/sessions are not processed. No Laravel Policies. No registered API router. Workspace routes lack `auth` middleware. Browser `localStorage` holds drafts, saved jobs, scorecards, notes, and notification read state.

**Critical truth:** Frontend completion is **not** an operational recruitment platform. Schema scaffolding ≠ wired product.

### 2.3 Major missing backend capabilities

1. Real authentication/session lifecycle and email verification/password reset delivery  
2. Server-side authorization (policies/gates) and tenant isolation  
3. Replace mock datasets with Eloquent/query services on page load  
4. Persist applications, pipeline transitions, interviews, scorecards, offers  
5. Secure document storage (resumes, company docs, offer letters)  
6. Messaging persistence (Candidate Messages UI exists; **no conversations/messages migrations found**)  
7. Saved jobs / preferences / alerts persistence (**no saved_jobs table found**)  
8. Admin moderation + real audit telemetry (`AuditLog` model references `audit_logs` but **no `audit_logs` migration found**)  
9. Queues, mail, calendar/video integrations, observability  
10. Completing incomplete migration conventions (`uid`/`luid`/`timestamps` often only commented placeholders)

### 2.4 Most important security requirements

- Never trust UI role chrome (`uiRole` view props) as authorization  
- Enforce company-scoped access to applicants, notes, evaluations  
- Treat interview notes, scorecards, resumes, and messages as sensitive PII  
- Migrate sensitive drafts out of `localStorage`  
- Prevent IDOR on public-looking string IDs (`ns-204`, `maya-chen`, `int-ns204-portfolio`) by introducing stable server IDs + authorization  
- CSRF on all mutating routes; rate limit auth and public contact  

### 2.5 Integration complexity

**High.** Schema exists but is incomplete/mismatched to frontend contracts; mock IDs ≠ DB PKs; many UI workflows are preview-only; permissions seed set does not cover all UI features (agency, messaging, moderation taxonomy, preferences).

### 2.6 Critical architectural decisions (need approval)

1. Hybrid Blade + selective JSON vs form-post-first (recommendation: **hybrid**, Blade-preserving)  
2. Organization model: `companies` table vs Admin UI “organizations” including agencies  
3. Whether Sanctum/API tokens are in scope (User model imports `HasApiTokens` but Sanctum **not** in `composer.json` require)  
4. Company Messages product scope  
5. Scorecard schema (competency ratings UI vs single `numeric_score` + notes columns)  
6. Offer approval workflow (UI implies approval; DB `offers.status` starts at `sent`)  
7. Default locale (`users.locale` default `'ar'` in migration vs EN-first frontend)  

### 2.7 High-priority dependencies

Identity → Organization membership → Jobs → Applications → Interviews/Evaluations → Offers → Admin/Audit. Messaging after product confirmation. External calendar/video after scheduling core.

### 2.8 Recommended sequence

Phases **B0→B8** in §23: contracts → identity/tenancy → profiles/jobs → applications → interviews → assessments/offers → messaging/notifications → admin/agency → hardening.

### 2.9 Primary risks

- Premature wiring of incomplete migrations (missing timestamps/audit columns)  
- Permission model drift between seeders and Figma roles  
- localStorage PII leakage across shared browsers  
- Treating DemoSeeder / Mock IDs as production identifiers  
- Building a separate SPA unnecessarily  

### 2.10 Items requiring Product/Tech Lead approval

See §26 Open Decisions register (blocking items marked).

---

## 3. Verified Repository Inventory

### 3.1 Runtime & packages (Verified)

| Area | Present | Notes |
|---|---|---|
| Laravel 13 / PHP 8.3 | Yes | |
| Laravel Sanctum | **Not in composer require**; User uses `HasApiTokens` | **Broken/incomplete reference — resolve before API tokens** |
| Laravel Breeze/Jetstream/Fortify | Not present | Auth is custom GET UI |
| Policies / Gates | Not present | |
| `routes/api.php` | Missing | |
| Queue/mail/broadcast packages | Framework only | Drivers env-dependent |
| Tailwind 4 / Vite 8 | Yes | No SPA framework |

### 3.2 Frontend mock layer (Verified)

| Class | Path | Used by |
|---|---|---|
| `MockPublicData` | `app/Support/Frontend/MockPublicData.php` | Public jobs/home/company |
| `MockCandidateData` | `app/Support/Frontend/MockCandidateData.php` | Candidate workspace |
| `MockCompanyData` | `app/Support/Frontend/MockCompanyData.php` | Company workspace |
| `MockInterviewerData` | `app/Support/Frontend/MockInterviewerData.php` | Interviewer workspace |
| `MockAdminData` | `app/Support/Frontend/MockAdminData.php` | Admin workspace |
| `BreadcrumbNavigator` | `app/Support/Frontend/BreadcrumbNavigator.php` | Breadcrumbs |

### 3.3 Domain models present (Verified — 45)

Users/RBAC: `User`, `Role`, `Permission`, `UserRole`  
Company: `Company`, `CompanyEmployee`, `CompanySocialLink`  
Candidate: `CandidateProfile`, `CandidateExperience`, `CandidateEducation`, `CandidateCourse`, `CandidateCv`, `CandidateSocialLink`, `Skill`  
Jobs: `Job`, `JobCategory`, `JobNomination`, `JobInterviewRound`, `JobExam`  
Hiring: `Application`, `ApplicationStatusHistory`, `Offer`, `Interview`, `InterviewBooking`, `InterviewerAvailability`, `InterviewEvaluation`, `CalendarIntegration`  
Assessments: `Exam`, `Question`, `QuestionOption`, `QuestionTestCase`, `ExamAttempt`, `Answer`, `GradingResult`, `ProctoringEvent`, `ProctoringReview`  
AI/salary: `CvMatchResult`, `AiReviewDecision`, `SalaryRecord`, `SalaryRecommendation`  
Comms/misc: `UserNotification`, `ChatbotSession`, `ChatbotMessage`, `Country`, `AuditLog`  

### 3.4 Seeders (Verified)

`DatabaseSeeder`, `RoleSeeder`, `PermissionSeeder`, `SuperAdminSeeder`, `SkillSeeder`, `JobCategorySeeder`, `DemoSeeder` (explicitly temporary local demo).

### 3.5 Middleware (Verified)

Only custom middleware: `App\Http\Middleware\SetFrontendLocale` (cookie `hb_locale`). Appended to `web` stack in `bootstrap/app.php`.

### 3.6 Localization (Verified)

`lang/en/*.php` and `lang/ar/*.php` for `admin`, `auth`, `candidate`, `common`, `company`, `interviewer`, `navigation`, `onboarding`, `public`, `js`.

### 3.7 Unavailable external docs

| Expected artifact | Status |
|---|---|
| Standalone Platform Workflows document | **Unavailable** in repo |
| Standalone Recruitment Platform ERD PDF/doc | **Unavailable**; use migrations/models |
| Product requirements pack | **Unavailable** beyond UI + seeders |

---

## 4. Current Architecture

### 4.1 Request flow (Verified)

```
Browser → routes/web.php (GET only for product UI)
       → Page controller (often sets uiRole, pageTitle)
       → Mock*Data::…() arrays
       → Blade view + optional Alpine-less JS in app.js
       → localStorage for drafts / toggles / preview “success”
```

### 4.2 Routing behavior (Verified)

- Named GET routes for all presentation screens (`routes/web.php`).  
- Comments explicitly mark Auth, Onboarding, Candidate, etc. as **frontend presentation only**.  
- No POST/PUT/PATCH/DELETE product routes for hiring operations.  
- Health: `/up`. Locale switch: `GET /locale/{locale}`.

### 4.3 Controllers

Lightweight invokable or resource-style `index/show/create` methods returning `view(...)`. Example: `Candidate\MessageController` sets `frontendOnly => true` and loads `MockCandidateData::messages()`.

### 4.4 Client JS responsibilities (Verified)

`resources/js/app.js` implements: theme, modals/focus trap, mobile drawer (`inert`), tabs, filters, saved jobs, apply drafts, messages drafts, preferences, notifications read state, onboarding local steps, company job wizard drafts, scorecard drafts, interviewer checklists/notes, talent shortlist, applicant notes, assessment local results, etc.

### 4.5 Persistence limitations (Verified)

| Layer | Reality |
|---|---|
| DB schema files | Present for many entities |
| Controllers → Eloquent | Generally **not wired** for UI |
| Auth sessions for roles | **Not enforced** on workspace routes |
| Mutating business actions | Preview/disabled or localStorage |
| Audit telemetry | Mock rows in `MockAdminData::auditEvents()` |
| File uploads | UI stubs; no production upload pipeline |

### 4.6 Tests (Verified)

PHPUnit feature tests largely assert HTTP 200, copy presence, localization, and presentation regressions — **not** authorization or persistence correctness for real hiring ops.

---

## 5. Recommended Target Architecture

### 5.1 Recommendation (Proposed)

**Hybrid Laravel monolith preserving Blade:**

1. Keep existing named GET page routes and Blade layouts.  
2. Replace `Mock*Data` gradually with **view composers / query services / Eloquent resources** returning compatible array shapes (or DTOs).  
3. Add **mutating Form Request + controller actions** (POST/PUT/PATCH) for classic flows (login, apply, schedule, submit scorecard).  
4. Add **selective JSON endpoints** under `/api` or `/webhooks`-adjacent only where existing JS already expects async updates (saved jobs toggle, message send, notification mark-read, slot search).  
5. Enforce `auth` + policies + company scoping on all non-public routes.  
6. Use queues for mail, notifications, calendar invites, virus scan.

**Do not** require a separate SPA rewrite.

### 5.2 Why this fits Hirebridge

- UI already Blade + progressive JS.  
- Minimizes rewrite risk while enabling real security.  
- Allows incremental cutover per module with mock fallback behind feature flags (**Proposed**).

### 5.3 Impact on controllers

| Today | Target (Proposed) |
|---|---|
| `return view(..., MockX::y())` | `return view(..., $service->present(...))` |
| No Form Requests | Form Requests + Policies |
| `uiRole` hard-coded | Derived from authenticated membership |

### 5.4 Mock replacement strategy (Proposed)

1. Keep Mock*Data for PHPUnit presentation tests and demo mode.  
2. Introduce `App\Services\*` / `App\ViewModels\*` with same array keys where practical.  
3. Feature flag `FRONTEND_DATA_SOURCE=mock|database`.  
4. Delete mocks only after parity + tests.

### 5.5 Validation & errors

- Server Form Request / API validation is authoritative.  
- Map Laravel `422` errors to existing `hb-field-error` / alert patterns.  
- Stop showing local-only success once endpoints exist.

### 5.6 Route compatibility

Preserve GET named routes for deep links (`jobs.show`, `candidate.applications.apply`, etc.). Add sibling mutate routes (`applications.store`) without breaking bookmarks.

---

## 6. Backend Capability Gap Matrix

> Priority: P0 blocker for any real usage · P1 core hiring loop · P2 important · P3 later/optional

### 6.1 Public

| Module | Screen / Workflow | Current Frontend Behavior | Missing Backend Capability | Required Entities | Required Service / API | Authorization | Priority | Dependencies | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| Public | Home | Static/marketing content via controller | Optional CMS not required initially | — | Keep static or config | Public | P3 | — | `HomeController` |
| Public | Job listing/filter | Mock jobs + client filters | Persist jobs; server filter/pagination | `jobs`, `companies`, `job_categories` | `GET jobs` query | Public published only | P1 | B2 | `MockPublicData`, `jobs.index` |
| Public | Job detail NS-204 style | Mock detail + Apply/Save links | Real job by public id/slug | `jobs` + public slug/code | `GET jobs/{id}` | Public + visibility rules | P1 | B2 | `jobs.show` |
| Public | Company profile | Mock company page | Persist company public profile | `companies` | `GET companies/{id}` | Public if approved | P2 | B2 | `companies.show` |
| Public | Contact | Presentation form | Store/send contact messages | **new** `contact_messages` or mail-only | `POST contact` | Public + rate limit | P2 | Mail | `contact` route |
| Public | Apply entry | Links into candidate apply GET | Auth gate + application create | `applications` | See Candidate apply | Candidate | P1 | Auth | apply routes |

### 6.2 Authentication

| Module | Screen / Workflow | Current Frontend Behavior | Missing Backend Capability | Required Entities | Required Service / API | Authorization | Priority | Dependencies | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| Auth | Register | GET UI only | Create user + role + profile/company stub | `users`, roles, profiles | `POST register` | Guest | P0 | B1 | `routes/web.php` auth comment |
| Auth | Login/Logout | GET login; sign-out disabled/soon | Session login/logout | `sessions` | `POST login`, `POST logout` | Guest/Auth | P0 | B1 | topbar Soon |
| Auth | Password reset | GET pages | Token mail + reset | `password_reset_tokens` | Fortify-like flow | Guest | P0 | Mail | migrations support tables |
| Auth | Email verification | GET notice | Signed URL verify | `users.email_verified_at` | verification routes | Auth | P0 | Mail | |
| Auth | Session mgmt | None | Secure cookies, idle timeout | sessions | middleware | Auth | P0 | B1 | |
| Auth | Role assignment | UI role chrome only | Persist `user_roles` / membership | `user_roles`, `company_employees` | registration + invites | System | P0 | B1 | RoleSeeder |

### 6.3 Onboarding

| Module | Screen / Workflow | Current Frontend Behavior | Missing Backend Capability | Required Entities | Service | AuthZ | Priority | Deps | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| Onboarding | Candidate wizard | localStorage `hb_onboarding_candidate_v1` | Persist profile steps | `candidate_profiles`, skills, etc. | PATCH profile + complete flag | Candidate | P1 | Auth | onboarding views |
| Onboarding | Company wizard | localStorage `hb_onboarding_company_v1` | Persist company + docs + pending status | `companies`, docs | POST company onboarding | Company owner | P1 | Auth, files | |
| Onboarding | Status | Presentation status page | Real verification state | `companies.status` | GET status | Owner | P1 | Admin review | |
| Onboarding | Team invite | UI patterns for employees | Invitation tokens | **new** invitations table | invite endpoints | company_admin/hr | P2 | Mail | employees UI |

### 6.4 Candidate

| Module | Screen / Workflow | Current Frontend Behavior | Missing Backend Capability | Required Entities | Service | AuthZ | Priority | Deps | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| Candidate | Dashboard | Mock metrics | Aggregations from DB | applications, interviews, offers | dashboard query | Candidate | P2 | B3 | |
| Candidate | Profile/resume | Draft in localStorage | CRUD profile + CV upload | profiles, cvs, experience… | profile APIs | Candidate own | P1 | Files | |
| Candidate | Jobs / saved | Saved IDs in `hb_candidate_saved_jobs_v2` | saved_jobs persistence | **new** `saved_jobs` | save/unsave | Candidate | P1 | Jobs | app.js |
| Candidate | Apply wizard | Draft localStorage; preview submit | Create application + history | applications, history, cv | POST apply | jobs.apply | P0 | Profile/CV | |
| Candidate | Applications track | Mock list/detail | Query own applications | applications | GET | applications.view_own | P1 | B3 | |
| Candidate | Interviews | Mock | Booked interviews query | interviews, bookings | GET | Candidate party | P1 | B4 | |
| Candidate | Messages | Mock threads; draft local | Conversations + send | **new messaging tables** | messaging APIs | Participant | P2 | Product decision | MessageController |
| Candidate | Assessments | Local result keys | Attempts server-timed | exams, attempts, answers | take/submit | exams.take | P1 | B5 | |
| Candidate | Offers | Preview accept/decline disabled | Respond to offers | offers | respond | offers.respond | P1 | B5 | |
| Candidate | Preferences/alerts | localStorage prefs | Persist prefs + digest jobs | **new** preferences/alerts | CRUD + scheduler | Candidate | P2 | Queues | |
| Candidate | Notifications | localStorage read map | Persist notifications | `notifications` / `UserNotification` | list/mark | Recipient | P2 | Events | |

### 6.5 Company / HR

| Module | Screen / Workflow | Current Frontend Behavior | Missing Backend Capability | Required Entities | Service | AuthZ | Priority | Deps | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| Company | Dashboard | Mock | Metrics | jobs, applications | queries | company roles | P2 | B3 | |
| Company | Profile | Draft localStorage | Persist company profile | companies | PATCH | company_admin | P1 | B2 | |
| Company | Jobs CRUD/publish | Wizard draft localStorage; preview | Job lifecycle | jobs | CRUD + publish | jobs.* perms | P0 | B2 | |
| Company | Applicants pipeline | Mock + notes localStorage | Pipeline + notes server | applications, **notes** | stage move, notes | applications.* | P0 | B3 | |
| Company | Schedule interview | Preview confirm only | Bookings + conflicts | interviews, bookings, availability | schedule | interviews.assign | P0 | B4 | |
| Company | Evaluations review | Mock | Read submitted evaluations | interview_evaluations | GET | company | P1 | B5 | |
| Company | Offers | Draft local; send disabled until approval UI | Offer create/approve/send | offers + **approval** | offer workflow | offers.manage | P1 | B5 | MockCompanyData send_note |
| Company | Talent pool | Shortlist localStorage | Persist pool/shortlist | **new** or tags | shortlist APIs | company | P3 | B3 | |
| Company | Team / join requests | Presentation | Membership approve | company_employees | approve/reject | employees.* | P1 | B1 | |
| Company | Analytics | Client filter mock | Aggregations | applications/jobs | report queries | reports-like | P3 | B3 | |
| Company | Assessments | Draft local | Exam CRUD assign | exams, job_exams | CRUD | exams.* | P2 | B5 | |
| Company | Messages | Sidebar “Soon” | **Product decision** | TBD | TBD | TBD | — | Confirm | sidebar |

### 6.6 Interviewer

| Module | Screen / Workflow | Current Frontend Behavior | Missing Backend Capability | Required Entities | Service | AuthZ | Priority | Deps | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| Interviewer | Dashboard/schedule | Mock | Assigned interviews | interviews, bookings | GET schedule | interviewer | P1 | B4 | |
| Interviewer | Availability | localStorage prefs | Persist slots | interviewer_availability | CRUD | interviewer | P1 | B4 | |
| Interviewer | Prepare/live | Checklist/notes localStorage | Persist notes/checklist | **new** or evaluation draft | PATCH notes | interviews.conduct | P1 | B4 | Sensitive |
| Interviewer | Scorecard | Draft `hb_iv_{id}_scorecard_v1` | Draft/submit evaluation | interview_evaluations (+ competencies) | save/submit | interviews.evaluate | P0 | B5 | MockInterviewerData |

### 6.7 Super Admin

| Module | Screen / Workflow | Current Frontend Behavior | Missing Backend Capability | Required Entities | Service | AuthZ | Priority | Deps | Evidence |
|---|---|---|---|---|---|---|---|---|---|
| Admin | Organizations | Mock list/detail | CRUD + verify/suspend | companies (+ agency type?) | admin org APIs | companies.review/suspend | P1 | B1/B7 | |
| Admin | Users & access | Mock | User admin | users, roles | users.manage | super_admin | P1 | B1 | |
| Admin | Job moderation | Mock decisions | Moderation cases | **new** moderation or job flags | moderate | platform | P1 | B7 | |
| Admin | Agency ops | Mock | Agency engagements | **new** agency model | agency APIs | platform | P2 | Product | |
| Admin | Settings & audit | Mock audit events | Real audit write/read | **audit_logs migration missing** | audit query | audit.view | P1 | B7 | AuditLog model vs no migration |
| Admin | Integrations | Review-only modals | Secrets + OAuth | calendar_integrations etc. | connect | super_admin | P3 | Vendors | |

---

## 7. Authentication, Roles & Permissions

### 7.1 Distinctions (Proposed + Verified seed baseline)

| Concern | Definition |
|---|---|
| Authentication | Prove user identity (session) |
| Authorization | Permit actions via roles/permissions/policies |
| Organization membership | `company_employees` status approved |
| Workspace permissions | Role scoped with `user_roles.company_id` |
| Platform administration | `super_admin` / platform-scoped permissions |
| Invitation acceptance | Pending → approved membership (**needs invitation entity**) |
| Ownership | `companies.owner_user_id` |
| Resource access | Policy checks on job/application/interview/offer |

### 7.2 Roles in seeders (Verified) vs UI personas (Verified UI)

| Seeder role | Scope | UI persona alignment | Gap |
|---|---|---|---|
| `super_admin` | platform | Admin | OK baseline |
| `candidate` | platform | Candidate | OK |
| `company_admin` | company | Company Owner/Admin | OK |
| `hr` | company | HR/Recruiter | Partial vs UI “recruiter” |
| `interviewer` | company | Interviewer | OK |
| `employee` | company | Team member | Limited perms |
| — | — | Agency user (Admin Agency Ops UI) | **Missing in RoleSeeder** |
| — | — | Messaging participants | **No message perms** |

### 7.3 Role Matrix (Proposed — confirm against product)

| Role | Workspace | Allowed Resources | Allowed Actions | Restricted Actions | Scope |
|---|---|---|---|---|---|
| Candidate | Candidate | Own profile, apps, messages, offers | Apply, save jobs, respond offers, take exams | Other candidates’ data; company admin | User |
| Company Admin | Company | Company jobs, applicants, team, offers | Full company hiring ops per PermissionSeeder | Other companies; platform settings | company_id |
| HR | Company | Jobs, applicants, interviews assign, offers | Create/publish/review | Suspend company; manage platform roles | company_id |
| Interviewer | Company | Assigned applications/CVs/interviews | Conduct/evaluate assigned | Publish jobs; manage offers (unless granted) | assignment |
| Employee | Company | Limited | Per empty perm set | Hiring ops | company_id |
| Super Admin | Platform | Orgs, users, moderation, audit | Review/suspend/manage users | Acting as company without break-glass policy | Platform |
| Agency (Proposed) | Agency workspace | Assigned engagements | Submit candidates to assigned jobs | Unassigned companies | engagement |

### 7.4 Permission Matrix (Verified seed keys + Proposed extensions)

**Existing keys** — see `PermissionSeeder::ALL` (companies.*, roles.*, users.*, audit.view, employees.*, jobs.*, applications.*, cvs.view, interviews.*, exams.*, offers.manage, salary.benchmark.view, jobs.apply, applications.view_own, exams.take, interviews.book, offers.respond, chatbot.*).

**Proposed additions (Requires Approval):**

| Permission key | Purpose |
|---|---|
| `messages.read` / `messages.send` | Candidate/company messaging |
| `jobs.moderate` | Platform job moderation |
| `organizations.verify` | Alias/clarify companies.review |
| `preferences.manage` | Candidate prefs |
| `analytics.view` | Company analytics |
| `talent_pool.manage` | Talent shortlist |
| `evaluations.view_company` | HR visibility of submitted scorecards |
| `notes.manage_applicant` | Recruiter applicant notes |

### 7.5 Security controls (Proposed)

| Control | Requirement |
|---|---|
| Password hashing | Laravel `hashed` cast (already on User model) |
| Email verification | Required before apply/publish as product decides |
| MFA | **Recommended** for company_admin + super_admin; product decision |
| Sessions | Encrypt cookies; regenerate on login; idle timeout |
| CSRF | All web POSTs |
| Rate limiting | login, register, password reset, contact, message send |
| Reset/invite tokens | Expiry + single use |
| Suspension | `users.account_status`, `companies.status=suspended` hard-deny |
| Policies | Laravel Policy per Application, Job, Interview, Offer, Company |
| Tenant isolation | Every company query constrained by membership |
| IDOR prevention | Authorize by id + membership; do not trust path alone |
| Audit | Append-only privileged actions |

**Frontend-only role gates are insufficient.**

---

## 8. Organization Tenancy & Data Ownership

### 8.1 Current schema tenancy (Verified)

- Tenant-like entity: `companies` with `owner_user_id` and `status`.  
- Membership: `company_employees` (unique `user_id` — **implies one company membership per user**).  
- Role assignment: `user_roles` with nullable `company_id`.  

### 8.2 Frontend Admin “Organizations” (Verified)

Admin UI presents companies **and agencies** as organizations (`MockAdminData`). Schema has **no agency type** on `companies` and no engagements table.

**Unresolved:** Agency as `companies.type=agency` vs separate `agencies` + `agency_engagements`.

### 8.3 Isolation requirements (Inferred / Proposed)

| Threat | Prevention |
|---|---|
| Company A reads Company B applicants | Policy + `jobs.company_id` join constraints |
| Agency sees other agency engagements | Engagement membership checks |
| Unauthorized interview notes | Only assigned interviewer + permitted HR after rules |
| Candidate sees others’ applications | `candidate_id` ownership |
| Non-admin platform actions | `super_admin` permission + middleware |

### 8.4 Resource scoping rule (Proposed)

Every sensitive row must resolve to either:

- `user_id` (candidate-owned), or  
- `company_id` (employer-owned), or  
- platform scope (admin), or  
- explicit share (interview assignment, message participant).

### 8.5 Decision needed

Whether a user may belong to **multiple companies** (schema currently `company_employees.user_id` unique — conflicts with multi-tenant recruiters). **Blocking before B1.**

---

## 9. Database & ERD Gap Analysis

> Standalone ERD document: **unavailable**. Analysis based on migrations/models vs frontend needs.

### 9.1 Cross-cutting schema quality issues (Verified)

1. Many migrations comment `// created_at, updated_at, uid, luid` without creating columns — but `HasAudit` expects `uid`/`luid`.  
2. `AuditLog` model expects table `audit_logs` — **migration not found**.  
3. `User` uses `Laravel\Sanctum\HasApiTokens` without Sanctum dependency.  
4. Defaults: `users.locale` default `ar`; jobs `currency` default `EGP` — may conflict with EN/USD presentation mocks.  
5. Frontend string IDs (`ns-204`, `org-ns-001`) ≠ bigint PKs.

### 9.2 Entity gap table (selected)

| Entity | Existing in DB / models? | Purpose | Key Fields (existing or proposed) | Relationships | Constraints | Ownership / Tenant | Indexes | Status |
|---|---|---|---|---|---|---|---|---|
| users | Yes | Identity | name, email, password, account_type, account_status, locale, theme | roles, profile, membership | email unique | Self | email, status | **Exists; wire auth** |
| roles / permissions | Yes | RBAC | key, scope | M2M | unique keys | Platform | | **Exists; extend** |
| user_roles | Yes | Role assignment | user_id, role_id, company_id | | unique triad | Company/platform | | **Exists** |
| companies | Yes | Employer org | name, status, owner_user_id, doc_path | jobs, employees | | Company | status | **Exists; add type?** |
| company_employees | Yes | Membership | status, role_id | company, user | user_id unique | Company | status | **Exists; revisit unique** |
| invitations | **No** | Invite team | token, email, role, expires | company | token unique | Company | | **Missing** |
| candidate_profiles | Yes | Candidate | user_id, headline… | apps, cvs | | User | | **Exists** |
| experience/education/skills/cvs | Yes | Profile graph | | | | User | | **Exists** |
| jobs | Yes | Requisitions | status, visibility, work_mode… | company, apps | FT title/desc | Company | status, visibility | **Exists** |
| job public code/slug | **No** | Stable public id | code/slug | job | unique | Company | unique | **Missing** (UI uses NS-204) |
| applications | Yes | Applications | status, cv_id, consent_at | job, candidate | unique job+candidate+attempt | Candidate+Company | status | **Exists** |
| application_status_history | Yes | Pipeline history | | application | | Company | | **Exists** |
| saved_jobs | **No** | Favorites | user_id, job_id | | unique pair | User | | **Missing** |
| candidate_preferences | **No** | Alerts/prefs | JSON or columns | user | | User | | **Missing** |
| interviews | Yes | Interview shell | type, status, meeting_link | application | | Company | status | **Exists** |
| interview_bookings | Yes | Time booking | | interview, interviewer | conflict rules TBD | Company | | **Exists** |
| interviewer_availability | Yes | Slots | | interviewer | | User/Company | | **Exists** |
| interview_evaluations | Yes | Scorecard | numeric_score, internal_notes | interview, evaluator | | Interviewer | | **Exists; may need competency rows** |
| scorecard_competencies | **No** | Multi-rating UI | rating, evidence per competency | evaluation | | Interviewer | | **Proposed** |
| offers | Yes | Offers | salary, status sent/accepted… | application | | Company | status | **Exists; approval draft state missing** |
| offer_approvals | **No** | Internal approval | steps, decisions | offer | | Company | | **Proposed** |
| exams/questions/attempts… | Yes | Assessments | rich exam schema | jobs via job_exams | | Company | | **Exists; wire** |
| notifications | Yes (table) | Notices | (inspect columns in migration) | users | | Recipient | | **Exists; confirm shape vs UI** |
| conversations/messages | **No** | Messaging | | participants | | Participants | | **Missing** |
| applicant_notes | **No** | Recruiter notes | | application, author | | Company | | **Missing** (localStorage today) |
| talent_shortlist | **No** | Talent pool | | company, candidate | | Company | | **Missing** |
| job_moderation_cases | **No** | Admin moderation | | job, reviewer | | Platform | | **Missing** |
| agency_engagements | **No** | Agency ops | | agency, company, job | | Agency | | **Missing** |
| audit_logs | Model yes / migration **no** | Audit | actor, action, entity | | append-only | Platform/Company | | **Broken gap** |
| calendar_integrations | Yes | OAuth calendar | | user | secrets encrypted | User | | **Exists; unused** |
| contact_messages | **No** | Public contact | | | | Platform | | **Proposed** |
| platform_settings | **No** | Admin settings | key/value | | | Platform | | **Proposed** |

### 9.3 Sensitive data classification (Proposed)

| Class | Examples | Controls |
|---|---|---|
| Highly sensitive | passwords, tokens, OAuth secrets | hash/encrypt; never log |
| PII | name, email, phone, address | minimize; access control |
| Recruitment sensitive | resumes, scorecards, interviewer notes, messages, salary | private storage; audit access |
| Internal | pipeline notes, moderation reasons | company/admin only |
| Public | published job descriptions | public read |

---

## 10. Backend API / Server Contracts

> All endpoints below are **Proposed, not existing**. Prefer Blade form posts unless JS already needs async.

### 10.1 Error format (Proposed)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."]
  },
  "error_code": "validation_failed",
  "request_id": "uuid"
}
```

Web form posts may redirect back with `$errors` bag (Laravel default) — preferred for auth/onboarding/apply.

### 10.2 Contract table (representative)

| ID | Method | Proposed Endpoint | Consumer Screen | Permission | Request | Response | Errors | Priority |
|---|---|---|---|---|---|---|---|---|
| AUTH-01 | POST | `/login` | `/login` | Guest | email, password, remember | Redirect session | 422/429/403 | P0 |
| AUTH-02 | POST | `/logout` | Topbar | Auth | — | Redirect | 401 | P0 |
| AUTH-03 | POST | `/register` | `/register` | Guest | account_type, profile fields | Redirect verify | 422 | P0 |
| AUTH-04 | POST | `/forgot-password` | forgot | Guest | email | 200 generic | 429 | P0 |
| AUTH-05 | POST | `/reset-password` | reset | Guest | token, email, password | Redirect login | 422 | P0 |
| PROF-01 | PUT | `/candidate/profile` | profile | Candidate | profile fields | 200/redirect | 422/403 | P1 |
| JOBS-01 | GET | `/jobs` (enhance) | public jobs | Public | filters, page | HTML or JSON | 422 | P1 |
| JOBS-02 | POST | `/candidate/jobs/{job}/save` | jobs/detail | Candidate | — | `{saved:true}` | 403/404 | P1 |
| JOBS-03 | DELETE | `/candidate/jobs/{job}/save` | saved | Candidate | — | `{saved:false}` | 403 | P1 |
| APP-01 | POST | `/candidate/applications` | apply wizard | jobs.apply | job_id, cv_id, answers, consent | redirect submitted | 422/409 | P0 |
| APP-02 | GET | `/candidate/applications/{id}` | application show | view_own | — | HTML | 403/404 | P1 |
| APP-03 | PATCH | `/company/applications/{id}/stage` | pipeline | applications.review | stage, reason | 200 | 422/403 | P0 |
| IV-01 | POST | `/company/interviews` | schedule | interviews.assign | application_id, interviewer_id, slot | 201 | 409 conflict | P0 |
| IV-02 | POST | `/company/interviews/{id}/reschedule` | schedule | interviews.assign | new slot | 200 | 409 | P1 |
| IV-03 | PUT | `/interviewer/evaluations/{interview}` | scorecard | interviews.evaluate | draft payload | 200 | 422 | P0 |
| IV-04 | POST | `/interviewer/evaluations/{interview}/submit` | scorecard | interviews.evaluate | — | 200 locked | 409 | P0 |
| OFF-01 | POST | `/company/offers` | offer create | offers.manage | application_id, comp | 201 draft | 422 | P1 |
| OFF-02 | POST | `/company/offers/{id}/approve` | offer | offers.manage (+ approver rule) | — | 200 | 403 | P1 |
| OFF-03 | POST | `/company/offers/{id}/send` | offer | offers.manage | — | 200 sent | 409 | P1 |
| OFF-04 | POST | `/candidate/offers/{id}/respond` | offer show | offers.respond | decision | 200 | 409 expired | P1 |
| NOT-01 | POST | `/notifications/{id}/read` | notifications | recipient | — | 200 | 403 | P2 |
| ADM-01 | POST | `/admin/organizations/{id}/verify` | org detail | companies.review | decision, reason | 200 | 422 | P1 |
| ADM-02 | POST | `/admin/jobs/{id}/moderate` | job moderation | jobs.moderate | decision, reason | 200 | 422 | P1 |
| AUD-01 | GET | `/admin/settings/audit` | settings | audit.view | filters, page | HTML/JSON | 403 | P1 |

### 10.3 Illustrative JSON examples (Proposed)

**Login session (form preferred; JSON alternative):**

```http
POST /api/auth/login
Content-Type: application/json

{"email":"maya@example.test","password":"********","remember":true}
```

```json
{"data":{"user":{"id":12,"name":"Maya Chen","roles":["candidate"]},"locale":"en"}}
```

**Save job:**

```http
POST /candidate/jobs/104/save
```

```json
{"data":{"job_id":104,"saved":true}}
```

**Application submit:**

```json
{
  "job_id": 104,
  "cv_id": 55,
  "portfolio_url": "https://…",
  "answers": {"hybrid_austin":"yes","motivation":"…","salary":150000},
  "consent": true,
  "talent_pool_opt_in": false
}
```

**Schedule interview:**

```json
{
  "application_id": 9001,
  "interviewer_id": 44,
  "starts_at": "2026-10-22T15:00:00Z",
  "ends_at": "2026-10-22T15:45:00Z",
  "timezone": "America/Chicago",
  "interview_type": "online"
}
```

**Scorecard draft:**

```json
{
  "ratings": {"problem_framing":4,"collaboration":3},
  "evidence": {"problem_framing":"…"},
  "recommendation": "advance",
  "internal_notes": "…"
}
```

**Moderation decision:**

```json
{"decision":"request_changes","reason":"Salary range missing","policy_key":"salary_transparency"}
```

---

## 11. Domain Workflows & State Machines

| Workflow | Initial State | Allowed Transitions | Triggering Role | Validation / Invariant | Side Effects | Audit |
|---|---|---|---|---|---|---|
| Candidate onboarding | not_started | in_progress → completed | Candidate | Required profile fields | Unlock apply | Optional |
| Company onboarding | draft | submitted → pending → approved/rejected | Owner / Super Admin | Docs uploaded | Notify owner | Yes |
| Job lifecycle | draft | review? → published → closed → archived | jobs.* | Pay range rules TBD | Index public catalog | Yes on publish/close |
| Application | submitted | screening → interview → offer → hired/rejected/withdrawn | HR + Candidate withdraw | Valid stage graph | Notify; history row | Yes |
| Interview | pending | scheduled → completed/canceled/no_show | HR/Interviewer | Slot free; participants | Calendar invite | Yes |
| Scorecard | draft | submitted (immutable) | Interviewer | All required ratings | Notify HR | Yes |
| Assessment | assigned | started → submitted → graded | Candidate/HR | Server timer | Proctor events | Yes |
| Offer | draft | pending_approval → approved → sent → accepted/declined/expired | HR/Approver/Candidate | Approval rules | Notify | Yes |
| Employee join | pending | approved/rejected/removed | employees.approve | Role assignment | Access grant | Yes |
| Job moderation | flagged/pending | approved/rejected/changes_requested | Admin | Reason required | Unpublish/notify | Yes |
| Agency engagement | Proposed TBD | TBD | Admin/Agency | Product | TBD | Yes |

**Concurrency:** optimistic locking or DB constraints on bookings (unique interviewer timespan) and application stage updates.  
**Duplicate apply:** enforce `unique(job_id, candidate_id, attempt_no)` already in migration.

---

## 12. Frontend-to-Backend Integration Map

| View | Current Data Source | Current Local Action | Replacement Backend Contract | State Migration | Required Frontend Adaptation | Risk |
|---|---|---|---|---|---|---|
| `public/jobs/index` | MockPublicData | Client filter | DB-backed index + query params | None | Keep Blade; optional JS filter→query | Med |
| `public/jobs/show` | MockPublicData | Save/Apply links | Real job + auth save | Map code→id | Replace mock ids | Med |
| `auth/login` | none | Client validation only | POST login | — | Enable form action | Low |
| `onboarding/candidate-profile` | mock + localStorage | Step local save | PATCH steps | Import draft once | Remove fake complete | Med |
| `candidate/jobs/saved` | localStorage ids + mock jobs | Toggle save | saved_jobs API | Import v2 ids | Sync UI | Med |
| `candidate/applications/_apply-form` | mock job + local draft | Preview submit | POST applications | Import draft | Real success page | High |
| `candidate/messages/index` | MockCandidateData | Draft localStorage | Messaging API | Draft import | Send real | High + product |
| `candidate/preferences` | Mock + localStorage | Save local | Preferences API | Import | Toast on server OK | Low |
| `company/jobs/wizard/create` | Mock + localStorage | Draft local | Job draft/publish API | Import drafts | Wizard posts | High |
| `company/applicants/show` | Mock + note localStorage | Notes local | Notes API + stages | Import notes | Remove local success | High |
| `company/interviews/schedule` | Mock | Preview confirm | POST interviews | — | Conflict errors | High |
| `interviewer/evaluations/create` | Mock + scorecard localStorage | Draft/submit local | PUT/POST evaluations | Import draft | Lock after submit | High |
| `admin/settings/index` | MockAdminData audit | Review modal | Audit query + settings | — | Real filters | Med |
| `admin/organizations/*` | MockAdminData | Preview actions | Admin org APIs | — | Enable writes carefully | High |

**Controllers to adapt (non-exhaustive):** all under `app/Http/Controllers/{Public,Auth,Onboarding,Candidate,Company,Interviewer,Admin}` currently mock-driven.

---

## 13. Browser Storage & Draft Migration

### 13.1 Inventory (Verified in source)

| Storage Key | Current Purpose | Data Shape (approx) | Sensitive? | Target Server Resource | Migration Strategy | Conflict Policy | Deletion / Retention |
|---|---|---|---|---|---|---|---|
| `hb_theme` | Theme | `light\|dark\|system` | No | `users.theme` optional | Keep browser; sync if logged in | Last-write user pref | Keep |
| `hb_locale` | Locale mirror | `en\|ar` | No | cookie `hb_locale` + `users.locale` | Cookie remains source for guests | User profile wins when authed | Keep |
| `hb_candidate_saved_jobs_v2` | Saved job ids | string[] | Low | `saved_jobs` | One-time import on login | Server authoritative | Clear local after import |
| `hb_candidate_message_draft_{threadId}` | Message draft | string | **Yes** | message drafts table or discard | Prompt import/discard | Server draft > local | Delete on send/logout |
| `hb_candidate_preferences_v1` | Prefs | object | Low-Med | preferences | Import | Server wins | Clear after sync |
| `hb_candidate_notifications_v1` | Read map | object | Low | notifications.read_at | Prefer server read state | Server wins | Clear |
| `hb_candidate_apply_draft_{jobId}` | Apply draft | object | **Yes** | application_drafts | Import if no app yet | Newer updated_at | TTL 30d **confirm** |
| `hb_candidate_profile_draft_v1` | Profile draft | object | **Yes** | profile | Import | Server wins | Clear |
| `hb_onboarding_candidate_v1` | Onboarding | object | Med | onboarding state | Import once | Server wins | Clear on complete |
| `hb_onboarding_company_v1` | Company onboarding | object | Med | company draft | Import once | Server wins | Clear |
| `hb_company_job_draft_*` | Job wizard | object | Med | jobs draft | Import | Server draft id | TTL |
| `hb_company_profile_draft_v1` | Company profile | object | Med | companies | Import | Server wins | Clear |
| `hb_offer_draft_{id}` | Offer draft | object | **Yes** | offers draft | Import | Server wins | Clear |
| `hb_assessment_draft` | Assessment builder | object | Med | exams draft | Import | Server wins | Clear |
| `hb_assessment_result_{id}` | Local exam result | object | Med | exam_attempts | **Do not trust**; discard | Server only | Delete |
| `hb_talent_shortlist` | Shortlist ids | array | Med | talent_shortlist | Import | Server wins | Clear |
| `hb_applicant_note_{applicantId}` | Recruiter notes | string | **Yes** | applicant_notes | Import w/ authz | Server wins | Clear; retain server per policy |
| `hb_interview_prep_{id}` | Prep checklist | object | Low | interview_prep_state | Optional | Server wins | Clear |
| `hb_interviewer_availability_prefs_v1` | Availability UI | object | Low | interviewer_availability | Import | Server wins | Clear |
| `hb_iv_{interviewId}_scorecard_v1` | Scorecard draft | object | **Yes** | evaluation draft | Import if not submitted | Server wins; never overwrite submitted | Clear after submit |
| `hb_iv_*_session_v1` / checklist keys | Live session checklist | object | Med | session state optional | Optional | Server wins | Clear |
| Live notes keys (JS) | Interviewer notes | string | **Yes** | private notes | Import | Server wins | Clear on logout |

**Remain browser-only (Proposed):** `hb_theme` for guests; ephemeral UI flags.  
**Must leave browser for production (Proposed):** scorecards, applicant notes, message drafts, apply drafts, resumes metadata.

**Account switching:** clear all `hb_*` sensitive keys on logout (**Proposed** mandatory).

---

## 14. Document/File Storage

| Use case | UI today | Backend need |
|---|---|---|
| Candidate CV/resume | Upload UI stubs | Private object storage; `candidate_cvs.path` |
| Company verification docs | Onboarding | Private; `companies.doc_path` |
| Offer letters | Offer UI | Private; new attachment table recommended |
| Assessment attachments | Possible | Private |
| Interview guides | Mock attachments in messages | Private + ACL |

**Proposed baseline (values require Security/Product confirmation):**

- Types: PDF, DOCX, PNG, JPG (deny exe/scripts)  
- Size: start **10 MB**/file pending approval  
- Storage: S3-compatible private bucket  
- Access: authorized download via **temporary signed URLs**  
- Scan: async malware scan job before download where feasible  
- Retention: align to application retention policy (decision)  
- Audit: log download of sensitive files  

---

## 15. Messaging, Notifications & Queues

### 15.1 Product split

| Channel | Status |
|---|---|
| Candidate Messages UI | Frontend mock exists |
| Company Messages module | **Not approved** / sidebar Soon — **product decision** |
| Notifications bell + page | Mock + local read state |
| Mail (verify, reset, invites, interview) | Not implemented |
| Chatbot tables | Schema exists; not in frontend acceptance scope as product channel |

### 15.2 Proposed event types

Application submitted/stage changed; interview scheduled/rescheduled/canceled; scorecard submitted; offer sent/responded; assessment assigned; org verified/rejected; moderation decision; team invite.

### 15.3 Delivery (Proposed)

- Channels: in-app + email (mandatory transactional); marketing/job digests opt-in only  
- Queue workers with retry/backoff; failed_jobs table already migrated  
- Idempotent notification keys  
- Respect `candidate.preferences` notification toggles  

---

## 16. Interviews, Scheduling & Time Zones

**Verified schema:** `interviews`, `interview_bookings`, `interviewer_availability`, `calendar_integrations`.  
**Verified UI:** company schedule preview; interviewer availability localStorage; Central Time copy in mocks.

**Proposed rules:**

- Store instants in **UTC**; persist IANA TZ (`America/Chicago`, etc.) for display  
- Conflict detection on interviewer bookings  
- Reschedule permissions: `interviews.assign` (+ candidate request flow TBD)  
- Meeting URL: provider integration optional after core booking  
- Reminders via queued mail  
- DST-safe using timezone libraries  

---

## 17. Assessments, Evaluations & Offers

| Area | Schema | UI | Gap |
|---|---|---|---|
| Exams | Rich tables | Candidate take + company create drafts | Wire attempts with **server time**; discard local results authority |
| Evaluations | Single score + notes | Multi-competency scorecard | Extend schema or serialize JSON ratings with validation |
| HR visibility | — | Company evaluation pages | Only **submitted** scorecards visible; hide `internal_notes` per policy |
| Offers | status sent/accepted/rejected/expired | Draft + approval UI | Add draft/pending_approval; approval steps |

---

## 18. Admin, Moderation & Audit

| Capability | Today | Required |
|---|---|---|
| Org verify/suspend | Mock | Real status transitions + reasons |
| Users & access | Mock | users.manage + role assignment |
| Job moderation | Mock | Moderation entity + job visibility effects |
| Agency ops | Mock | Productized agency model |
| Settings | Mock | platform_settings |
| Audit | Mock events; AuditLog model orphaned | Create `audit_logs` migration; append-only; redact secrets |

**Dual-control:** recommend for suspend org / permission changes (**Product decision**).

---

## 19. Security & Privacy

Checklist (Proposed):

- [ ] Data minimization on APIs  
- [ ] PII access logging for resumes/scorecards  
- [ ] Tenant scoping tests  
- [ ] IDOR/BOLA test suite  
- [ ] Mass assignment guarded (`$fillable` review)  
- [ ] CSRF + XSS escaping (Blade) + CSP later  
- [ ] SQL injection via Eloquent/query bindings  
- [ ] Session fixation prevention  
- [ ] Secrets in env/vault; never in repo  
- [ ] Signed URLs for files  
- [ ] Rate limits / lockouts  
- [ ] Generic password-reset responses (anti-enumeration)  
- [ ] Structured logs without message bodies/PII  
- [ ] Encrypted DB backups; TLS in transit  
- [ ] Retention + deletion request workflow (**legal decision**)  

**Do not claim GDPR/CCPA applicability without jurisdiction review.**

---

## 20. Error Handling & Localization

| Error Scenario | HTTP | Backend Response | Frontend Behavior | Retry? | Logging / Audit |
|---|---|---|---|---|---|
| Validation | 422 | Field errors | Inline `hb-field-error`; focus first invalid | Yes | Debug |
| Unauthenticated | 401/302 login | — | Redirect login | — | |
| Forbidden | 403 | Safe message | Error page / toast | No | Audit attempt |
| Not found | 404 | — | Not found page | No | |
| Duplicate apply | 409 | error_code | Explain existing application | No | |
| Schedule conflict | 409 | conflict details | Show conflicting slot | Yes different slot | |
| Expired offer/invite | 410/422 | — | Expired state UI | No | |
| Upload failure | 422/413 | — | Keep form data | Yes | |
| Rate limited | 429 | Retry-After | Wait message | Later | Yes |
| Integration outage | 503 | — | Degrade gracefully | Yes | Yes |
| Server error | 500 | Generic | Friendly page | Maybe | Yes |

All user-facing strings via `lang/en` + `lang/ar`. Success only after **confirmed** server response.

---

## 21. Backend Testing Strategy

**Pyramid (Proposed):**

1. Unit: state machines, conflict detection, permission helpers  
2. Feature: HTTP auth, policies, CRUD, 403 cross-tenant  
3. Integration: mail/queue fakes, storage fakes  
4. Contract: JSON shapes for async endpoints  
5. E2E (Dusk/Playwright) after auth exists — optional package approval  
6. Security: IDOR suite, rate limit, file ACL  

Representative cases: cannot read other company applicants; cannot submit others’ scorecards; duplicate booking rejected; offer respond after expiry fails; moderation unpublishes job; audit row written on suspend.

---

## 22. Infrastructure & DevOps

| Need | Proposed | Required vs optional |
|---|---|---|
| Runtime | PHP 8.3 + Laravel 13 | Required |
| Web server | Laravel Cloud / Forge / containers TBD | Decision |
| DB | PostgreSQL recommended for prod; sqlite ok local | Decision (default sqlite now) |
| Queue | Redis + workers | Required for mail/notify |
| Cache/session | Redis | Recommended |
| Object storage | S3-compatible | Required for files |
| Email | SES/Postmark/etc. | Required |
| Realtime | Optional Echo/Pusher | Optional |
| Scheduler | `interview:reminders`, digests | Required later |
| Secrets | env / vault | Required |
| CI | run pint + phpunit | Required |
| Observability | logs + error tracker (Sentry etc.) | Required prod |
| Backups | automated DB + bucket | Required prod |

**Proposed env inventory (names only):** `APP_KEY`, `APP_URL`, `DB_*`, `QUEUE_CONNECTION`, `REDIS_*`, `MAIL_*`, `FILESYSTEM_DISK`, `AWS_*`, `SESSION_DRIVER`, `FRONTEND_DATA_SOURCE`, `SCOUT_*` (if search), OAuth client IDs for calendar/video.

---

## 23. Phased Backend Roadmap

### Phase B0 — Architecture & Contract Approval
Goal: freeze tenancy, RBAC extensions, ID strategy, error format, env.  
Deliverables: approved ADR + this doc sign-off.  
Gate: Product + Tech Lead + Security.

### Phase B1 — Identity, Organizations & Permissions
Scope: register/login/logout/verify/reset; membership; policies middleware; fix Sanctum/audit schema gaps.  
Frontend: enable auth forms; hide Soon sign-out.  
Tests: auth + 403.  
Gate: security review.

### Phase B2 — Profiles & Jobs
Scope: candidate/company profiles; job CRUD/publish; public catalog from DB; public codes/slugs.  
Frontend: replace MockPublic/Company job reads.  
Gate: public jobs parity.

### Phase B3 — Applications & Pipeline
Scope: apply, history, recruiter stage moves, saved jobs, applicant notes.  
Frontend: apply wizard real submit; pipeline.  
Gate: end-to-end apply→pipeline.

### Phase B4 — Interviews & Scheduling
Scope: availability, booking, conflicts, prepare/live notes server-side.  
Frontend: schedule confirm real; interviewer schedule.  
Gate: conflict tests green.

### Phase B5 — Assessments, Evaluations & Offers
Scope: attempts, scorecard submit, HR visibility, offer approval/send/respond.  
Gate: scorecard immutability + offer lifecycle.

### Phase B6 — Messaging, Alerts & Notifications
Scope: only after Company Messages decision; candidate messaging; email + in-app; prefs/digests.  
Gate: product sign-off on scope.

### Phase B7 — Agency & Admin
Scope: org verify/suspend, moderation, agency model, audit search, settings.  
Gate: admin security review.

### Phase B8 — Hardening & Production Readiness
Scope: performance, monitoring, backups, accessibility recheck, penetration basics, load.  
Gate: go-live checklist.

---

## 24. Team Ownership & RACI

| Workstream | Backend Lead | Backend Eng | Frontend Eng | QA | DevOps | Product | Security |
|---|---|---|---|---|---|---|---|
| API/form contracts | A | R | C | C | I | C | C |
| Schema/migrations | A | R | I | I | C | C | C |
| Policies/tenancy | A | R | I | C | I | C | A/R |
| Blade wiring | C | C | R | C | I | I | I |
| localStorage migration UX | C | C | R | C | I | C | C |
| Queues/mail/infra | C | C | I | I | R | I | C |
| Workflow rules | C | R | I | C | I | A | C |
| Acceptance tests | C | C | C | R | I | C | C |
| Go-live | A | C | C | A | R | A | A |

R=Responsible A=Accountable C=Consulted I=Informed

---

## 25. Detailed Implementation Backlog

| Task ID | Epic | Deliverable | Dependencies | Priority | Complexity | Owner Role | Acceptance Criteria |
|---|---|---|---|---|---|---|---|
| BE-ARCH-001 | B0 | ADR: hybrid Blade architecture | — | P0 | M | Backend Lead | Signed ADR |
| BE-ARCH-002 | B0 | Public ID strategy (slug/code vs ULID) | — | P0 | M | Backend Lead | Mapping doc approved |
| BE-ARCH-003 | B0 | Fix schema conventions (timestamps/uid/luid) | — | P0 | L | DB/Backend | Migrations consistent; HasAudit works |
| BE-ARCH-004 | B0 | Resolve Sanctum dependency or remove trait | — | P0 | S | Backend | App boots cleanly without missing package |
| BE-ARCH-005 | B0 | Create audit_logs migration matching AuditLog | — | P0 | M | Backend | Model saves |
| BE-AUTH-001 | B1 | POST login/logout/register | BE-ARCH-001 | P0 | L | Backend | Session auth works |
| BE-AUTH-002 | B1 | Email verification + password reset mail | BE-AUTH-001, mail | P0 | M | Backend | Tokens expire; generic responses |
| BE-AUTH-003 | B1 | auth middleware on workspace routes | BE-AUTH-001 | P0 | M | Backend | Unauth redirected |
| BE-ORG-001 | B1 | Membership + multi-company decision implementation | Product decision | P0 | L | Backend | Isolation tests |
| BE-ORG-002 | B1 | Policies for Company/Job/Application | BE-ORG-001 | P0 | L | Backend | Cross-tenant 403 |
| BE-PERM-001 | B1 | Align PermissionSeeder with UI permissions | Product | P1 | M | Backend | Matrix published |
| BE-JOBS-001 | B2 | Job CRUD + publish/close | BE-ORG-002 | P0 | L | Backend | Public only published |
| BE-JOBS-002 | B2 | Public job code/slug | BE-JOBS-001 | P1 | M | Backend | `/jobs/ns-204` style works or redirect map |
| BE-PROF-001 | B2 | Candidate profile + CV upload | Files | P1 | L | Backend | Private CV download ACL |
| BE-APP-001 | B3 | Application submit + history | BE-JOBS-001, BE-PROF-001 | P0 | L | Backend | Duplicate blocked |
| BE-APP-002 | B3 | Pipeline stage transitions | BE-APP-001 | P0 | M | Backend | Invalid transition 422 |
| BE-APP-003 | B3 | Saved jobs API + import | BE-AUTH-001 | P1 | M | Backend | localStorage import once |
| BE-APP-004 | B3 | Applicant notes API | BE-APP-001 | P1 | M | Backend | Notes not in localStorage prod |
| BE-IV-001 | B4 | Availability + booking + conflicts | BE-APP-001 | P0 | L | Backend | Overlap rejected |
| BE-IV-002 | B4 | Reschedule/cancel + notifications | BE-IV-001 | P1 | M | Backend | Audit + email |
| BE-IV-003 | B5 | Scorecard draft/submit | BE-IV-001 | P0 | L | Backend | Immutable submit |
| BE-ASSESS-001 | B5 | Exam attempt server timer | Exams schema | P1 | L | Backend | Client clock irrelevant |
| BE-OFF-001 | B5 | Offer draft/approve/send/respond | BE-APP-001 | P1 | L | Backend | Expiry enforced |
| BE-MSG-001 | B6 | Messaging schema + send/list | Product decision | P2 | L | Backend | Participant ACL |
| BE-NOT-001 | B6 | Notification writer + mark read | Events | P2 | M | Backend | Prefs respected |
| BE-ADMIN-001 | B7 | Org verify/suspend | BE-ORG-001 | P1 | M | Backend | Audit row |
| BE-ADMIN-002 | B7 | Job moderation | BE-JOBS-001 | P1 | M | Backend | Visibility enforced |
| BE-ADMIN-003 | B7 | Agency model (if approved) | Product | P2 | L | Backend | Engagement ACL |
| BE-ADMIN-004 | B7 | Audit search/export | audit_logs | P1 | M | Backend | Redaction rules |
| BE-SEC-001 | B8 | Security test pack + rate limits | B1+ | P0 | M | Security/Backend | Report attached |
| BE-OPS-001 | B8 | Staging + backups + monitoring | Infra | P0 | M | DevOps | Runbook |

Complexity: S/M/L = relative only (no dates).

---

## 26. Open Product/Architecture Decisions

| ID | Question | Class | Owner | Notes |
|---|---|---|---|---|
| D-01 | Session auth only vs Sanctum/API tokens? | Blocking B1 | Tech Lead | User model references Sanctum without package |
| D-02 | Multi-company membership allowed? | Blocking B1 | Product/Tech | Contrasts `company_employees.user_id` unique |
| D-03 | Agency data model? | Blocking B7 | Product | Admin UI exists |
| D-04 | Is Company Messages in scope? | Blocking B6 | Product | Sidebar Soon |
| D-05 | Job publish requires platform moderation? | Blocking B2/B7 | Product | |
| D-06 | Who moves application stages? | Blocking B3 | Product | |
| D-07 | When can HR see scorecards / private notes? | Blocking B5 | Product | |
| D-08 | Offer approval chain? | Blocking B5 | Product | UI implies approval |
| D-09 | Scorecard competency schema vs single score | Blocking B5 | Tech/Product | |
| D-10 | Calendar/video providers? | Blocking integrations | Product | |
| D-11 | Assessment question types required for v1? | Module | Product | Schema supports many |
| D-12 | Jurisdictions, retention, deletion SLA? | Blocking prod | Legal/Product | |
| D-13 | MFA required for admins? | Before prod | Security | |
| D-14 | Default locale/currency (ar/EGP vs en/USD)? | Before B2 seed | Product | Migration defaults vs UI |
| D-15 | Public job identifier format | Blocking B2 | Tech | NS-204 mock codes |
| D-16 | AI CV match / chatbot / salary benchmark in v1? | Deferrable | Product | Tables exist |

---

## 27. Backend Team Handoff Checklist

### Product Decisions
- [ ] Roles/permissions final matrix approved  
- [ ] Tenancy + agency model approved  
- [ ] Workflow transitions approved  
- [ ] Company Messages go/no-go  
- [ ] Privacy/retention baseline approved  

### Architecture
- [ ] Hybrid Blade approach approved  
- [ ] ID/slug strategy approved  
- [ ] Error format approved  
- [ ] Schema repair plan for timestamps/uid/audit_logs approved  

### Security
- [ ] AuthN/AuthZ approach approved  
- [ ] Session/MFA policy approved  
- [ ] Audit strategy approved  
- [ ] File access model approved  

### Infrastructure
- [ ] DB engine chosen  
- [ ] Queue/cache/mail/storage provisioned in staging  
- [ ] Secrets management defined  
- [ ] CI pipeline includes phpunit  

### Implementation
- [ ] Models policies services endpoints jobs validation per phase  
- [ ] Feature flag for mock vs DB  

### Frontend Integration
- [ ] Mock replacement plan per screen  
- [ ] localStorage migration UX  
- [ ] Remove fake success toasts  

### Testing
- [ ] Tenant isolation suite  
- [ ] Workflow + security tests  
- [ ] Accessibility regression after wiring  

### Release
- [ ] Staging sign-off  
- [ ] Migration/rollback plan  
- [ ] Monitoring/alerts  
- [ ] Production go-live approval  

---

## 28. Integration Definition of Done

A feature is done only when:

1. Contract approved  
2. Schema reviewed  
3. Server authorization enforced  
4. Validation complete  
5. Tenant scoping enforced  
6. Real persistence implemented  
7. Correct errors returned  
8. Audit logged when required  
9. Notifications/events hooked if required  
10. Frontend shows **server** success only  
11. Frontend displays genuine server state  
12. Loading/error/empty verified  
13. Automated tests pass  
14. Cross-role consistency verified  
15. Accessibility smoke checked  
16. Security review done  
17. QA sign-off obtained  
18. Docs updated  

---

## 29. Risks, Assumptions & Dependencies

### Risks
- Schema drift / incomplete migrations cause rewrite  
- Premature messaging build before product approval  
- localStorage PII incident  
- Permission seeder insufficient for UI  
- DemoSeeder data mistaken for production seed  

### Assumptions
- Blade UI remains primary client for v1  
- Existing GET route names should remain stable  
- English/Arabic must both work for server messages  

### Dependencies
- Product decisions in §26  
- Email/SMS/calendar vendor accounts  
- Human keyboard a11y sign-off (frontend gate; parallel track)  
- Legal retention guidance  

---

## 30. Final Recommendations

1. Treat current system as **UI + partial domain schema**, not a live ATS.  
2. Adopt **hybrid Blade-preserving** backend integration.  
3. Start with **B0 schema repair + B1 auth/tenancy** before any hiring writes.  
4. Introduce **public business identifiers** alongside numeric PKs.  
5. Prioritize removing sensitive **localStorage** authorities (scorecards, notes, messages, apply drafts).  
6. Extend RBAC for messaging/moderation/agency only after product confirmation.  
7. Keep Mock*Data for tests/demo behind a flag during migration.  
8. Do not implement Company Messages until explicitly approved.  
9. Create `audit_logs` properly; stop relying on mock audit arrays.  
10. Require security + tenant isolation tests as merge gates from B1 onward.

**STOP:** This document is the handoff. No backend implementation should start without explicit approval of B0 decisions.

---

## 31. Appendix: Verified Route Inventory

All **71** application routes (`php artisan route:list --except-vendor`), name / URI / action:

| `home` | `/` | `Public\HomeController` |
| `frontend.components` | `_frontend/components` | `Frontend\ComponentShowcaseController` |
| `frontend.layouts.app` | `_frontend/layouts/app` | `Frontend\LayoutPreviewController@app` |
| `frontend.layouts.auth` | `_frontend/layouts/auth` | `Frontend\LayoutPreviewController@auth` |
| `about` | `about` | `Public\AboutController` |
| `admin.agencies.index` | `admin/agencies` | `Admin\AgencyController@index` |
| `admin.dashboard` | `admin/dashboard` | `Admin\DashboardController` |
| `admin.jobs.index` | `admin/jobs` | `Admin\JobModerationController@index` |
| `admin.organizations.index` | `admin/organizations` | `Admin\OrganizationController@index` |
| `admin.organizations.show` | `admin/organizations/{organization}` | `Admin\OrganizationController@show` |
| `admin.settings.index` | `admin/settings` | `Admin\SettingsController@index` |
| `admin.users.index` | `admin/users` | `Admin\UserController@index` |
| `candidate.applications.index` | `candidate/applications` | `Candidate\ApplicationController@index` |
| `candidate.applications.apply` | `candidate/applications/apply/{job?}` | `Candidate\ApplicationController@apply` |
| `candidate.applications.submitted` | `candidate/applications/submitted` | `Candidate\ApplicationController@submitted` |
| `candidate.applications.show` | `candidate/applications/{application}` | `Candidate\ApplicationController@show` |
| `candidate.assessments.index` | `candidate/assessments` | `Candidate\AssessmentController@index` |
| `candidate.assessments.show` | `candidate/assessments/{assessment}` | `Candidate\AssessmentController@show` |
| `candidate.assessments.result` | `candidate/assessments/{assessment}/result` | `Candidate\AssessmentController@result` |
| `candidate.assessments.take` | `candidate/assessments/{assessment}/take` | `Candidate\AssessmentController@take` |
| `candidate.dashboard` | `candidate/dashboard` | `Candidate\DashboardController` |
| `candidate.interviews.index` | `candidate/interviews` | `Candidate\InterviewController@index` |
| `candidate.interviews.show` | `candidate/interviews/{interview}` | `Candidate\InterviewController@show` |
| `candidate.jobs.index` | `candidate/jobs` | `Candidate\JobController@index` |
| `candidate.jobs.saved` | `candidate/jobs/saved` | `Candidate\JobController@saved` |
| `candidate.messages.index` | `candidate/messages` | `Candidate\MessageController@index` |
| `candidate.notifications.index` | `candidate/notifications` | `Candidate\NotificationController@index` |
| `candidate.offers.index` | `candidate/offers` | `Candidate\OfferController@index` |
| `candidate.offers.show` | `candidate/offers/{offer}` | `Candidate\OfferController@show` |
| `candidate.preferences` | `candidate/preferences` | `Candidate\PreferenceController@edit` |
| `candidate.profile` | `candidate/profile` | `Candidate\ProfileController@index` |
| `companies.show` | `companies/{company}` | `Public\CompanyController@show` |
| `company.analytics.index` | `company/analytics` | `Company\AnalyticsController@index` |
| `company.applicants.index` | `company/applicants` | `Company\ApplicantController@index` |
| `company.applicants.show` | `company/applicants/{applicant}` | `Company\ApplicantController@show` |
| `company.assessments.index` | `company/assessments` | `Company\AssessmentController@index` |
| `company.assessments.create` | `company/assessments/create` | `Company\AssessmentController@create` |
| `company.assessments.show` | `company/assessments/{assessment}` | `Company\AssessmentController@show` |
| `company.assessments.results` | `company/assessments/{assessment}/results` | `Company\AssessmentController@results` |
| `company.dashboard` | `company/dashboard` | `Company\DashboardController` |
| `company.employees.index` | `company/employees` | `Company\EmployeeController@index` |
| `company.employees.requests` | `company/employees/requests` | `Company\EmployeeController@requests` |
| `company.employees.show` | `company/employees/{employee}` | `Company\EmployeeController@show` |
| `company.evaluations.show` | `company/evaluations/{evaluation}` | `Company\EvaluationController@show` |
| `company.interviews.index` | `company/interviews` | `Company\InterviewController@index` |
| `company.interviews.schedule` | `company/interviews/schedule` | `Company\InterviewController@schedule` |
| `company.interviews.show` | `company/interviews/{interview}` | `Company\InterviewController@show` |
| `company.jobs.index` | `company/jobs` | `Company\JobController@index` |
| `company.jobs.create` | `company/jobs/create` | `Company\JobController@create` |
| `company.jobs.show` | `company/jobs/{job}` | `Company\JobController@show` |
| `company.offers.create` | `company/offers/create` | `Company\OfferController@create` |
| `company.profile` | `company/profile` | `Company\ProfileController@show` |
| `company.talent-pool.index` | `company/talent-pool` | `Company\TalentPoolController@index` |
| `contact` | `contact` | `Public\ContactController` |
| `password.request` | `forgot-password` | `Auth\ForgotPasswordController@create` |
| `interviewer.dashboard` | `interviewer/dashboard` | `Interviewer\DashboardController` |
| `interviewer.evaluations.index` | `interviewer/evaluations` | `Interviewer\EvaluationController@index` |
| `interviewer.evaluations.create` | `interviewer/evaluations/{interview}` | `Interviewer\EvaluationController@create` |
| `interviewer.interviews.live` | `interviewer/interviews/{interview}/live` | `Interviewer\InterviewController@live` |
| `interviewer.interviews.prepare` | `interviewer/interviews/{interview}/prepare` | `Interviewer\InterviewController@prepare` |
| `interviewer.schedule.index` | `interviewer/schedule` | `Interviewer\ScheduleController@index` |
| `jobs.index` | `jobs` | `Public\JobController@index` |
| `jobs.show` | `jobs/{job}` | `Public\JobController@show` |
| `locale.switch` | `locale/{locale}` | `Frontend\LocaleController` |
| `login` | `login` | `Auth\LoginController@create` |
| `onboarding.candidate` | `onboarding/candidate` | `Onboarding\CandidateOnboardingController@create` |
| `onboarding.company` | `onboarding/company` | `Onboarding\CompanyOnboardingController@create` |
| `onboarding.company.status` | `onboarding/company/status` | `Onboarding\CompanyOnboardingController@status` |
| `register` | `register` | `Auth\RegisterController@create` |
| `password.reset` | `reset-password` | `Auth\ResetPasswordController@create` |
| `verification.notice` | `verify-email` | `Auth\VerifyEmailController@notice` |


---

## 32. Appendix: LocalStorage Contract Inventory

See §13 for full verified key list and migration guidance. Primary source files:

- `resources/js/app.js`
- `resources/views/partials/theme-boot.blade.php`
- `resources/views/candidate/**/*.blade.php`
- `resources/views/company/**/*.blade.php`
- `resources/views/interviewer/**/*.blade.php`
- `resources/views/onboarding/**/*.blade.php`
- Cookie: `hb_locale` (`SetFrontendLocale::COOKIE`)

Scorecard key pattern (Verified): `hb_iv_{interviewId}_scorecard_v1` from `MockInterviewerData`.

---

## 33. Appendix: Proposed Endpoint Catalog

See §10 tables AUTH-*, PROF-*, JOBS-*, APP-*, IV-*, OFF-*, NOT-*, ADM-*, AUD-*.  

**Reminder:** None of these mutating endpoints exist in `routes/web.php` today. Only GET presentation routes and `locale.switch` are verified.

Additional Proposed (deferrable) JSON endpoints:

| ID | Method | Endpoint | Purpose |
|---|---|---|---|
| MSG-01 | GET | `/candidate/messages` | List threads (JSON optional) |
| MSG-02 | POST | `/candidate/messages/{thread}` | Send message |
| PREF-01 | PUT | `/candidate/preferences` | Save prefs |
| TAL-01 | POST | `/company/talent-pool/shortlist` | Toggle shortlist |
| AVAIL-01 | PUT | `/interviewer/availability` | Replace availability week |

---

## 34. Appendix: Repository Evidence References

| Topic | Path |
|---|---|
| Composer / Laravel version | `composer.json` |
| JS/CSS toolchain | `package.json` |
| Routing | `routes/web.php`, `bootstrap/app.php` |
| Locale middleware | `app/Http/Middleware/SetFrontendLocale.php` |
| Mock providers | `app/Support/Frontend/Mock*.php` |
| Models | `app/Models/*.php` |
| Migrations | `database/migrations/2026_10_20_*.php` |
| Roles/permissions | `database/seeders/RoleSeeder.php`, `PermissionSeeder.php` |
| Demo data warning | `database/seeders/DemoSeeder.php` |
| Client state | `resources/js/app.js` |
| Lang | `lang/en/*`, `lang/ar/*` |
| Example presentation-only controller | `app/Http/Controllers/Candidate/MessageController.php` |
| Audit model without migration | `app/Models/AuditLog.php` |
| HasAudit uid/luid expectation | `app/Models/Concerns/HasAudit.php` |
| User Sanctum trait | `app/Models/User.php` |
| README (generic Laravel) | `README.md` |

---

*End of Hirebridge Backend Integration Requirements & Technical Handoff.*
