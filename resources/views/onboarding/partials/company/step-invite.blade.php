<div class="space-y-5 hidden" data-onboarding-panel data-step="4" hidden>
    <div>
        <h2 class="text-base font-semibold text-hb-navy">{{ __('onboarding.company.invite.title') }}</h2>
        <p class="mt-1 text-sm text-hb-muted">{{ __('onboarding.company.invite_intro') }}</p>
    </div>

    <div class="space-y-3" data-invite-rows>
        <div class="grid gap-3 sm:grid-cols-[1fr_180px_auto]" data-invite-row>
            <div data-field="invite_email_0">
                <label for="invite-email-0" class="hb-label">{{ __('onboarding.company.invite.work_email') }}</label>
                <input id="invite-email-0" name="invites[0][email]" type="email" class="hb-input" placeholder="name@company.example" value="alex.morgan@northstarlabs.example">
            </div>
            <div>
                <label for="invite-role-0" class="hb-label">{{ __('onboarding.company.invite.role') }}</label>
                <select id="invite-role-0" name="invites[0][role]" class="hb-input">
                    <option value="hiring_manager">{{ __('onboarding.company.invite.roles.hiring_manager') }}</option>
                    <option value="interviewer" selected>{{ __('onboarding.company.invite.roles.interviewer') }}</option>
                    <option value="admin">{{ __('onboarding.company.invite.roles.admin') }}</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="button" class="hb-btn-ghost text-hb-muted" data-remove-invite disabled title="{{ __('onboarding.company.invite.remove_title') }}">{{ __('onboarding.company.invite.remove') }}</button>
            </div>
        </div>
    </div>

    <button type="button" class="hb-btn-outline" data-add-invite>
        {{ __('onboarding.company.invite.add_person') }}
    </button>

    <p class="text-xs text-hb-muted">{{ __('onboarding.company.invite.skip_hint') }}</p>
</div>
