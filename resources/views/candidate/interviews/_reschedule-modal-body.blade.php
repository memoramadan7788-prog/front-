<p class="text-sm text-hb-muted">{{ __('candidate.interviews.reschedule_modal_intro') }}</p>
<p class="mt-2 text-xs text-hb-muted">{{ __('candidate.interviews.cutoff_note', ['hours' => 24]) }}</p>
<p class="mt-1 text-xs text-hb-muted">{{ __('candidate.interviews.reschedule_preview_only') }}</p>
<label class="hb-label mt-4" for="reschedule-note">{{ __('candidate.interviews.preferred_times') }}</label>
<textarea id="reschedule-note" class="hb-input" rows="3" placeholder="{{ __('candidate.interviews.reschedule_placeholder') }}" data-reschedule-note></textarea>
<p class="mt-3 hidden text-sm text-hb-success" data-reschedule-status role="status"></p>
