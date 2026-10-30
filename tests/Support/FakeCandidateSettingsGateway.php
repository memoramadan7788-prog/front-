<?php

namespace Tests\Support;

use App\Support\Frontend\CandidateSettings\CandidateSettingsGateway;

/**
 * In-memory Candidate settings gateway for Feature tests (no live Backend mutations).
 */
class FakeCandidateSettingsGateway implements CandidateSettingsGateway
{
    /** @var array{statusCode: int, message: string, data: mixed, errors: mixed} */
    public array $preferencesResponse;

    /** @var array{statusCode: int, message: string, data: mixed, errors: mixed} */
    public array $notificationsResponse;

    /** @var array{statusCode: int, message: string, data: mixed, errors: mixed} */
    public array $privacyResponse;

    /** @var array{statusCode: int, message: string, data: mixed, errors: mixed} */
    public array $savedSearchesResponse;

    /** @var array{statusCode: int, message: string, data: mixed, errors: mixed}|null */
    public ?array $updatePreferencesResponse = null;

    /** @var array{statusCode: int, message: string, data: mixed, errors: mixed}|null */
    public ?array $updateNotificationsResponse = null;

    /** @var array{statusCode: int, message: string, data: mixed, errors: mixed}|null */
    public ?array $updatePrivacyResponse = null;

    /** @var array{statusCode: int, message: string, data: mixed, errors: mixed}|null */
    public ?array $createSavedSearchResponse = null;

    /** @var array{statusCode: int, message: string, data: mixed, errors: mixed}|null */
    public ?array $updateSavedSearchResponse = null;

    /** @var array{statusCode: int, message: string, data: mixed, errors: mixed}|null */
    public ?array $deleteSavedSearchResponse = null;

    /** @var array<string, mixed>|null */
    public ?array $lastPreferencesPayload = null;

    /** @var array<string, mixed>|null */
    public ?array $lastNotificationsPayload = null;

    /** @var array<string, mixed>|null */
    public ?array $lastPrivacyPayload = null;

    /** @var array<string, mixed>|null */
    public ?array $lastCreateSearchPayload = null;

    public int $updatePreferencesCalls = 0;

    public int $updateNotificationsCalls = 0;

    public function __construct()
    {
        $this->preferencesResponse = $this->ok([
            'desired_titles' => ['Backend Engineer'],
            'location' => 'Cairo',
            'employment_type' => 'full_time',
            'min_salary' => 15000,
            'work_model' => 'hybrid',
            'open_to_relocation' => true,
            'include_agency' => false,
        ]);
        $this->notificationsResponse = $this->ok([
            'weekly_digest' => false,
            'status_updates' => true,
            'interview_reminders' => true,
            'messages' => true,
            'marketing' => false,
        ]);
        $this->privacyResponse = $this->ok([
            'visibility' => 'employers_only',
            'allow_agency_recruiters' => false,
            'show_in_public_search' => false,
        ]);
        $this->savedSearchesResponse = $this->ok([]);
    }

    public function getPreferences(): array
    {
        return $this->preferencesResponse;
    }

    public function updatePreferences(array $payload): array
    {
        $this->updatePreferencesCalls++;
        $this->lastPreferencesPayload = $payload;

        return $this->updatePreferencesResponse ?? $this->ok($payload);
    }

    public function getNotificationSettings(): array
    {
        return $this->notificationsResponse;
    }

    public function updateNotificationSettings(array $payload): array
    {
        $this->updateNotificationsCalls++;
        $this->lastNotificationsPayload = $payload;

        return $this->updateNotificationsResponse ?? $this->ok($payload);
    }

    public function getPrivacy(): array
    {
        return $this->privacyResponse;
    }

    public function updatePrivacy(array $payload): array
    {
        $this->lastPrivacyPayload = $payload;

        return $this->updatePrivacyResponse ?? $this->ok($payload);
    }

    public function listSavedSearches(): array
    {
        return $this->savedSearchesResponse;
    }

    public function createSavedSearch(array $payload): array
    {
        $this->lastCreateSearchPayload = $payload;

        return $this->createSavedSearchResponse ?? $this->ok(array_merge(['id' => 99], $payload), 201);
    }

    public function updateSavedSearch(int $id, array $payload): array
    {
        return $this->updateSavedSearchResponse ?? $this->ok(array_merge(['id' => $id], $payload));
    }

    public function deleteSavedSearch(int $id): array
    {
        return $this->deleteSavedSearchResponse ?? $this->ok(null);
    }

    /**
     * @param  mixed  $data
     * @return array{statusCode: int, message: string, data: mixed, errors: mixed}
     */
    public function ok(mixed $data, int $status = 200): array
    {
        return [
            'statusCode' => $status,
            'message' => 'OK',
            'data' => $data,
            'errors' => null,
        ];
    }

    /**
     * @param  mixed  $errors
     * @return array{statusCode: int, message: string, data: mixed, errors: mixed}
     */
    public function fail(int $status, string $message, mixed $errors = null): array
    {
        return [
            'statusCode' => $status,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ];
    }
}
