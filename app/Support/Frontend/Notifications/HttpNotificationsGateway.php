<?php

namespace App\Support\Frontend\Notifications;

use App\Frontend\Api\CurlClient;
use App\Frontend\Auth\ApiSession;

/**
 * Candidate notifications HTTP adapter.
 * Never fabricates items/counts; never calls Candidate APIs for Company/Admin shells.
 */
class HttpNotificationsGateway implements NotificationsGateway
{
    public const CANDIDATE_LIST_PATH = '/candidate/notifications';

    public function __construct(
        private ?CurlClient $client = null,
    ) {
        $this->client ??= new CurlClient(
            baseUrl: config('services.backend.url'),
            defaults: ['token_key' => ApiSession::token() ?? '']
        );
    }

    public function isAvailable(): bool
    {
        // Role isolation: Candidate notification APIs are Candidate-scoped only.
        if (ApiSession::accountType() !== 'candidate') {
            return false;
        }

        $path = $this->listPath();

        return is_string($path) && trim($path) !== '';
    }

    public function list(int $page = 1, int $perPage = 15): array
    {
        if (! $this->isAvailable()) {
            return [
                'available' => false,
                'statusCode' => 0,
                'message' => 'Notifications are not available for this account type.',
                'data' => [],
                'meta' => null,
                'errors' => null,
            ];
        }

        $path = $this->listPath();
        $query = http_build_query([
            'page' => max(1, $page),
            'per_page' => max(1, min(50, $perPage)),
        ]);

        $res = $this->client->reset()->method('GET')
            ->url($path.'?'.$query)
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

        return [
            'available' => true,
            'statusCode' => 200,
            'message' => $res->message ?: 'OK',
            'data' => array_values(array_filter(
                is_array($rows) && array_is_list($rows) ? $rows : [],
                static fn ($row) => is_array($row)
            )),
            'meta' => $this->normalizeMeta($res->meta, $res->pagination()),
            'errors' => null,
        ];
    }

    public function unreadCount(): array
    {
        if (! $this->isAvailable()) {
            return [
                'available' => false,
                'statusCode' => 0,
                'message' => 'Notifications are not available for this account type.',
                'unread' => null,
                'errors' => null,
            ];
        }

        // Backend has no dedicated unread endpoint — unread_count lives on list meta.
        $list = $this->list(1, 1);
        if (! ($list['available'] ?? false)) {
            return [
                'available' => false,
                'statusCode' => (int) ($list['statusCode'] ?? 0),
                'message' => (string) ($list['message'] ?? ''),
                'unread' => null,
                'errors' => $list['errors'] ?? null,
            ];
        }

        if ((int) ($list['statusCode'] ?? 500) !== 200) {
            return [
                'available' => true,
                'statusCode' => (int) ($list['statusCode'] ?? 500),
                'message' => (string) ($list['message'] ?? 'Error'),
                'unread' => null,
                'errors' => $list['errors'] ?? null,
            ];
        }

        $unread = data_get($list['meta'], 'unread_count');

        return [
            'available' => true,
            'statusCode' => 200,
            'message' => 'OK',
            'unread' => is_numeric($unread) ? max(0, (int) $unread) : null,
            'errors' => null,
        ];
    }

    public function markAsRead(int $id): array
    {
        if (! $this->isAvailable()) {
            return [
                'available' => false,
                'statusCode' => 0,
                'message' => 'Notifications are not available for this account type.',
                'data' => null,
                'errors' => null,
            ];
        }

        if ($id < 1) {
            return [
                'available' => true,
                'statusCode' => 422,
                'message' => 'Invalid notification id.',
                'data' => null,
                'errors' => ['id' => ['Invalid notification id.']],
            ];
        }

        $path = rtrim($this->listPath(), '/').'/'.$id.'/read';
        $res = $this->client->reset()->method('PATCH')
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

        $payload = is_array($res->data) ? $res->data : null;

        return [
            'available' => true,
            'statusCode' => 200,
            'message' => $res->message ?: 'OK',
            'data' => $payload,
            'errors' => null,
        ];
    }

    public function markAllAsRead(): array
    {
        if (! $this->isAvailable()) {
            return [
                'available' => false,
                'statusCode' => 0,
                'message' => 'Notifications are not available for this account type.',
                'updated_count' => null,
                'errors' => null,
            ];
        }

        $path = rtrim($this->listPath(), '/').'/read-all';
        $res = $this->client->reset()->method('PATCH')
            ->url($path)
            ->bearer(ApiSession::token() ?? '')
            ->send();

        if (! $res->ok()) {
            return [
                'available' => true,
                'statusCode' => (int) $res->statusCode,
                'message' => $res->message ?: "HTTP {$res->statusCode}",
                'updated_count' => null,
                'errors' => $res->errors,
            ];
        }

        $count = data_get($res->data, 'updated_count');
        if ($count === null && is_array($res->data) && isset($res->data['updated_count'])) {
            $count = $res->data['updated_count'];
        }
        // Backend returns top-level updated_count when data is whole JSON body in some shapes.
        if ($count === null) {
            $count = data_get(is_array($res->data) ? $res->data : [], 'updated_count');
        }

        return [
            'available' => true,
            'statusCode' => 200,
            'message' => $res->message ?: 'OK',
            'updated_count' => is_numeric($count) ? (int) $count : null,
            'errors' => null,
        ];
    }

    private function listPath(): string
    {
        $configured = config('services.backend.notifications_path');
        if (is_string($configured) && trim($configured) !== '') {
            return trim($configured);
        }

        return self::CANDIDATE_LIST_PATH;
    }

    /**
     * @param  array<string, mixed>|null  $rawMeta
     * @param  array<string, mixed>|null  $pagination
     * @return array<string, mixed>|null
     */
    private function normalizeMeta(?array $rawMeta, ?array $pagination): ?array
    {
        $base = $pagination ?? (is_array($rawMeta) ? $rawMeta : null);
        if ($base === null) {
            return null;
        }

        // Flatten pagination wrapper for Blade consumers.
        $meta = $base;
        if (isset($base['pagination']) && is_array($base['pagination'])) {
            $meta = $base['pagination'];
        }

        return $meta;
    }
}
