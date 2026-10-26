@extends('frontend.layouts.app')

@section('title', __('common.change_password'))

@section('content')
    @include('frontend.partials.change-password-form', [
        'action' => 'frontend.candidate.change-password.submit',
        'cancel' => 'frontend.candidate.profile',
        'passwordChangeAvailable' => $passwordChangeAvailable ?? false,
    ])
@endsection
