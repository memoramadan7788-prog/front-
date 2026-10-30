@extends('frontend.layouts.app')

@section('title', __('Application details'))

@php
    $application = is_array($application ?? null) ? \App\Support\Rows::get($application, 'data', $application) : [];
    $status = strtolower((string) data_get($application, 'status', 'submitted'));
    $jobTitle = data_get($application, 'job.title') ?? data_get($application, 'job_title') ?? __('Application');
    $companyName = data_get($application, 'job.company.name') ?? data_get($application, 'company.name') ?? data_get($application, 'company_name');
    $activities = \App\Support\Rows::from(data_get($application, 'activity', data_get($application, 'activities', [])));
    $documents = \App\Support\Rows::from(data_get($application, 'submitted_materials', data_get($application, 'documents', [])));
    $contact = data_get($application, 'hiring_contact') ?? data_get($application, 'recruiter') ?? data_get($application, 'company.contact');
    $interviews = \App\Support\Rows::from(data_get($application, 'interviews', []));
    $stages = ['applied', 'screening', 'shortlisted', 'interviewing', 'offered', 'hired'];
    $progressStatus = $status === 'submitted' ? 'applied' : $status;
    $stageIndex = array_search($progressStatus, $stages, true);
    $terminal = in_array($status, ['withdrawn', 'rejected'], true);
    $canWithdraw = ! $terminal && ! in_array($status, ['hired', 'closed'], true);
@endphp

@section('page_header')
    <div>
        <p class="text-sm font-semibold text-hb-blue">{{ __('Application') }} #{{ data_get($application, 'id', '—') }}</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-hb-navy">{{ $jobTitle }}</h1>
        @if ($companyName)<p class="mt-1 text-sm text-hb-muted">{{ $companyName }}</p>@endif
    </div>
@endsection

@section('page_actions')
    <x-button variant="light" size="md" :href="route('frontend.candidate.applications.index')">{{ __('Back to applications') }}</x-button>
@endsection

