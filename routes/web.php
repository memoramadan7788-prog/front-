<?php

use App\Frontend\Account\AccountController;
use App\Frontend\Admin\AdminController;
use App\Frontend\Admin\AdminUsersAccessController;
use App\Frontend\Application\ApplicationController;
use App\Frontend\Ats\AtsController;
use App\Frontend\Auth\AuthController;
use App\Frontend\Candidate\CandidateController;
use App\Frontend\Candidate\CandidateOnboardingController;
use App\Frontend\Candidate\CandidatePreferencesController;
use App\Frontend\Company\CompanyController;
use App\Frontend\Company\CompanyCreateJobController;
use App\Frontend\Company\CompanyScheduleInterviewController;
use App\Frontend\Company\CompanyTeamPermissionsController;
use App\Frontend\Employee\EmployeeController;
use App\Frontend\Exam\ExamController;
use App\Frontend\Home\ConsultationController;
use App\Frontend\Home\HomeController;
use App\Frontend\Interview\InterviewController;
use App\Frontend\Interviewer\InterviewerController;
use App\Frontend\Interviewer\InterviewerLiveWorkspaceController;
use App\Frontend\Job\JobController;
use App\Frontend\Lookup\LookupController;
use App\Frontend\Notifications\NotificationsController;
use App\Frontend\Offer\OfferController;
use App\Http\Controllers\Frontend\LocaleController;
use App\Http\Controllers\HallController;
use Illuminate\Support\Facades\Route;

