@extends('frontend.layouts.app')

@section('title', __('candidate.skills.manage_title'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('candidate.skills.manage_title') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.skills.manage_intro') }}</p>
@endsection

@section('content')
@php
    $assignedSkills = \App\Support\Rows::from($assignedSkills ?? []);
    $catalogSkills = \App\Support\Rows::from($catalogSkills ?? []);
    $skillsAvailable = $skillsAvailable ?? false;
    $profileFallback = ($skillsSource ?? null) === 'profile';
    $localFallback = in_array($skillsSource ?? null, ['profile', 'local'], true);
    $localSkillsStorageKey = 'candidate-local-skills-v1-'.preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($candidateId ?? 'candidate'));
    $localSkillPresets = [
        ['name' => 'React', 'type' => 'Development'],
        ['name' => 'Node.js', 'type' => 'Development'],
        ['name' => 'PHP', 'type' => 'Development'],
        ['name' => 'Laravel', 'type' => 'Development'],
        ['name' => 'Python', 'type' => 'Development'],
        ['name' => 'JavaScript', 'type' => 'Development'],
        ['name' => 'TypeScript', 'type' => 'Development'],
        ['name' => 'Vue.js', 'type' => 'Development'],
        ['name' => 'HTML & CSS', 'type' => 'Development'],
        ['name' => 'Java', 'type' => 'Development'],
        ['name' => 'C#', 'type' => 'Development'],
        ['name' => 'SQL', 'type' => 'Data'],
        ['name' => 'MySQL', 'type' => 'Data'],
        ['name' => 'PostgreSQL', 'type' => 'Data'],
        ['name' => 'Data Analysis', 'type' => 'Data'],
        ['name' => 'Microsoft Excel', 'type' => 'Data'],
        ['name' => 'Power BI', 'type' => 'Data'],
        ['name' => 'Tableau', 'type' => 'Data'],
        ['name' => 'Machine Learning', 'type' => 'Data'],
        ['name' => 'Statistics', 'type' => 'Data'],
        ['name' => 'AWS', 'type' => 'Cloud & DevOps'],
        ['name' => 'Azure', 'type' => 'Cloud & DevOps'],
        ['name' => 'Docker', 'type' => 'Cloud & DevOps'],
        ['name' => 'Kubernetes', 'type' => 'Cloud & DevOps'],
        ['name' => 'Git', 'type' => 'Cloud & DevOps'],
        ['name' => 'Linux', 'type' => 'Cloud & DevOps'],
        ['name' => 'CI/CD', 'type' => 'Cloud & DevOps'],
        ['name' => 'Cybersecurity', 'type' => 'Cloud & DevOps'],
        ['name' => 'REST APIs', 'type' => 'Development'],
        ['name' => 'Automated Testing', 'type' => 'Development'],
        ['name' => 'Human Resources', 'type' => 'Management'],
        ['name' => 'Recruiting', 'type' => 'Management'],
        ['name' => 'Talent Acquisition', 'type' => 'Management'],
        ['name' => 'Project Management', 'type' => 'Management'],
        ['name' => 'Agile', 'type' => 'Management'],
        ['name' => 'Scrum', 'type' => 'Management'],
        ['name' => 'Strategic Planning', 'type' => 'Management'],
        ['name' => 'Stakeholder Management', 'type' => 'Management'],
        ['name' => 'Leadership', 'type' => 'Professional'],
        ['name' => 'Communication', 'type' => 'Professional'],
        ['name' => 'Teamwork', 'type' => 'Professional'],
        ['name' => 'Problem Solving', 'type' => 'Professional'],
        ['name' => 'Critical Thinking', 'type' => 'Professional'],
        ['name' => 'Time Management', 'type' => 'Professional'],
        ['name' => 'Adaptability', 'type' => 'Professional'],
        ['name' => 'Conflict Resolution', 'type' => 'Professional'],
        ['name' => 'Customer Service', 'type' => 'Professional'],
        ['name' => 'Negotiation', 'type' => 'Professional'],
        ['name' => 'Attention to Detail', 'type' => 'Professional'],
    ];
    foreach ($localFallback ? $catalogSkills : [] as $catalogSkill) {
        $catalogSkillName = data_get($catalogSkill, 'name');

        if (is_string($catalogSkillName) && trim($catalogSkillName) !== '') {
            $localSkillPresets[] = [
                'name' => trim($catalogSkillName),
                'type' => is_string(data_get($catalogSkill, 'type')) ? data_get($catalogSkill, 'type') : __('candidate.skills.catalog_skill'),
            ];
        }
    }
    $localSkillPresets = collect($localSkillPresets)
        ->unique(fn (array $skill) => mb_strtolower($skill['name']))
        ->values();
    if ($skillsAvailable) {
        $catalogSkillNames = collect($catalogSkills)->map(fn ($skill) => mb_strtolower((string) data_get($skill, 'name')))->all();
        $localSkillPresets = $localSkillPresets
            ->reject(fn (array $skill) => in_array(mb_strtolower($skill['name']), $catalogSkillNames, true))
            ->values();
    }
@endphp

<div class="space-y-6">
    <x-card class="max-w-3xl">
        <x-slot:header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-bold text-hb-navy">{{ __('candidate.skills.assigned_heading') }}</h2>
                <x-button variant="light" size="sm" :href="route('frontend.candidate.profile')">{{ __('candidate.skills.back_to_profile') }}</x-button>
            </div>
        </x-slot:header>

        @if (filled($assignedError ?? null))
            <div class="rounded-xl border border-hb-error/30 bg-hb-error/5 px-4 py-3 text-sm text-hb-error" role="alert" data-skills-assigned-error>
                <p class="font-semibold">{{ __('candidate.skills.load_error_title') }}</p>
                <p class="mt-1">{{ $assignedError }}</p>
            </div>
        @else
            @if ($profileFallback)
                <div class="mb-4 rounded-xl border border-hb-pending/30 bg-hb-pending-soft px-4 py-3 text-sm text-hb-pending" role="status" data-skills-profile-fallback>
                    {{ __('candidate.skills.profile_fallback') }}
                </div>
            @elseif ($localFallback)
                <div class="mb-4 rounded-xl border border-hb-pending/30 bg-hb-pending-soft px-4 py-3 text-sm text-hb-pending" role="status" data-skills-local-fallback>
                    {{ __('candidate.skills.local_fallback') }}
                </div>
            @endif
        @endif

        @if (blank($assignedError ?? null) && empty($assignedSkills) && ! $localFallback)
            <div class="text-sm text-hb-muted" role="status" data-skills-assigned-empty>
                <p class="font-semibold text-hb-navy">{{ __('candidate.skills.empty_title') }}</p>
                <p class="mt-1">{{ __('candidate.skills.empty_body') }}</p>
            </div>
        @elseif (blank($assignedError ?? null))
            <ul class="divide-y divide-hb-border" data-skills-assigned-list>
                @foreach ($assignedSkills as $skill)
                    @php
                        $id = (int) (data_get($skill, 'id') ?? data_get($skill, 'skill_id') ?? 0);
                        $name = data_get($skill, 'name') ?? data_get($skill, 'skill.name') ?? data_get($skill, 'label');
                        $level = data_get($skill, 'level');
                        $type = data_get($skill, 'type');
                    @endphp
                    @continue($id < 1 || blank($name))
                    <li class="flex flex-wrap items-center justify-between gap-3 py-3" data-skill-id="{{ $id }}">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-hb-navy">{{ $name }}</p>
                            <p class="mt-0.5 text-xs text-hb-muted">
                                @if (filled($level))
                                    @php
                                        $levelKey = 'candidate.skills.levels.'.$level;
                                        $levelText = __($levelKey);
                                        if ($levelText === $levelKey) {
                                            $levelText = (string) $level;
                                        }
                                    @endphp
                                    {{ __('candidate.skills.level_label', ['level' => $levelText]) }}
                                @else
                                    {{ __('candidate.skills.level_unset') }}
                                @endif
                                @if (filled($type))
                                    <span class="mx-1" aria-hidden="true">·</span>{{ $type }}
                                @endif
                            </p>
                        </div>
                        @if ($skillsAvailable)
                            <form method="POST" action="{{ route('frontend.candidate.skills.remove', ['skillId' => $id]) }}" data-skills-remove-form>
                                @csrf
                                <x-button type="submit" variant="destructive" size="sm" data-skills-remove-submit>
                                    {{ __('candidate.skills.remove') }}
                                </x-button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
        <ul class="mt-4 hidden divide-y divide-hb-border" data-local-skills-list aria-label="{{ __('Skills saved on this device') }}"></ul>
        <p class="mt-3 text-sm text-hb-muted" data-local-skills-status role="status" aria-live="polite"></p>
    </x-card>

    <x-card class="max-w-3xl">
        <x-slot:header>
            <h2 class="text-lg font-bold text-hb-navy">{{ __('candidate.skills.add_heading') }}</h2>
            <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.skills.add_intro') }}</p>
        </x-slot:header>

        @if (! $skillsAvailable && ! $localFallback)
            <div class="rounded-xl border border-hb-pending/30 bg-hb-pending-soft px-4 py-3 text-sm text-hb-pending" role="status" data-skills-add-disabled>
                {{ __('candidate.skills.add_disabled') }}
            </div>
        @else
            <form method="GET" action="{{ route('frontend.candidate.skills.add') }}" class="mb-5 flex flex-wrap items-end gap-3" data-skills-search-form>
                <div class="min-w-[12rem] flex-1">
                    <label for="skills-q" class="mb-1.5 block text-sm font-semibold text-hb-text">{{ __('candidate.skills.search_label') }}</label>
                    <input
                        id="skills-q"
                        name="q"
                        type="search"
                        value="{{ $catalogQuery ?? '' }}"
                        placeholder="{{ __('candidate.skills.search_placeholder') }}"
                        class="min-h-11 w-full rounded-md border border-hb-border bg-hb-surface px-3 text-sm text-hb-text focus:border-hb-blue focus:outline-none focus:ring-2 focus:ring-hb-blue/30"
                    >
                </div>
                <x-button type="submit" variant="light">{{ __('candidate.skills.search_submit') }}</x-button>
                @if (filled($catalogQuery ?? null))
                    <x-button variant="ghost" :href="route('frontend.candidate.skills.add')">{{ __('candidate.skills.search_clear') }}</x-button>
                @endif
            </form>

            @if (filled($catalogError ?? null))
                <div class="mb-4 rounded-xl border border-hb-error/30 bg-hb-error/5 px-4 py-3 text-sm text-hb-error" role="alert" data-skills-catalog-error>
                    <p class="font-semibold">{{ __('candidate.skills.catalog_error_title') }}</p>
                    <p class="mt-1">{{ $catalogError }}</p>
                </div>
            @endif

            @if ($localFallback)
                <p class="mb-4 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-950" role="status">
                    {{ __('Adding skills here saves them in this browser only. They will not sync to your profile until the backend skills endpoint is available.') }}
                </p>
            @endif
                <form method="{{ $skillsAvailable ? 'POST' : 'GET' }}" action="{{ $skillsAvailable ? route('frontend.candidate.skills.add.submit') : route('frontend.candidate.skills.add') }}" class="space-y-5" data-local-skill-form autocomplete="off">
                    @if ($skillsAvailable) @csrf @endif
                    <div class="relative" data-skill-picker>
                        <label for="skill-combobox" class="block text-sm font-semibold text-hb-text">{{ __('candidate.skills.skill_label') }}</label>
                        <input
                            id="skill-combobox"
                            type="search"
                            role="combobox"
                            aria-autocomplete="list"
                            aria-expanded="false"
                            aria-controls="skill-options"
                            data-skill-combobox
                            maxlength="100"
                            class="mt-1.5 min-h-11 w-full rounded-md border border-hb-border bg-white px-3 text-sm focus:border-hb-blue focus:outline-none focus:ring-2 focus:ring-hb-blue/30"
                            placeholder="{{ __('candidate.skills.search_placeholder') }}"
                            autocomplete="off"
                        >
                        <p class="mt-1.5 text-xs text-hb-muted">{{ __('candidate.skills.combobox_hint') }}</p>
                        <div id="skill-options" role="listbox" class="absolute z-20 mt-1 hidden max-h-64 w-full overflow-y-auto rounded-xl border border-hb-border bg-white p-1 shadow-lg" data-skill-options></div>
                        <button type="button" class="mt-2 hidden text-sm font-semibold text-hb-blue hover:underline" data-add-custom-skill>{{ __('candidate.skills.add_custom_skill') }}</button>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-hb-muted">{{ __('candidate.skills.selected_skills') }}</p>
                        <ul class="mt-2 flex min-h-10 flex-wrap gap-2" data-pending-skills aria-live="polite"></ul>
                    </div>
                    <label class="block text-sm font-semibold text-hb-text">
                        {{ __('candidate.skills.level_field') }}
                        <select name="level" class="mt-1.5 min-h-11 w-full rounded-md border border-hb-border bg-white px-3 text-sm">
                            <option value="">{{ __('candidate.skills.level_optional') }}</option>
                            @foreach (['beginner', 'intermediate', 'advanced', 'expert'] as $lvl)
                                <option value="{{ $lvl }}">{{ __('candidate.skills.levels.'.$lvl) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button type="submit" data-save-selected-skills disabled class="inline-flex min-h-11 items-center justify-center rounded-full bg-hb-blue px-5 py-2.5 text-sm font-semibold text-white hover:bg-hb-navy disabled:cursor-not-allowed disabled:opacity-50">
                        {{ $skillsAvailable ? __('candidate.skills.save') : __('candidate.skills.add_selected_skills') }}
                    </button>
                    <template data-skill-option-template>
                        @foreach ($localSkillPresets as $index => $preset)
                            <button type="button" role="option" data-skill-option data-skill-id="preset-{{ $index }}" data-skill-name="{{ $preset['name'] }}" data-skill-type="{{ $preset['type'] }}" data-skill-source="local" class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-start text-sm text-hb-text hover:bg-slate-50 focus:bg-slate-50">
                                <span>{{ $preset['name'] }}</span>
                                <span class="text-xs text-hb-muted">{{ $preset['type'] }}</span>
                            </button>
                        @endforeach
                        @foreach ($catalogSkills as $catalogSkill)
                            <button type="button" role="option" data-skill-option data-skill-id="{{ data_get($catalogSkill, 'id') }}" data-skill-name="{{ data_get($catalogSkill, 'name') }}" data-skill-type="{{ data_get($catalogSkill, 'type', __('candidate.skills.catalog_skill')) }}" data-skill-source="api" class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-start text-sm text-hb-text hover:bg-slate-50 focus:bg-slate-50">
                                <span>{{ data_get($catalogSkill, 'name') }}</span>
                                <span class="text-xs text-hb-muted">{{ data_get($catalogSkill, 'type', __('candidate.skills.catalog_skill')) }}</span>
                            </button>
                        @endforeach
                    </template>
                </form>
        @endif
    </x-card>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const form = document.querySelector('[data-local-skill-form]');
    const list = document.querySelector('[data-local-skills-list]');
    const status = document.querySelector('[data-local-skills-status]');
    if (!form || !list || !status) return;

    const storageKey = @json($localSkillsStorageKey);
    const apiEnabled = @json($skillsAvailable);
    const emptyState = document.querySelector('[data-skills-assigned-empty]');
    const assignedIds = new Set([...document.querySelectorAll('[data-skills-assigned-list] [data-skill-id]')].map((item) => item.dataset.skillId));
    const assignedNames = new Set([...document.querySelectorAll('[data-skills-assigned-list] [data-skill-id] p:first-child')].map((item) => item.textContent.trim().toLocaleLowerCase()));
    const combo = form.querySelector('[data-skill-combobox]');
    const picker = form.querySelector('[data-skill-picker]');
    const optionsList = form.querySelector('[data-skill-options]');
    const optionsTemplate = form.querySelector('[data-skill-option-template]');
    const customButton = form.querySelector('[data-add-custom-skill]');
    const pendingList = form.querySelector('[data-pending-skills]');
    const saveButton = form.querySelector('[data-save-selected-skills]');
    if (!combo || !optionsList || !optionsTemplate || !pendingList || !saveButton) return;

    const options = [...optionsTemplate.content.querySelectorAll('[data-skill-option]')].map((option) => ({
        id: option.dataset.skillId,
        name: option.dataset.skillName,
        type: option.dataset.skillType || '',
        source: option.dataset.skillSource || 'local',
    }));
    let localSkills = [];
    let pendingSkills = [];
    let activeOptionIndex = -1;
    let dropdownOpen = false;

    function normalize(value) {
        return String(value || '').trim().toLocaleLowerCase();
    }

    function isAlreadySaved(skill) {
        const skillName = normalize(skill.name);
        return assignedIds.has(skill.id)
            || assignedNames.has(skillName)
            || localSkills.some((row) => row.id === skill.id || normalize(row.name) === skillName)
            || pendingSkills.some((row) => row.id === skill.id || normalize(row.name) === skillName);
    }

    function render() {
        list.replaceChildren();
        localSkills.forEach((skill) => {
            const item = document.createElement('li');
            item.className = 'flex flex-wrap items-center justify-between gap-3 py-3';
            const detail = document.createElement('div');
            const name = document.createElement('p');
            name.className = 'text-sm font-semibold text-hb-navy';
            name.textContent = skill.name;
            const meta = document.createElement('p');
            meta.className = 'mt-0.5 text-xs text-hb-muted';
            meta.textContent = [skill.type, skill.level ? `${@json(__('Level'))}: ${skill.level}` : ''].filter(Boolean).join(' · ');
            detail.append(name, meta);
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'rounded-full border border-hb-border px-3 py-1.5 text-xs font-semibold text-hb-navy hover:bg-slate-50';
            remove.textContent = '×';
            remove.setAttribute('aria-label', `${@json(__('Remove from this device'))}: ${skill.name}`);
            remove.addEventListener('click', () => {
                localSkills = localSkills.filter((row) => row.id !== skill.id);
                persist();
                render();
            });
            item.append(detail, remove);
            list.append(item);
        });
        list.classList.toggle('hidden', localSkills.length === 0);
        if (emptyState) emptyState.classList.toggle('hidden', assignedIds.size > 0 || localSkills.length > 0);
        pendingList.replaceChildren();
        pendingSkills.forEach((skill) => {
            const chip = document.createElement('li');
            chip.className = 'inline-flex items-center gap-1 rounded-full bg-sky-50 px-3 py-1.5 text-sm font-medium text-sky-900';
            const label = document.createElement('span');
            label.textContent = skill.name;
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'grid size-5 place-items-center rounded-full text-sky-800 hover:bg-sky-100';
            remove.textContent = '×';
            remove.setAttribute('aria-label', `${@json(__('Remove'))}: ${skill.name}`);
            remove.addEventListener('click', () => {
                pendingSkills = pendingSkills.filter((row) => row.id !== skill.id);
                render();
                renderOptions();
            });
            chip.append(label, remove);
            pendingList.append(chip);
        });
        saveButton.disabled = pendingSkills.length === 0;
    }

    function persist() {
        try {
            localStorage.setItem(storageKey, JSON.stringify(localSkills));
            status.textContent = @json(__('Your local skills were saved on this device.'));
        } catch {
            status.textContent = @json(__('Could not save your local skills in this browser.'));
        }
    }

    function addPendingSkill(skill) {
        if (isAlreadySaved(skill)) {
            status.textContent = @json(__('This skill is already in your list.'));
            closeSuggestions();
            return;
        }
        pendingSkills.push(skill);
        combo.value = '';
        activeOptionIndex = -1;
        dropdownOpen = false;
        render();
        renderOptions();
    }

    function closeSuggestions() {
        dropdownOpen = false;
        activeOptionIndex = -1;
        renderOptions();
    }

    function addCustomSkill() {
        const name = combo.value.trim();
        if (!name) return;

        const matchingOption = options.find((option) => normalize(option.name) === normalize(name));
        if (matchingOption) {
            addPendingSkill(matchingOption);
            return;
        }

        addPendingSkill({
            id: `custom-${globalThis.crypto?.randomUUID?.() || `${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 8)}`}`,
            name,
            type: @json(__('candidate.skills.custom_skill_type')),
        });
    }

    function renderOptions() {
        const query = normalize(combo.value);
        const matchingOptions = options
            .filter((option) => ! isAlreadySaved(option) && (! query || normalize(option.name).includes(query)))
            .slice(0, 30);
        optionsList.replaceChildren();
        matchingOptions.forEach((option, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.role = 'option';
            button.id = `local-skill-option-${index}`;
            button.className = 'flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-start text-sm text-hb-text hover:bg-slate-50 focus:bg-slate-50';
            button.setAttribute('aria-selected', String(index === activeOptionIndex));
            const name = document.createElement('span');
            name.textContent = option.name;
            const type = document.createElement('span');
            type.className = 'text-xs text-hb-muted';
            type.textContent = option.type;
            button.append(name, type);
            button.addEventListener('mousedown', (event) => event.preventDefault());
            button.addEventListener('click', () => addPendingSkill(option));
            optionsList.append(button);
        });

        const exactMatch = options.some((option) => normalize(option.name) === query)
            || assignedNames.has(query)
            || localSkills.some((skill) => normalize(skill.name) === query)
            || pendingSkills.some((skill) => normalize(skill.name) === query);
        const showCustom = query !== '' && ! exactMatch;
        if (customButton) {
            customButton.hidden = ! (dropdownOpen && showCustom);
            customButton.classList.toggle('hidden', ! (dropdownOpen && showCustom));
            customButton.textContent = `${@json(__('candidate.skills.add_custom_skill'))} “${combo.value.trim()}”`;
        }
        const isOpen = dropdownOpen && (matchingOptions.length > 0 || showCustom);
        optionsList.classList.toggle('hidden', ! isOpen);
        combo.setAttribute('aria-expanded', String(isOpen));
        combo.removeAttribute('aria-activedescendant');
    }

    function savePendingSkills() {
        if (pendingSkills.length === 0) return;
        const level = form.elements.level.value;
        const skillsToSave = [...pendingSkills];
        pendingSkills = [];
        const apiSkills = apiEnabled ? skillsToSave.filter((skill) => skill.source === 'api') : [];
        skillsToSave.filter((skill) => !apiEnabled || skill.source !== 'api').forEach((skill) => {
            if (!localSkills.some((row) => normalize(row.name) === normalize(skill.name))) {
                localSkills.push({ ...skill, level });
            }
        });
        persist();
        render();
        renderOptions();
        if (apiSkills.length > 0) {
            apiSkills.forEach((skill) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'skill_ids[]';
                input.value = skill.id;
                form.append(input);
            });
            form.submit();
        } else if (apiEnabled) {
            status.textContent = @json(__('Your preset and custom skills were saved on this device.'));
        }
    }

    try {
        const saved = JSON.parse(localStorage.getItem(storageKey) || '[]');
        localSkills = Array.isArray(saved) ? saved.filter((skill) =>
            skill && typeof skill.id === 'string' && typeof skill.name === 'string'
        ) : [];
    } catch {
        status.textContent = @json(__('Could not read your local skills in this browser.'));
    }
    render();
    renderOptions();

    combo.addEventListener('input', () => {
        activeOptionIndex = -1;
        dropdownOpen = true;
        renderOptions();
    });
    combo.addEventListener('focus', () => {
        dropdownOpen = true;
        renderOptions();
    });
    combo.addEventListener('click', () => {
        dropdownOpen = true;
        renderOptions();
    });
    document.addEventListener('click', (event) => {
        if (picker && !picker.contains(event.target)) {
            closeSuggestions();
        }
    });

    combo.addEventListener('keydown', (event) => {
        const optionButtons = [...optionsList.querySelectorAll('[role="option"]')];
        if (event.key === 'ArrowDown' && optionButtons.length > 0) {
            event.preventDefault();
            activeOptionIndex = Math.min(activeOptionIndex + 1, optionButtons.length - 1);
            renderOptions();
            const activeOption = optionsList.querySelector(`#local-skill-option-${activeOptionIndex}`);
            if (activeOption) {
                combo.setAttribute('aria-activedescendant', activeOption.id);
            }
        } else if (event.key === 'Enter') {
            event.preventDefault();
            if (activeOptionIndex >= 0 && optionButtons[activeOptionIndex]) {
                const selectedOption = optionButtons[activeOptionIndex];
                if (selectedOption) selectedOption.click();
            } else {
                const exactOption = options.find((option) => normalize(option.name) === normalize(combo.value));
                if (exactOption) {
                    addPendingSkill(exactOption);
                } else {
                    addCustomSkill();
                }
            }
        } else if (event.key === 'Escape') {
            closeSuggestions();
        }
    });

    customButton?.addEventListener('click', addCustomSkill);

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        savePendingSkills();
    });
})();
</script>
@endpush
