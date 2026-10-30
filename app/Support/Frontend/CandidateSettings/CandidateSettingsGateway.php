<?php

namespace App\Support\Frontend\CandidateSettings;

/**
 * Candidate Preferences & Alerts Backend reads/writes.
 *
 * @phpstan-type ApiEnvelope array{
 *     statusCode: int,
 *     message: string,
 *     data: mixed,
 *     errors: mixed
 * }
 */
interface CandidateSettingsGateway
{
    /** GET /candidate/preferences @return ApiEnvelope */
    public function getPreferences(): array;

    /**
     * PUT /candidate/preferences
     *
     * @param  array<string, mixed>  $payload
     * @return ApiEnvelope
     */
    public function updatePreferences(array $payload): array;

    /** GET /candidate/notification-settings @return ApiEnvelope */
    public function getNotificationSettings(): array;

    /**
     * PUT /candidate/notification-settings
     *
     * @param  array<string, mixed>  $payload
     * @return ApiEnvelope
     */
    public function updateNotificationSettings(array $payload): array;

    /** GET /candidate/privacy @return ApiEnvelope */
    public function getPrivacy(): array;

    /**
     * PUT /candidate/privacy
     *
     * @param  array<string, mixed>  $payload
     * @return ApiEnvelope
     */
    public function updatePrivacy(array $payload): array;

    /** GET /candidate/saved-searches @return ApiEnvelope */
    public function listSavedSearches(): array;

    /**
     * POST /candidate/saved-searches
     *
     * @param  array<string, mixed>  $payload
     * @return ApiEnvelope
     */
    public function createSavedSearch(array $payload): array;

    /**
     * PUT /candidate/saved-searches/{id}
     *
     * @param  array<string, mixed>  $payload
     * @return ApiEnvelope
     */
    public function updateSavedSearch(int $id, array $payload): array;

    /** DELETE /candidate/saved-searches/{id} @return ApiEnvelope */
    public function deleteSavedSearch(int $id): array;
}
