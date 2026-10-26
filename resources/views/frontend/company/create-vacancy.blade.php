@extends('frontend.layouts.app')

@section('title', __('New vacancy'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('New vacancy') }}</h1>
@endsection

@section('content')
<x-card class="max-w-3xl">
    <x-slot:header>
        <h2 class="text-lg font-bold text-hb-navy">{{ __('New vacancy') }}</h2>
    </x-slot:header>

    
    <form method="POST" action="{{ route('frontend.company.vacancies.create.submit') }}" class="space-y-5">
        @csrf
        <x-form.input name="title" :label="__('Title')" type="text" :required="true" :error="$errors->first('title')" value="{{ old('title', data_get([], 'title', '')) }}" />
        <x-form.textarea name="description" :label="__('Description')" rows="6" :required="true" :error="$errors->first('description')">{{ old('description', data_get([], 'description', '')) }}</x-form.textarea>
        <x-form.select name="category_id" :label="__('Category')" :required="true" :error="$errors->first('category_id')">
        <option value="">{{ __('Select…') }}</option>
        @foreach (\App\Support\Rows::from($categories ?? []) as $opt)
            <option value="{{ data_get($opt, 'id') }}" @selected((string) old('category_id', data_get([], 'category_id', '')) === (string) data_get($opt, 'id'))>{{ data_get($opt, 'name') }}</option>
        @endforeach
    </x-form.select>
        <x-form.input name="location" :label="__('Location')" type="text" :required="true" :error="$errors->first('location')" value="{{ old('location', data_get([], 'location', '')) }}" />
        <x-form.select name="work_mode" :label="__('Work mode')" :required="true" :error="$errors->first('work_mode')">
        <option value="">{{ __('Select…') }}</option>
        <option value="onsite" @selected((string) old('work_mode', data_get([], 'work_mode', '')) === 'onsite')>{{ __('On-site') }}</option>
        <option value="remote" @selected((string) old('work_mode', data_get([], 'work_mode', '')) === 'remote')>{{ __('Remote') }}</option>
        <option value="hybrid" @selected((string) old('work_mode', data_get([], 'work_mode', '')) === 'hybrid')>{{ __('Hybrid') }}</option>
    </x-form.select>
        <x-form.select name="employment_type" :label="__('Employment type')" :required="true" :error="$errors->first('employment_type')">
        <option value="">{{ __('Select…') }}</option>
        <option value="full_time" @selected((string) old('employment_type', data_get([], 'employment_type', '')) === 'full_time')>{{ __('Full time') }}</option>
        <option value="part_time" @selected((string) old('employment_type', data_get([], 'employment_type', '')) === 'part_time')>{{ __('Part time') }}</option>
        <option value="contract" @selected((string) old('employment_type', data_get([], 'employment_type', '')) === 'contract')>{{ __('Contract') }}</option>
        <option value="internship" @selected((string) old('employment_type', data_get([], 'employment_type', '')) === 'internship')>{{ __('Internship') }}</option>
    </x-form.select>
        <x-form.input name="salary_min" :label="__('Minimum salary')" type="number" :error="$errors->first('salary_min')" value="{{ old('salary_min', data_get([], 'salary_min', '')) }}" min="0" />
        <x-form.input name="salary_max" :label="__('Maximum salary')" type="number" :error="$errors->first('salary_max')" value="{{ old('salary_max', data_get([], 'salary_max', '')) }}" min="0" />
        <x-form.select name="currency" :label="__('Currency')" :error="$errors->first('currency')">
        <option value="">{{ __('Select…') }}</option>
        <option value="EGP" @selected((string) old('currency', data_get([], 'currency', '')) === 'EGP')>{{ __('EGP') }}</option>
        <option value="USD" @selected((string) old('currency', data_get([], 'currency', '')) === 'USD')>{{ __('USD') }}</option>
        <option value="EUR" @selected((string) old('currency', data_get([], 'currency', '')) === 'EUR')>{{ __('EUR') }}</option>
        <option value="SAR" @selected((string) old('currency', data_get([], 'currency', '')) === 'SAR')>{{ __('SAR') }}</option>
        <option value="AED" @selected((string) old('currency', data_get([], 'currency', '')) === 'AED')>{{ __('AED') }}</option>
    </x-form.select>
        <x-form.select name="salary_period" :label="__('Salary period')" :error="$errors->first('salary_period')">
        <option value="">{{ __('Select…') }}</option>
        <option value="hour" @selected((string) old('salary_period', data_get([], 'salary_period', '')) === 'hour')>{{ __('Hourly') }}</option>
        <option value="month" @selected((string) old('salary_period', data_get([], 'salary_period', '')) === 'month')>{{ __('Monthly') }}</option>
        <option value="year" @selected((string) old('salary_period', data_get([], 'salary_period', '')) === 'year')>{{ __('Yearly') }}</option>
    </x-form.select>
        <x-form.input name="experience_years" :label="__('Years of experience')" type="number" :error="$errors->first('experience_years')" value="{{ old('experience_years', data_get([], 'experience_years', '')) }}" min="0" />
        <x-form.input name="department" :label="__('Department')" type="text" :error="$errors->first('department')" value="{{ old('department', data_get([], 'department', '')) }}" />
        <x-form.input name="openings_count" :label="__('Openings')" type="number" :required="true" :error="$errors->first('openings_count')" value="{{ old('openings_count', data_get([], 'openings_count', 1)) }}" min="1" />
        <x-form.input name="deadline" :label="__('Deadline')" type="date" :error="$errors->first('deadline')" value="{{ old('deadline', data_get([], 'deadline', '')) }}" />
        <x-form.select name="visibility" :label="__('Visibility')" :required="true" :error="$errors->first('visibility')">
        <option value="">{{ __('Select…') }}</option>
        <option value="public" @selected((string) old('visibility', data_get([], 'visibility', '')) === 'public')>{{ __('Public') }}</option>
        <option value="private" @selected((string) old('visibility', data_get([], 'visibility', '')) === 'private')>{{ __('Private') }}</option>
    </x-form.select>
        <x-form.select name="private_apply_mode" :label="__('Who can apply (private jobs)')" :error="$errors->first('private_apply_mode')">
        <option value="">{{ __('Select…') }}</option>
        <option value="all_employees" @selected((string) old('private_apply_mode', data_get([], 'private_apply_mode', '')) === 'all_employees')>{{ __('All employees') }}</option>
        <option value="nominated_only" @selected((string) old('private_apply_mode', data_get([], 'private_apply_mode', '')) === 'nominated_only')>{{ __('Nominated only') }}</option>
    </x-form.select>
        <div class="flex flex-wrap items-center gap-3 pt-1">
            <x-button type="submit">{{ __('Save') }}</x-button>
            <x-button variant="light" :href="route('frontend.company.vacancies.index')">{{ __('Cancel') }}</x-button>
        </div>
    </form>
    
</x-card>
@endsection
