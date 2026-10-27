<?php

namespace App\Frontend\Admin;

use App\Models\AdminAPI;

/**
 * Builds a complete company option list for Admin filters by paging the authorized list API.
 *
 * Never treats a single page as the full catalog.
 */
final class AdminCompanyCatalog
{
    public const MAX_PAGES = 40;

    public const PER_PAGE = 100;

    public function __construct(
        private readonly AdminAPI $api
    ) {}

    /**
     * @return array{
     *     options: list<array{id: int|string, name: string, status: ?string}>,
     *     status: 'ok'|'empty'|'incomplete'|'error',
     *     message: ?string,
     *     pages_loaded: int,
     *     total: ?int
     * }
     */
    public function load(?string $status = null): array
    {
        $options = [];
        $pagesLoaded = 0;
        $reportedTotal = null;
        $lastPage = null;

        for ($page = 1; $page <= self::MAX_PAGES; $page++) {
            $filters = array_filter([
                'page' => $page,
                'per_page' => self::PER_PAGE,
                'status' => $status,
            ], static fn ($v) => $v !== null && $v !== '');

            $response = $this->api->getCompanies($filters);
            $code = (int) ($response['statusCode'] ?? 500);

            if ($code === 401 || $code === 403) {
                return [
                    'options' => [],
                    'status' => 'error',
                    'message' => trim((string) ($response['message'] ?? '')) ?: 'Unauthorized.',
                    'pages_loaded' => $pagesLoaded,
                    'total' => $reportedTotal,
                ];
            }

            if ($code !== 200) {
                return [
                    'options' => [],
                    'status' => 'error',
                    'message' => trim((string) ($response['message'] ?? '')) ?: 'Could not load companies.',
                    'pages_loaded' => $pagesLoaded,
                    'total' => $reportedTotal,
                ];
            }

            $pagesLoaded++;
            $rows = is_array($response['data'] ?? null) ? $response['data'] : [];
            $meta = is_array($response['meta'] ?? null) ? $response['meta'] : [];

            if (is_numeric($meta['total'] ?? null)) {
                $reportedTotal = (int) $meta['total'];
            }
            if (is_numeric($meta['last_page'] ?? null)) {
                $lastPage = (int) $meta['last_page'];
            }

            foreach ($rows as $row) {
                if (! is_array($row) || ! isset($row['id'])) {
                    continue;
                }
                $name = trim((string) ($row['name'] ?? ''));
                if ($name === '') {
                    continue;
                }
                $options[] = [
                    'id' => $row['id'],
                    'name' => $name,
                    'status' => isset($row['status']) ? (string) $row['status'] : null,
                ];
            }

            // Prefer Backend last_page; otherwise stop when a short page arrives.
            if ($lastPage !== null && $page >= $lastPage) {
                break;
            }
            if ($lastPage === null && count($rows) < self::PER_PAGE) {
                break;
            }

            if ($page === self::MAX_PAGES && ($lastPage === null || $page < $lastPage)) {
                return [
                    'options' => $this->uniqueSorted($options),
                    'status' => 'incomplete',
                    'message' => 'Company list truncated before all pages were loaded.',
                    'pages_loaded' => $pagesLoaded,
                    'total' => $reportedTotal,
                ];
            }
        }

        $unique = $this->uniqueSorted($options);

        return [
            'options' => $unique,
            'status' => $unique === [] ? 'empty' : 'ok',
            'message' => null,
            'pages_loaded' => $pagesLoaded,
            'total' => $reportedTotal ?? count($unique),
        ];
    }

    /**
     * @param  list<array{id: int|string, name: string, status: ?string}>  $options
     * @return list<array{id: int|string, name: string, status: ?string}>
     */
    private function uniqueSorted(array $options): array
    {
        $byId = [];
        foreach ($options as $row) {
            $byId[(string) $row['id']] = $row;
        }
        $list = array_values($byId);
        usort($list, static fn ($a, $b) => strcasecmp((string) $a['name'], (string) $b['name']));

        return $list;
    }
}
