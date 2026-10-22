@include('partials.sidebar-nav', [
    'eyebrow' => __('navigation.candidate.eyebrow'),
    'workspaceLabel' => __('navigation.candidate.workspace'),
    'items' => [
        [
            'label' => __('navigation.candidate.overview'),
            'route' => 'candidate.dashboard',
            'active' => request()->routeIs('candidate.dashboard'),
        ],
        [
            'label' => __('navigation.candidate.discover_jobs'),
            'route' => 'candidate.jobs.index',
            'active' => request()->routeIs('candidate.jobs.index'),
        ],
        [
            'label' => __('navigation.candidate.saved_jobs'),
            'route' => 'candidate.jobs.saved',
            'active' => request()->routeIs('candidate.jobs.saved'),
        ],
        [
            'label' => __('navigation.candidate.applications'),
            'route' => 'candidate.applications.index',
            'active' => request()->routeIs('candidate.applications.*'),
        ],
        [
            'label' => __('navigation.candidate.interviews'),
            'route' => 'candidate.interviews.index',
            'active' => request()->routeIs('candidate.interviews.*'),
        ],
        [
            'label' => __('navigation.candidate.messages'),
            'route' => 'candidate.messages.index',
            'active' => request()->routeIs('candidate.messages.*'),
        ],
        [
            'label' => __('navigation.candidate.assessments'),
            'route' => 'candidate.assessments.index',
            'active' => request()->routeIs('candidate.assessments.*'),
        ],
        [
            'label' => __('navigation.candidate.profile'),
            'route' => 'candidate.profile',
            'active' => request()->routeIs('candidate.profile'),
        ],
        [
            'label' => __('navigation.candidate.membership'),
            'route' => 'candidate.membership',
            'active' => request()->routeIs('candidate.membership'),
        ],
        [
            'label' => __('navigation.candidate.preferences'),
            'route' => 'candidate.preferences',
            'active' => request()->routeIs('candidate.preferences'),
        ],
        [
            'label' => __('navigation.candidate.offers'),
            'route' => 'candidate.offers.index',
            'active' => request()->routeIs('candidate.offers.*'),
        ],
        [
            'label' => __('navigation.candidate.notifications'),
            'route' => 'candidate.notifications.index',
            'active' => request()->routeIs('candidate.notifications.*'),
        ],
    ],
])
