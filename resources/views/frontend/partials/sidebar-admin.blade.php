@include('frontend.partials.sidebar-nav', [
    'eyebrow' => __('navigation.admin.eyebrow'),
    'workspaceLabel' => __('navigation.admin.workspace'),
    'items' => [
        [
            'label' => __('navigation.admin.overview'),
            'route' => 'frontend.dashboard',
            'active' => request()->routeIs('frontend.dashboard'),
        ],
        [
            'label' => __('navigation.admin.organizations'),
            'route' => 'frontend.admin.companies.index',
            'active' => request()->routeIs('frontend.admin.companies.*'),
        ],
        [
            'label' => __('Job moderation preview'),
            'route' => 'frontend.admin.jobs.index',
            'active' => request()->routeIs('frontend.admin.jobs.*'),
        ],
        [
            'label' => __('navigation.admin.users'),
            'route' => 'frontend.admin.users.index',
            'active' => request()->routeIs('frontend.admin.users.*'),
        ],
        [
            'label' => __('navigation.admin.roles'),
            'route' => 'frontend.admin.roles.index',
            'active' => request()->routeIs('frontend.admin.roles.*'),
        ],
        [
            'label' => __('Audit logs'),
            'route' => 'frontend.admin.audit-logs.index',
            'active' => request()->routeIs('frontend.admin.audit-logs.*'),
        ],
    ],
])
