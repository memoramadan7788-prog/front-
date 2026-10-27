<?php

namespace App\Support\Frontend\CompanyMembership;

use App\Frontend\Api\CurlClient;
use App\Frontend\Auth\ApiSession;
use App\Models\EmployeeAPI;

/**
 * HTTP adapter for Candidate membership.
 *
 * - Join request: delegates to EmployeeAPI → POST /company/join-requests (unchanged).
 * - Discovery / personal status: call proposed paths only when configured; otherwise
 *   return available=false with empty data (never fabricate; never hit Admin APIs).
 */
class HttpCompanyMembershipGateway implements CompanyMembershipGateway
{
    public function __construct(
        private EmployeeAPI $employees,
        private ?CurlClient $client = null,
    ) {
        $this->client ??= new CurlClient(
            baseUrl: config('services.backend.url'),
            defaults: ['token_key' => ApiSession::token() ?? '']
        );
    }

    public function searchCompanies(?string $query = null, int $page = 1): array
    {
        $path = config('services.backend.company_discovery_path');

        if (! is_string($path) || trim($path) === '') {
            return $this->unavailable(
                data: [],
                message: 'Company discovery endpoint is not configured.',
                meta: null,
            );
        }

        // Dropdown selector: load approved companies by name (optional q; paginated).
        $params = array_filter([
            'q' => $query !== null && trim($query) !== '' ? trim($query) : null,
            'page' => max(1, $page),
            'per_page' => 100,
            'status' => 'approved',
        ], static fn ($v) => $v !== null);

        $queryString = http_build_query($params);
        $url = $path.($queryString !== '' ? '?'.$queryString : '');

        $res = $this->client->reset()->method('GET')
            ->url($url)
            ->bearer(ApiSession::token() ?? '')
            ->send();

        if (! $res->ok()) {
            return [
                'available' => true,
                'statusCode' => (int) $res->statusCode,
                'message' => $res->message ?: "HTTP {$res->statusCode}",
                'data' => [],
                'meta' => null,
                'errors' => $res->errors,
            ];
        }

        $rows = is_array($res->data) ? $res->data : [];
        if (isset($rows['data']) && is_array($rows['data'])) {
            $rows = $rows['data'];
        }

        $meta = is_array($res->meta ?? null) ? $res->meta : null;
        if ($meta === null && is_array($res->data) && isset($res->data['current_page'])) {
            $meta = [
                'current_page' => $res->data['current_page'] ?? 1,
                'last_page' => $res->data['last_page'] ?? 1,
                'per_page' => $res->data['per_page'] ?? null,
                'total' => $res->data['total'] ?? null,
            ];
        }

        return [
            'available' => true,
            'statusCode' => 200,
            'message' => $res->message ?: 'OK',
            'data' => array_values(array_filter(
                is_array($rows) && array_is_list($rows) ? $rows : [],
                static fn ($row) => is_array($row)
            )),
            'meta' => $meta,
            'errors' => null,
        ];
    }

    public function myMembership(): array
    {
        $path = config('services.backend.candidate_membership_path');

        if (! is_string($path) || trim($path) === '') {
            return $this->unavailable(
                data: null,
                message: 'Personal membership status endpoint is not configured.',
            );
        }

        $res = $this->client->reset()->method('GET')
            ->url($path)
            ->bearer(ApiSession::token() ?? '')
            ->send();

        if (! $res->ok()) {
            return [
                'available' => true,
                'statusCode' => (int) $res->statusCode,
                'message' => $res->message ?: "HTTP {$res->statusCode}",
                'data' => null,
                'errors' => $res->errors,
            ];
        }

        return [
            'available' => true,
            'statusCode' => 200,
            'message' => $res->message ?: 'OK',
            'data' => $res->data,
            'errors' => null,
        ];
    }

    public function canSubmitJoinRequest(): bool
    {
        // Candidates must pick a company by name; raw ID entry is not allowed.
        return $this->searchCompanies()['available'] === true;
    }

    public function requestToJoin(array $data): array
    {
        return $this->employees->requestToJoin($data);
    }

    /**
     * @param  list<array<string, mixed>>|null  $data
     * @return array{
     *     available: bool,
     *     statusCode: int,
     *     message: string,
     *     data: mixed,
     *     meta?: array<string, mixed>|null,
     *     errors: null
     * }
     */
    private function unavailable(mixed $data, string $message, ?array $meta = null): array
    {
        $payload = [
            'available' => false,
            'statusCode' => 0,
            'message' => $message,
            'data' => $data,
            'errors' => null,
        ];

        if (func_num_args() >= 3) {
            $payload['meta'] = $meta;
        }

        return $payload;
    }
}
