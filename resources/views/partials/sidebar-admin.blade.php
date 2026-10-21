@include('partials.sidebar-nav', [
    'eyebrow' => 'Super Admin',
    'workspaceLabel' => 'Hirebridge platform',
    'items' => [
        [
            'label' => 'Overview',
            'route' => 'frontend.layouts.app',
            'params' => ['role' => 'admin'],
            'active' => ($uiRole ?? null) === 'admin' && request()->routeIs('frontend.layouts.app'),
        ],
        ['label' => 'Organizations', 'pending' => true],
        ['label' => 'Users & access', 'pending' => true],
        ['label' => 'Job moderation', 'pending' => true],
        ['label' => 'Agency operations', 'pending' => true],
        ['label' => 'Settings & audit', 'pending' => true],
    ],
])
