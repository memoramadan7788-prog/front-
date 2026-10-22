<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Candidate\ApplicationController as CandidateApplicationController;
use App\Http\Controllers\Candidate\AssessmentController as CandidateAssessmentController;
use App\Http\Controllers\Candidate\DashboardController as CandidateDashboardController;
use App\Http\Controllers\Candidate\InterviewController as CandidateInterviewController;
use App\Http\Controllers\Candidate\JobController as CandidateJobController;
use App\Http\Controllers\Candidate\MessageController as CandidateMessageController;
use App\Http\Controllers\Candidate\NotificationController as CandidateNotificationController;
use App\Http\Controllers\Candidate\OfferController as CandidateOfferController;
use App\Http\Controllers\Candidate\PreferenceController as CandidatePreferenceController;
use App\Http\Controllers\Candidate\ProfileController as CandidateProfileController;
use App\Http\Controllers\Company\AnalyticsController as CompanyAnalyticsController;
use App\Http\Controllers\Company\ApplicantController as CompanyApplicantController;
use App\Http\Controllers\Company\AssessmentController as CompanyAssessmentController;
use App\Http\Controllers\Company\DashboardController as CompanyDashboardController;
use App\Http\Controllers\Company\EmployeeController as CompanyEmployeeController;
use App\Http\Controllers\Company\EvaluationController as CompanyEvaluationController;
use App\Http\Controllers\Company\InterviewController as CompanyInterviewController;
use App\Http\Controllers\Company\JobController as CompanyJobController;
use App\Http\Controllers\Company\OfferController as CompanyOfferController;
use App\Http\Controllers\Company\ProfileController as CompanyProfileController;
use App\Http\Controllers\Company\TalentPoolController as CompanyTalentPoolController;
use App\Http\Controllers\Frontend\ComponentShowcaseController;
use App\Http\Controllers\Frontend\LayoutPreviewController;
use App\Http\Controllers\Frontend\LocaleController;
use App\Http\Controllers\Interviewer\DashboardController as InterviewerDashboardController;
use App\Http\Controllers\Interviewer\EvaluationController as InterviewerEvaluationController;
use App\Http\Controllers\Interviewer\InterviewController as InterviewerInterviewController;
use App\Http\Controllers\Interviewer\ScheduleController as InterviewerScheduleController;
use App\Http\Controllers\Onboarding\CandidateOnboardingController;
use App\Http\Controllers\Onboarding\CompanyOnboardingController;
use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\CompanyController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\JobController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Frontend presentation preferences (locale cookie only)
|--------------------------------------------------------------------------
*/
Route::get('/locale/{locale}', LocaleController::class)
    ->whereIn('locale', ['en', 'ar'])
    ->name('locale.switch');

Route::get('/', HomeController::class)->name('home');

Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{job}', [JobController::class, 'show'])->name('jobs.show');

Route::get('/about', AboutController::class)->name('about');
Route::get('/contact', ContactController::class)->name('contact');
Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('companies.show');

/*
|--------------------------------------------------------------------------
| Authentication UI (Batch 3B — frontend presentation only)
|--------------------------------------------------------------------------
| GET page routes only. No credential processing, sessions, or email delivery.
*/
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
Route::get('/verify-email', [VerifyEmailController::class, 'notice'])->name('verification.notice');
Route::get('/reset-password', [ResetPasswordController::class, 'create'])->name('password.reset');

/*
|--------------------------------------------------------------------------
| Onboarding UI (Batch 3C — frontend presentation only)
|--------------------------------------------------------------------------
| GET page routes only. No profile/company persistence or document uploads.
*/
Route::get('/onboarding/candidate', [CandidateOnboardingController::class, 'create'])
    ->name('onboarding.candidate');
Route::get('/onboarding/company', [CompanyOnboardingController::class, 'create'])
    ->name('onboarding.company');
Route::get('/onboarding/company/status', [CompanyOnboardingController::class, 'status'])
    ->name('onboarding.company.status');

/*
|--------------------------------------------------------------------------
| Candidate workspace UI (Batch 4A — frontend presentation only)
|--------------------------------------------------------------------------
| GET page routes only. No auth middleware, persistence, or live metrics.
*/
Route::get('/candidate/dashboard', CandidateDashboardController::class)
    ->name('candidate.dashboard');
Route::get('/candidate/profile', [CandidateProfileController::class, 'index'])
    ->name('candidate.profile');

