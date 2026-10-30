<?php

namespace App\Frontend\Admin;

use App\Frontend\Auth\ApiSession;
use App\Models\AdminAPI;
use App\Support\Frontend\SafeApiMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Mission 10 — Super Admin Users & Access (Task #47).
 *
 * Realm: Platform Admin only (EnsureAdminAccess → GET /api/admin/me).
 * List contract: GET /api/admin/users?search&account_type&account_status_id&page&per_page
 * Role assign/remove APIs exist but lack safe guards — FE keeps assignment disabled.
 * No platform invitation or full-directory export Admin APIs.
 */
class AdminUsersAccessController extends Controller
{
    /** Backend account_statuses ids (AccountStatus). */
    public const STATUS_ACTIVE = 1;

    public const STATUS_DEACTIVATED = 2;

    public const STATUS_BLOCKED = 3;

    public const STATUS_DELETED = 4;

    /** Role assignment remains disabled until Backend enforces safe guards. */
    public const ROLE_ASSIGNMENT_ENABLED = false;

    public function __construct(private readonly AdminAPI $api) {}

    public function index(Request $request): View|RedirectResponse
    {
        $filters = $this->buildListFilters($request);
        $response = $this->api->getUsers($filters);
        $code = (int) ($response['statusCode'] ?? 500);

        if ($code === 401) {
            ApiSession::forget();

            return redirect()->route('frontend.account.admin-login')
                ->withErrors(['error' => __('admin.users.session_expired')]);
        }

        $users = [];
        $pagination = null;
        $loadError = null;

        if ($code === 403) {
            $loadError = __('admin.users.forbidden');
        } elseif ($code < 200 || $code >= 300) {
            $loadError = SafeApiMessage::forUser(
                is_string($response['message'] ?? null) ? $response['message'] : null,
                __('admin.users.load_error'),
                ['action' => 'admin.users.index', 'status' => $code]
            );
        } else {
            $users = is_array($response['data'] ?? null) ? $response['data'] : [];
            $pagination = is_array($response['meta'] ?? null) ? $response['meta'] : null;
        }

        $mapped = array_map(fn ($row) => $this->mapUser(is_array($row) ? $row : []), $users);
        $privilegedOnPage = array_values(array_filter($mapped, fn (array $u) => ($u['privilege_status'] ?? '') === 'privileged'));
        $unknownPrivilegeOnPage = array_values(array_filter($mapped, fn (array $u) => ($u['privilege_status'] ?? '') === 'unknown'));

        $pageRoleFilter = trim((string) $request->query('page_role', ''));
        $pageOrgFilter = trim((string) $request->query('page_org', ''));
        $displayUsers = $this->applyPageOnlyFilters($mapped, $pageRoleFilter, $pageOrgFilter);

        $auditSnippet = $this->loadPrivilegedAuditSnippet();

        return view('frontend.admin.users', [
            'users' => $displayUsers,
            'pagination' => $pagination,
            'loadError' => $loadError,
            'statusTab' => $this->statusTabFromRequest($request),
            'search' => (string) ($filters['search'] ?? ''),
            'accountType' => (string) ($filters['account_type'] ?? ''),
            'accountStatusId' => isset($filters['account_status_id']) ? (string) $filters['account_status_id'] : '',
            'pageRoleFilter' => $pageRoleFilter,
            'pageOrgFilter' => $pageOrgFilter,
            'pageFilterNote' => $pageRoleFilter !== '' || $pageOrgFilter !== '',
            'privilegedOnPage' => $privilegedOnPage,
            'privilegedCountOnPage' => count($privilegedOnPage),
            'unknownPrivilegeOnPage' => $unknownPrivilegeOnPage,
            'invitationsSupported' => false,
            'exportFullSupported' => false,
            'roleAssignmentEnabled' => self::ROLE_ASSIGNMENT_ENABLED,
            'auditSnippet' => $auditSnippet['rows'],
            'auditSnippetError' => $auditSnippet['error'],
            'auditSnippetAvailable' => $auditSnippet['available'],
            'listFilters' => $filters,
        ]);
    }

    /**
     * CSV of the current authorized Backend page only — not a full-directory export.
     */
    public function exportCurrentPage(Request $request): StreamedResponse|Response|RedirectResponse
    {
        $filters = $this->buildListFilters($request);
        $response = $this->api->getUsers($filters);
        $code = (int) ($response['statusCode'] ?? 500);

        if ($code === 401) {
            ApiSession::forget();

            return redirect()->route('frontend.account.admin-login')
                ->withErrors(['error' => __('admin.users.session_expired')]);
        }

        if ($code === 403) {
            return response(__('admin.users.forbidden'), 403);
        }

        if ($code < 200 || $code >= 300 || ! is_array($response['data'] ?? null)) {
            return redirect()->route('frontend.admin.users.index', $request->query())
                ->withErrors(['error' => __('admin.users.export_page_failed')]);
        }

        $rows = array_map(fn ($row) => $this->mapUser(is_array($row) ? $row : []), $response['data']);
        $pageRoleFilter = trim((string) $request->query('page_role', ''));
        $pageOrgFilter = trim((string) $request->query('page_org', ''));
        $rows = $this->applyPageOnlyFilters($rows, $pageRoleFilter, $pageOrgFilter);

        $filename = 'talentbridge-users-page-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            // UTF-8 BOM helps spreadsheet apps preserve Unicode; not a secret.
            fwrite($out, "\xEF\xBB\xBF");
            // Scope banner — explicit Current Page Only (never claim full directory).
            fputcsv($out, [
                $this->csvCell('SCOPE'),
                $this->csvCell('Current Page Only — authorized records on this Backend page; not a full Users directory export'),
            ]);
            fputcsv($out, [
                'id', 'name', 'email', 'account_type', 'account_status', 'roles', 'organizations', 'created_at', 'privilege_status',
            ]);
            foreach ($rows as $u) {
                // Safe columns only — never password, tokens, permissions arrays, or internal secrets.
                fputcsv($out, [
                    $this->csvCell($u['id'] ?? ''),
                    $this->csvCell($u['name'] ?? ''),
                    $this->csvCell($u['email'] ?? ''),
                    $this->csvCell($u['account_type'] ?? ''),
                    $this->csvCell($u['account_status'] ?? ''),
                    $this->csvCell(implode(' | ', $u['role_labels'] ?? [])),
                    $this->csvCell(implode(' | ', $u['org_labels'] ?? [])),
                    $this->csvCell($u['created_at'] ?? ''),
                    $this->csvCell($u['privilege_status'] ?? 'unknown'),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Neutralize spreadsheet formula injection while preserving fputcsv quoting for commas/quotes/newlines.
     */
    public function csvCell(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        $text = (string) $value;
        // Normalize newlines for single-cell safety; fputcsv will still quote when needed.
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        if ($text === '') {
            return '';
        }

        $first = $text[0];
        if (in_array($first, ['=', '+', '-', '@', "\t", "\r", "\n"], true)) {
            return "'".$text;
        }

        return $text;
    }

    /**
     * @return array<string, scalar>
     */
    private function buildListFilters(Request $request): array
    {
        $filters = [];

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $filters['search'] = $search;
        }

        $type = (string) $request->query('account_type', '');
        if (in_array($type, ['candidate', 'company', 'admin'], true)) {
            $filters['account_type'] = $type;
        }

        $tab = $this->statusTabFromRequest($request);
        $statusId = match ($tab) {
            'active' => self::STATUS_ACTIVE,
            'deactivated' => self::STATUS_DEACTIVATED,
            'blocked' => self::STATUS_BLOCKED,
            'deleted' => self::STATUS_DELETED,
            default => null,
        };

        // Explicit query wins over tab when both present and valid.
        $rawStatus = $request->query('account_status_id');
        if ($rawStatus !== null && $rawStatus !== '' && ctype_digit((string) $rawStatus)) {
            $id = (int) $rawStatus;
            if (in_array($id, [self::STATUS_ACTIVE, self::STATUS_DEACTIVATED, self::STATUS_BLOCKED, self::STATUS_DELETED], true)) {
                $statusId = $id;
            }
        }

        if ($statusId !== null) {
            $filters['account_status_id'] = $statusId;
        }

        $page = (int) $request->query('page', 1);
        if ($page > 1) {
            $filters['page'] = $page;
        }

        $perPage = (int) $request->query('per_page', 15);
        if ($perPage >= 1 && $perPage <= 100) {
            $filters['per_page'] = $perPage;
        }

        return $filters;
    }

    private function statusTabFromRequest(Request $request): string
    {
        $tab = strtolower((string) $request->query('status_tab', 'all'));
        if (in_array($tab, ['all', 'active', 'deactivated', 'blocked', 'deleted'], true)) {
            return $tab;
        }

        $id = (string) $request->query('account_status_id', '');

        return match ($id) {
            '1' => 'active',
            '2' => 'deactivated',
            '3' => 'blocked',
            '4' => 'deleted',
            default => 'all',
        };
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function mapUser(array $row): array
    {
        $roles = collect(data_get($row, 'roles', []))->filter(fn ($r) => is_array($r))->values();
        $owned = collect(data_get($row, 'owned_companies', []))->filter(fn ($c) => is_array($c))->values();

        $roleLabels = $roles->map(function (array $role) {
            $name = (string) (data_get($role, 'name') ?: '—');
            $scope = (string) data_get($role, 'scope', '');
            $companyId = data_get($role, 'company_id');
            $suffix = $scope !== '' ? " [{$scope}]" : '';
            if ($companyId) {
                $suffix .= ' #'.$companyId;
            }

            return $name.$suffix;
        })->all();

        $orgLabels = [];
        foreach ($owned as $company) {
            $label = trim((string) (data_get($company, 'name') ?: ''));
            if ($label !== '') {
                $orgLabels[] = $label.(data_get($company, 'status') ? ' ('.data_get($company, 'status').')' : '');
            }
        }
        foreach ($roles as $role) {
            $companyId = data_get($role, 'company_id');
            if ($companyId) {
                $orgLabels[] = __('admin.users.org_via_role', ['id' => $companyId]);
            }
        }
        $orgLabels = array_values(array_unique($orgLabels));

        $accountType = strtolower((string) data_get($row, 'account_type', ''));
        $rolesKeyPresent = array_key_exists('roles', $row);
        $hasPlatformRole = $roles->contains(fn (array $r) => strtolower((string) data_get($r, 'scope', '')) === 'platform');

        // Classify only from verified Backend fields. Never invent certification state.
        if ($accountType === 'admin' || $hasPlatformRole) {
            $privilegeStatus = 'privileged';
        } elseif ($rolesKeyPresent) {
            $privilegeStatus = 'standard';
        } else {
            $privilegeStatus = 'unknown';
        }

        $statusName = (string) (data_get($row, 'account_status') ?: '—');
        $statusId = data_get($row, 'account_status_id');

        return [
            'id' => data_get($row, 'id'),
            'name' => (string) (data_get($row, 'name') ?: '—'),
            'email' => (string) (data_get($row, 'email') ?: '—'),
            'account_type' => $accountType !== '' ? $accountType : '—',
            'account_status' => $statusName,
            'account_status_id' => $statusId,
            'roles_raw' => $roles->all(),
            'role_labels' => $roleLabels,
            'org_labels' => $orgLabels,
            'owned_companies' => $owned->all(),
            // Permission keys are not exported; count is display-only and never shown as a security score.
            'permission_count' => collect(data_get($row, 'permissions', []))->filter()->count(),
            'created_at' => data_get($row, 'created_at'),
            'privilege_status' => $privilegeStatus,
            'is_privileged' => $privilegeStatus === 'privileged',
            'is_admin_account' => $accountType === 'admin',
            'can_change_status' => $accountType !== 'admin' && $accountType !== '—',
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $users
     * @return list<array<string, mixed>>
     */
    private function applyPageOnlyFilters(array $users, string $pageRole, string $pageOrg): array
    {
        return array_values(array_filter($users, function (array $u) use ($pageRole, $pageOrg) {
            if ($pageRole !== '') {
                $hay = strtolower(implode(' ', $u['role_labels'] ?? []));
                if (! str_contains($hay, strtolower($pageRole))) {
                    return false;
                }
            }
            if ($pageOrg !== '') {
                $hay = strtolower(implode(' ', $u['org_labels'] ?? []));
                if (! str_contains($hay, strtolower($pageOrg))) {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * @return array{available: bool, error: ?string, rows: list<array<string, mixed>>}
     */
    private function loadPrivilegedAuditSnippet(): array
    {
        $response = $this->api->getAuditLogs([
            'auditable_type' => 'User',
            'per_page' => 8,
        ]);
        $code = (int) ($response['statusCode'] ?? 500);

        if ($code === 403) {
            return [
                'available' => false,
                'error' => __('admin.users.audit_forbidden'),
                'rows' => [],
            ];
        }

        if ($code < 200 || $code >= 300 || ! is_array($response['data'] ?? null)) {
            return [
                'available' => false,
                'error' => __('admin.users.audit_unavailable'),
                'rows' => [],
            ];
        }

        $rows = [];
        foreach ($response['data'] as $log) {
            if (! is_array($log)) {
                continue;
            }
            $action = (string) data_get($log, 'action', '');
            // Prefer access-related actions when present; still show general User audits.
            $rows[] = [
                'id' => data_get($log, 'id'),
                'action' => $action !== '' ? $action : '—',
                'auditable_id' => data_get($log, 'auditable_id'),
                'actor' => data_get($log, 'admin.name')
                    ?? data_get($log, 'user.name')
                    ?? '—',
                'reason' => filled(data_get($log, 'reason')) ? (string) data_get($log, 'reason') : null,
                'created_at' => data_get($log, 'created_at'),
            ];
        }

        return [
            'available' => true,
            'error' => null,
            'rows' => $rows,
        ];
    }
}
