@extends('frontend.layouts.app')

@section('title', __('Send offer'))

@section('page_header')
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold tracking-tight text-hb-navy">{{ __('Send offer') }}</h1>
        <button type="button" onclick="togglePreview()" class="px-4 py-2 bg-hb-page text-hb-navy border border-hb-border text-sm font-medium rounded-xl hover:bg-hb-border/50 transition">
            👁️ {{ __('Candidate Preview') }}
        </button>
    </div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Form (2 Cols) -->
    <div class="lg:col-span-2">
        <x-card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-hb-navy">{{ __('Create and Manage Offer') }}</h2>
                    <span id="approval-badge" class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                        ⚠️ {{ __('Approval Required Before Sending') }}
                    </span>
                </div>
            </x-slot:header>

            <form method="POST" action="{{ route('frontend.company.offers.create.submit') }}" class="space-y-5" id="offer-form">
                @csrf
                
                <!-- Template Selection -->
                <div>
                    <label class="block text-xs font-semibold text-hb-navy mb-1">{{ __('Offer Template') }}</label>
                    <select id="template-selector" onchange="applyTemplate(this.value)" class="w-full rounded-xl border border-hb-border px-3 py-2 text-sm focus:border-hb-navy focus:ring-1 focus:ring-hb-navy">
                        <option value="">{{ __('Custom Offer (No Template)') }}</option>
                        <option value="software_engineer">{{ __('Software Engineer Standard Package') }}</option>
                        <option value="product_manager">{{ __('Product Manager Senior Package') }}</option>
                    </select>
                </div>

                <x-form.input name="application_id" :label="__('Application ID')" type="number" :required="true" :error="$errors->first('application_id')" value="{{ old('application_id', data_get([], 'application_id', request('application_id'))) }}" />
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-form.input name="salary_amount" id="salary_amount" :label="__('Base Salary')" type="number" :required="true" :error="$errors->first('salary_amount')" value="{{ old('salary_amount', '') }}" min="0" />
                    
                    <x-form.select name="currency" :label="__('Currency')" :required="true" :error="$errors->first('currency')">
                        <option value="">{{ __('Select…') }}</option>
                        <option value="EGP" @selected(old('currency') === 'EGP')>{{ __('EGP') }}</option>
                        <option value="USD" @selected(old('currency') === 'USD')>{{ __('USD') }}</option>
                        <option value="EUR" @selected(old('currency') === 'EUR')>{{ __('EUR') }}</option>
                        <option value="SAR" @selected(old('currency') === 'SAR')>{{ __('SAR') }}</option>
                        <option value="AED" @selected(old('currency') === 'AED')>{{ __('AED') }}</option>
                    </x-form.select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-form.select name="salary_period" :label="__('Salary period')" :required="true" :error="$errors->first('salary_period')">
                        <option value="">{{ __('Select…') }}</option>
                        <option value="hour" @selected(old('salary_period') === 'hour')>{{ __('Hourly') }}</option>
                        <option value="month" @selected(old('salary_period') === 'month')>{{ __('Monthly') }}</option>
                        <option value="year" @selected(old('salary_period') === 'year')>{{ __('Yearly') }}</option>
                    </x-form.select>

                    <x-form.input name="bonus_amount" id="bonus_amount" :label="__('Signing / Annual Bonus')" type="number" :error="$errors->first('bonus_amount')" value="{{ old('bonus_amount', '') }}" min="0" />
                </div>

                <!-- Equity / Shares Option -->
                <x-form.input name="equity_details" id="equity_details" :label="__('Equity / Stock Options Details')" type="text" :error="$errors->first('equity_details')" value="{{ old('equity_details', '') }}" placeholder="e.g. 0.5% Stock Options vested over 4 years" />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-form.input name="start_date" id="start_date" :label="__('Start date')" type="date" :required="true" :error="$errors->first('start_date')" value="{{ old('start_date', '') }}" />
                    <x-form.input name="expires_at" id="expires_at" :label="__('Offer expires on')" type="date" :required="true" :error="$errors->first('expires_at')" value="{{ old('expires_at', '') }}" />
                </div>

                <!-- E-Signature Requirement -->
                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="require_esignature" id="require_esignature" value="1" class="rounded border-hb-border text-hb-navy focus:ring-hb-navy" checked>
                    <label for="require_esignature" class="text-sm font-medium text-hb-navy">{{ __('Require candidate e-signature upon acceptance') }}</label>
                </div>

                <!-- Action Buttons: Request Approval vs Submit -->
                <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-hb-border">
                    <button type="button" onclick="requestApproval()" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-xl transition">
                        📋 {{ __('Request Management Approval') }}
                    </button>
                    
                    <div class="flex items-center gap-2">
                        <x-button type="submit" id="submit-btn" disabled class="opacity-50 cursor-not-allowed">
                            {{ __('Send Offer to Candidate') }}
                        </x-button>
                        <x-button variant="light" :href="route('frontend.company.dashboard')">{{ __('Cancel') }}</x-button>
                    </div>
                </div>
            </form>
        </x-card>
    </div>

    <!-- Candidate Preview Sidebar Card -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl border border-hb-border p-5 shadow-sm sticky top-6">
            <h3 class="font-bold text-sm text-hb-navy mb-3 flex items-center justify-between">
                <span>{{ __('Candidate View Preview') }}</span>
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
            </h3>
            <div class="bg-hb-page/50 rounded-xl p-4 border border-hb-border space-y-3 text-xs text-hb-navy">
                <p><strong>{{ __('Position') }}</strong>: <span id="prev-title">Software Engineer</span></p>
                <p><strong>{{ __('Compensation') }}</strong>: <span id="prev-salary">—</span> <span id="prev-currency"></span> / <span id="prev-period">month</span></p>
                <p><strong>{{ __('Bonus') }}</strong>: <span id="prev-bonus">0</span></p>
                <p><strong>{{ __('Equity') }}</strong>: <span id="prev-equity">None</span></p>
                <p><strong>{{ __('Start Date') }}</strong>: <span id="prev-start">—</span></p>
                <hr class="border-hb-border">
                <p class="text-hb-muted italic">{{ __('Candidate will be able to review terms and sign digitally.') }}</p>
            </div>
        </div>
    </div>