/*
|--------------------------------------------------------------------------
| Candidate jobs UI (Batch 4B — frontend presentation only)
|--------------------------------------------------------------------------
| GET page routes only. Filters/save state are client-side mock interactions.
*/
Route::get('/candidate/jobs', [CandidateJobController::class, 'index'])
    ->name('candidate.jobs.index');
Route::get('/candidate/jobs/saved', [CandidateJobController::class, 'saved'])
    ->name('candidate.jobs.saved');

/*
|--------------------------------------------------------------------------
| Candidate applications UI (Batch 4C — frontend presentation only)
|--------------------------------------------------------------------------
| GET page routes only. Apply / confirmation are mock UI — no persistence.
*/
Route::get('/candidate/applications', [CandidateApplicationController::class, 'index'])
    ->name('candidate.applications.index');
Route::get('/candidate/applications/apply/{job?}', [CandidateApplicationController::class, 'apply'])
    ->name('candidate.applications.apply');
Route::get('/candidate/applications/submitted', [CandidateApplicationController::class, 'submitted'])
    ->name('candidate.applications.submitted');
Route::get('/candidate/applications/{application}', [CandidateApplicationController::class, 'show'])
    ->name('candidate.applications.show');

/*
|--------------------------------------------------------------------------
| Candidate interviews, messages & assessments (Batch 4D — frontend only)
|--------------------------------------------------------------------------
| GET page routes only. No booking, delivery, grading, or proctoring.
*/
Route::get('/candidate/interviews', [CandidateInterviewController::class, 'index'])
    ->name('candidate.interviews.index');
Route::get('/candidate/interviews/{interview}', [CandidateInterviewController::class, 'show'])
    ->name('candidate.interviews.show');
Route::get('/candidate/messages', [CandidateMessageController::class, 'index'])
    ->name('candidate.messages.index');
Route::get('/candidate/assessments', [CandidateAssessmentController::class, 'index'])
    ->name('candidate.assessments.index');
Route::get('/candidate/assessments/{assessment}/take', [CandidateAssessmentController::class, 'take'])
    ->name('candidate.assessments.take');
Route::get('/candidate/assessments/{assessment}/result', [CandidateAssessmentController::class, 'result'])
    ->name('candidate.assessments.result');
Route::get('/candidate/assessments/{assessment}', [CandidateAssessmentController::class, 'show'])
    ->name('candidate.assessments.show');

/*
|--------------------------------------------------------------------------
| Candidate preferences, offers & notifications (Batch 4E — frontend only)
|--------------------------------------------------------------------------
| GET page routes only. No preference persistence or offer decisions.
*/
Route::get('/candidate/preferences', [CandidatePreferenceController::class, 'edit'])
    ->name('candidate.preferences');
Route::get('/candidate/offers', [CandidateOfferController::class, 'index'])
    ->name('candidate.offers.index');
Route::get('/candidate/offers/{offer}', [CandidateOfferController::class, 'show'])
    ->name('candidate.offers.show');
Route::get('/candidate/notifications', [CandidateNotificationController::class, 'index'])
    ->name('candidate.notifications.index');

/*
|--------------------------------------------------------------------------
| Company / HR workspace UI (Batch 5A — frontend presentation only)
|--------------------------------------------------------------------------
| GET page routes only. No auth middleware, persistence, or live analytics.
*/
Route::get('/company/dashboard', CompanyDashboardController::class)
    ->name('company.dashboard');
Route::get('/company/profile', [CompanyProfileController::class, 'show'])
    ->name('company.profile');

/*
|--------------------------------------------------------------------------
| Company jobs & requisitions UI (Batch 5B — frontend presentation only)
|--------------------------------------------------------------------------
| GET page routes only. Create / publish / archive are mock UI — no persistence.
*/
Route::get('/company/jobs', [CompanyJobController::class, 'index'])
    ->name('company.jobs.index');
Route::get('/company/jobs/create', [CompanyJobController::class, 'create'])
    ->name('company.jobs.create');
Route::get('/company/jobs/{job}', [CompanyJobController::class, 'show'])
    ->name('company.jobs.show');

/*
|--------------------------------------------------------------------------
| Company applicants UI (Batch 5C — frontend presentation only)
|--------------------------------------------------------------------------
| GET page routes only. Stage moves / notes are client-side mock interactions.
*/
Route::get('/company/applicants', [CompanyApplicantController::class, 'index'])
    ->name('company.applicants.index');
Route::get('/company/applicants/{applicant}', [CompanyApplicantController::class, 'show'])
    ->name('company.applicants.show');

