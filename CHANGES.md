# Frontend → Backend wiring (what changed)

Same structure as before (one *API model per area, CurlClient, ApiSession). Only paths / payloads / small view tweaks.

## Models (app/Models)
- Any 2xx counts as 200 in every model (backend answers 201 on creates) so `=== 200` in controllers keeps working.
- Candidate: /candidates/* -> /candidate/* ; resume -> POST /candidate/cvs (field `cv`), DELETE /candidate/cvs/{id};
  education/experience -> /candidate/educations|experiences ; vacancies -> GET /jobs ; apply -> POST /jobs/{id}/apply ;
  withdraw -> DELETE /candidate/applications/{id}/withdraw.
- Company: /company/vacancies -> /company/jobs (+ company_id taken from the logged-in user); application status -> PATCH;
  profile read from /auth/me (no /company/profile yet); createExam sends company_id.
- Exams: start = /candidate/exams/{exam}/applications/{application}/start (application_id required);
  submit/result work on the attempt id (/candidate/exam-attempts/{attempt}/submit|result).
- Interviews: slots -> /candidate/available-slots ; book needs application_id + availability_id ;
  reschedule sends new_availability_id.
- Offers: /candidate/offers (+accept/reject).

## Controllers / views / routes
- AuthController: company registered -> its {id,name,status} is kept in the session user.
- Exam routes `submit` / `result` use {attemptId}; take view posts answers as [{question_id, selected_option_id|answer_text|code}].
- Interview book/slots use application_id; evaluation requires internal_notes; application status options match backend
  (screening, shortlisted, interviewing, hired, rejected ...); question type `voice` removed.
- config/services.php default API_URL port is now 8000 (same as ApiBase).

## Still missing in the BACKEND (frontend shows "not available" / empty)
candidate exams list, CV download, candidate skills, change-password, delete-account, reset-password,
company profile get/update, employees + join-requests, admin companies/users/audit-logs.

## Hall (site start)
- `/` is now the hall entrance (exterior) = `frontend.home`; then `/landing`, `/lobby` (auth), `/exhibit` (auth).
  The old plain home page moved to `/welcome` (`frontend.welcome`).
- `App\Support\Hall::url()` resolves `scene:x` -> `frontend.hall.x` and `route:x` -> `frontend.x`.
- Hall views/nav/config now use the new route names (account.login/register/logout, workspace, jobs.index, candidate.interviews.index).
- Lounge / Assistant have no pages yet -> shown as "Soon". `lobby.webp` and `exhibit.webp` are missing from public/images/halls.
