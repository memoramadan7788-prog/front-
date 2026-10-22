@include('partials.sidebar-nav', [
    'eyebrow' => __('navigation.admin.eyebrow'),
    'workspaceLabel' => __('navigation.admin.workspace'),
    'items' => [
        [
            'label' => __('navigation.admin.overview'),
            'route' => 'admin.dashboard',
            'active' => request()->routeIs('admin.dashboard'),
        ],
        [
            'label' => __('navigation.admin.organizations'),
            'route' => 'admin.organizations.index',
            'active' => request()->routeIs('admin.organizations.*'),
        ],
        [
            'label' => __('navigation.admin.users'),
            'route' => 'admin.users.index',
            'active' => request()->routeIs('admin.users.*'),
        ],
        [
            'label' => __('navigation.admin.moderation'),
            'route' => 'admin.jobs.index',
            'active' => request()->routeIs('admin.jobs.*'),
        ],
        [
            'label' => __('navigation.admin.agency'),
            'route' => 'admin.agencies.index',
            'active' => request()->routeIs('admin.agencies.*'),
        ],
        [
            'label' => __('navigation.admin.settings'),
            'route' => 'admin.settings.index',
            'active' => request()->routeIs('admin.settings.*'),
        ],
    ],
])
