@include('partials.sidebar-nav', [
    'eyebrow' => __('navigation.interviewer.eyebrow'),
    'workspaceLabel' => __('navigation.interviewer.workspace'),
    'items' => [
        [
            'label' => __('navigation.interviewer.overview'),
            'route' => 'frontend.layouts.app',
            'params' => ['role' => 'interviewer'],
            'active' => ($uiRole ?? null) === 'interviewer' && request()->routeIs('frontend.layouts.app'),
        ],
        ['label' => __('navigation.interviewer.schedule'), 'pending' => true],
        ['label' => __('navigation.interviewer.preparation'), 'pending' => true],
        ['label' => __('navigation.interviewer.scorecards'), 'pending' => true],
        ['label' => __('navigation.interviewer.availability'), 'pending' => true],
    ],
])
