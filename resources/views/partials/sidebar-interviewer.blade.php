@include('partials.sidebar-nav', [
    'eyebrow' => 'Interviewer',
    'workspaceLabel' => 'Northstar Labs',
    'items' => [
        [
            'label' => 'Overview',
            'route' => 'frontend.layouts.app',
            'params' => ['role' => 'interviewer'],
            'active' => ($uiRole ?? null) === 'interviewer' && request()->routeIs('frontend.layouts.app'),
        ],
        ['label' => 'My schedule', 'pending' => true],
        ['label' => 'Interview preparation', 'pending' => true],
        ['label' => 'Scorecards', 'pending' => true],
        ['label' => 'Availability', 'pending' => true],
    ],
])
