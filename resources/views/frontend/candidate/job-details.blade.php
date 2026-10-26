@extends('frontend.layouts.app')

@section('title', __('Job details'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Job details') }}</h1>
@endsection

@section('content')
<div class="max-w-3xl">
    @include('frontend.partials.job-details-body', [
        'job' => $job ?? [],
        'workspace' => 'candidate',
    ])
</div>
@endsection
