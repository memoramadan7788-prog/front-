@extends('frontend.layouts.app')

@section('title', __('Overview'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Overview') }}</h1>
@endsection

@section('content')
<x-welcome-banner :subtitle="__('Manage your vacancies, assessments, offers and team from here.')" />
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    <x-stat-card :label="__('Vacancies')" value="→" :meta="__('Manage your job posts')" :href="route('frontend.company.vacancies.index')" />
    <x-stat-card :label="__('New vacancy')" value="→" :meta="__('Publish a new job')" :href="route('frontend.company.vacancies.create')" />
    <x-stat-card :label="__('New exam')" value="→" :meta="__('Create an assessment')" :href="route('frontend.company.exams.create')" />
    <x-stat-card :label="__('Send offer')" value="→" :meta="__('Make an offer to a candidate')" :href="route('frontend.company.offers.create')" />
    <x-stat-card :label="__('Team')" value="→" :meta="__('Approve and manage employees')" :href="route('frontend.company.employees.index')" />
    <x-stat-card :label="__('Company profile')" value="→" :meta="__('Edit company details')" :href="route('frontend.company.profile')" />
</div>
@endsection
