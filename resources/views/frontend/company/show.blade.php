@extends('frontend.layouts.app')

@section('content')
<style>[x-cloak]{display:none !important;}</style>

<div class="max-w-7xl mx-auto px-4 py-8 overflow-x-hidden" x-data="{ activeTab: 'overview' }">
    @if(isset($company))
        @php
            $logo = data_get($company, 'logo') ?: data_get($company, 'logo_url');
        @endphp

        {{-- Company Header Section --}}
        <div class="bg-white shadow rounded-lg p-6 mb-6 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                @if(!empty($logo))
                    <img src="{{ $logo }}" alt="{{ data_get($company, 'name') }}" class="w-20 h-20 object-cover rounded-full border shadow-sm">
                @elseif(!empty($company->name))
                    <div class="w-20 h-20 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-2xl shadow-sm">
                        {{ mb_substr($company->name, 0, 1) }}
                    </div>
                @endif
                <div>
                    @if(!empty($company->name))
                        <h1 class="text-3xl font-bold text-gray-900">{{ $company->name }}</h1>
                    @endif
                    @if(!empty($company->industry))
                        <p class="text-gray-600 mt-1">{{ $company->industry }}</p>
                    @endif
                    @if(!empty($company->address))
                        <p class="text-sm text-gray-500 mt-1 flex items-center gap-1">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            {{ $company->address }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                @if(!empty($company->website))
                    <a href="{{ $company->website }}" target="_blank" rel="noopener" class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        {{ __('Visit Website') }}
                    </a>
                @endif
                @if(!empty($company->contact_email))
                    <a href="mailto:{{ $company->contact_email }}" class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 shadow-sm">
                        {{ __('Contact Company') }}
                    </a>
                @endif
            </div>
        </div>

        @php
            $tabs = [
                'overview'  => __('Overview'),
                'culture'   => __('Culture'),
                'roles'     => __('Open Roles'),
                'team'      => __('Hiring Team'),
                'locations' => __('Locations'),
            ];
        @endphp

        {{-- Tabs Navigation & Content Box --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            {{-- Tabs Headers --}}
            <div class="border-b border-gray-200 px-6">
                <nav class="flex flex-wrap items-end gap-8" role="tablist" aria-label="Tabs">
                    @foreach($tabs as $key => $label)
                        <button type="button"
                                role="tab"
                                @click="activeTab = '{{ $key }}'"
                                :aria-selected="activeTab === '{{ $key }}'"
                                :class="activeTab === '{{ $key }}'
                                    ? 'border-blue-600 text-blue-600 font-bold text-lg'
                                    : 'border-transparent text-gray-500 font-medium text-sm hover:text-blue-600 hover:font-bold hover:border-blue-300'"
                                class="whitespace-nowrap py-4 px-1 -mb-px border-b-2 transition-all duration-200 ease-out focus:outline-none cursor-pointer">
                            {{ $label }}
                        </button>
                    @endforeach
                </nav>
            </div>

            {{-- Tabs Body --}}
            <div class="p-6">
                {{-- Overview Tab --}}
                <div x-show="activeTab === 'overview'" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('About Company') }}</h3>
                    @if(!empty($company->about))
                        <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $company->about }}</p>
                    @else
                        <p class="text-gray-500 text-sm py-4">{{ __('No description provided by the company yet.') }}</p>
                    @endif
                </div>

                {{-- Culture Tab --}}
                <div x-show="activeTab === 'culture'" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Our Culture & Values') }}</h3>
                    @if(!empty($company->culture))
                        <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $company->culture }}</p>
                    @else
                        <p class="text-gray-500 text-sm py-4">{{ __('No culture details provided yet.') }}</p>
                    @endif
                </div>

                {{-- Open Roles Tab --}}
                <div x-show="activeTab === 'roles'" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Open Positions') }}</h3>
                    @if(!empty($company->jobs) && count($company->jobs) > 0)
                        <div class="grid gap-4">
                            @foreach($company->jobs as $job)
                                @php
                                    $jobId = data_get($job, 'id');
                                    $jobTitle = data_get($job, 'title') ?: data_get($job, 'name');
                                    $jobLocation = data_get($job, 'location');
                                    $jobType = data_get($job, 'type') ?: data_get($job, 'employment_type');
                                @endphp
                                <div class="border rounded-lg p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-gray-50 hover:bg-gray-100 transition">
                                    <div>
                                        <h4 class="font-semibold text-gray-900 text-base">{{ $jobTitle }}</h4>
                                        <p class="text-sm text-gray-500">
                                            {{ $jobLocation }}
                                            @if($jobLocation && $jobType) • @endif
                                            <span class="font-medium text-blue-600">{{ $jobType }}</span>
                                        </p>
                                    </div>
                                    @if($jobId)
                                        <a href="{{ route('frontend.jobs.show', ['jobId' => $jobId]) }}" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 transition">
                                            {{ __('Apply Now') }}
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-sm py-4">{{ __('No open roles available at the moment.') }}</p>
                    @endif
                </div>

                {{-- Hiring Team Tab --}}
                <div x-show="activeTab === 'team'" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Meet The Hiring Team') }}</h3>
                    @if(!empty($company->teamMembers) && count($company->teamMembers) > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($company->teamMembers as $member)
                                @php
                                    $memberName = data_get($member, 'name') ?: data_get($member, 'full_name');
                                    $memberRole = data_get($member, 'role') ?: data_get($member, 'title') ?: data_get($member, 'position');
                                @endphp
                                <div class="border rounded-lg p-4 flex items-center gap-3 bg-white shadow-sm">
                                    <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-700">
                                        {{ mb_substr((string) $memberName, 0, 1) }}
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900 text-sm">{{ $memberName }}</h4>
                                        <p class="text-xs text-gray-500">{{ $memberRole }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-sm py-4">{{ __('No team members listed yet.') }}</p>
                    @endif
                </div>

                {{-- Locations Tab --}}
                <div x-show="activeTab === 'locations'" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Our Locations') }}</h3>
                    @if(!empty($company->locations) && count($company->locations) > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($company->locations as $location)
                                @php
                                    $isText = is_string($location);
                                    $locCity = $isText ? $location : data_get($location, 'city');
                                    $locAddress = $isText ? null : data_get($location, 'address');
                                @endphp
                                <div class="border rounded-lg p-4 bg-white shadow-sm">
                                    <h4 class="font-semibold text-gray-900 text-sm flex items-center gap-2">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        {{ $locCity }}
                                    </h4>
                                    @if($locAddress)
                                        <p class="text-sm text-gray-600 mt-1">{{ $locAddress }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-sm py-4">{{ __('No locations specified yet.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="bg-white shadow rounded-lg p-8 text-center">
            <h2 class="text-2xl font-bold text-gray-800">{{ __('Company Not Found') }}</h2>
            <p class="text-gray-600 mt-2">{{ __('The requested company profile is unavailable or does not exist.') }}</p>
        </div>
    @endif
</div>
@endsection