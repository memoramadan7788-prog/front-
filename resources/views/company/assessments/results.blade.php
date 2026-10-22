@extends('layouts.app')

@section('title', $pageTitle ?? __('company.assessments.results_page_title', ['title' => $assessment['title']]))
@section('breadcrumb', __('company.assessments.results_breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ __('company.assessments.results_heading') }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ $assessment['title'] }} · {{ $assessment['job_code'] }}</p>
@endsection

@section('page_actions')
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('company.assessments.show', $assessment['id']) }}" class="hb-btn-outline">{{ __('company.assessments.view') }}</a>
        <a href="{{ route('company.assessments.index') }}" class="hb-btn-outline">{{ __('company.assessments.back') }}</a>
    </div>
@endsection

@section('content')
    <section class="hb-surface overflow-hidden" aria-labelledby="results-heading">
        <div class="border-b border-hb-border px-5 py-4">
            <h2 id="results-heading" class="text-lg font-semibold text-hb-navy">{{ $assessment['title'] }}</h2>
            <p class="mt-1 text-sm text-hb-muted">
                {{ __('company.assessments.passing_score') }}: <span dir="ltr">{{ $assessment['passing_score'] }}%</span>
            </p>
        </div>

        @if (count($assessment['results']) === 0)
            <p class="px-5 py-10 text-center text-sm text-hb-muted">{{ __('company.assessments.no_results') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <caption class="sr-only">{{ __('company.assessments.results_heading') }}</caption>
                    <thead class="bg-hb-page text-start text-hb-muted">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold">{{ __('company.assessments.assignees_heading') }}</th>
                            <th scope="col" class="px-5 py-3 font-semibold">{{ __('company.assessments.score') }}</th>
                            <th scope="col" class="px-5 py-3 font-semibold">{{ __('company.assessments.attempt') }}</th>
                            <th scope="col" class="px-5 py-3 font-semibold">{{ __('company.assessments.completed_at') }}</th>
                            <th scope="col" class="px-5 py-3 font-semibold">{{ __('company.assessments.pass') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-hb-border">
                        @foreach ($assessment['results'] as $row)
                            <tr>
                                <th scope="row" class="px-5 py-3 text-start font-semibold text-hb-navy">
                                    @if (! empty($row['candidate_id']))
                                        <a href="{{ route('company.applicants.show', $row['candidate_id']) }}" class="text-hb-blue hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-hb-blue/40">
                                            {{ $row['candidate'] }}
                                        </a>
                                    @else
                                        {{ $row['candidate'] }}
                                    @endif
                                </th>
                                <td class="px-5 py-3 text-hb-text" dir="ltr">{{ $row['score'] }}%</td>
                                <td class="px-5 py-3 text-hb-text" dir="ltr">{{ $row['attempt'] }}</td>
                                <td class="px-5 py-3 text-hb-text">{{ $row['completed_at'] }}</td>
                                <td class="px-5 py-3">
                                    <x-badge :variant="$row['pass'] ? 'success' : 'error'" :dot="true">
                                        {{ $row['pass'] ? __('company.assessments.pass') : __('company.assessments.fail') }}
                                    </x-badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <p class="mt-8 text-center text-xs text-hb-muted">Hirebridge · Private workspace · All times Central unless noted · October 20, 2026</p>
@endsection
