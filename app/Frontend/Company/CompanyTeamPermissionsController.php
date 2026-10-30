<?php

namespace App\Frontend\Company;

use App\Frontend\Auth\ApiSession;
use App\Models\EmployeeAPI;
use App\Support\Frontend\SafeApiMessage;
use App\Support\Rows;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Mission 08 — HR Team & Permissions (Members | Invitations | Roles).
 *
 * Members & roles use live company APIs. Invitations require `company_invitations`
 * (migration 000009) — when the table/API fails, the Invites tab stays blocked
 * and does not claim email delivery. Hiring-team (job-scoped) is a separate API.
 *
 * Security gate: PUT /company/employees/{id}/role is unsafe on Backend
 * (exists:roles,id only; no permission middleware; no self/last-admin guards).
 * Role-change mutations stay disabled in this Audit Frontend.
 */
class CompanyTeamPermissionsController extends Controller
{
    public const TABS = ['members', 'invitations', 'roles'];

    /** Status values accepted by GET /company/employees?status= */
    public const MEMBER_STATUSES = ['pending', 'approved', 'rejected', 'removed'];

    /**
     * Backend PUT /company/employees/{id}/role is not safe for Audit FE use.
     * Keep false until Backend validates company-scope roles + permission + self/last-admin.
     */
    public const ROLE_UPDATES_ENABLED = false;

    /** Company role rank for approve privilege-ceiling checks (higher = more privilege). */
    private const ROLE_RANK = [
        'employee' => 1,
        'interviewer' => 2,
        'hr' => 3,
        'company_admin' => 4,
    ];

    public function __construct(private readonly EmployeeAPI $employees) {}