/*
|--------------------------------------------------------------------------
| Company interviews & evaluations UI (Batch 5D — frontend presentation only)
|--------------------------------------------------------------------------
| GET page routes only. Scheduling / decisions are client-side mock interactions.
*/
Route::get('/company/interviews', [CompanyInterviewController::class, 'index'])
    ->name('company.interviews.index');
Route::get('/company/interviews/schedule', [CompanyInterviewController::class, 'schedule'])
    ->name('company.interviews.schedule');
Route::get('/company/interviews/{interview}', [CompanyInterviewController::class, 'show'])
    ->name('company.interviews.show');
Route::get('/company/evaluations/{evaluation}', [CompanyEvaluationController::class, 'show'])
    ->name('company.evaluations.show');

/*
|--------------------------------------------------------------------------
| Company offers & talent pool UI (Batch 5E — frontend presentation only)
|--------------------------------------------------------------------------
| GET page routes only. Save / send / invite are client-side mock interactions.
*/
Route::get('/company/offers/create', [CompanyOfferController::class, 'create'])
    ->name('company.offers.create');
Route::get('/company/talent-pool', [CompanyTalentPoolController::class, 'index'])
    ->name('company.talent-pool.index');

/*
|--------------------------------------------------------------------------
| Company team, employees & join requests UI (Batch 5F — frontend presentation only)
|--------------------------------------------------------------------------
| GET page routes only. Invites / approvals are client-side mock interactions.
| Static /requests must be registered before /{employee}.
*/
Route::get('/company/employees', [CompanyEmployeeController::class, 'index'])
    ->name('company.employees.index');
Route::get('/company/employees/requests', [CompanyEmployeeController::class, 'requests'])
    ->name('company.employees.requests');
Route::get('/company/employees/{employee}', [CompanyEmployeeController::class, 'show'])
    ->name('company.employees.show');

/*
|--------------------------------------------------------------------------
| Company analytics & assessments UI (Batch 5G — frontend presentation only)
|--------------------------------------------------------------------------
| GET page routes only. Export / grading / assignment are client-side mock interactions.
| Static assessment paths must be registered before /{assessment}.
*/
Route::get('/company/analytics', [CompanyAnalyticsController::class, 'index'])
    ->name('company.analytics.index');
Route::get('/company/assessments', [CompanyAssessmentController::class, 'index'])
    ->name('company.assessments.index');
Route::get('/company/assessments/create', [CompanyAssessmentController::class, 'create'])
    ->name('company.assessments.create');
Route::get('/company/assessments/{assessment}/results', [CompanyAssessmentController::class, 'results'])
    ->name('company.assessments.results');
Route::get('/company/assessments/{assessment}', [CompanyAssessmentController::class, 'show'])
    ->name('company.assessments.show');

/*
|--------------------------------------------------------------------------
| Interviewer workspace UI (Stage 6 · Batches 6A–6C — frontend presentation only)
|--------------------------------------------------------------------------
| GET page routes only. No evaluation POST / backend submission.
*/
Route::get('/interviewer/dashboard', InterviewerDashboardController::class)
    ->name('interviewer.dashboard');
Route::get('/interviewer/schedule', [InterviewerScheduleController::class, 'index'])
    ->name('interviewer.schedule.index');
Route::get('/interviewer/evaluations', [InterviewerEvaluationController::class, 'index'])
    ->name('interviewer.evaluations.index');
Route::get('/interviewer/evaluations/{interview}', [InterviewerEvaluationController::class, 'create'])
    ->name('interviewer.evaluations.create');
Route::get('/interviewer/interviews/{interview}/prepare', [InterviewerInterviewController::class, 'prepare'])
    ->name('interviewer.interviews.prepare');
Route::get('/interviewer/interviews/{interview}/live', [InterviewerInterviewController::class, 'live'])
    ->name('interviewer.interviews.live');

/*
|--------------------------------------------------------------------------
| Development-only frontend previews (Stages 1–2)
|--------------------------------------------------------------------------
| Unavailable in production.
*/
if (! app()->isProduction()) {
    Route::prefix('_frontend/layouts')->name('frontend.layouts.')->group(function () {
        Route::get('/auth', [LayoutPreviewController::class, 'auth'])->name('auth');
        Route::get('/app', [LayoutPreviewController::class, 'app'])->name('app');
    });

    Route::get('/_frontend/components', ComponentShowcaseController::class)
        ->name('frontend.components');
}
