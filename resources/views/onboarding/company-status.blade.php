@extends('layouts.app')

@section('title', $pageTitle ?? 'Company review status · Hirebridge')
@section('breadcrumb', '12 / Company workspace / Overview')

@section('page_header')
    <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">Review in progress</h1>
    <p class="mt-1 text-sm text-hb-muted">Supporting status UI — no verification was performed from this frontend screen.</p>
@endsection

@section('content')
    @php($companyName = $companyName ?? 'Northstar Labs')

    <div class="hb-mock-banner mb-6 rounded-lg text-start">
        Supporting review-state UI (not the primary Frame 12 form). Status is presentation-only.
    </div>

    <div class="grid max-w-3xl gap-4">
        <div class="rounded-xl border border-hb-pending/30 bg-hb-pending-soft px-4 py-4">
            <p class="text-sm font-semibold text-hb-pending">● Pending review</p>
            <p class="mt-1 text-sm text-hb-muted">
                When Backend Team registration is connected,
                <span class="font-medium text-hb-text">{{ $companyName }}</span>
                would appear here while documents are reviewed.
            </p>
        </div>

        <ul class="space-y-2 text-sm text-hb-muted">
            <li class="flex gap-2"><span class="text-hb-success" aria-hidden="true">☑</span> Organization details captured in UI</li>
            <li class="flex gap-2"><span class="text-hb-success" aria-hidden="true">☑</span> Verification document selected in UI</li>
            <li class="flex gap-2"><span class="text-hb-muted" aria-hidden="true">○</span> Admin document review <span class="text-xs">(Backend)</span></li>
            <li class="flex gap-2"><span class="text-hb-muted" aria-hidden="true">○</span> Workspace activation <span class="text-xs">(Backend)</span></li>
        </ul>

        <div class="flex flex-wrap gap-3 pt-2">
            <a href="{{ route('onboarding.company') }}" class="hb-btn-outline">Back to workspace setup</a>
            <a href="{{ route('login') }}" class="hb-btn-primary">Return to sign in</a>
        </div>
    </div>
@endsection
