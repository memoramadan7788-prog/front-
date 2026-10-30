<?php

return [
    'page_title' => 'AI CV screening',
    'open' => 'AI screening',
    'run' => 'Run AI screening',
    'rerun' => 'Run again',
    'advisory_note' => 'The AI result is advisory only. Final hiring decisions stay with your team.',
    'error_generic' => 'The AI screening request could not be completed. Please try again.',

    'none_title' => 'No AI screening yet',
    'none_body' => 'Run the screening to compare this candidate resume with the job requirements.',
    'pending_title' => 'AI screening is running',
    'pending_body' => 'This page refreshes automatically. You can leave it and come back later.',
    'pending_stale_title' => 'This is taking longer than expected',
    'pending_stale_body' => 'The earlier run did not finish. You can start it again.',
    'failed_title' => 'AI screening failed',
    'failed_body' => 'The candidate is not rejected. Review the application manually, or run the screening again.',

    'tier_strong' => 'Strong match',
    'tier_moderate' => 'Moderate match',
    'tier_weak' => 'Weak match',

    'breakdown_heading' => 'How the score was calculated',
    'breakdown_note' => 'The score is calculated from the parts below, not typed by the AI.',
    'component_skills' => 'Skills',
    'component_experience' => 'Experience',
    'component_education' => 'Education',
    'component_excluded' => 'Not scored',
    'weight' => ':percent% of the score',

    'none_matched' => 'No required skills were found in the resume.',
    'none_missing' => 'No missing skills.',
    'via_alias' => 'found as ":term"',
    'via_implied' => 'implied by ":term"',
    'via_sub_items' => 'found via ":term"',
    'skills_source_job' => 'Skills come from the job posting.',
    'skills_source_model' => 'Skills were extracted from the job description by the AI.',

    'strengths_heading' => 'Verified strengths',
    'strengths_hint' => 'Each strength is backed by a quote that exists in the resume.',
    'strengths_none' => 'No verified strengths.',
    'dropped_note' => ':count AI claim(s) were removed because their quote was not found in the resume.',

    'opinion_heading' => 'AI opinion',
    'opinion_note' => 'AI-generated text, not verified against the resume. Treat it as a suggestion.',
    'summary_label' => 'Summary',
    'experience_label' => 'Experience',
    'education_label' => 'Education',
    'weaknesses_label' => 'Possible gaps',
    'truncated_note' => 'The resume was long, so the AI read only the first part. Skills were still checked against the full text.',

    'review_heading' => 'Your review',
    'review_intro' => 'Record whether you agree with the AI signal. You can change it later.',
    'decision_label' => 'Decision',
    'decision_select' => 'Select...',
    'decision_accept' => 'Agree with the AI signal',
    'decision_override' => 'Override the AI signal',
    'reason_label' => 'Reason',
    'reason_hint' => 'Required when you override.',
    'save_review' => 'Save review',

    'meta_model' => 'Model: :model',

    'flash' => [
        'queued' => 'AI screening was queued. The result will appear on this page.',
        'already_running' => 'An AI screening for this application is already running.',
        'reviewed' => 'Your review was saved.',
    ],
];
