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
            'active' => request()->routeIs('company.applicants.*')
                || request()->routeIs('company.offers.*'),
        ],
        [
            'label' => __('navigation.company.interviews'),
            'route' => 'company.interviews.index',
            'active' => request()->routeIs('company.interviews.*')
                || request()->routeIs('company.evaluations.*'),
        ],
        [
            'label' => __('navigation.company.talent_pool'),
            'route' => 'company.talent-pool.index',
            'active' => request()->routeIs('company.talent-pool.*'),
        ],
        ['label' => __('navigation.company.messages'), 'pending' => true],
        [
            'label' => __('navigation.company.team'),
            'route' => 'company.employees.index',
            'active' => request()->routeIs('company.employees.*'),
        ],
        ['label' => __('navigation.company.analytics'), 'pending' => true],
    ],
])
