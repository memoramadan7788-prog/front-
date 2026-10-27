<?php

namespace App\Support\Frontend\Notifications;

use App\Support\Frontend\SafeInternalRedirect;

/**
 * Maps notification payloads to safe in-app destinations.
 * Never invents absolute off-site URLs or Admin destinations.
 */
class NotificationDestination
{
    /**
     * @param  array<string, mixed>  $item
     */
    public static function href(array $item): ?string
    {
        $explicit = data_get($item, 'action_url', data_get($item, 'url'));
        if (is_string($explicit) && trim($explicit) !== '') {
            $trimmed = trim($explicit);
            // Only relative same-app paths (Backend schema has no action_url today).
            if (str_starts_with($trimmed, '/') && ! str_starts_with($trimmed, '//')) {
                $safe = SafeInternalRedirect::sanitize($trimmed, '/__unsafe__');

                return $safe === '/__unsafe__' ? null : $safe;
            }

            return null;
        }

        $type = strtolower(trim((string) data_get($item, 'type', '')));

        return match (true) {
            str_starts_with($type, 'interview.') => route('frontend.candidate.interviews.index', [], false),
            str_starts_with($type, 'company.join') || str_contains($type, 'membership')
                => route('frontend.candidate.join-company', [], false),
            str_starts_with($type, 'offer.') => route('frontend.candidate.offers.index', [], false),
            str_starts_with($type, 'application.') => route('frontend.candidate.applications.index', [], false),
            default => null,
        };
    }
}
