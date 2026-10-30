<?php

namespace App\Support\Frontend\CandidateSettings;

use App\Frontend\Api\CurlClient;
use App\Frontend\Auth\ApiSession;

/**
 * Authenticated Candidate settings HTTP adapter — no fabricated payloads.
 */
class HttpCandidateSettingsGateway implements CandidateSettingsGateway
{
    public function __construct(
        private ?CurlClient $client = null,
    ) {
        $this->client ??= new CurlClient(
            baseUrl: config('services.backend.url'),
            defaults: ['token_key' => ApiSession::token() ?? '']
        );
    }

    public function getPreferences(): array
    {
        return $this->send('GET', '/candidate/preferences');
    }

    public function updatePreferences(array $payload): array
    {
        return $this->send('PUT', '/candidate/preferences', $payload);
    }

    public function getNotificationSettings(): array
    {
        return $this->send('GET', '/candidate/notification-settings');
    }

    public function updateNotificationSettings(array $payload): array
    {
        return $this->send('PUT', '/candidate/notification-settings', $payload);
    }

    public function getPrivacy(): array
    {
        return $this->send('GET', '/candidate/privacy');
    }

    public function updatePrivacy(array $payload): array
    {
        return $this->send('PUT', '/candidate/privacy', $payload);
    }

    public function listSavedSearches(): array
    {
        return $this->send('GET', '/candidate/saved-searches');
    }

    public function createSavedSearch(array $payload): array
    {
        return $this->send('POST', '/candidate/saved-searches', $payload);
    }

    public function updateSavedSearch(int $id, array $payload): array
    {
        return $this->send('PUT', "/candidate/saved-searches/{$id}", $payload);
    }

    public function deleteSavedSearch(int $id): array
    {
        return $this->send('DELETE', "/candidate/saved-searches/{$id}");
    }

    /**
     * @param  array<string, mixed>|null  $body
     * @return array{statusCode: int, message: string, data: mixed, errors: mixed}
     */
    private function send(string $method, string $path, ?array $body = null): array
    {
        $req = $this->client->reset()
            ->method($method)
            ->url($path)
            ->bearer(ApiSession::token() ?? '');

        if ($body !== null) {
            $req->body($body);
        }

        $res = $req->send();

        return [
            'statusCode' => (int) $res->statusCode,
            'message' => (string) ($res->message ?: ($res->ok() ? 'OK' : "HTTP {$res->statusCode}")),
            'data' => $res->data,
            'errors' => $res->errors,
        ];
    }
}