    public function show(Request $request): View|RedirectResponse
    {
        $tab = $this->normalizeTab((string) $request->query('tab', 'members'));
        $status = $this->normalizeStatus($request->query('status'));
        $q = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));

        $rolesPayload = $this->loadCompanyRoles();
        $membersPayload = $this->loadMembers($status, $page);
        $invitesPayload = $tab === 'invitations'
            ? $this->loadInvitations()
            : ['available' => null, 'items' => [], 'meta' => null, 'message' => null, 'blocked' => false];

        $clientFiltering = $q !== '' || filled($request->query('role'));
        $members = $this->filterMembersClientSide($membersPayload['items'], $q, $request->query('role'));
        $sessionUser = ApiSession::user() ?? [];
        $sessionRoles = $this->sessionRoleNames($sessionUser);

        return view('frontend.company.team-permissions', [
            'tab' => $tab,
            'statusFilter' => $status,
            'searchQuery' => $q,
            'roleFilter' => (string) $request->query('role', ''),
            'members' => $members,
            'membersMeta' => $clientFiltering ? null : $membersPayload['meta'],
            'membersAvailable' => $membersPayload['available'],
            'membersMessage' => $membersPayload['message'],
            'memberCounts' => $this->countByStatus($membersPayload['items']),
            'roles' => $rolesPayload['items'],
            'rolesAvailable' => $rolesPayload['available'],
            'rolesMessage' => $rolesPayload['message'],
            'assignableRoles' => $this->assignableRolesForApprove($rolesPayload['items'], $sessionRoles),
            'roleCapabilityNotes' => $this->roleCapabilityNotes(),
            'invitations' => $invitesPayload['items'],
            'invitationsMeta' => $invitesPayload['meta'],
            'invitationsAvailable' => $invitesPayload['available'],
            'invitationsBlocked' => (bool) ($invitesPayload['blocked'] ?? false),
            'invitationsMessage' => $invitesPayload['message'],
            'currentUserId' => (int) data_get($sessionUser, 'id', 0),
            'currentUserRoles' => $sessionRoles,
            'adminCount' => $this->countApprovedAdmins($membersPayload['items']),
            'roleUpdatesEnabled' => self::ROLE_UPDATES_ENABLED,
            'searchClientSide' => true,
        ]);
    }

    public function updateRole(Request $request, int $employeeId): RedirectResponse
    {
        // Hard block: Backend validates exists:roles,id only and has no
        // permission middleware / self-role / last-admin / company-scope role checks.
        return redirect()
            ->route('frontend.company.employees.index', ['tab' => 'members'])
            ->withErrors(['error' => __('company.employees.role_update_disabled')]);
    }

    public function approve(Request $request, int $employeeId): RedirectResponse
    {
        $rolesPayload = $this->loadCompanyRoles();
        $sessionRoles = $this->sessionRoleNames(ApiSession::user() ?? []);
        $allowedIds = collect($this->assignableRolesForApprove($rolesPayload['items'], $sessionRoles))
            ->pluck('id')
            ->all();

        $validated = $request->validate([
            'role_id' => ['required', 'integer', Rule::in($allowedIds !== [] ? $allowedIds : [0])],
        ], [
            'role_id.required' => __('company.employees.assign_role_required_live'),
            'role_id.in' => __('company.employees.role_not_assignable'),
        ]);

        $response = $this->employees->approveEmployee($employeeId, [
            'role_id' => (int) $validated['role_id'],
        ]);

        return $this->mutationRedirect($response, 'company.employees.flash_approved', [
            'tab' => 'members',
            'status' => 'pending',
        ]);
    }

    public function remove(Request $request, int $employeeId): RedirectResponse
    {
        $membersPayload = $this->loadMembers('approved', 1, 100);
        $target = collect($membersPayload['items'])->firstWhere('id', $employeeId);
        if (! is_array($target)) {
            // Fall back: still allow Backend to decide if member is not on the approved page.
            $allPayload = $this->loadMembers(null, 1, 100);
            $target = collect($allPayload['items'])->firstWhere('id', $employeeId);
            if (! is_array($target)) {
                return back()->withErrors(['error' => __('company.employees.member_not_found')]);
            }
            $membersPayload = $allPayload;
        }

        $roleName = strtolower((string) data_get($target, 'role.name', ''));
        if ($roleName === 'company_admin' && $this->countApprovedAdmins($membersPayload['items']) <= 1) {
            return back()->withErrors(['error' => __('company.employees.last_admin_remove_blocked')]);
        }

        $currentUserId = (int) data_get(ApiSession::user(), 'id', 0);
        if ($currentUserId > 0 && (int) data_get($target, 'user.id') === $currentUserId) {
            return back()->withErrors(['error' => __('company.employees.self_remove_blocked')]);
        }

        $response = $this->employees->removeEmployee($employeeId);

        return $this->mutationRedirect($response, 'company.employees.flash_removed', [
            'tab' => 'members',
        ]);
    }

    public function createInvitation(Request $request): RedirectResponse
    {
        // Probe availability — do not claim create when table/API is down.
        $probe = $this->loadInvitations();
        if ($probe['blocked'] || $probe['available'] === false) {
            return redirect()
                ->route('frontend.company.employees.index', ['tab' => 'invitations'])
                ->withErrors(['error' => __('company.employees.invites_blocked_create')]);
        }

        $rolesPayload = $this->loadCompanyRoles();
        $sessionRoles = $this->sessionRoleNames(ApiSession::user() ?? []);
        $allowedIds = collect($this->assignableRolesForApprove($rolesPayload['items'], $sessionRoles))
            ->pluck('id')
            ->all();

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role_id' => ['nullable', 'integer', Rule::in($allowedIds !== [] ? $allowedIds : [0])],
        ]);

        $payload = ['email' => $validated['email']];
        if (! empty($validated['role_id'])) {
            $payload['role_id'] = (int) $validated['role_id'];
        }

        $response = $this->employees->createInvitation($payload);
        $code = (int) ($response['statusCode'] ?? 500);

        if ($code >= 200 && $code < 300) {
            return redirect()
                ->route('frontend.company.employees.index', ['tab' => 'invitations'])
                ->with('success_key', 'company.employees.flash_invite_created');
        }

        return $this->mutationRedirect($response, 'company.employees.flash_invite_created', [
            'tab' => 'invitations',
        ]);
    }

    public function deleteInvitation(Request $request, int $invitationId): RedirectResponse
    {
        $probe = $this->loadInvitations();
        if ($probe['blocked'] || $probe['available'] === false) {
            return redirect()
                ->route('frontend.company.employees.index', ['tab' => 'invitations'])
                ->withErrors(['error' => __('company.employees.invites_blocked_create')]);
        }

        $response = $this->employees->deleteInvitation($invitationId);

        return $this->mutationRedirect($response, 'company.employees.flash_invite_revoked', [
            'tab' => 'invitations',
        ]);
    }

    private function normalizeTab(string $tab): string
    {
        return in_array($tab, self::TABS, true) ? $tab : 'members';
    }

    private function normalizeStatus(mixed $status): ?string
    {
        $status = is_string($status) ? strtolower(trim($status)) : '';
        if ($status === '' || $status === 'all') {
            return null;
        }

        return in_array($status, self::MEMBER_STATUSES, true) ? $status : null;
    }

    /**
     * @return array{items: list<array<string, mixed>>, available: bool, message: ?string, meta: ?array}
     */
    private function loadMembers(?string $status, int $page = 1, int $perPage = 15): array
    {
        $filters = ['page' => $page, 'per_page' => $perPage];
        if ($status !== null) {
            $filters['status'] = $status;
        }

        $res = $this->employees->getEmployees($filters);
        $code = (int) ($res['statusCode'] ?? 500);

        if ($code === 401) {
            ApiSession::forget();
        }

        if ($code < 200 || $code >= 300) {
            return [
                'items' => [],
                'available' => false,
                'meta' => null,
                'message' => SafeApiMessage::forUser(
                    is_string($res['message'] ?? null) ? $res['message'] : null,
                    __('company.employees.members_unavailable'),
                    ['action' => 'company.employees.index', 'status' => $code]
                ),
            ];
        }

        $payload = $res['data'];
        $items = Rows::from($payload);
        $meta = is_array($res['meta'] ?? null)
            ? $res['meta']
            : Rows::meta(is_array($payload) ? $payload : null);

        return [
            'items' => array_values(array_filter($items, 'is_array')),
            'available' => true,
            'meta' => $meta,
            'message' => null,
        ];
    }

    /**
     * @return array{items: list<array<string, mixed>>, available: bool, message: ?string}
     */
    private function loadCompanyRoles(): array
    {
        $res = $this->employees->getCompanyRoles();
        $code = (int) ($res['statusCode'] ?? 500);

        if ($code < 200 || $code >= 300) {
            return [
                'items' => [],
                'available' => false,
                'message' => SafeApiMessage::forUser(
                    is_string($res['message'] ?? null) ? $res['message'] : null,
                    __('company.employees.roles_unavailable'),
                    ['action' => 'company.roles.index', 'status' => $code]
                ),
            ];
        }

        $items = [];
        foreach (Rows::from($res['data']) as $row) {
            if (! is_array($row)) {
                continue;
            }
            if (strtolower((string) data_get($row, 'scope', 'company')) !== 'company') {
                continue;
            }
            $items[] = $row;
        }

        return ['items' => $items, 'available' => true, 'message' => null];
    }

    /**
     * @return array{items: list<array<string, mixed>>, available: ?bool, blocked: bool, message: ?string, meta: ?array}
     */
    private function loadInvitations(): array
    {
        $res = $this->employees->getInvitations();
        $code = (int) ($res['statusCode'] ?? 500);
        $message = is_string($res['message'] ?? null) ? $res['message'] : '';

        // Missing table / migration typically surfaces as 500 with SQLSTATE or similar.
        $looksMissing = $code >= 500
            || str_contains(strtolower($message), 'company_invitations')
            || str_contains(strtolower($message), 'sqlstate');

        if ($code < 200 || $code >= 300) {
            return [
                'items' => [],
                'available' => false,
                'blocked' => $looksMissing || $code >= 500,
                'meta' => null,
                'message' => $looksMissing
                    ? __('company.employees.invites_migration_blocked')
                    : SafeApiMessage::forUser(
                        $message !== '' ? $message : null,
                        __('company.employees.invites_unavailable'),
                        ['action' => 'company.invitations.index', 'status' => $code]
                    ),
            ];
        }

        $payload = $res['data'];
        $items = Rows::from($payload);
        $meta = is_array($res['meta'] ?? null)
            ? $res['meta']
            : Rows::meta(is_array($payload) ? $payload : null);

        return [
            'items' => array_values(array_filter($items, 'is_array')),
            'available' => true,
            'blocked' => false,
            'meta' => $meta,
            'message' => null,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    private function filterMembersClientSide(array $items, string $q, mixed $roleFilter): array
    {
        $roleFilter = is_string($roleFilter) ? strtolower(trim($roleFilter)) : '';

        return array_values(array_filter($items, function (array $row) use ($q, $roleFilter): bool {
            if ($roleFilter !== '') {
                $roleName = strtolower((string) data_get($row, 'role.name', ''));
                if ($roleName !== $roleFilter) {
                    return false;
                }
            }
            if ($q === '') {
                return true;
            }
            $hay = strtolower(implode(' ', [
                (string) data_get($row, 'user.name', ''),
                (string) data_get($row, 'user.email', ''),
                (string) data_get($row, 'role.name', ''),
                (string) data_get($row, 'requested_job_title', ''),
                (string) data_get($row, 'requested_department', ''),
            ]));

            return str_contains($hay, strtolower($q));
        }));
    }

    /**
     * Company-scoped roles the current session may assign on approve.
     * Caps privilege at the caller's highest session company role (defense in depth;
     * Backend ApproveEmployeeRequest still enforces company-scope only).
     *
     * @param  list<array<string, mixed>>  $roles
     * @param  list<string>  $sessionRoles
     * @return list<array<string, mixed>>
     */
    private function assignableRolesForApprove(array $roles, array $sessionRoles): array
    {
        $ceiling = $this->sessionRoleCeiling($sessionRoles);

        return array_values(array_filter($roles, function (array $role) use ($ceiling): bool {
            if (strtolower((string) data_get($role, 'scope', 'company')) !== 'company') {
                return false;
            }
            if (! filled(data_get($role, 'id'))) {
                return false;
            }

            $name = strtolower((string) data_get($role, 'name', ''));
            $rank = self::ROLE_RANK[$name] ?? 0;

            // Unknown company role names are withheld rather than offered blindly.
            return $rank > 0 && $rank <= $ceiling;
        }));
    }

    /**
     * @param  list<string>  $sessionRoles
     */
    private function sessionRoleCeiling(array $sessionRoles): int
    {
        $ceiling = 0;
        foreach ($sessionRoles as $name) {
            $rank = self::ROLE_RANK[strtolower($name)] ?? 0;
            if ($rank > $ceiling) {
                $ceiling = $rank;
            }
        }

        // If session roles are missing, allow only the lowest company role on approve UI.
        return $ceiling > 0 ? $ceiling : (self::ROLE_RANK['employee'] ?? 1);
    }

    /**
     * Backend seed documentation for company roles (RoleSeeder). Not a live permission catalog API.
     *
     * @return array<string, list<string>>
     */
    private function roleCapabilityNotes(): array
    {
        return [
            'company_admin' => [
                'employees.view', 'employees.approve', 'employees.manage',
                'jobs.create', 'jobs.update', 'jobs.publish', 'jobs.close', 'jobs.nominate',
                'applications.view', 'applications.review', 'applications.override_ai', 'cvs.view',
                'interviews.assign', 'interviews.conduct', 'interviews.evaluate',
                'exams.create', 'exams.manage', 'proctoring.review', 'offers.manage', 'salary.benchmark.view',
            ],
            'hr' => [
                'employees.view', 'jobs.create', 'jobs.update', 'jobs.publish', 'jobs.close', 'jobs.nominate',
                'applications.view', 'applications.review', 'applications.override_ai', 'cvs.view',
                'interviews.assign', 'exams.create', 'exams.manage', 'proctoring.review',
                'offers.manage', 'salary.benchmark.view',
            ],
            'interviewer' => [
                'applications.view', 'cvs.view', 'interviews.conduct', 'interviews.evaluate',
            ],
            'employee' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $user
     * @return list<string>
     */
    private function sessionRoleNames(array $user): array
    {
        $roles = data_get($user, 'roles', []);
        if (! is_array($roles)) {
            return [];
        }

        $names = [];
        foreach ($roles as $role) {
            if (is_string($role) && $role !== '') {
                $names[] = $role;
            } elseif (is_array($role) && filled(data_get($role, 'name'))) {
                $names[] = (string) data_get($role, 'name');
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array<string, int>
     */
    private function countByStatus(array $items): array
    {
        $counts = ['all' => count($items), 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'removed' => 0];
        foreach ($items as $row) {
            $status = strtolower((string) data_get($row, 'status', ''));
            if (isset($counts[$status])) {
                $counts[$status]++;
            }
        }

        return $counts;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function countApprovedAdmins(array $items): int
    {
        $n = 0;
        foreach ($items as $row) {
            if (strtolower((string) data_get($row, 'status')) !== 'approved') {
                continue;
            }
            if (strtolower((string) data_get($row, 'role.name')) === 'company_admin') {
                $n++;
            }
        }

        return $n;
    }

    /**
     * @param  array{statusCode?: int, message?: mixed, errors?: mixed}  $response
     * @param  array<string, mixed>  $query
     */
    private function mutationRedirect(array $response, string $successKey, array $query = []): RedirectResponse
    {
        $code = (int) ($response['statusCode'] ?? 500);

        if ($code === 401) {
            ApiSession::forget();

            return redirect()->route('frontend.account.login')
                ->withErrors(['error' => __('company.employees.session_expired')]);
        }

        if ($code >= 200 && $code < 300) {
            return redirect()
                ->route('frontend.company.employees.index', $query)
                ->with('success_key', $successKey);
        }

        $fallback = match (true) {
            $code === 403 => __('company.employees.forbidden_action'),
            $code === 422 => __('company.employees.validation_error'),
            $code >= 500 => __('company.employees.server_error'),
            default => __('company.employees.save_error'),
        };

        $errors = is_array($response['errors'] ?? null) && ($response['errors'] ?? []) !== []
            ? $response['errors']
            : ['error' => SafeApiMessage::forUser(
                is_string($response['message'] ?? null) ? $response['message'] : null,
                $fallback,
                ['action' => 'company.employees.mutate', 'status' => $code]
            )];

        return redirect()
            ->route('frontend.company.employees.index', $query)
            ->withErrors($errors)
            ->withInput();
    }
}
