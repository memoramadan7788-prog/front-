@include('partials.sidebar-nav', [
    'eyebrow' => 'Company Admin / HR',
    'workspaceLabel' => 'Northstar Labs',
    'items' => [
        [
            'label' => 'Overview',
            'route' => 'company.dashboard',
            'active' => request()->routeIs('company.dashboard')
                || (($uiRole ?? null) === 'company' && request()->routeIs('frontend.layouts.app')),
        ],
        [
            'label' => 'Company profile',
            'route' => 'company.profile',
            'active' => request()->routeIs('company.profile'),
        ],
        ['label' => 'Jobs & requisitions', 'pending' => true],
        ['label' => 'Applicants', 'pending' => true],
        ['label' => 'Interviews', 'pending' => true],
        ['label' => 'Talent pool', 'pending' => true],
        ['label' => 'Messages', 'pending' => true],
        ['label' => 'Team & permissions', 'pending' => true],
        ['label' => 'Hiring analytics', 'pending' => true],
    ],
])
