<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Temporary local-only demo data for visually verifying the Company > Job
 * workspace pages against Figma. Not part of the application's real seed set.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $ownerId = DB::table('users')->insertGetId([
            'name' => 'Olivia Bennett',
            'email' => 'olivia@acme.test',
            'password' => Hash::make('password'),
            'account_type' => 'company',
            'account_status' => 'active',
        ]);

        $managerId = DB::table('users')->insertGetId([
            'name' => 'Daniel Brooks',
            'email' => 'daniel@acme.test',
            'password' => Hash::make('password'),
            'account_type' => 'company',
            'account_status' => 'active',
        ]);

        $interviewerUserId = DB::table('users')->insertGetId([
            'name' => 'Alex Morgan',
            'email' => 'alex@acme.test',
            'password' => Hash::make('password'),
            'account_type' => 'company',
            'account_status' => 'active',
        ]);

        $candidateNames = ['Maya Chen', 'Luis Romero', 'Aisha Patel', 'Ethan Cole', 'Priya Nair', 'Sam Okafor', 'Jordan Blake', 'Noah Kim', 'Grace Liu', 'Omar Haddad', 'Sofia Reyes', 'Ben Carter'];
        $candidateUserIds = [];
        $candidateProfileIds = [];
        foreach ($candidateNames as $i => $name) {
            $uid = DB::table('users')->insertGetId([
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)).'@example.test',
                'password' => Hash::make('password'),
                'account_type' => 'candidate',
                'account_status' => 'active',
            ]);
            $candidateUserIds[$name] = $uid;

            $candidateProfileIds[$name] = DB::table('candidate_profiles')->insertGetId([
                'user_id' => $uid,
                'headline' => 'Product Designer',
            ]);
        }

        $companyId = DB::table('companies')->insertGetId([
            'name' => 'Acme Inc.',
            'status' => 'approved',
            'owner_user_id' => $ownerId,
        ]);

        DB::table('company_employees')->insert([
            ['company_id' => $companyId, 'user_id' => $ownerId, 'requested_job_title' => 'Requisition Owner', 'status' => 'approved', 'requested_at' => $now, 'joined_at' => $now],
            ['company_id' => $companyId, 'user_id' => $managerId, 'requested_job_title' => 'Hiring Manager', 'status' => 'approved', 'requested_at' => $now, 'joined_at' => $now],
            ['company_id' => $companyId, 'user_id' => $interviewerUserId, 'requested_job_title' => 'Portfolio Interviewer', 'status' => 'approved', 'requested_at' => $now, 'joined_at' => $now],
        ]);

        $categoryId = DB::table('job_categories')->insertGetId([
            'name' => 'Product & Design',
        ]);

        $jobId = DB::table('jobs')->insertGetId([
            'company_id' => $companyId,
            'category_id' => $categoryId,
            'created_by' => $ownerId,
            'title' => 'Senior Product Designer',
            'description' => 'We are looking for an experienced product designer to join our team and help shape the future of our platform.',
            'location' => 'Austin',
            'work_mode' => 'hybrid',
            'employment_type' => 'full_time',
            'salary_min' => 140000,
            'salary_max' => 170000,
            'currency' => 'USD',
            'salary_period' => 'year',
            'department' => 'Product & Design',
            'openings_count' => 1,
            'visibility' => 'public',
            'private_apply_mode' => 'all_employees',
            'status' => 'published',
            'published_at' => '2026-10-08 09:00:00',
        ]);

        DB::table('job_interview_rounds')->insert([
            ['job_id' => $jobId, 'round_no' => 1, 'interview_type' => 'online', 'duration_min' => 45],
            ['job_id' => $jobId, 'round_no' => 2, 'interview_type' => 'onsite', 'duration_min' => 60],
        ]);

        $cvId = fn (string $name) => DB::table('candidate_cvs')->insertGetId([
            'candidate_id' => $candidateProfileIds[$name],
            'version' => 1,
            'storage_path' => 'cvs/demo.pdf',
            'original_filename' => 'cv.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 102400,
            'is_active' => 1,
            'uploaded_at' => $now,
        ]);

        $applyAttempt = fn (string $name, string $status, int $daysAgo = 3) => DB::table('applications')->insertGetId([
            'job_id' => $jobId,
            'candidate_id' => $candidateProfileIds[$name],
            'cv_id' => $cvId($name),
            'consent_at' => $now,
            'status' => $status,
            'applied_at' => $now->copy()->subDays($daysAgo),
        ]);

        // Applied (3) — new, no action needed yet.
        foreach (['Priya Nair', 'Sam Okafor', 'Jordan Blake'] as $name) {
            $applyAttempt($name, 'applied', 1);
        }

        // Screening (3) — review needed.
        foreach (['Noah Kim', 'Grace Liu', 'Omar Haddad'] as $name) {
            $applyAttempt($name, 'screening', 2);
        }

        // Interview (3) — Maya, Luis, Aisha, each with a scheduled booking.
        $interviewPlans = [
            'Maya Chen' => ['round' => 1, 'start' => '2026-10-22 10:00:00', 'end' => '2026-10-22 10:45:00'],
            'Luis Romero' => ['round' => 2, 'start' => '2026-10-21 11:00:00', 'end' => '2026-10-21 12:00:00'],
            'Aisha Patel' => ['round' => 1, 'start' => '2026-10-23 11:00:00', 'end' => '2026-10-23 11:45:00'],
        ];
        foreach ($interviewPlans as $name => $plan) {
            $appId = $applyAttempt($name, 'interview', 7);

            $interviewId = DB::table('interviews')->insertGetId([
                'application_id' => $appId,
                'round_no' => $plan['round'],
                'interview_type' => $plan['round'] === 1 ? 'online' : 'onsite',
                'status' => 'scheduled',
                'created_by' => $ownerId,
            ]);

            $availabilityId = DB::table('interviewer_availability')->insertGetId([
                'interviewer_id' => $interviewerUserId,
                'start_at' => $plan['start'],
                'end_at' => $plan['end'],
                'availability_status' => 'booked',
            ]);

            DB::table('interview_bookings')->insert([
                'interview_id' => $interviewId,
                'interviewer_id' => $interviewerUserId,
                'availability_id' => $availabilityId,
                'start_at' => $plan['start'],
                'end_at' => $plan['end'],
                'booking_status' => 'booked',
            ]);
        }

        // Offer (2) — Ethan Cole has a freshly sent offer awaiting response; Sofia's offer already accepted (no action needed).
        $ethanAppId = $applyAttempt('Ethan Cole', 'offer', 10);
        DB::table('offers')->insert([
            'application_id' => $ethanAppId,
            'salary_amount' => 160000,
            'currency' => 'USD',
            'salary_period' => 'year',
            'start_date' => '2026-11-10',
            'status' => 'sent',
            'sent_by' => $ownerId,
            'sent_at' => $now->copy()->subDay(),
        ]);

        $sofiaAppId = $applyAttempt('Sofia Reyes', 'offer', 12);
        DB::table('offers')->insert([
            'application_id' => $sofiaAppId,
            'salary_amount' => 155000,
            'currency' => 'USD',
            'salary_period' => 'year',
            'start_date' => '2026-11-15',
            'status' => 'accepted',
            'sent_by' => $ownerId,
            'sent_at' => $now->copy()->subDays(5),
            'responded_at' => $now->copy()->subDays(2),
        ]);

        // Hired (1) — Ben Carter, closed out, no action needed.
        $applyAttempt('Ben Carter', 'hired', 20);
    }
}