</div>

<script>
    // Templates Data Simulator
    const templates = {
        software_engineer: { salary: 25000, currency: 'EGP', period: 'month', bonus: 5000, equity: '0.25% Vested' },
        product_manager: { salary: 40000, currency: 'EGP', period: 'month', bonus: 10000, equity: '0.5% Vested' }
    };

    function applyTemplate(key) {
        if(!templates[key]) return;
        let t = templates[key];
        document.getElementById('salary_amount').value = t.salary;
        document.querySelector('select[name="currency"]').value = t.currency;
        document.querySelector('select[name="salary_period"]').value = t.period;
        document.getElementById('bonus_amount').value = t.bonus;
        document.getElementById('equity_details').value = t.equity;
        updatePreview();
    }

    // Request Approval Simulation
    function requestApproval() {
        alert('{{ __("Approval request sent to HR manager successfully! Once approved, sending to candidate will be unlocked.") }}');
        document.getElementById('approval-badge').className = 'px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800';
        document.getElementById('approval-badge').innerHTML = '✅ {{ __("Approved by Management") }}';
        
        let subBtn = document.getElementById('submit-btn');
        subBtn.removeAttribute('disabled');
        subBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }

    // Live Preview Updater
    function updatePreview() {
        document.getElementById('prev-salary').innerText = document.getElementById('salary_amount').value || '—';
        document.getElementById('prev-currency').innerText = document.querySelector('select[name="currency"]').value;
        document.getElementById('prev-period').innerText = document.querySelector('select[name="salary_period"]').value;
        document.getElementById('prev-bonus').innerText = document.getElementById('bonus_amount').value || '0';
        document.getElementById('prev-equity').innerText = document.getElementById('equity_details').value || 'None';
        document.getElementById('prev-start').innerText = document.getElementById('start_date').value || '—';
    }

    document.querySelectorAll('#offer-form input, #offer-form select').forEach(el => {
        el.addEventListener('input', updatePreview);
        el.addEventListener('change', updatePreview);
    });

    function togglePreview() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
</script>
@endsection