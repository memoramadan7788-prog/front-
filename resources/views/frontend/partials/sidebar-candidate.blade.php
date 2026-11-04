<div class="px-4 py-2 font-['Cairo','Inter',sans-serif]">
    <!-- Workspace Title / Eyebrow -->
    <div class="mb-4 px-2">
        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 opacity-70">{{ __('navigation.candidate.eyebrow') }}</span>
        <div class="text-sm font-semibold text-white mt-0.5">{{ __('navigation.candidate.workspace') }}</div>
    </div>

    <!-- MAIN Section -->
    <div class="mb-6">
        <div class="px-2 mb-2 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">MAIN</div>
        <nav class="space-y-1.5">
            <!-- Overview / Dashboard -->
            <a href="{{ route('frontend.candidate.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('frontend.candidate.dashboard') ? 'bg-gradient-to-r from-[#2B3267] to-[#1E234B] text-white shadow-md border border-indigo-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <span>{{ __('navigation.candidate.overview') }}</span>
            </a>

            <!-- Discover jobs -->
            <a href="{{ route('frontend.candidate.vacancies.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('frontend.candidate.vacancies.*') ? 'bg-gradient-to-r from-[#2B3267] to-[#1E234B] text-white shadow-md border border-indigo-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <span>{{ __('navigation.candidate.discover_jobs') }}</span>
            </a>

            <!-- My applications -->
            <a href="{{ route('frontend.candidate.applications.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('frontend.candidate.applications.*') ? 'bg-gradient-to-r from-[#2B3267] to-[#1E234B] text-white shadow-md border border-indigo-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <span>{{ __('navigation.candidate.applications') }}</span>
            </a>

            <!-- Interviews -->
            <a href="{{ route('frontend.candidate.interviews.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('frontend.candidate.interviews.*') ? 'bg-gradient-to-r from-[#2B3267] to-[#1E234B] text-white shadow-md border border-indigo-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <span>{{ __('navigation.candidate.interviews') }}</span>
            </a>

            <!-- Assessments -->
            <a href="{{ route('frontend.candidate.exams.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('frontend.candidate.exams.*') ? 'bg-gradient-to-r from-[#2B3267] to-[#1E234B] text-white shadow-md border border-indigo-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <span>{{ __('navigation.candidate.assessments') }}</span>
            </a>

            <!-- Offers -->
            <a href="{{ route('frontend.candidate.offers.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('frontend.candidate.offers.*') ? 'bg-gradient-to-r from-[#2B3267] to-[#1E234B] text-white shadow-md border border-indigo-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <span>{{ __('navigation.candidate.offers') }}</span>
            </a>

            <!-- Messages -->
            <a href="{{ route('frontend.candidate.messages.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('frontend.candidate.messages*') ? 'bg-gradient-to-r from-[#2B3267] to-[#1E234B] text-white shadow-md border border-indigo-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <span>{{ __('candidate.messages.title') ?: 'Messages' }}</span>
            </a>
        </nav>
    </div>

    <!-- MANAGE Section -->
    <div class="mb-6">
        <div class="px-2 mb-2 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">MANAGE</div>
        <nav class="space-y-1.5">
            <!-- Profile & resume -->
            <a href="{{ route('frontend.candidate.profile') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('frontend.candidate.profile*') ? 'bg-gradient-to-r from-[#2B3267] to-[#1E234B] text-white shadow-md border border-indigo-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <span>{{ __('navigation.candidate.profile') }}</span>
            </a>

            <!-- My Skills -->
            <a href="{{ route('frontend.candidate.skills.add') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('frontend.candidate.skills.*') ? 'bg-gradient-to-r from-[#2B3267] to-[#1E234B] text-white shadow-md border border-indigo-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <span>My Skills</span>
            </a>

            <!-- My Education -->
            <a href="{{ route('frontend.candidate.education.add') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('frontend.candidate.education.*') ? 'bg-gradient-to-r from-[#2B3267] to-[#1E234B] text-white shadow-md border border-indigo-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <span>My Education</span>
            </a>

            <!-- My Platforms -->
            <a href="{{ Route::has('frontend.candidate.platforms.index') ? route('frontend.candidate.platforms.index') : route('frontend.candidate.profile') . '#platforms-section-card' }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('frontend.candidate.platforms.*') ? 'bg-gradient-to-r from-[#2B3267] to-[#1E234B] text-white shadow-md border border-indigo-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <span>My Platforms</span>
            </a>

            <!-- Preferences & alerts -->
            <a href="{{ route('frontend.candidate.preferences') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('frontend.candidate.preferences*') ? 'bg-gradient-to-r from-[#2B3267] to-[#1E234B] text-white shadow-md border border-indigo-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <span>{{ __('navigation.candidate.preferences') }}</span>
            </a>

            <!-- Company membership -->
            <a href="{{ route('frontend.candidate.join-company') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('frontend.candidate.join-company*') ? 'bg-gradient-to-r from-[#2B3267] to-[#1E234B] text-white shadow-md border border-indigo-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <span>{{ __('navigation.candidate.membership') }}</span>
            </a>
        </nav>
    </div>

    <!-- ACCOUNT Section -->
    <div class="mb-6">
        <div class="px-2 mb-2 text-[10px] font-extrabold uppercase tracking-widest text-slate-400">ACCOUNT</div>
        <nav class="space-y-1.5">
            <!-- Profile -->
            <a href="{{ route('frontend.candidate.profile') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('frontend.candidate.profile*') ? 'bg-gradient-to-r from-[#2B3267] to-[#1E234B] text-white shadow-md border border-indigo-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                <span>Profile</span>
            </a>

            <!-- Logout Form (Kept exactly as it is) -->
            <form method="POST" action="{{ Route::has('frontend.account.logout') ? route('frontend.account.logout') : (Route::has('logout') ? route('logout') : url('/logout')) }}" class="w-full">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium text-red-400 hover:bg-white/5 hover:text-red-300 transition-colors text-start">
                    <span>Logout</span>
                </button>
            </form>
        </nav>
    </div>
</div>