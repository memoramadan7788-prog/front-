<?php

namespace App\Support\Frontend\CandidateDashboard;

use App\Frontend\Api\CurlClient;
use App\Frontend\Auth\ApiSession;

/**
 * Authenticated Candidate HTTP reads for Overview KPIs.
 * Does not invent contracts; passes through Backend status codes and pagination meta.
 */
class HttpCandidateDashboardKpiGateway implements CandidateDashboardKpiGateway
{
    public function __construct(
        private ?CurlClient $client = null,
    ) {
        $this->client ??= new CurlClient(
            baseUrl: config('services.backend.url'),
            defaults: ['token_key' => ApiSession::token() ?? '']
        );
    }

    public function applications(int $page = 1, int $perPage = 100): array
    {
        return $this->get('/candidate/applications', [
            'page' => max(1, $page),
            'per_page' => max(1, min(100, $perPage)),
        ]);
    }

    public function interviews(array $query = []): array
    {
        $params = array_filter(
            $query,
            static fn ($v) => $v !== null && $v !== ''
        );

        return $this->get('/candidate/interviews', $params);
    }

    public function offers(): array
    {
        return $this->get('/candidate/offers');
    }

    public function profile(): array
    {
        return $this->get('/candidate/profile');
    }

    public function recommendedJobs(int $page = 1, int $perPage = 6): array
    {
        return $this->get('/candidate/recommended-jobs', [
            'page' => max(1, $page),
            'per_page' => max(1, min(50, $perPage)),
        ]);
    }

    /**
     * @param  array<string, scalar>  $query
     * @return array{statusCode: int, message: string, data: mixed, meta: ?array, errors: mixed}
     */
    private function get(string $path, array $query = []): array
    {
        $queryString = $query !== [] ? '?'.http_build_query($query) : '';

        $res = $this->client->reset()
            ->method('GET')
            ->url($path.$queryString)
            ->bearer(ApiSession::token() ?? '')
            ->send();

        $meta = $res->pagination();
        if ($meta === null && is_array($res->meta)) {
            $meta = $res->meta;
        }

        return [
            'statusCode' => (int) $res->statusCode,
            'message' => (string) ($res->message ?: ($res->ok() ? 'OK' : "HTTP {$res->statusCode}")),
            'data' => $res->data,
            'meta' => $meta,
            'errors' => $res->errors,
        ];
    }
}
