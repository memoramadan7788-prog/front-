@include('frontend.partials.sidebar-nav', [
    'eyebrow' => __('navigation.company.eyebrow'),
    'workspaceLabel' => __('navigation.workspace'),
    'items' => [
        [
            'label' => __('navigation.company.overview'),
            'route' => 'frontend.company.dashboard',
            'active' => request()->routeIs('frontend.company.dashboard'),
        ],
        [
            'label' => __('navigation.company.profile'),
            'route' => 'frontend.company.profile',
            'active' => request()->routeIs('frontend.company.profile*'),
        ],
        [
            'label' => __('navigation.company.jobs'),
            'route' => 'frontend.company.vacancies.index',
            'active' => request()->routeIs('frontend.company.vacancies.*'),
        ],
        [
            'label' => __('navigation.company.assessments'),
            'route' => 'frontend.company.exams.create',
            'active' => request()->routeIs('frontend.company.exams.*'),
        ],
        [
            'label' => __('navigation.company.team'),
            'route' => 'frontend.company.employees.index',
            'active' => request()->routeIs('frontend.company.employees.*'),
        ],
    ],
])
