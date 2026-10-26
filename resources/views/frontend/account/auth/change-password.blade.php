@extends('frontend.layouts.app')

@section('title', __('common.change_password'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('common.change_password') }}</h1>
@endsection

@section('content')
    @include('frontend.partials.change-password-form', [
        'action' => 'frontend.account.change-password.submit',
        'cancel' => Route::has('frontend.account.profile') ? 'frontend.account.profile' : 'frontend.workspace',
        'passwordChangeAvailable' => $passwordChangeAvailable ?? false,
    ])
@endsection
