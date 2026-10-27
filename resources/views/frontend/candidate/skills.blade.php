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
        @elseif (empty($assignedSkills))
            <div class="text-sm text-hb-muted" role="status" data-skills-assigned-empty>
                <p class="font-semibold text-hb-navy">{{ __('candidate.skills.empty_title') }}</p>
                <p class="mt-1">{{ __('candidate.skills.empty_body') }}</p>
            </div>
        @else
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
                        <form method="POST" action="{{ route('frontend.candidate.skills.remove', ['skillId' => $id]) }}" data-skills-remove-form>
                            @csrf
                            <x-button type="submit" variant="destructive" size="sm" data-skills-remove-submit>
                                {{ __('candidate.skills.remove') }}
                            </x-button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-card>

    <x-card class="max-w-3xl">
        <x-slot:header>
            <h2 class="text-lg font-bold text-hb-navy">{{ __('candidate.skills.add_heading') }}</h2>
            <p class="mt-1 text-sm text-hb-muted">{{ __('candidate.skills.add_intro') }}</p>
        </x-slot:header>

        @unless ($skillsAvailable)
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

            <form method="POST" action="{{ route('frontend.candidate.skills.add.submit') }}" class="space-y-5" data-skills-add-form autocomplete="off">
                @csrf
                <x-form.select name="skill_id" :label="__('candidate.skills.skill_label')" :required="true" :error="$errors->first('skill_id')">
                    <option value="">{{ __('candidate.skills.select_skill') }}</option>
                    @forelse ($catalogSkills as $opt)
                        <option value="{{ data_get($opt, 'id') }}" @selected((string) old('skill_id') === (string) data_get($opt, 'id'))>
                            {{ data_get($opt, 'name') }}@if (filled(data_get($opt, 'type'))) ({{ data_get($opt, 'type') }})@endif
                        </option>
                    @empty
                        <option value="" disabled>{{ __('candidate.skills.catalog_empty') }}</option>
                    @endforelse
                </x-form.select>

                <x-form.select name="level" :label="__('candidate.skills.level_field')" :error="$errors->first('level')">
                    <option value="">{{ __('candidate.skills.level_optional') }}</option>
                    @foreach (['beginner', 'intermediate', 'advanced', 'expert'] as $lvl)
                        <option value="{{ $lvl }}" @selected((string) old('level') === $lvl)>{{ __('candidate.skills.levels.'.$lvl) }}</option>
                    @endforeach
                </x-form.select>

                <div class="flex flex-wrap items-center gap-3 pt-1">
                    <x-button type="submit" data-skills-add-submit :disabled="empty($catalogSkills)">
                        <span data-skills-add-idle>{{ __('candidate.skills.save') }}</span>
                        <span class="hidden items-center gap-2" data-skills-add-loading aria-hidden="true">
                            <span class="inline-block h-3.5 w-3.5 animate-spin rounded-full border-2 border-current border-r-transparent"></span>
                            {{ __('candidate.skills.saving') }}
                        </span>
                    </x-button>
                    <x-button variant="light" :href="route('frontend.candidate.profile')">{{ __('Cancel') }}</x-button>
                </div>
            </form>
        @endunless
    </x-card>
</div>
@endsection
