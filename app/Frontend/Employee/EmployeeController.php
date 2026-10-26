<?php

namespace App\Frontend\Employee;

use App\Frontend\Auth\ApiSession;
use App\Models\EmployeeAPI;
use App\Support\Frontend\CompanyMembership\CompanyMembershipGateway;
use App\Support\Frontend\FrontendStatusVocabulary;
use App\Support\Rows;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class EmployeeController extends Controller
{
    public function __construct(
        private EmployeeAPI $api,
        private CompanyMembershipGateway $membership,
    ) {}

    public function requestToJoin(Request $request)
    {
        if ($request->isMethod('post')) {
            if (! $this->membership->canSubmitJoinRequest()) {
                return redirect()
                    ->route('frontend.candidate.join-company')
                    ->withErrors(['error' => __('candidate.membership.submit_disabled')])
                    ->withInput();
            }

            $request->validate([
                'company_id' => 'required|integer|min:1',
                'requested_job_title' => 'required|string|max:255',
                'requested_department' => 'required|string|max:255',
            ], [
                'company_id.required' => __('candidate.membership.validation_company'),
                'requested_job_title.required' => __('candidate.membership.validation_title'),
                'requested_department.required' => __('candidate.membership.validation_department'),
            ]);

            $data = $request->only(['company_id', 'requested_job_title', 'requested_department']);
            $response = $this->membership->requestToJoin($data);
            $status = (int) ($response['statusCode'] ?? 500);

            if ($status === 401) {
                ApiSession::forget();

                return redirect()->route('frontend.account.login')
                    ->withErrors(['error' => __('candidate.membership.session_expired')]);
            }

            if ($status === 200) {
                return redirect()
                    ->route('frontend.candidate.join-company')
                    ->with('success_key', 'flash.employee.join_requested');
            }

            return redirect()
                ->route('frontend.candidate.join-company')
                ->withErrors($this->errorBag($response, $status))
                ->withInput();
        }

        $companyQuery = trim((string) $request->query('q', ''));
        $companyPage = max(1, (int) $request->query('page', 1));

        $discovery = $this->membership->searchCompanies(
            $companyQuery !== '' ? $companyQuery : null,
            $companyPage,
        );
        $mine = $this->membership->myMembership();

        if ((int) ($discovery['statusCode'] ?? 0) === 401 || (int) ($mine['statusCode'] ?? 0) === 401) {
            ApiSession::forget();

            return redirect()->route('frontend.account.login')
                ->withErrors(['error' => __('candidate.membership.session_expired')]);
        }

        $membershipRows = [];
        $membershipError = null;
        if ($mine['available'] === true) {
            if ((int) ($mine['statusCode'] ?? 500) === 200) {
                $membershipRows = $this->normalizeMembershipRows($mine['data'] ?? null);
            } else {
                $membershipError = $this->errorBag($mine, (int) ($mine['statusCode'] ?? 500))['error']
                    ?? __('candidate.membership.status_load_error');
            }
        }

        $discoveryError = null;
        $companies = [];
        $companiesMeta = null;
        if ($discovery['available'] === true) {
            if ((int) ($discovery['statusCode'] ?? 500) === 200) {
                $companies = $this->normalizeCompanyRows($discovery['data'] ?? []);
                $companiesMeta = is_array($discovery['meta'] ?? null) ? $discovery['meta'] : null;
            } else {
                $discoveryError = $this->errorBag($discovery, (int) ($discovery['statusCode'] ?? 500))['error']
                    ?? __('candidate.membership.discovery_load_error');
            }
        }

        return view('frontend.employee.join-company', [
            'discoveryAvailable' => (bool) ($discovery['available'] ?? false),
            'discoveryError' => $discoveryError,
            'discoveryLoading' => false,
            'companies' => $companies,
            'companiesMeta' => $companiesMeta,
            'companyQuery' => $companyQuery,
            'companyPage' => $companyPage,
            'canSubmit' => $this->membership->canSubmitJoinRequest() && $discoveryError === null,
            'membershipAvailable' => (bool) ($mine['available'] ?? false),
            'membershipError' => $membershipError,
            'membershipRows' => $membershipRows,
            'statusLegend' => FrontendStatusVocabulary::membership(),
        ]);
    }

    public function getEmployees(Request $request)
    {
        $filters = $request->query();
        $response = $this->api->getEmployees($filters);

        if ((int) ($response['statusCode'] ?? 500) === 200) {
            return view('frontend.employee.employees', ['employees' => $response['data']]);
        }

        return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message'] ?? __('candidate.membership.submit_failed')]);
    }

    public function approveEmployee(Request $request, $employeeId)
    {
        $response = $this->api->approveEmployee($employeeId);

        if ((int) ($response['statusCode'] ?? 500) === 200) {
            return redirect()->back()->with('success_key', 'flash.employee.approved');
        }

        return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message'] ?? __('candidate.membership.submit_failed')]);
    }

    public function rejectEmployee(Request $request, $employeeId)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'rejection_reason' => 'required|string|max:1000',
            ]);

            $data = $request->only(['rejection_reason']);
            $response = $this->api->rejectEmployee($employeeId, $data);

            if ((int) ($response['statusCode'] ?? 500) === 200) {
                return redirect()->back()->with('success_key', 'flash.employee.rejected');
            }

            return redirect()->back()
                ->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message'] ?? __('candidate.membership.submit_failed')])
                ->withInput();
        }

        return view('frontend.employee.reject-employee', get_defined_vars());
    }

    public function removeEmployee(Request $request, $employeeId)
    {
        $response = $this->api->removeEmployee($employeeId);

        if ((int) ($response['statusCode'] ?? 500) === 200) {
            return redirect()->back()->with('success_key', 'flash.employee.deleted');
        }

        return redirect()->back()->withErrors(($response['errors'] ?? []) ?: ['error' => $response['message'] ?? __('candidate.membership.submit_failed')]);
    }

    /**
     * @param  array{message?: mixed, errors?: mixed}  $response
     * @return array<string, mixed>
     */
    private function errorBag(array $response, int $status): array
    {
        $errors = $response['errors'] ?? null;
        if (is_array($errors) && $errors !== []) {
            return $errors;
        }

        $message = is_string($response['message'] ?? null) ? trim((string) $response['message']) : '';
        if ($message !== '') {
            return ['error' => $message];
        }

        return ['error' => match (true) {
            $status === 403 => __('candidate.membership.forbidden'),
            $status === 404 => __('candidate.membership.not_found'),
            $status === 409 => __('candidate.membership.duplicate'),
            $status === 422 => __('candidate.membership.validation_error'),
            $status >= 500 => __('candidate.membership.server_error'),
            default => __('candidate.membership.submit_failed'),
        }];
    }

    /**
     * Normalize personal membership payload without inventing rows.
     *
     * @return list<array<string, mixed>>
     */
    private function normalizeMembershipRows(mixed $data): array
    {
        if ($data === null || $data === []) {
            return [];
        }

        if (is_array($data) && isset($data['id']) && ! array_is_list($data)) {
            return [$data];
        }

        return array_values(array_filter(
            Rows::from($data),
            static fn ($row) => is_array($row) && $row !== []
        ));
    }

    /**
     * Normalize discovery list rows; drop entries missing id/name (never invent).
     *
     * @return list<array<string, mixed>>
     */
    private function normalizeCompanyRows(mixed $data): array
    {
        return array_values(array_filter(
            Rows::from($data),
            static function ($row) {
                if (! is_array($row)) {
                    return false;
                }

                $id = data_get($row, 'id');
                $name = data_get($row, 'name', data_get($row, 'company_name'));

                return filled($id) && filled($name);
            }
        ));
    }
}
