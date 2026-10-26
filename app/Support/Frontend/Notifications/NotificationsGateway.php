<?php

namespace App\Support\Frontend\Notifications;

/**
 * Candidate/Company/Admin notifications integration surface.
 *
 * Existing Backend: Notification model + internal creates — no Candidate/Company
 * HTTP list/read/unread APIs found. Proposed contracts:
 *   GET  /api/notifications
 *   GET  /api/notifications/unread-count
 *   POST /api/notifications/{id}/read
 *   POST /api/notifications/read-all
 *
 * See reports/PROFILE_AVATAR_NOTIFICATIONS_BACKEND_REQUIREMENTS.md
 */
interface NotificationsGateway
{
    /**
     * @return array{
     *     available: bool,
     *     statusCode: int,
     *     message: string,
     *     data: list<array<string, mixed>>,
     *     meta: array<string, mixed>|null,
     *     errors: array<string, mixed>|null
     * }
     */
    public function list(int $page = 1): array;

    /**
     * @return array{
     *     available: bool,
     *     statusCode: int,
     *     message: string,
     *     unread: int|null,
     *     errors: array<string, mixed>|null
     * }
     */
    public function unreadCount(): array;

    public function isAvailable(): bool;
}
