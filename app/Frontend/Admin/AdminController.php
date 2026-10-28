<?php

namespace App\Frontend\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use App\Frontend\Auth\ApiSession;
use App\Models\AdminAPI;
use App\Support\Frontend\SafeApiMessage;

class AdminController extends Controller
{
    private $api = null;

    /** account_statuses ids used by the backend (AccountStatus model) */
    private const USER_STATUSES = ['active' => 1, 'deactivated' => 2, 'blocked' => 3];

    public function __construct(AdminAPI $api)
    {
        $this->api = $api;
    }

    /** Keep only the query-string filters the backend understands (drop empty ones). */
    private function filters(Request $request, array $allowed): array
    {
        return array_filter($request->only($allowed), fn ($v) => $v !== null && $v !== '');
    }

    /** Friendly flash message; never surface SQLSTATE / connection dumps. */
    private function apiFlashError(array $response, string $fallback, string $action): string
    {
        return SafeApiMessage::forUser(
            is_string($response['message'] ?? null) ? $response['message'] : null,
            $fallback,
            [
                'action' => $action,
                'status' => $response['statusCode'] ?? null,
            ]
        );
    }

    public function dashboard(Request $request)
    {
        $months = (int) $request->integer('months', 6);
        $months = min(max($months, 3), 12);

        $adapter = new AdminDashboardAdapter($this->api);
        $result = $adapter->load($months);

        if (! empty($result['unauthorized'])) {
            $loginRoute = ApiSession::isPlatformAdmin()
                ? 'frontend.account.admin-login'
                : 'frontend.account.login';
            ApiSession::forget();

            return redirect()->route($loginRoute)
                ->withErrors(['error' => __('Your session has expired. Please sign in again.')]);
        }

        return view('frontend.admin.dashboard', [
            'stats' => $result['stats'] ?? [],
            'analytics' => $result['analytics'] ?? null,
            'analyticsAvailable' => (bool) ($result['analyticsAvailable'] ?? false),
            'months' => (int) ($result['months'] ?? $months),
            'loadError' => $result['loadError'] ?? null,
            'forbidden' => (bool) ($result['forbidden'] ?? false),
        ]);
    }

    function getCompanies(Request $request)
    {
        $status = trim((string) $request->query('status', ''));
        $companyId = trim((string) $request->query('company_id', ''));
        $page = max(1, (int) $request->integer('page', 1));

        // Dropdown options: page through the authorized list API (never first-page-only).
        $catalog = (new AdminCompanyCatalog($this->api))->load();

        // Backend list supports status + search only — no company_id filter.
        // When a company is selected, use GET /admin/companies/{id} for an exact match.
        if ($companyId !== '') {
            $show = $this->api->getCompany($companyId);
            $showStatus = (int) ($show['statusCode'] ?? 500);

            if ($showStatus === 401) {
                $loginRoute = ApiSession::isPlatformAdmin()
                    ? 'frontend.account.admin-login'
                    : 'frontend.account.login';
                ApiSession::forget();

                return redirect()->route($loginRoute)
                    ->withErrors(['error' => __('Your session has expired. Please sign in again.')]);
            }

            if ($showStatus !== 200) {
                // Never surface ModelNotFound / SQL dumps for a missing company option.
                SafeApiMessage::forUser(
                    is_string($show['message'] ?? null) ? $show['message'] : null,
                    __('admin.companies.filter_company_unavailable'),
                    ['action' => 'admin.companies.show', 'status' => $showStatus, 'company_id' => $companyId]
                );

                return view('frontend.admin.companies', [
                    'companies' => [],
                    'pagination' => null,
                    'companyOptions' => $catalog['options'],
                    'companyOptionsStatus' => $catalog['status'],
                    'companyOptionsMessage' => $catalog['message'],
                    'selectedCompanyId' => $companyId,
                    'selectedStatus' => $status,
                    'filterError' => __('admin.companies.filter_company_unavailable'),
                ]);
            }

            $company = $this->unwrapCompany($show['data'] ?? null);
            $matchesStatus = $status === '' || (string) ($company['status'] ?? '') === $status;
            $rows = ($company !== null && $matchesStatus) ? [$company] : [];

            return view('frontend.admin.companies', [
                'companies' => $rows,
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 1,
                    'total' => count($rows),
                ],
                'companyOptions' => $catalog['options'],
                'companyOptionsStatus' => $catalog['status'],
                'companyOptionsMessage' => $catalog['message'],
                'selectedCompanyId' => $companyId,
                'selectedStatus' => $status,
                'filterError' => null,
            ]);
        }

