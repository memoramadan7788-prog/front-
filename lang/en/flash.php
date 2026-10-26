<?php

/**
 * Locale-resolved flash messages. Controllers flash the key via success_key;
 * layouts call __(session('success_key')) at display time.
 */
return [
    'candidate' => [
        'profile_updated' => 'Your profile was updated successfully.',
        'resume_deleted' => 'Your resume was deleted successfully.',
        'education_added' => 'Education was added successfully.',
        'experience_added' => 'Work experience was added successfully.',
        'skill_added' => 'Skill was added successfully.',
        'exam_submitted' => 'Your exam was submitted successfully.',
    ],
    'admin' => [
        'company_approved' => 'Company approved successfully.',
        'company_rejected' => 'Company rejected successfully.',
        'company_suspended' => 'Company suspended successfully.',
        'user_status_updated' => 'User status updated successfully.',
    ],
    'company' => [
        'profile_updated' => 'Company profile updated successfully.',
        'vacancy_created' => 'Job created successfully.',
        'vacancy_updated' => 'Job updated successfully.',
        'vacancy_deleted' => 'Job deleted successfully.',
        'application_status_updated' => 'Application status updated successfully.',
        'exam_created' => 'Exam created successfully.',
        'offer_created' => 'Offer created successfully.',
    ],
    'employee' => [
        'join_requested' => 'Join request sent successfully.',
        'approved' => 'Employee approved successfully.',
        'rejected' => 'Employee rejected successfully.',
        'deleted' => 'Employee removed successfully.',
    ],
    'application' => [
        'withdrawn' => 'Application withdrawn successfully.',
        'status_updated' => 'Application status updated successfully.',
    ],
    'interview' => [
        'booked' => 'Interview booked successfully.',
        'rescheduled' => 'Interview rescheduled successfully.',
        'cancelled' => 'Interview cancelled successfully.',
        'evaluation_submitted' => 'Evaluation submitted successfully.',
    ],
    'interviewer' => [
        'slot_added' => 'Availability slot added successfully.',
        'evaluation_submitted' => 'Evaluation submitted successfully.',
    ],
    'offer' => [
        'accepted' => 'Offer accepted successfully.',
        'rejected' => 'Offer declined successfully.',
        'created' => 'Offer created successfully.',
    ],
    'exam' => [
        'submitted' => 'Exam submitted successfully.',
        'created' => 'Exam created successfully.',
        'question_added' => 'Question added successfully.',
    ],
];
