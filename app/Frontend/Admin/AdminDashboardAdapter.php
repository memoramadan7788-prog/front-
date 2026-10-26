<?php

namespace App\Frontend\Admin;

use App\Models\AdminAPI;

class AdminDashboardAdapter
{
    /** Matches Backend AccountStatus::DEACTIVATED / BLOCKED (and AdminController::USER_STATUSES). */
    private const STATUS_DEACTIVATED = 2;

    private const STATUS_BLOCKED = 3;

    public function __construct(
        private readonly AdminAPI $api
    ) {}

    public function load(): array
    {
        $users = $this->api->getUsers(['per_page' => 1]);
        $companies = $this->api->getCompanies(['per_page' => 1]);

        $errors = [];

        foreach (['users' => $users, 'companies' => $companies] as $name => $response) {
            if (($response['statusCode'] ?? 500) !== 200) {
                $errors[] = "Could not load {$name} statistics.";
            }
        }

        $stats = [
            'users' => [
                'total' => $this->total($users),
                // Admin users API has no created_at / date-range filters.
                'growth_available' => false,
                'new_30d' => null,
                'prev_30d' => null,
                'inactive_available' => false,
                'inactive' => null,
                'by_type' => [],
            ],
            'companies' => [
                'total' => $this->total($companies),
                'by_status' => [],
            ],
            'pending_companies' => [],
            'recent_activity' => [],
            // Backend has no admin platform-wide Jobs/Applications stats APIs.
            'jobs' => [
                'available' => false,
                'published' => null,
                'total' => null,
            ],
            'applications' => [
                'available' => false,
                'total' => null,
                'last_30d' => null,
                'prev_30d' => null,
            ],
            // Interviews APIs are company / candidate / interviewer scoped only.
            'interviews' => [
                'available' => false,
                'scheduled' => null,
            ],
            // No admin trend / registration-by-month endpoints.
            'trend' => [],
            'trend_available' => false,
        ];

        foreach (['approved', 'pending', 'suspended', 'rejected'] as $status) {
            $result = $this->api->getCompanies([
                'status' => $status,
                'per_page' => 1,
            ]);

            $stats['companies']['by_status'][$status] = $this->total($result);

            if (($result['statusCode'] ?? 500) !== 200) {
                $errors[] = "Could not load {$status} company statistics.";
            }
        }

        foreach (['candidate', 'company', 'admin'] as $type) {
            $result = $this->api->getUsers([
                'account_type' => $type,
                'per_page' => 1,
            ]);

            $stats['users']['by_type'][$type] = $this->total($result);

            if (($result['statusCode'] ?? 500) !== 200) {
                $errors[] = "Could not load {$type} user statistics.";
            }
        }

        $deactivated = $this->api->getUsers([
            'account_status_id' => self::STATUS_DEACTIVATED,
            'per_page' => 1,
        ]);
        $blocked = $this->api->getUsers([
            'account_status_id' => self::STATUS_BLOCKED,
            'per_page' => 1,
        ]);

        $deactivatedTotal = $this->total($deactivated);
        $blockedTotal = $this->total($blocked);

        if ($deactivatedTotal !== null && $blockedTotal !== null) {
            $stats['users']['inactive'] = $deactivatedTotal + $blockedTotal;
            $stats['users']['inactive_available'] = true;
        } else {
            $errors[] = 'Could not load inactive account statistics.';
        }

        $pendingResponse = $this->api->getCompanies([
            'status' => 'pending',
            'per_page' => 5,
        ]);

        if (($pendingResponse['statusCode'] ?? 500) === 200) {
            $stats['pending_companies'] = is_array($pendingResponse['data'] ?? null)
                ? $pendingResponse['data']
                : [];
        } else {
            $errors[] = 'Could not load pending companies.';
        }

        $logsResponse = $this->api->getAuditLogs([
            'per_page' => 5,
        ]);

        if (($logsResponse['statusCode'] ?? 500) === 200) {
            $stats['recent_activity'] = is_array($logsResponse['data'] ?? null)
                ? $logsResponse['data']
                : [];
        } else {
            $errors[] = 'Could not load recent activity.';
        }

        return [
            'stats' => $stats,
            'loadError' => $errors ? implode(' ', $errors) : null,
        ];
    }

    private function total(array $response): ?int
    {
        if (($response['statusCode'] ?? 500) !== 200) {
            return null;
        }

        $total = $response['meta']['total'] ?? null;

        return is_numeric($total) ? (int) $total : null;
    }
}
