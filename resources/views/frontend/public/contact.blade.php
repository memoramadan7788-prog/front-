@extends('frontend.layouts.public')

@section('title', __('public.contact.page_title'))

@section('content')
    <section class="relative overflow-hidden bg-hb-page">
        <div class="hb-container py-12 lg:py-20">
            <p class="inline-flex rounded-full border border-hb-border bg-hb-surface px-3.5 py-1.5 text-xs font-semibold text-hb-blue uppercase">{{ __('public.contact.eyebrow') }}</p>
            <div class="mt-4 grid gap-10 lg:grid-cols-[minmax(0,1fr)_360px]">
                <div>
                    <h1 class="text-4xl font-semibold tracking-tight text-hb-navy sm:text-5xl">{{ __('public.contact.title') }}</h1>
                    <p class="mt-4 max-w-2xl text-base leading-relaxed text-hb-muted">{{ __('public.contact.general_intro') }}</p>

                    <form id="contact-form" method="POST" action="{{ route('frontend.contact.submit') }}" class="mt-8 hb-surface space-y-5 p-6 shadow-hb-md lg:p-9" novalidate data-public-form>
                        @csrf
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-lg font-semibold text-hb-navy">{{ __('public.contact.general_form_title') }}</h2>
                            <span class="rounded-full bg-hb-pending-soft px-2 py-0.5 text-[10px] font-semibold text-hb-pending uppercase">{{ __('public.contact.badge') }}</span>
                        </div>

                        @if (session('contact_submitted'))
                            <p class="rounded-lg border border-hb-success/40 bg-hb-success-soft px-4 py-3 text-sm text-hb-success" role="status">{{ __('public.contact.success') }}</p>
                        @endif
                        @if ($errors->has('error'))
                            <p class="rounded-lg border border-hb-error/40 bg-hb-error-soft px-4 py-3 text-sm text-hb-error" role="alert">{{ $errors->first('error') }}</p>
                        @endif

                        @guest
                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="name" class="hb-label">{{ __('public.contact.name') }} <span class="text-hb-error">*</span></label>
                                    <input id="name" name="name" type="text" required autocomplete="name" value="{{ old('name') }}" class="hb-input mt-1.5" aria-describedby="name-error">
                                    <p id="name-error" class="mt-1 text-xs text-hb-error" data-error-for="name">{{ $errors->first('name') }}</p>
                                </div>
                                <div>
                                    <label for="email" class="hb-label">{{ __('public.contact.email') }} <span class="text-hb-error">*</span></label>
                                    <input id="email" name="email" type="email" required autocomplete="email" value="{{ old('email') }}" class="hb-input mt-1.5" aria-describedby="email-error">
                                    <p id="email-error" class="mt-1 text-xs text-hb-error" data-error-for="email">{{ $errors->first('email') }}</p>
                                </div>
                            </div>
                        @else
                            <div class="rounded-lg bg-hb-page p-4 text-sm text-hb-text">
                                <p class="font-semibold">{{ auth()->user()->name }}</p>
                                <p class="mt-1 text-hb-muted">{{ auth()->user()->email }}</p>
                            </div>
                        @endguest

                        <div>
                            <label for="subject" class="hb-label">{{ __('public.contact.subject') }} <span class="text-hb-error">*</span></label>
                            <input id="subject" name="subject" type="text" required value="{{ old('subject') }}" class="hb-input mt-1.5" aria-describedby="subject-error">
                            <p id="subject-error" class="mt-1 text-xs text-hb-error" data-error-for="subject">{{ $errors->first('subject') }}</p>
                        </div>
                        <div>
                            <label for="description" class="hb-label">{{ __('public.contact.description') }} <span class="text-hb-error">*</span></label>
                            <textarea id="description" name="description" rows="6" required class="hb-input mt-1.5" aria-describedby="description-error">{{ old('description') }}</textarea>
                            <p id="description-error" class="mt-1 text-xs text-hb-error" data-error-for="description">{{ $errors->first('description') }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <x-button type="submit" variant="primary" data-submit-button>
                                <span data-submit-label>{{ __('public.contact.submit_legacy') }}</span>
                                <span class="hidden" data-submit-loading>{{ __('public.contact.sending') }}</span>
                            </x-button>
                            <p class="text-xs text-hb-muted">{{ __('common.required_fields') }}</p>
                        </div>
                    </form>
                </div>
                <aside class="space-y-6">
                    <div class="hb-surface p-6">
                        <h2 class="text-lg font-semibold text-hb-navy">{{ __('public.contact.consultation_link_title') }}</h2>
                        <p class="mt-3 text-sm leading-relaxed text-hb-muted">{{ __('public.contact.consultation_link_body') }}</p>
                        <a href="{{ route('frontend.consultation') }}" class="mt-5 inline-flex font-semibold text-hb-blue hover:underline">{{ __('public.contact.consultation_link') }}</a>
                    </div>
                    <div class="text-sm text-hb-muted">
                        <p>{{ __('public.contact.already_using') }} <a href="{{ route('frontend.account.login') }}" class="font-semibold text-hb-blue hover:underline">{{ __('public.contact.sign_in') }}</a> {{ __('public.contact.workspace_support') }}</p>
                        <p class="mt-2">{{ __('public.contact.looking_role') }} <a href="{{ route('frontend.jobs.index') }}" class="font-semibold text-hb-blue hover:underline">{{ __('public.contact.browse_jobs') }}</a>.</p>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('contact-form');
            if (!form) return;
            form.addEventListener('submit', (event) => {
                let valid = true;
                form.querySelectorAll('[required]').forEach((field) => {
                    const message = field.type === 'email' && field.value.trim() && !field.validity.valid
                        ? @json(__('public.contact.validation_work_email'))
                        : (!field.value.trim() ? @json(__('js.validation.required')) : '');
                    const error = form.querySelector(`[data-error-for="${field.name}"]`);
                    if (error) error.textContent = message;
                    field.setAttribute('aria-invalid', message ? 'true' : 'false');
                    valid = valid && !message;
                });
                if (!valid) {
                    event.preventDefault();
                    form.querySelector('[aria-invalid="true"]')?.focus();
                    return;
                }
                const button = form.querySelector('[data-submit-button]');
                if (button) {
                    button.disabled = true;
                    button.setAttribute('aria-busy', 'true');
                    button.querySelector('[data-submit-label]')?.classList.add('hidden');
                    button.querySelector('[data-submit-loading]')?.classList.remove('hidden');
                }
            });
        });
    </script>
@endpush
