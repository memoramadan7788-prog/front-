@extends('frontend.layouts.app')

@section('title', __('common.change_password'))

@section('content')
    @include('frontend.partials.change-password-form', [
        'action' => 'frontend.company.change-password.submit',
        'cancel' => 'frontend.company.profile',
        'passwordChangeAvailable' => $passwordChangeAvailable ?? false,
    ])
@endsection
