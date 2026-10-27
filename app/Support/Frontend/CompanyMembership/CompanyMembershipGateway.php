<?php

namespace App\Support\Frontend\CompanyMembership;

/**
 * Candidate company-membership integration surface.
 *
 * Existing Backend contract reused unchanged:
 *   POST /api/company/join-requests
 *
 * Proposed (Backend approval required — see reports/COMPANY_MEMBERSHIP_BACKEND_API_REQUIREMENTS.md):
 *   GET /api/companies/approved — discovery for Company dropdown (pagination + optional q)
 *   GET /api/candidate/membership (or equivalent personal join-request status)
 *
 * Implementations must never call Admin-only company APIs.
 */
interface CompanyMembershipGateway
{
    /**
     * List eligible (approved) companies for the Candidate Company dropdown.
     *
     * Returns companies (id + name). Optional $query may filter; current UI uses a
     * simple single-select populated from the default list.
     *
     * @return array{
     *     available: bool,
     *     statusCode: int,
     *     message: string,
     *     data: list<array<string, mixed>>,
     *     meta: array<string, mixed>|null,
     *     errors: array<string, mixed>|null
     * }
     */
    public function searchCompanies(?string $query = null, int $page = 1): array;

    /**
     * Load the authenticated user's membership / join-request record(s).
     *
     * @return array{
     *     available: bool,
     *     statusCode: int,
     *     message: string,
     *     data: mixed,
     *     errors: array<string, mixed>|null
     * }
     */
    public function myMembership(): array;

    /**
     * True only when company discovery is wired so Candidates never type raw company IDs.
     */
    public function canSubmitJoinRequest(): bool;

    /**
     * Existing join-request contract (POST /company/join-requests).
     *
     * @param  array{company_id: int|string, requested_job_title: string, requested_department?: string|null}  $data
     * @return array{message: mixed, data: mixed, errors: mixed, statusCode: int}
     */
    public function requestToJoin(array $data): array;
}
