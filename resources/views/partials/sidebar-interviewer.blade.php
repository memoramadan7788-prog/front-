@include('partials.sidebar-nav', [
    'eyebrow' => __('navigation.interviewer.eyebrow'),
    'workspaceLabel' => __('navigation.interviewer.workspace'),
    'items' => [
        [
            'label' => __('navigation.interviewer.overview'),
            'route' => 'interviewer.dashboard',
            'active' => request()->routeIs('interviewer.dashboard'),
        ],
        [
            'label' => __('navigation.interviewer.schedule'),
            'route' => 'interviewer.schedule.index',
            'active' => request()->routeIs('interviewer.schedule.*'),
        ],
        [
            'label' => __('navigation.interviewer.preparation'),
            'route' => 'interviewer.interviews.prepare',
            'params' => ['interview' => 'int-ns204-portfolio'],
            'active' => request()->routeIs('interviewer.interviews.prepare') || request()->routeIs('interviewer.interviews.live'),
        ],
        [
            'label' => __('navigation.interviewer.scorecards'),
            'route' => 'interviewer.evaluations.index',
            'active' => request()->routeIs('interviewer.evaluations.*'),
        ],
        ['label' => __('navigation.interviewer.availability'), 'pending' => true],
    ],
])
