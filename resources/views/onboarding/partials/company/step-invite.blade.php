<div class="space-y-5 hidden" data-onboarding-panel data-step="4" hidden>
    <div>
        <h2 class="text-base font-semibold text-hb-navy">Invite your team</h2>
        <p class="mt-1 text-sm text-hb-muted">Invite hiring managers and interviewers with scoped access. Invites are not sent from this frontend screen.</p>
    </div>

    <div class="space-y-3" data-invite-rows>
        <div class="grid gap-3 sm:grid-cols-[1fr_180px_auto]" data-invite-row>
            <div data-field="invite_email_0">
                <label for="invite-email-0" class="hb-label">Work email</label>
                <input id="invite-email-0" name="invites[0][email]" type="email" class="hb-input" placeholder="name@company.example" value="alex.morgan@northstarlabs.example">
            </div>
            <div>
                <label for="invite-role-0" class="hb-label">Role</label>
                <select id="invite-role-0" name="invites[0][role]" class="hb-input">
                    <option value="hiring_manager">Hiring manager</option>
                    <option value="interviewer" selected>Interviewer</option>
                    <option value="admin">Company admin</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="button" class="hb-btn-ghost text-hb-muted" data-remove-invite disabled title="Keep at least one row">Remove</button>
            </div>
        </div>
    </div>

    <button type="button" class="hb-btn-outline" data-add-invite>
        Add another person
    </button>

    <p class="text-xs text-hb-muted">You can skip invites and add teammates later from Team &amp; permissions once Backend Team membership is connected.</p>
</div>
