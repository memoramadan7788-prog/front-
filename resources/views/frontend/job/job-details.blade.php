@extends('frontend.layouts.public')

@section('title', __('Job details'))

@section('content')
<div class="hb-container max-w-3xl py-12 lg:py-16">
    @include('frontend.partials.job-details-body', [
        'job' => $job ?? [],
        'workspace' => 'public',
    ])
</div>
@endsection
