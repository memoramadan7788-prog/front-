@include('frontend.partials.sidebar-nav', [
    'eyebrow' => __('navigation.candidate.eyebrow'),
    'workspaceLabel' => __('navigation.candidate.workspace'),
    'items' => [
        [
            'label' => __('navigation.candidate.overview'),
            'route' => 'frontend.candidate.dashboard',
            'active' => request()->routeIs('frontend.candidate.dashboard'),
        ],
        [
            'label' => __('navigation.candidate.discover_jobs'),
            'route' => 'frontend.candidate.vacancies.index',
            'active' => request()->routeIs('frontend.candidate.vacancies.*'),
        ],
        [
            'label' => __('navigation.candidate.applications'),
            'route' => 'frontend.candidate.applications.index',
            'active' => request()->routeIs('frontend.candidate.applications.*'),
        ],
        [
            'label' => __('navigation.candidate.interviews'),
            'route' => 'frontend.candidate.interviews.index',
            'active' => request()->routeIs('frontend.candidate.interviews.*'),
        ],
        [
            'label' => __('navigation.candidate.assessments'),
            'route' => 'frontend.candidate.exams.index',
            'active' => request()->routeIs('frontend.candidate.exams.*'),
        ],
        [
            'label' => __('navigation.candidate.offers'),
            'route' => 'frontend.candidate.offers.index',
            'active' => request()->routeIs('frontend.candidate.offers.*'),
        ],
        // Task 20: Candidate Messages Link
        [
            'label' => __('candidate.messages.title') ?? __('navigation.candidate.messages', 'الرسائل'),
            'route' => 'frontend.candidate.messages.index',
            'active' => request()->routeIs('frontend.candidate.messages*'),
        ],
        [
            'label' => __('navigation.candidate.profile'),
            'route' => 'frontend.candidate.profile',
            'active' => request()->routeIs('frontend.candidate.profile*'),
        ],
        [
            'label' => __('navigation.candidate.preferences'),
            'route' => 'frontend.candidate.preferences',
            'active' => request()->routeIs('frontend.candidate.preferences*'),
        ],
        // Employee onboarding (WF2), separate from apply→offer. Join UI ready; discovery/status APIs proposed.
        [
            'label' => __('navigation.candidate.membership'),
            'route' => 'frontend.candidate.join-company',
            'active' => request()->routeIs('frontend.candidate.join-company*'),
        ],
    ],
])