@section('content')
<div class="space-y-5">
    <section class="rounded-2xl border border-hb-border bg-white p-5 shadow-sm" aria-labelledby="application-progress-title">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 id="application-progress-title" class="font-bold text-hb-navy">{{ __('Application progress') }}</h2>
                <p class="mt-1 text-sm text-hb-muted">{{ __('Current status') }}: <x-badge :variant="\App\Support\Rows::badgeVariant($status)" :dot="true">{{ \Illuminate\Support\Str::headline($status) }}</x-badge></p>
            </div>
            @if ($canWithdraw)
                <form method="POST" action="{{ route('frontend.candidate.applications.withdraw', ['applicationId' => data_get($application, 'id')]) }}" onsubmit="return confirm('{{ __('Withdraw this application? This cannot be undone.') }}')">
                    @csrf
                    <x-button type="submit" variant="destructive">{{ __('Withdraw application') }}</x-button>
                </form>
            @endif
        </div>
        <ol class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6" aria-label="{{ __('Application stage timeline') }}">
            @foreach ($stages as $index => $stage)
                @php $complete = ! $terminal && $stageIndex !== false && $index <= $stageIndex; @endphp
                <li class="flex items-center gap-2 text-xs {{ $complete ? 'font-semibold text-emerald-800' : 'text-hb-muted' }}">
                    <span class="grid size-7 shrink-0 place-items-center rounded-full {{ $complete ? 'bg-emerald-100' : 'bg-slate-100' }}" aria-hidden="true">{{ $complete ? '✓' : $index + 1 }}</span>
                    <span>{{ \Illuminate\Support\Str::headline($stage) }}</span>
                </li>
            @endforeach
        </ol>
        @if ($terminal)
            <p class="mt-4 rounded-lg bg-slate-50 p-3 text-sm text-slate-600">{{ __('This application is :status.', ['status' => $status]) }}</p>
        @endif
    </section>

    <div class="flex gap-2 overflow-x-auto border-b border-hb-border" role="tablist" aria-label="{{ __('Application details sections') }}">
        @foreach (['overview' => __('Overview'), 'documents' => __('Documents'), 'activity' => __('Activity')] as $tab => $label)
            <button type="button" id="tab-{{ $tab }}" role="tab" aria-controls="panel-{{ $tab }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" tabindex="{{ $loop->first ? '0' : '-1' }}" data-application-tab="{{ $tab }}" class="shrink-0 border-b-2 px-4 py-3 text-sm font-semibold {{ $loop->first ? 'border-hb-blue text-hb-blue' : 'border-transparent text-hb-muted hover:text-hb-navy' }}">{{ $label }}</button>
        @endforeach
    </div>

    <section id="panel-overview" role="tabpanel" aria-labelledby="tab-overview" data-application-panel="overview" class="grid gap-5 lg:grid-cols-[minmax(0,1.4fr)_minmax(16rem,0.8fr)]">
        <x-card>
            <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('Application overview') }}</h2></x-slot:header>
            @include('frontend.partials.record', ['record' => $application])
        </x-card>
        <div class="space-y-5">
            <x-card>
                <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('Interview') }}</h2></x-slot:header>
                @forelse ($interviews as $interview)
                    <div class="rounded-xl border border-hb-border p-4">
                        <p class="font-semibold text-hb-navy">{{ data_get($interview, 'title') ?? data_get($interview, 'interview_type') ?? __('Interview') }}</p>
                        <p class="mt-1 text-sm text-hb-muted">{{ \App\Support\Rows::date(data_get($interview, 'scheduled_at') ?? data_get($interview, 'start_at')) }}</p>
                        <p class="mt-1 text-sm text-hb-muted">{{ data_get($interview, 'status', __('Details will appear when provided by the hiring team.')) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-hb-muted">{{ __('No interview details have been shared yet.') }}</p>
                @endforelse
            </x-card>
            <x-card>
                <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('Hiring contact') }}</h2></x-slot:header>
                @if (is_array($contact))
                    <p class="font-semibold text-hb-navy">{{ data_get($contact, 'name') ?? __('Recruiter') }}</p>
                    @if (filter_var(data_get($contact, 'email'), FILTER_VALIDATE_EMAIL))
                        <a class="mt-1 inline-flex text-sm font-semibold text-hb-blue hover:underline" href="mailto:{{ data_get($contact, 'email') }}?subject={{ rawurlencode(__('Question about :job', ['job' => $jobTitle])) }}">{{ __('Message hiring contact') }}</a>
                    @else
                        <p class="mt-1 text-sm text-hb-muted">{{ __('Contact details are not available yet.') }}</p>
                    @endif
                @else
                    <p class="text-sm text-hb-muted">{{ __('Contact details are not available yet.') }}</p>
                @endif
            </x-card>
        </div>
    </section>

    <section id="panel-documents" role="tabpanel" aria-labelledby="tab-documents" data-application-panel="documents" class="hidden">
        <x-card>
            <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('Submitted materials') }}</h2></x-slot:header>
            @if (count($documents))
                <ul class="grid gap-3 sm:grid-cols-2">
                    @foreach ($documents as $document)
                        @php
                            $documentName = data_get($document, 'name') ?? data_get($document, 'filename') ?? data_get($document, 'title') ?? __('Submitted document');
                            $documentUrl = data_get($document, 'url') ?? data_get($document, 'download_url');
                        @endphp
                        <li class="flex items-center justify-between gap-3 rounded-xl border border-hb-border p-4">
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-hb-navy">{{ $documentName }}</span>
                                <span class="mt-1 block text-xs text-hb-muted">{{ data_get($document, 'type') ?? data_get($document, 'mime_type') ?? __('Submitted with this application') }}</span>
                            </span>
                            @if (is_string($documentUrl) && in_array(parse_url($documentUrl, PHP_URL_SCHEME), ['http', 'https'], true))
                                <a href="{{ $documentUrl }}" target="_blank" rel="noopener noreferrer" class="shrink-0 text-sm font-semibold text-hb-blue hover:underline">{{ __('Open') }}</a>
                            @else
                                <span class="shrink-0 text-xs text-hb-muted">{{ __('Preview unavailable') }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="rounded-xl bg-slate-50 p-4 text-sm text-hb-muted">{{ __('No submitted documents were included in the application response.') }}</p>
            @endif
        </x-card>
    </section>

    <section id="panel-activity" role="tabpanel" aria-labelledby="tab-activity" data-application-panel="activity" class="hidden">
        <x-card>
            <x-slot:header><h2 class="text-lg font-bold text-hb-navy">{{ __('Activity history') }}</h2></x-slot:header>
            @if (count($activities))
                <ol class="space-y-4">
                    @foreach ($activities as $activity)
                        <li class="flex gap-3">
                            <span class="mt-1 size-2 shrink-0 rounded-full bg-hb-blue" aria-hidden="true"></span>
                            <div>
                                <p class="text-sm font-semibold text-hb-navy">{{ data_get($activity, 'title') ?? data_get($activity, 'event') ?? data_get($activity, 'status') ?? __('Application updated') }}</p>
                                <p class="mt-1 text-sm text-hb-muted">{{ data_get($activity, 'description') ?? data_get($activity, 'message') }}</p>
                                <time class="mt-1 block text-xs text-hb-muted">{{ \App\Support\Rows::date(data_get($activity, 'created_at') ?? data_get($activity, 'occurred_at')) }}</time>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="rounded-xl bg-slate-50 p-4 text-sm text-hb-muted">{{ __('No activity history was included in the application response.') }}</p>
            @endif
        </x-card>
    </section>
</div>

<script>
    (() => {
        const tabs = [...document.querySelectorAll('[data-application-tab]')];
        const panels = [...document.querySelectorAll('[data-application-panel]')];
        tabs.forEach((tab) => tab.addEventListener('click', () => {
            tabs.forEach((item) => {
                const active = item === tab;
                item.setAttribute('aria-selected', String(active));
                item.tabIndex = active ? 0 : -1;
                item.classList.toggle('border-hb-blue', active);
                item.classList.toggle('text-hb-blue', active);
                item.classList.toggle('border-transparent', !active);
                item.classList.toggle('text-hb-muted', !active);
            });
            panels.forEach((panel) => panel.classList.toggle('hidden', panel.dataset.applicationPanel !== tab.dataset.applicationTab));
        }));
    })();
</script>
@endsection
