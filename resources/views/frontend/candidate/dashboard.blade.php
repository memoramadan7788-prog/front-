@extends('frontend.layouts.app')

@section('title', __('Overview'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Overview') }}</h1>
@endsection

@section('content')
<x-welcome-banner :subtitle="__('Pick up where you left off: jobs, exams, interviews and offers in one place.')" />
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    <x-stat-card :label="__('Discover jobs')" value="→" :meta="__('Browse open vacancies')" :href="route('frontend.candidate.vacancies.index')" />
    <x-stat-card :label="__('My applications')" value="→" :meta="__('Track your applications')" :href="route('frontend.candidate.applications.index')" />
    <x-stat-card :label="__('Assessments')" value="→" :meta="__('Take your exams')" :href="route('frontend.candidate.exams.index')" />
    <x-stat-card :label="__('Interviews')" value="→" :meta="__('Book and manage interviews')" :href="route('frontend.candidate.interviews.index')" />
    <x-stat-card :label="__('Offers')" value="→" :meta="__('Review your offers')" :href="route('frontend.candidate.offers.index')" />
    <x-stat-card :label="__('Profile & resume')" value="→" :meta="__('Keep your profile up to date')" :href="route('frontend.candidate.profile')" />
</div>
@endsection
