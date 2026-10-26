
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Application tables that use Eloquent timestamps.
     * Framework-managed tables are intentionally excluded.
     */
    private array $timestampTables = [
        'users',
        'roles',
        'permissions',
        'role_permissions',
        'companies',
        'user_roles',
        'company_social_links',
        'company_employees',
        'candidate_profiles',
        'candidate_experience',
        'skills',
        'candidate_skills',
        'candidate_cvs',
        'candidate_education',
        'candidate_courses',
        'candidate_social_links',
        'job_categories',
        'jobs',
        'job_skills',
        'job_nominations',
        'job_interview_rounds',
        'applications',
        'application_status_history',
        'offers',
        'interviews',
        'interviewer_availability',
        'interview_bookings',
        'interview_evaluations',
        'calendar_integrations',
        'exams',
        'job_exams',
        'questions',
        'question_options',
        'question_test_cases',
        'exam_attempts',
        'answers',
        'grading_results',
        'proctoring_events',
        'proctoring_reviews',
        'cv_match_results',
        'ai_review_decisions',
        'salary_records',
        'salary_recommendations',
        'notifications',
        'chatbot_sessions',
        'chatbot_messages',
    ];

    /**
     * Tables whose models use the HasAudit trait.
     */
    private array $auditTables = [
        'users',
        'roles',
        'permissions',
        'companies',
        'company_employees',
        'company_social_links',
        'candidate_profiles',
        'candidate_experience',
        'candidate_education',
        'candidate_courses',
        'candidate_cvs',
        'candidate_social_links',
        'skills',
        'jobs',
        'job_categories',
        'job_nominations',
        'job_interview_rounds',
        'applications',
        'application_status_history',
        'offers',
        'interviews',
        'interviewer_availability',
        'interview_bookings',
        'interview_evaluations',
        'calendar_integrations',
        'exams',
        'job_exams',
        'questions',
        'question_options',
        'question_test_cases',
        'exam_attempts',
        'answers',
        'grading_results',
        'proctoring_events',
        'proctoring_reviews',
        'cv_match_results',
        'ai_review_decisions',
        'salary_records',
        'salary_recommendations',
        'chatbot_sessions',
        'chatbot_messages',
        'user_roles',
    ];

    public function up(): void
    {
        foreach ($this->timestampTables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            $missingCreatedAt = !Schema::hasColumn(
                $tableName,
                'created_at'
            );

            $missingUpdatedAt = !Schema::hasColumn(
                $tableName,
                'updated_at'
            );

            if ($missingCreatedAt || $missingUpdatedAt) {
                Schema::table($tableName, function (Blueprint $table) use (
                    $missingCreatedAt,
                    $missingUpdatedAt
                ) {
                    if ($missingCreatedAt) {
                        $table->timestamp('created_at')->nullable();
                    }

                    if ($missingUpdatedAt) {
                        $table->timestamp('updated_at')->nullable();
                    }
                });
            }
        }

        foreach ($this->auditTables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            $missingUid = !Schema::hasColumn($tableName, 'uid');
            $missingLuid = !Schema::hasColumn($tableName, 'luid');

            if ($missingUid || $missingLuid) {
                Schema::table($tableName, function (Blueprint $table) use (
                    $missingUid,
                    $missingLuid
                ) {
                    if ($missingUid) {
                        $table->unsignedBigInteger('uid')->nullable();
                    }

                    if ($missingLuid) {
                        $table->unsignedBigInteger('luid')->nullable();
                    }
                });
            }
        }

        // These pivot tables explicitly use withTimestamps().
        foreach (['candidate_skills', 'job_skills', 'role_permissions'] as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            $missingCreatedAt = !Schema::hasColumn($tableName, 'created_at');
            $missingUpdatedAt = !Schema::hasColumn($tableName, 'updated_at');

            if ($missingCreatedAt || $missingUpdatedAt) {
                Schema::table($tableName, function (Blueprint $table) use (
                    $missingCreatedAt,
                    $missingUpdatedAt
                ) {
                    if ($missingCreatedAt) {
                        $table->timestamp('created_at')->nullable();
                    }

                    if ($missingUpdatedAt) {
                        $table->timestamp('updated_at')->nullable();
                    }
                });
            }
        }
    }

    public function down(): void
    {
        // Intentionally no automatic rollback:
        // this migration may encounter columns that existed before it ran.
    }
};
