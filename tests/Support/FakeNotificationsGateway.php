<?php

namespace Tests\Support;

use App\Support\Frontend\Notifications\NotificationsGateway;

/**
 * In-memory notifications gateway for non-mutating Feature / Unit tests.
 * Mutation methods record calls and never touch a live Backend.
 */
class FakeNotificationsGateway implements NotificationsGateway
{
    public bool $available = true;

    /** @var list<array<string, mixed>> */
    public array $items = [];

    public ?int $unread = 0;

    public ?array $meta = null;

    public int $listStatus = 200;

    public string $listMessage = 'OK';

    public int $markReadStatus = 200;

    public string $markReadMessage = 'Notification marked as read.';

    public int $markAllStatus = 200;

    public string $markAllMessage = 'All notifications marked as read.';

    /** @var list<int> */
    public array $markedReadIds = [];

    public int $markAllCalls = 0;

    public int $listCalls = 0;

    public function isAvailable(): bool
    {
        return $this->available;
    }

    public function list(int $page = 1, int $perPage = 15): array
    {
        $this->listCalls++;

        if (! $this->available) {
            return [
                'available' => false,
                'statusCode' => 0,
                'message' => 'Unavailable',
                'data' => [],
                'meta' => null,
                'errors' => null,
            ];
        }

        $meta = $this->meta ?? [
            'current_page' => $page,
            'last_page' => 1,
            'per_page' => $perPage,
            'total' => count($this->items),
            'unread_count' => $this->unread,
        ];

        return [
            'available' => true,
            'statusCode' => $this->listStatus,
            'message' => $this->listMessage,
            'data' => $this->listStatus === 200 ? $this->items : [],
            'meta' => $this->listStatus === 200 ? $meta : null,
            'errors' => $this->listStatus === 200 ? null : ['error' => $this->listMessage],
        ];
    }

    public function unreadCount(): array
    {
        if (! $this->available) {
            return [
                'available' => false,
                'statusCode' => 0,
                'message' => 'Unavailable',
                'unread' => null,
                'errors' => null,
            ];
        }

        $list = $this->list(1, 1);

        return [
            'available' => true,
            'statusCode' => (int) ($list['statusCode'] ?? 500),
            'message' => (string) ($list['message'] ?? ''),
            'unread' => ((int) ($list['statusCode'] ?? 500) === 200 && is_numeric($this->unread))
                ? $this->unread
                : null,
            'errors' => $list['errors'] ?? null,
        ];
    }

    public function markAsRead(int $id): array
    {
        $this->markedReadIds[] = $id;

        if (! $this->available) {
            return [
                'available' => false,
                'statusCode' => 0,
                'message' => 'Unavailable',
                'data' => null,
                'errors' => null,
            ];
        }

        return [
            'available' => true,
            'statusCode' => $this->markReadStatus,
            'message' => $this->markReadMessage,
            'data' => $this->markReadStatus === 200 ? ['id' => $id, 'read_at' => '2026-10-27T12:00:00+00:00'] : null,
            'errors' => $this->markReadStatus === 200 ? null : ['error' => $this->markReadMessage],
        ];
    }

    public function markAllAsRead(): array
    {
        $this->markAllCalls++;

        if (! $this->available) {
            return [
                'available' => false,
                'statusCode' => 0,
                'message' => 'Unavailable',
                'updated_count' => null,
                'errors' => null,
            ];
        }

        return [
            'available' => true,
            'statusCode' => $this->markAllStatus,
            'message' => $this->markAllMessage,
            'updated_count' => $this->markAllStatus === 200 ? count(array_filter(
                $this->items,
                static fn ($row) => blank(data_get($row, 'read_at'))
            )) : null,
            'errors' => $this->markAllStatus === 200 ? null : ['error' => $this->markAllMessage],
        ];
    }
}
