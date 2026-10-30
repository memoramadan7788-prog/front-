<?php

return [
    'visibility' => [
        'public' => 'Public',
        'private' => 'Private',
    ],

    'application_mode' => [
        'open' => 'Open applications',
        'all_employees' => 'Employees only',
        'nominated_only' => 'Nomination only',
    ],

    'details' => [
        'role' => 'Open role',
        'salary_title' => 'Salary range',
        'salary_not_listed' => 'Not listed',
        'save_job' => 'Save job',
        'saved_job' => 'Saved on this browser',
        'save_unavailable' => 'Browser saving is unavailable. Try enabling local storage.',
        'consent_notice' => 'By continuing, you agree that the hiring team may review the application materials you submit for this role.',
        'journey_title' => 'Application journey',
        'journey_hint' => 'Typical stages; this is not live application status.',
        'stages' => [
            'applied' => 'Applied',
            'review' => 'In review',
            'interview' => 'Interview',
            'offer' => 'Offer',
        ],
        'role_details' => 'Role details',
        'description_unavailable' => 'A role description has not been provided.',
        'responsibilities' => 'Responsibilities',
        'requirements' => 'Requirements',
        'benefits' => 'Benefits',
        'core_skills' => 'Skills',
        'company_overview' => 'Company overview',
        'company_name_unavailable' => 'Company information unavailable',
        'company_overview_unavailable' => 'A company overview has not been provided.',
        'industry' => 'Industry',
        'hiring_contact' => 'Hiring contact',
        'contact_hiring_team' => 'Contact hiring team',
        'contact_unavailable' => 'Contact details are not available for this role.',
    ],

    'eligibility' => [
        'restricted_title' => 'Application not available',
        'restricted_generic' => 'You are not eligible to apply to this role in the current preview.',
        'authority_note' => 'This eligibility preview is presentation-only. Server authorization remains required.',
        'open_membership' => 'Open membership →',
        'nomination_hint' => 'Ask your company to nominate you for this role, or switch the nomination demo scenario.',
        'job_meta' => ':visibility · :mode',
        'apply_blocked_heading' => 'You cannot continue this application',
        'apply_blocked_body' => 'Eligibility rules for this role block the apply flow in the current preview.',
        'back_discover' => 'Back to Discover jobs',
        'unavailable' => 'Unavailable',
        'reasons' => [
            'job_closed' => 'This role is closed and is not accepting applications.',
            'job_draft' => 'This requisition is still a draft.',
            'employee_public_blocked' => 'Approved employees cannot apply to their own company’s public jobs.',
            'membership_pending' => 'Your company membership request is still pending review. Private employee roles stay unavailable until approval.',
            'membership_required' => 'This private role is visible to approved company employees only.',
            'nomination_required' => 'You are an approved member, but this role is nomination-only. A nomination from your company is required to apply.',
            'nomination_membership_required' => 'This nomination-only role requires approved company membership first.',
            'nomination_membership_pending' => 'Membership must be approved before a nomination can unlock this role.',
            'unknown' => 'Eligibility could not be determined in this preview.',
        ],
    ],

    'scenario' => [
        'heading' => 'Eligibility demo scenarios',
        'intro' => 'Switch membership and nomination context for private-job previews. Not real authorization.',
        'public_default' => 'Public / non-member',
        'approved_member' => 'Approved member',
        'pending_member' => 'Pending member',
        'nominated_member' => 'Approved + nominated',
        'current' => 'Current: :scenario',
    ],
];
