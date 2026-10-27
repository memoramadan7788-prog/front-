<?php

namespace App\Support\Frontend\Notifications;

/**
 * Notifications integration surface.
 *
 * Candidate Backend (shipped):
 *   GET   /api/candidate/notifications          (meta.unread_count + pagination)
 *   PATCH /api/candidate/notifications/{id}/read
 *   PATCH /api/candidate/notifications/read-all
 *
 * Company / Super Admin: no Candidate-safe shared HTTP surface — gateway reports unavailable.
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
    public function list(int $page = 1, int $perPage = 15): array;

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

    /**
     * PATCH mark-one-read. Tests must mock this — never hit a live Backend in PHPUnit.
     *
     * @return array{
     *     available: bool,
     *     statusCode: int,
     *     message: string,
     *     data: array<string, mixed>|null,
     *     errors: array<string, mixed>|null
     * }
     */
    public function markAsRead(int $id): array;

    /**
     * PATCH mark-all-read.
     *
     * @return array{
     *     available: bool,
     *     statusCode: int,
     *     message: string,
     *     updated_count: int|null,
     *     errors: array<string, mixed>|null
     * }
     */
    public function markAllAsRead(): array;

    public function isAvailable(): bool;
}
