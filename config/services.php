<?php

return [

    'backend' => [
        'url' => env('API_URL', env('EXTERNAL_API_URL', 'https://hrapi.itsolution.solutions/api')),
        'timeout' => (int) env('API_TIMEOUT', 30),
        // Proposed Candidate-safe paths (Backend approval required). Leave null until wired.
        // Example proposals: '/companies/approved', '/candidate/membership'
        'company_discovery_path' => env('API_COMPANY_DISCOVERY_PATH'),
        'candidate_membership_path' => env('API_CANDIDATE_MEMBERSHIP_PATH'),
        // Proposed avatar (see PROFILE_AVATAR_NOTIFICATIONS_BACKEND_REQUIREMENTS.md)
        'profile_avatar_path' => env('API_PROFILE_AVATAR_PATH'),
        // Candidate notifications (Backend: GET/PATCH /api/candidate/notifications*)
        'notifications_path' => env('API_NOTIFICATIONS_PATH', '/candidate/notifications'),
        // Unused: unread_count is returned on list meta (kept for optional override / legacy)
        'notifications_unread_path' => env('API_NOTIFICATIONS_UNREAD_PATH'),
    ],

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