        $filters = array_filter([
            'status' => $status !== '' ? $status : null,
            'page' => $page,
        ], static fn ($v) => $v !== null && $v !== '');

        $response = $this->api->getCompanies($filters);
        $listStatus = (int) ($response['statusCode'] ?? 500);

        if ($listStatus === 401) {
            $loginRoute = ApiSession::isPlatformAdmin()
                ? 'frontend.account.admin-login'
                : 'frontend.account.login';
            ApiSession::forget();

            return redirect()->route($loginRoute)
                ->withErrors(['error' => __('Your session has expired. Please sign in again.')]);
        }

        if ($listStatus !== 200) {
            return redirect()->route('frontend.dashboard')->withErrors([
                'error' => $this->apiFlashError(
                    $response,
                    __('admin.companies.load_failed'),
                    'admin.companies.index'
                ),
            ]);
        }

        return view('frontend.admin.companies', [
            'companies' => is_array($response['data'] ?? null) ? $response['data'] : [],
            'pagination' => $response['meta'] ?? null,
            'companyOptions' => $catalog['options'],
            'companyOptionsStatus' => $catalog['status'],
            'companyOptionsMessage' => $catalog['message'],
            'selectedCompanyId' => '',
            'selectedStatus' => $status,
            'filterError' => null,
        ]);
    }

    function approveCompany(Request $request, $companyId)
    {
        $response = $this->api->approveCompany($companyId);

        if ($response['statusCode'] === 200) {
            return redirect()->back()->with('success_key', 'flash.admin.company_approved');
        }

        return redirect()->back()->withErrors([
            'error' => $this->apiFlashError(
                $response,
                __('admin.companies.mutation_failed'),
                'admin.companies.approve'
            ),
        ]);
    }

    function rejectCompany(Request $request, $companyId)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'reason' => 'required|string|max:1000',
            ]);

            // the backend field is called rejection_reason
            $data = ['rejection_reason' => $fields['reason']];

            $response = $this->api->rejectCompany($companyId, $data);

            if ($response['statusCode'] === 200) {
                return redirect()->route('frontend.admin.companies.index')->with('success_key', 'flash.admin.company_rejected');
            }

            return redirect()->back()->withErrors([
                'error' => $this->apiFlashError(
                    $response,
                    __('admin.companies.mutation_failed'),
                    'admin.companies.reject'
                ),
            ])->withInput();
        }

        return view('frontend.admin.reject-company', get_defined_vars());
    }

    function suspendCompany(Request $request, $companyId)
    {
        $fields = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $response = $this->api->suspendCompany($companyId, array_filter(['reason' => $fields['reason'] ?? null]));

        if ($response['statusCode'] === 200) {
            return redirect()->back()->with('success_key', 'flash.admin.company_suspended');
        }

        // Backend platform Admin suspend currently fails audit FK (user_id=0) — never claim success.
        return redirect()->back()->withErrors([
            'error' => $this->apiFlashError(
                $response,
                __('admin.companies.suspend_failed'),
                'admin.companies.suspend'
            ),
        ]);
    }

    /** @param  mixed  $data */
    private function unwrapCompany(mixed $data): ?array
    {
        if (! is_array($data)) {
            return null;
        }
        if (isset($data['id'], $data['name'])) {
            return $data;
        }
        if (isset($data['data']) && is_array($data['data'])) {
            return $this->unwrapCompany($data['data']);
        }
        if (isset($data['company']) && is_array($data['company'])) {
            return $this->unwrapCompany($data['company']);
        }

        return null;
    }

    function getUsers(Request $request)
    {
        $response = $this->api->getUsers($this->filters($request, ['search', 'account_type', 'account_status_id', 'page']));

        if ($response['statusCode'] === 200) {
            // Users retrieval successful, you can perform additional actions here if needed
            return view('frontend.admin.users', ['users' => $response['data'], 'pagination' => $response['meta']]);
        } else {
            // Users retrieval failed, return the error message
            return redirect()->route('frontend.dashboard')->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    function updateUserStatus(Request $request, $userId)
    {
        if ($request->isMethod('post')) {

            $fields = $request->validate([
                'account_status' => 'required|string|in:' . implode(',', array_keys(self::USER_STATUSES)),
                'reason' => 'nullable|string|max:500',
            ]);

            // the backend expects account_status_id (+ optional reason)
            $data = array_filter([
                'account_status_id' => self::USER_STATUSES[$fields['account_status']],
                'reason' => $fields['reason'] ?? null,
            ], fn ($v) => $v !== null && $v !== '');

            $response = $this->api->updateUserStatus($userId, $data);

            if ($response['statusCode'] === 200) {
                // User status update successful, you can perform additional actions here if needed
                return redirect()->route('frontend.admin.users.index')->with('success_key', 'flash.admin.user_status_updated');
            } else {
                // User status update failed, return the error message
                return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
            }
        }

        // show the user being edited (name, email and current status)
        $userResponse = $this->api->getUser($userId);
        $user = $userResponse['statusCode'] === 200 ? \App\Support\Rows::get($userResponse['data'], 'data', $userResponse['data']) : [];

        return view('frontend.admin.update-user-status', get_defined_vars());
    }

    function getAuditLogs(Request $request)
    {
        $response = $this->api->getAuditLogs($this->filters($request, ['action', 'auditable_type', 'page']));

        if ($response['statusCode'] === 200) {
            // Audit logs retrieval successful, you can perform additional actions here if needed
            return view('frontend.admin.audit-logs', ['logs' => $response['data'], 'pagination' => $response['meta']]);
        } else {
            // Audit logs retrieval failed, return the error message
            return redirect()->route('frontend.dashboard')->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
        }
    }

    /**
     * Read-only role catalog (Phase 1).
     * Does not assign or remove roles — Backend guards remain authoritative.
     */
    function getRoles(Request $request)
    {
        $response = $this->api->getRoles();

        if (($response['statusCode'] ?? 500) === 200) {
            return view('frontend.admin.roles', [
                'roles' => is_array($response['data'] ?? null) ? $response['data'] : [],
                'loadError' => null,
            ]);
        }

        $status = (int) ($response['statusCode'] ?? 500);
        $loadError = in_array($status, [401, 403], true)
            ? __('You are not authorized to view roles and permissions.')
            : trim((string) ($response['message'] ?? __('Could not load roles.')));

        return view('frontend.admin.roles', [
            'roles' => [],
            'loadError' => $loadError !== '' ? $loadError : __('Could not load roles.'),
        ]);
    }

    function showCompany(Request $request, $companyId)
    {
        $response = $this->api->getCompany($companyId);

        if ($response['statusCode'] === 200) {
            return view('frontend.admin.company-show', ['company' => (array) $response['data']]);
        }

        return redirect()->route('frontend.admin.companies.index')
            ->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
    }

    function updateCompany(Request $request, $companyId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'website' => 'nullable|url',
            'industry' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:30',
            'address' => 'nullable|string',
        ]);

        $response = $this->api->updateCompany($companyId, $request->only(['name', 'website', 'industry', 'contact_email', 'contact_phone', 'address']));

        if ($response['statusCode'] === 200) {
            return redirect()->route('frontend.admin.companies.show', ['companyId' => $companyId])
                ->with('success', __('Company updated successfully.'));
        }

        return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']])->withInput();
    }

    function uploadCompanyDocument(Request $request, $companyId)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'type' => 'nullable|in:commercial_register,tax_card,license,other',
            'title' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $fields = array_filter($request->only(['type', 'title']), fn ($v) => $v !== null && $v !== '');

        $response = $this->api->uploadCompanyDocument($companyId, $file->getRealPath(), $file->getClientOriginalName(), (string) $file->getClientMimeType(), $fields);

        if ($response['statusCode'] === 200) {
            return redirect()->back()->with('success', __('Document uploaded successfully.'));
        }

        return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
    }

    function downloadCompanyDocument(Request $request, $companyId, $documentId)
    {
        $response = $this->api->downloadCompanyDocument($companyId, $documentId);

        if ($response['statusCode'] === 200) {
            $name = preg_replace('/[^\w.\- ]+/u', '_', basename((string) $request->query('name', 'document'))) ?: 'document';

            return response()->streamDownload(function () use ($response) {
                echo $response['data'];
            }, $name, ['Content-Type' => 'application/octet-stream']);
        }

        return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
    }

    function deleteCompanyDocument(Request $request, $companyId, $documentId)
    {
        $response = $this->api->deleteCompanyDocument($companyId, $documentId);

        if ($response['statusCode'] === 200) {
            return redirect()->back()->with('success', __('Document deleted successfully.'));
        }

        return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message']]);
    }
}
