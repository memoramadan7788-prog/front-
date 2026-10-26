@include('frontend.partials.sidebar-nav', [
    'eyebrow' => __('navigation.interviewer.eyebrow'),
    'workspaceLabel' => __('navigation.interviewer.workspace'),
    'items' => [
        [
            'label' => __('navigation.interviewer.schedule'),
            'route' => 'frontend.interviewer.interviews.index',
            'active' => request()->routeIs('frontend.interviewer.interviews.*'),
        ],
        [
            'label' => __('navigation.interviewer.availability'),
            'route' => 'frontend.interviewer.availability.add',
            'active' => request()->routeIs('frontend.interviewer.availability.*'),
        ],
        [
            'label' => __('navigation.company.overview'),
            'route' => 'frontend.company.dashboard',
            'active' => request()->routeIs('frontend.company.dashboard'),
        ],
    ],
])
