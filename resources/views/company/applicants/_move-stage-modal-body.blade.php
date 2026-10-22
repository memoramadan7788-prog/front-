<p class="text-sm text-hb-muted">
    {{ __('company.applicants.move_stage_body', ['name' => $applicant['name']]) }}
</p>
<label class="mt-4 block">
    <span class="hb-label">{{ __('company.applicants.col_stage') }}</span>
    <select class="hb-input mt-1 w-full" data-move-stage-select>
        @foreach ($applicant['pipeline'] as $step)
            <option value="{{ $step['label'] }}" @selected($step['status'] === 'current')>{{ $step['label'] }}</option>
        @endforeach
    </select>
</label>
<p class="mt-3 hidden text-sm text-hb-blue" data-move-feedback role="status"></p>
