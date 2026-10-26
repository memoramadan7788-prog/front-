<?php

namespace App\Support\Frontend\Notifications;

use App\Frontend\Api\CurlClient;
use App\Frontend\Auth\ApiSession;

/**
 * HTTP adapter for notifications. Calls configured paths only; never fabricates items or counts.
 */
class HttpNotificationsGateway implements NotificationsGateway
{
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
        $path = config('services.backend.notifications_path');

        return is_string($path) && trim($path) !== '';
    }

    public function list(int $page = 1): array
    {
        $path = config('services.backend.notifications_path');

        if (! is_string($path) || trim($path) === '') {
            return [
                'available' => false,
                'statusCode' => 0,
                'message' => 'Notifications list endpoint is not configured.',
                'data' => [],
                'meta' => null,
                'errors' => null,
            ];
        }

        $query = http_build_query(['page' => max(1, $page), 'per_page' => 15]);
        $res = $this->client->reset()->method('GET')
            ->url($path.($query !== '' ? '?'.$query : ''))
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
            'meta' => is_array($res->meta ?? null) ? $res->meta : null,
            'errors' => null,
        ];
    }

    public function unreadCount(): array
    {
        $path = config('services.backend.notifications_unread_path');

        if (! is_string($path) || trim($path) === '') {
            // Fall back: unavailable count (do not invent 0 as “all read” when API missing).
            if (! $this->isAvailable()) {
                return [
                    'available' => false,
                    'statusCode' => 0,
                    'message' => 'Notifications unread-count endpoint is not configured.',
                    'unread' => null,
                    'errors' => null,
                ];
            }

            return [
                'available' => false,
                'statusCode' => 0,
                'message' => 'Notifications unread-count endpoint is not configured.',
                'unread' => null,
                'errors' => null,
            ];
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
                'unread' => null,
                'errors' => $res->errors,
            ];
        }

        $count = data_get($res->data, 'unread_count', data_get($res->data, 'count'));
        if ($count === null && is_numeric($res->data)) {
            $count = $res->data;
        }

        return [
            'available' => true,
            'statusCode' => 200,
            'message' => $res->message ?: 'OK',
            'unread' => is_numeric($count) ? max(0, (int) $count) : null,
            'errors' => null,
        ];
    }
}
