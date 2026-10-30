<?php

namespace Tests\Support;

use App\Support\Frontend\CandidateDashboard\CandidateDashboardKpiGateway;

/**
 * In-memory Candidate KPI gateway for non-mutating Feature / Unit tests.
 */
class FakeCandidateDashboardKpiGateway implements CandidateDashboardKpiGateway
{
    /** @var list<array{statusCode: int, message: string, data: mixed, meta: ?array, errors: mixed}> */
    public array $applicationPages = [];

    /** @var list<array{statusCode: int, message: string, data: mixed, meta: ?array, errors: mixed}> */
    public array $interviewPages = [];

    /** @var array{statusCode: int, message: string, data: mixed, meta: ?array, errors: mixed} */
    public array $offersResponse = [
        'statusCode' => 200,
        'message' => 'OK',
        'data' => [],
        'meta' => null,
        'errors' => null,
    ];

    /** @var array{statusCode: int, message: string, data: mixed, meta: ?array, errors: mixed} */
    public array $profileResponse = [
        'statusCode' => 200,
        'message' => 'OK',
        'data' => [
            'phone' => null,
            'location' => null,
            'headline' => null,
            'bio' => null,
            'has_no_experience' => false,
            'years_experience' => null,
            'experiences' => [],
            'educations' => [],
            'cvs' => [],
        ],
        'meta' => null,
        'errors' => null,
    ];

    /** @var array{statusCode: int, message: string, data: mixed, meta: ?array, errors: mixed} */
    public array $recommendedJobsResponse = [
        'statusCode' => 200,
        'message' => 'OK',
        'data' => [],
        'meta' => ['total' => 0, 'last_page' => 1, 'current_page' => 1, 'per_page' => 6],
        'errors' => null,
    ];

    public int $applicationCalls = 0;

    public int $interviewCalls = 0;

    public int $recommendedJobsCalls = 0;

    public function __construct()
    {
        $this->applicationPages = [
            $this->okPage([], ['total' => 0, 'last_page' => 1, 'current_page' => 1, 'per_page' => 100]),
        ];
        $this->interviewPages = [
            $this->okPage([], ['total' => 0, 'last_page' => 1, 'current_page' => 1, 'per_page' => 100]),
        ];
    }

    public function applications(int $page = 1, int $perPage = 100): array
    {
        $this->applicationCalls++;
        $index = max(0, $page - 1);

        return $this->applicationPages[$index]
            ?? $this->okPage([], ['total' => 0, 'last_page' => 1, 'current_page' => $page, 'per_page' => $perPage]);
    }

    public function interviews(array $query = []): array
    {
        $this->interviewCalls++;
        $page = max(1, (int) ($query['page'] ?? 1));
        $index = $page - 1;

        return $this->interviewPages[$index]
            ?? $this->okPage([], ['total' => 0, 'last_page' => 1, 'current_page' => $page, 'per_page' => 100]);
    }

    public function offers(): array
    {
        return $this->offersResponse;
    }

    public function profile(): array
    {
        return $this->profileResponse;
    }

    public function recommendedJobs(int $page = 1, int $perPage = 6): array
    {
        $this->recommendedJobsCalls++;

        return $this->recommendedJobsResponse;
    }

    /**
     * @param  list<array<string, mixed>>  $data
     * @param  array<string, mixed>|null  $meta
     * @return array{statusCode: int, message: string, data: mixed, meta: ?array, errors: mixed}
     */
    public function okPage(array $data, ?array $meta = null): array
    {
        return [
            'statusCode' => 200,
            'message' => 'OK',
            'data' => $data,
            'meta' => $meta,
            'errors' => null,
        ];
    }

    /**
     * @return array{statusCode: int, message: string, data: mixed, meta: ?array, errors: mixed}
     */
    public function error(int $status, string $message = 'Error'): array
    {
        return [
            'statusCode' => $status,
            'message' => $message,
            'data' => null,
            'meta' => null,
            'errors' => ['error' => $message],
        ];
    }
}
