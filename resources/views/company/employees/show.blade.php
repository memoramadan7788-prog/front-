@extends('layouts.app')

@section('title', $pageTitle ?? __('company.employees.show_page_title', ['name' => $member['name']]))
@section('breadcrumb', __('company.employees.show_breadcrumb'))

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy sm:text-3xl">{{ $member['name'] }}</h1>
    <p class="mt-1 text-sm text-hb-muted">{{ $member['role'] }} · {{ $member['status'] }}</p>
@endsection

@section('page_actions')
    <a href="{{ route('company.employees.index') }}" class="hb-btn-outline">{{ __('company.employees.back_to_team') }}</a>
@endsection

@section('content')
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_300px]">
        <section class="hb-surface p-5 sm:p-6" aria-labelledby="member-details-heading">
            <h2 id="member-details-heading" class="text-lg font-semibold text-hb-navy">{{ __('company.employees.view_profile') }}</h2>
            <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="hb-label">{{ __('company.employees.member_email') }}</dt>
                    <dd class="mt-1 text-sm text-hb-text" dir="ltr">{{ $member['email'] }}</dd>
                </div>
                <div>
                    <dt class="hb-label">{{ __('company.employees.member_role') }}</dt>
                    <dd class="mt-1 text-sm text-hb-text">{{ $member['role'] }}</dd>
                </div>
                <div>
                    <dt class="hb-label">{{ __('company.employees.member_scope') }}</dt>
                    <dd class="mt-1 text-sm text-hb-text">{{ $member['scope'] }}</dd>
                </div>
                <div>
                    <dt class="hb-label">{{ __('company.employees.member_status') }}</dt>
                    <dd class="mt-1 text-sm text-hb-text">{{ $member['status'] }}</dd>
                </div>
            </dl>
        </section>

        <aside class="hb-surface p-5" aria-labelledby="member-boundaries-heading">
            <h2 id="member-boundaries-heading" class="text-base font-semibold text-hb-navy">{{ __('company.employees.permissions_heading') }}</h2>
            <ul class="mt-4 space-y-3 text-sm text-hb-muted">
                @foreach ($team['roles'] as $role)
                    <li>
                        <p class="font-semibold text-hb-navy">{{ $role['title'] }}</p>
                        <p class="mt-1">{{ $role['body'] }}</p>
                    </li>
                @endforeach
            </ul>
        </aside>
    </div>

    <div class="mt-5 rounded-lg border border-hb-blue/20 bg-hb-info-soft/50 px-4 py-3 text-sm text-hb-text" role="note">
        <p class="font-semibold text-hb-navy">{{ __('company.employees.audit_heading') }}</p>
        <p class="mt-1 text-hb-muted">{{ $team['audit_body'] }}</p>
    </div>

    <p class="mt-8 text-center text-xs text-hb-muted">{{ $team['footer'] }}</p>
@endsection
