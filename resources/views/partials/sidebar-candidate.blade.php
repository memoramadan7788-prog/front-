@include('partials.sidebar-nav', [
    'eyebrow' => 'Candidate',
    'workspaceLabel' => 'Personal workspace',
    'items' => [
        [
            'label' => 'Overview',
            'route' => 'candidate.dashboard',
            'active' => request()->routeIs('candidate.dashboard'),
        ],
        [
            'label' => 'Discover jobs',
            'route' => 'candidate.jobs.index',
            'active' => request()->routeIs('candidate.jobs.index'),
        ],
        [
            'label' => 'Saved jobs',
            'route' => 'candidate.jobs.saved',
            'active' => request()->routeIs('candidate.jobs.saved'),
        ],
        [
            'label' => 'My applications',
            'route' => 'candidate.applications.index',
            'active' => request()->routeIs('candidate.applications.*'),
        ],
        [
            'label' => 'Interviews',
            'route' => 'candidate.interviews.index',
            'active' => request()->routeIs('candidate.interviews.*'),
        ],
        [
            'label' => 'Messages',
            'route' => 'candidate.messages.index',
            'active' => request()->routeIs('candidate.messages.*'),
        ],
        [
            'label' => 'Assessments',
            'route' => 'candidate.assessments.index',
            'active' => request()->routeIs('candidate.assessments.*'),
        ],
        [
            'label' => 'Profile & resume',
            'route' => 'candidate.profile',
            'active' => request()->routeIs('candidate.profile'),
        ],
        [
            'label' => 'Preferences & alerts',
            'route' => 'candidate.preferences',
            'active' => request()->routeIs('candidate.preferences'),
        ],
        [
            'label' => 'Offers',
            'route' => 'candidate.offers.index',
            'active' => request()->routeIs('candidate.offers.*'),
        ],
        [
            'label' => 'Notifications',
            'route' => 'candidate.notifications.index',
            'active' => request()->routeIs('candidate.notifications.*'),
        ],
    ],
])

