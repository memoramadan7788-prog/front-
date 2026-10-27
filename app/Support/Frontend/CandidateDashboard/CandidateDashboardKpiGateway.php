<?php

namespace App\Support\Frontend\CandidateDashboard;

/**
 * Raw Candidate Backend reads used by the Overview KPI adapter.
 *
 * @phpstan-type ApiEnvelope array{
 *     statusCode: int,
 *     message: string,
 *     data: mixed,
 *     meta: ?array,
 *     errors: mixed
 * }
 */
interface CandidateDashboardKpiGateway
{
    /**
     * GET /candidate/applications
     *
     * @return ApiEnvelope
     */
    public function applications(int $page = 1, int $perPage = 100): array;

    /**
     * GET /candidate/interviews
     *
     * @param  array<string, scalar|null>  $query
     * @return ApiEnvelope
     */
    public function interviews(array $query = []): array;

    /**
     * GET /candidate/offers
     *
     * @return ApiEnvelope
     */
    public function offers(): array;

    /**
     * GET /candidate/profile
     *
     * @return ApiEnvelope
     */
    public function profile(): array;
}
