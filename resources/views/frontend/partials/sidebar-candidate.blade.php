<div class="px-4 py-2">
    <!-- Workspace Title / Eyebrow -->
    <div class="mb-4 px-2">
        <span class="text-[10px] font-bold uppercase tracking-wider text-hb-nav-muted opacity-70">{{ __('navigation.candidate.eyebrow') }}</span>
        <div class="text-sm font-semibold text-white mt-0.5">{{ __('navigation.candidate.workspace') }}</div>
    </div>

    <!-- MAIN Section -->
    <div class="mb-6">
        <div class="px-2 mb-2 text-[10px] font-extrabold uppercase tracking-widest text-hb-nav-muted">MAIN</div>
        <nav class="space-y-1">
            <a href="{{ route('frontend.candidate.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('frontend.candidate.dashboard') ? 'bg-white/10 text-white' : 'text-hb-nav-muted hover:bg-white/5 hover:text-white' }}">
                <span>{{ __('navigation.candidate.overview') }}</span>
            </a>
            <a href="{{ route('frontend.candidate.vacancies.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('frontend.candidate.vacancies.*') ? 'bg-white/10 text-white' : 'text-hb-nav-muted hover:bg-white/5 hover:text-white' }}">
                <span>{{ __('navigation.candidate.discover_jobs') }}</span>
            </a>
            <a href="{{ route('frontend.candidate.applications.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('frontend.candidate.applications.*') ? 'bg-white/10 text-white' : 'text-hb-nav-muted hover:bg-white/5 hover:text-white' }}">
                <span>{{ __('navigation.candidate.applications') }}</span>
            </a>
            <a href="{{ route('frontend.candidate.interviews.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('frontend.candidate.interviews.*') ? 'bg-white/10 text-white' : 'text-hb-nav-muted hover:bg-white/5 hover:text-white' }}">
                <span>{{ __('navigation.candidate.interviews') }}</span>
            </a>
            <a href="{{ route('frontend.candidate.exams.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('frontend.candidate.exams.*') ? 'bg-white/10 text-white' : 'text-hb-nav-muted hover:bg-white/5 hover:text-white' }}">
                <span>{{ __('navigation.candidate.assessments') }}</span>
            </a>
            <a href="{{ route('frontend.candidate.offers.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('frontend.candidate.offers.*') ? 'bg-white/10 text-white' : 'text-hb-nav-muted hover:bg-white/5 hover:text-white' }}">
                <span>{{ __('navigation.candidate.offers') }}</span>
            </a>
            <a href="{{ route('frontend.candidate.messages.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('frontend.candidate.messages*') ? 'bg-white/10 text-white' : 'text-hb-nav-muted hover:bg-white/5 hover:text-white' }}">
                <span>{{ __('candidate.messages.title') ?: 'Messages' }}</span>
            </a>
        </nav>
    </div>

    <!-- MANAGE Section -->
    <div class="mb-6">
        <div class="px-2 mb-2 text-[10px] font-extrabold uppercase tracking-widest text-hb-nav-muted">MANAGE</div>
        <nav class="space-y-1">
            <a href="{{ route('frontend.candidate.profile') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('frontend.candidate.profile*') ? 'bg-white/10 text-white' : 'text-hb-nav-muted hover:bg-white/5 hover:text-white' }}">
                <span>{{ __('navigation.candidate.profile') }}</span>
            </a>
            <a href="{{ route('frontend.candidate.skills.add') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('frontend.candidate.skills.*') ? 'bg-white/10 text-white' : 'text-hb-nav-muted hover:bg-white/5 hover:text-white' }}">
                <span>My Skills</span>
            </a>
            <a href="{{ route('frontend.candidate.education.add') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('frontend.candidate.education.*') ? 'bg-white/10 text-white' : 'text-hb-nav-muted hover:bg-white/5 hover:text-white' }}">
                <span>My Education</span>
            </a>
            <a href="{{ Route::has('frontend.candidate.platforms.index') ? route('frontend.candidate.platforms.index') : route('frontend.candidate.profile') . '#platforms-section-card' }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('frontend.candidate.platforms.*') ? 'bg-white/10 text-white' : 'text-hb-nav-muted hover:bg-white/5 hover:text-white' }}">
                <span>My Platforms</span>
            </a>
            <a href="{{ route('frontend.candidate.preferences') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('frontend.candidate.preferences*') ? 'bg-white/10 text-white' : 'text-hb-nav-muted hover:bg-white/5 hover:text-white' }}">
                <span>{{ __('navigation.candidate.preferences') }}</span>
            </a>
            <a href="{{ route('frontend.candidate.join-company') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('frontend.candidate.join-company*') ? 'bg-white/10 text-white' : 'text-hb-nav-muted hover:bg-white/5 hover:text-white' }}">
                <span>{{ __('navigation.candidate.membership') }}</span>
            </a>
        </nav>
    </div>

    <!-- ACCOUNT Section -->
    <div class="mb-6">
        <div class="px-2 mb-2 text-[10px] font-extrabold uppercase tracking-widest text-hb-nav-muted">ACCOUNT</div>
        <nav class="space-y-1">
            <a href="{{ route('frontend.candidate.profile') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('frontend.candidate.profile*') ? 'bg-white/10 text-white' : 'text-hb-nav-muted hover:bg-white/5 hover:text-white' }}">
                <span>Profile</span>
            </a>
            <form method="POST" action="{{ Route::has('frontend.account.logout') ? route('frontend.account.logout') : (Route::has('logout') ? route('logout') : url('/logout')) }}" class="w-full">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-red-400 hover:bg-white/5 hover:text-red-300 transition-colors text-start">
                    <span>Logout</span>
                </button>
            </form>
        </nav>
    </div>
</div>