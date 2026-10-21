@include('partials.sidebar-nav', [
    'eyebrow' => __('navigation.admin.eyebrow'),
    'workspaceLabel' => __('navigation.admin.workspace'),
    'items' => [
        [
            'label' => __('navigation.admin.overview'),
            'route' => 'frontend.layouts.app',
            'params' => ['role' => 'admin'],
            'active' => ($uiRole ?? null) === 'admin' && request()->routeIs('frontend.layouts.app'),
        ],
        ['label' => __('navigation.admin.organizations'), 'pending' => true],
        ['label' => __('navigation.admin.users'), 'pending' => true],
        ['label' => __('navigation.admin.moderation'), 'pending' => true],
        ['label' => __('navigation.admin.agency'), 'pending' => true],
        ['label' => __('navigation.admin.settings'), 'pending' => true],
    ],
])
