@include('partials.sidebar-nav', [
    'eyebrow' => __('navigation.company.eyebrow'),
    'workspaceLabel' => 'Northstar Labs',
    'items' => [
        [
            'label' => __('navigation.company.overview'),
            'route' => 'company.dashboard',
            'active' => request()->routeIs('company.dashboard')
                || (($uiRole ?? null) === 'company' && request()->routeIs('frontend.layouts.app')),
        ],
        [
            'label' => __('navigation.company.profile'),
            'route' => 'company.profile',
            'active' => request()->routeIs('company.profile'),
        ],
        [
            'label' => __('navigation.company.jobs'),
            'route' => 'company.jobs.index',
            'active' => request()->routeIs('company.jobs.*'),
        ],
        [
            'label' => __('navigation.company.applicants'),
            'route' => 'company.applicants.index',
            'active' => request()->routeIs('company.applicants.*'),
        ],
        ['label' => __('navigation.company.interviews'), 'pending' => true],
        ['label' => __('navigation.company.talent_pool'), 'pending' => true],
        ['label' => __('navigation.company.messages'), 'pending' => true],
        ['label' => __('navigation.company.team'), 'pending' => true],
        ['label' => __('navigation.company.analytics'), 'pending' => true],
    ],
])