// Frontend Routes
Route::prefix('/')->name('frontend.')->group(function () {

    // Site start = the virtual hall: entrance (exterior) -> landing -> lobby -> exhibit halls
    Route::get('/', [HallController::class, 'exterior'])->name('home');
    Route::get('/landing', [HallController::class, 'landing'])->name('hall.landing');
    Route::get('/lobby', [HallController::class, 'lobby'])->middleware('auth')->name('hall.lobby');
    Route::get('/exhibit', [HallController::class, 'exhibit'])->middleware('auth')->name('hall.exhibit');
    // the plain (non-hall) home page
    Route::get('/welcome', [HomeController::class, 'index'])->name('welcome');
    Route::get('/about', [HomeController::class, 'about'])->name('about');
    Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
    Route::post('/contact', [HomeController::class, 'submitContact'])->name('contact.submit');
    Route::get('/consultation', [ConsultationController::class, 'show'])->name('consultation');
    Route::post('/consultation', [ConsultationController::class, 'submit'])->name('consultation.submit');
    Route::get('/workspace', [HomeController::class, 'workspace'])->middleware('auth')->name('workspace');
    Route::get('/locale/{locale}', LocaleController::class)->name('locale.switch');

    // Public jobs, companies + lookups (no login needed)
    Route::prefix('jobs')->name('jobs.')->group(function () {
        Route::get('/', [JobController::class, 'getJobs'])->name('index');
        Route::get('/search', [JobController::class, 'searchJobs'])->name('search');
        Route::post('/{jobId}/save', [JobController::class, 'saveJob'])
            ->middleware(['auth', 'account:candidate'])
            ->whereNumber('jobId')
            ->name('save');
        Route::delete('/{jobId}/save', [JobController::class, 'unsaveJob'])
            ->middleware(['auth', 'account:candidate'])
            ->whereNumber('jobId')
            ->name('unsave');
        Route::get('/{jobId}', [JobController::class, 'getJob'])->whereNumber('jobId')->name('show');
    });

    // Public Company Page Route (Task 6 - مريم)
    Route::prefix('companies')->name('companies.')->group(function () {
        Route::get('/{id}', [CompanyController::class, 'showPublicProfile'])
            ->whereNumber('id')
            ->name('show');
    });

    Route::prefix('lookup')->name('lookup.')->group(function () {
        Route::get('/countries', [LookupController::class, 'getCountries'])->name('countries');
        Route::get('/categories', [LookupController::class, 'getCategories'])->name('categories');
        Route::get('/skills', [LookupController::class, 'getSkills'])->name('skills');
    });

    // Auth (AccountAPI)
    Route::prefix('account')->middleware('guest')->name('account.')->group(function () {
        Route::get('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
        // Platform Super Admin (Admin model) — separate from Candidate/Company /auth/login.
        Route::get('/admin-login', [AuthController::class, 'adminLogin'])->name('admin-login');
        Route::post('/admin-login', [AuthController::class, 'adminLogin'])->name('admin-login.submit');
        Route::get('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
        Route::get('/verification', [AuthController::class, 'verification'])->name('verification');

        // Task 11: Forgot & Reset Password Routes
        Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password.submit');
        Route::get('/reset-password/{token?}', [AuthController::class, 'resetPassword'])->name('reset-password');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password.submit');
    });

    Route::prefix('account')->middleware('auth')->name('account.')->group(function () {
        Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
        // same page for every account type (topbar link) - see AccountController
        Route::get('/change-password', [AccountController::class, 'changePasswordForm'])->name('change-password');
        Route::post('/change-password', [AccountController::class, 'changePassword'])->name('change-password.submit');
    });

    // Candidate
    Route::prefix('candidate')->middleware(['auth', 'account:candidate'])->name('candidate.')->group(function () {
        Route::get('/onboarding', [CandidateOnboardingController::class, 'show'])->name('onboarding');
        Route::post('/onboarding', [CandidateOnboardingController::class, 'save'])->name('onboarding.save');
        Route::get('/dashboard', [CandidateController::class, 'dashboard'])->name('dashboard');
        Route::post('/notifications/read-all', [NotificationsController::class, 'markAllRead'])->name('notifications.read-all');
        Route::post('/notifications/{id}/read', [NotificationsController::class, 'markRead'])->name('notifications.read');
        Route::get('/profile', [CandidateController::class, 'getProfile'])->name('profile');
        Route::get('/profile/update', [CandidateController::class, 'updateProfile'])->name('profile.update');
        Route::post('/profile/update', [CandidateController::class, 'updateProfile'])->name('profile.update.submit');

        // Task 20: Candidate Messages & Conversations Routes
        Route::get('/messages', [CandidateController::class, 'getMessages'])->name('messages.index');
        Route::get('/messages/{conversationId}', [CandidateController::class, 'showConversation'])->whereNumber('conversationId')->name('messages.show');
        Route::post('/messages/{conversationId}/send', [CandidateController::class, 'sendMessage'])->whereNumber('conversationId')->name('messages.send');

        Route::get('/resume/upload', [CandidateController::class, 'uploadResume'])->name('resume.upload');
        Route::post('/resume/upload', [CandidateController::class, 'uploadResume'])->name('resume.upload.submit');
        Route::get('/resume/{resumeId}/download', [CandidateController::class, 'downloadResume'])->name('resume.download');
        Route::delete('/resume/{resumeId}/delete', [CandidateController::class, 'deleteResume'])->name('resume.delete');
        Route::get('/education/add', [CandidateController::class, 'addEducation'])->name('education.add');
        Route::post('/education/add', [CandidateController::class, 'addEducation'])->name('education.add.submit');
        Route::get('/experience/add', [CandidateController::class, 'addExperience'])->name('experience.add');
        Route::post('/experience/add', [CandidateController::class, 'addExperience'])->name('experience.add.submit');
        Route::get('/skills/add', [CandidateController::class, 'addSkill'])->name('skills.add');
        Route::post('/skills/add', [CandidateController::class, 'addSkill'])->name('skills.add.submit');
        Route::post('/skills/{skillId}/remove', [CandidateController::class, 'removeSkill'])->name('skills.remove');

        Route::get('/vacancies', [CandidateController::class, 'getVacancies'])->name('vacancies.index');
        // Workspace job details (same public JobAPI payload as /jobs/{id}; layout only differs).
        Route::get('/vacancies/{vacancyId}', [CandidateController::class, 'showVacancy'])->name('vacancies.show');
        Route::get('/vacancies/{vacancyId}/apply', [CandidateController::class, 'applyForVacancy'])->name('vacancies.apply');
        Route::post('/vacancies/{vacancyId}/apply', [CandidateController::class, 'applyForVacancy'])->name('vacancies.apply.submit');
        Route::get('/applications', [CandidateController::class, 'getApplications'])->name('applications.index');
        Route::get('/applications/success/{vacancyId}', [CandidateController::class, 'applicationSuccess'])->whereNumber('vacancyId')->name('applications.success');
        Route::get('/applications/{applicationId}', [CandidateController::class, 'getApplication'])->name('applications.show');
        Route::post('/applications/{applicationId}/withdraw', [ApplicationController::class, 'withdrawApplication'])->name('applications.withdraw');

        Route::get('/preferences', [CandidatePreferencesController::class, 'show'])->name('preferences');
        Route::post('/preferences/job', [CandidatePreferencesController::class, 'updatePreferences'])->name('preferences.job');
        Route::post('/preferences/notifications', [CandidatePreferencesController::class, 'updateNotifications'])->name('preferences.notifications');
        Route::post('/preferences/privacy', [CandidatePreferencesController::class, 'updatePrivacy'])->name('preferences.privacy');
        Route::post('/preferences/saved-searches', [CandidatePreferencesController::class, 'storeSavedSearch'])->name('preferences.saved-searches.store');
        Route::post('/preferences/saved-searches/{searchId}', [CandidatePreferencesController::class, 'updateSavedSearch'])->whereNumber('searchId')->name('preferences.saved-searches.update');
        Route::post('/preferences/saved-searches/{searchId}/delete', [CandidatePreferencesController::class, 'destroySavedSearch'])->whereNumber('searchId')->name('preferences.saved-searches.destroy');

        Route::get('/exams', [ExamController::class, 'getCandidateExams'])->name('exams.index');
        Route::get('/exams/{examId}/start', [ExamController::class, 'startExam'])->name('exams.start');
        Route::post('/exams/{examId}/start', [ExamController::class, 'startExam'])->name('exams.start.submit');
        Route::get('/exams/{attemptId}/submit', [ExamController::class, 'submitExam'])->name('exams.submit');
        Route::post('/exams/{attemptId}/submit', [ExamController::class, 'submitExam'])->name('exams.submit.submit');
        Route::get('/exams/{attemptId}/result', [ExamController::class, 'getExamResult'])->name('exams.result');

        Route::get('/interviews', [InterviewController::class, 'getInterviews'])->name('interviews.index');
        Route::get('/interviews/slots', [InterviewController::class, 'getAvailableSlots'])->name('interviews.slots');
        Route::get('/interviews/book', [InterviewController::class, 'bookInterview'])->name('interviews.book');
        Route::post('/interviews/book', [InterviewController::class, 'bookInterview'])->name('interviews.book.submit');
        Route::get('/interviews/{interviewId}/reschedule', [InterviewController::class, 'rescheduleInterview'])->name('interviews.reschedule');
        Route::post('/interviews/{interviewId}/reschedule', [InterviewController::class, 'rescheduleInterview'])->name('interviews.reschedule.submit');
        Route::post('/interviews/{interviewId}/cancel', [InterviewController::class, 'cancelInterview'])->name('interviews.cancel');

        Route::get('/offers', [OfferController::class, 'getOffers'])->name('offers.index');
        Route::get('/offers/{offerId}', [OfferController::class, 'getOffer'])->name('offers.show');
        Route::post('/offers/{offerId}/accept', [OfferController::class, 'acceptOffer'])->name('offers.accept');
        Route::get('/offers/{offerId}/reject', [OfferController::class, 'rejectOffer'])->name('offers.reject');
        Route::post('/offers/{offerId}/reject', [OfferController::class, 'rejectOffer'])->name('offers.reject.submit');
        Route::get('/change-password', [AccountController::class, 'changePasswordForm'])->name('change-password');
        Route::post('/change-password', [AccountController::class, 'changePassword'])->name('change-password.submit');
        Route::delete('/delete-account', [AccountController::class, 'deleteAccount'])->name('delete-account');
        Route::get('/join-company', [EmployeeController::class, 'requestToJoin'])->name('join-company');
        Route::post('/join-company', [EmployeeController::class, 'requestToJoin'])->name('join-company.submit');
    });

    // Company
    Route::prefix('company')->middleware(['auth', 'account:company'])->name('company.')->group(function () {
        // Task 26: Updated company dashboard route pointing to CompanyController@dashboard
        Route::get('/dashboard', [CompanyController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [CompanyController::class, 'getProfile'])->name('profile');
        Route::get('/profile/update', [CompanyController::class, 'updateProfile'])->name('profile.update');
        Route::post('/profile/update', [CompanyController::class, 'updateProfile'])->name('profile.update.submit');

        Route::post('/profile/documents', [CompanyController::class, 'uploadDocument'])->name('documents.upload');
        Route::get('/profile/documents/{documentId}/download', [CompanyController::class, 'downloadDocument'])->name('documents.download');
        Route::delete('/profile/documents/{documentId}', [CompanyController::class, 'deleteDocument'])->name('documents.delete');

        Route::get('/vacancies', [CompanyController::class, 'getVacancies'])->name('vacancies.index');
        Route::view('/talent-pool', 'frontend.company.talent-pool')->name('talent-pool.index');
        Route::get('/vacancies/create', [CompanyCreateJobController::class, 'show'])->name('vacancies.create');
        Route::post('/vacancies/create', [CompanyCreateJobController::class, 'handle'])->name('vacancies.create.submit');
        Route::get('/vacancies/create/preview', [CompanyCreateJobController::class, 'preview'])->name('vacancies.create.preview');
        Route::get('/vacancies/{vacancyId}/update', [CompanyController::class, 'updateVacancy'])->name('vacancies.update');
        Route::post('/vacancies/{vacancyId}/update', [CompanyController::class, 'updateVacancy'])->name('vacancies.update.submit');
        Route::get('/vacancies/{vacancyId}/overview', [CompanyController::class, 'jobOverview'])->name('vacancies.overview');
        Route::delete('/vacancies/{vacancyId}/delete', [CompanyController::class, 'deleteVacancy'])->name('vacancies.delete');
        Route::get('/vacancies/{vacancyId}/applications', [CompanyController::class, 'getApplications'])->name('vacancies.applications');
        Route::get('/applications/{applicationId}', [CompanyController::class, 'getApplicantProfile'])->whereNumber('applicationId')->name('applications.show');
        Route::get('/applications/{applicationId}/status', [CompanyController::class, 'updateApplicationStatus'])->name('applications.status');
        Route::post('/applications/{applicationId}/status', [CompanyController::class, 'updateApplicationStatus'])->name('applications.status.submit');

        Route::get('/applications/{applicationId}/ats', [AtsController::class, 'show'])->whereNumber('applicationId')->name('applications.ats');
        Route::post('/applications/{applicationId}/ats', [AtsController::class, 'start'])->whereNumber('applicationId')->name('applications.ats.start');
        Route::post('/applications/{applicationId}/ats/decision', [AtsController::class, 'decide'])->whereNumber('applicationId')->name('applications.ats.decision');

        Route::get('/exams/create', [CompanyController::class, 'createExam'])->name('exams.create');
        Route::post('/exams/create', [CompanyController::class, 'createExam'])->name('exams.create.submit');
        Route::get('/exams/{examId}/questions/add', [ExamController::class, 'addQuestion'])->name('exams.questions.add');
        Route::post('/exams/{examId}/questions/add', [ExamController::class, 'addQuestion'])->name('exams.questions.add.submit');
        Route::get('/offers/create', [CompanyController::class, 'createOffer'])->name('offers.create');
        Route::post('/offers/create', [CompanyController::class, 'createOffer'])->name('offers.create.submit');
        Route::get('/interviews/{interviewId}/evaluation', [InterviewController::class, 'submitEvaluation'])->name('interviews.evaluation');
        Route::post('/interviews/{interviewId}/evaluation', [InterviewController::class, 'submitEvaluation'])->name('interviews.evaluation.submit');
        Route::get('/salary-benchmark', [JobController::class, 'getSalaryBenchmark'])->name('salary-benchmark');
        Route::get('/interview-calendar', [CompanyController::class, 'teamInterviewCalendar'])->name('interview-calendar');
        Route::get('/interviews/schedule', [CompanyScheduleInterviewController::class, 'show'])->name('interviews.schedule');
        Route::post('/interviews/schedule', [CompanyScheduleInterviewController::class, 'handle'])->name('interviews.schedule.submit');
        Route::get('/hiring-analytics', [CompanyController::class, 'hiringAnalytics'])->name('analytics');

        Route::get('/change-password', [AccountController::class, 'changePasswordForm'])->name('change-password');
        Route::post('/change-password', [AccountController::class, 'changePassword'])->name('change-password.submit');
        Route::delete('/delete-account', [AccountController::class, 'deleteAccount'])->name('delete-account');

        Route::get('/employees', [CompanyTeamPermissionsController::class, 'show'])->name('employees.index');
        Route::post('/employees/{employeeId}/approve', [CompanyTeamPermissionsController::class, 'approve'])->whereNumber('employeeId')->name('employees.approve');
        Route::put('/employees/{employeeId}/role', [CompanyTeamPermissionsController::class, 'updateRole'])->whereNumber('employeeId')->name('employees.role.update');
        Route::get('/employees/{employeeId}/reject', [EmployeeController::class, 'rejectEmployee'])->whereNumber('employeeId')->name('employees.reject');
        Route::post('/employees/{employeeId}/reject', [EmployeeController::class, 'rejectEmployee'])->whereNumber('employeeId')->name('employees.reject.submit');
        Route::delete('/employees/{employeeId}/remove', [CompanyTeamPermissionsController::class, 'remove'])->whereNumber('employeeId')->name('employees.remove');
        Route::post('/employees/invitations', [CompanyTeamPermissionsController::class, 'createInvitation'])->name('employees.invitations.store');
        Route::delete('/employees/invitations/{invitationId}', [CompanyTeamPermissionsController::class, 'deleteInvitation'])->whereNumber('invitationId')->name('employees.invitations.destroy');
    });

    // Interviewer (a company account that has the interviewer role - the backend enforces the permissions)
    Route::prefix('interviewer')->middleware(['auth', 'account:company'])->name('interviewer.')->group(function () {
        Route::get('/interviews', [InterviewerController::class, 'getInterviews'])->name('interviews.index');
        Route::get('/interviews/{interviewId}/live', [InterviewerLiveWorkspaceController::class, 'show'])->whereNumber('interviewId')->name('interviews.live');
        Route::get('/interviews/{interviewId}', [InterviewerController::class, 'getInterview'])->whereNumber('interviewId')->name('interviews.show');
        Route::get('/interviews/{interviewId}/evaluation', [InterviewerController::class, 'submitEvaluation'])->whereNumber('interviewId')->name('interviews.evaluation');
        Route::post('/interviews/{interviewId}/evaluation', [InterviewerController::class, 'submitEvaluation'])->whereNumber('interviewId')->name('interviews.evaluation.submit');
        Route::get('/availability/add', [InterviewerController::class, 'addAvailability'])->name('availability.add');
        Route::post('/availability/add', [InterviewerController::class, 'addAvailability'])->name('availability.add.submit');
    });

    // Admin (Super Admin): platform-admin realm + Backend /admin/me revalidation.
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->middleware(['auth', 'account:admin', 'admin.session'])->name('dashboard');
    Route::prefix('admin')->middleware(['auth', 'account:admin', 'admin.session'])->name('admin.')->group(function () {
        Route::get('/jobs', [AdminController::class, 'jobModerationQueue'])->name('jobs.index');
        Route::get('/companies', [AdminController::class, 'getCompanies'])->name('companies.index');
        Route::post('/companies/{companyId}/approve', [AdminController::class, 'approveCompany'])->name('companies.approve');
        Route::post('/companies/{companyId}/suspend', [AdminController::class, 'suspendCompany'])->name('companies.suspend');
        Route::get('/companies/{companyId}/reject', [AdminController::class, 'rejectCompany'])->name('companies.reject');
        Route::post('/companies/{companyId}/reject', [AdminController::class, 'rejectCompany'])->name('companies.reject.submit');
        Route::get('/companies/{companyId}', [AdminController::class, 'showCompany'])->name('companies.show');
        Route::post('/companies/{companyId}/update', [AdminController::class, 'updateCompany'])->name('companies.update');
        Route::post('/companies/{companyId}/documents', [AdminController::class, 'uploadCompanyDocument'])->name('companies.documents.upload');
        Route::get('/companies/{companyId}/documents/{documentId}/download', [AdminController::class, 'downloadCompanyDocument'])->name('companies.documents.download');
        Route::delete('/companies/{companyId}/documents/{documentId}', [AdminController::class, 'deleteCompanyDocument'])->name('companies.documents.delete');
        Route::get('/users', [AdminUsersAccessController::class, 'index'])->name('users.index');
        Route::get('/users/export-page', [AdminUsersAccessController::class, 'exportCurrentPage'])->name('users.export-page');
        Route::get('/users/{userId}/status', [AdminController::class, 'updateUserStatus'])->whereNumber('userId')->name('users.status');
        Route::post('/users/{userId}/status', [AdminController::class, 'updateUserStatus'])->whereNumber('userId')->name('users.status.submit');
        Route::get('/roles', [AdminController::class, 'getRoles'])->name('roles.index');
        Route::get('/audit-logs', [AdminController::class, 'getAuditLogs'])->name('audit-logs.index');
    });
});
