<?php

namespace App\Support\Frontend;

/**
 * Profile picture presentation helpers.
 *
 * Backend today: candidate_profiles.photo_path is returned on GET profile, but
 * UpdateProfileRequest does not accept photo uploads and no dedicated avatar
 * upload/delete/media URL endpoints exist. Frontend stays preview-only until APIs ship.
 *
 * Proposed client validation (Backend approval required):
 *   mime: image/jpeg, image/png, image/webp
 *   max: 2 MB
 */
class ProfileAvatar
{
    /** @return list<string> */
    public static function acceptedMimeTypes(): array
    {
        return ['image/jpeg', 'image/png', 'image/webp'];
    }

    /** @return list<string> */
    public static function acceptedExtensions(): array
    {
        return ['jpg', 'jpeg', 'png', 'webp'];
    }

    public static function maxBytes(): int
    {
        return 2 * 1024 * 1024;
    }

    public static function maxMb(): int
    {
        return (int) round(self::maxBytes() / (1024 * 1024));
    }

    /**
     * Resolve a safely renderable avatar URL from a profile payload (never invent).
     */
    public static function urlFromProfile(mixed $profile): ?string
    {
        if (! is_array($profile)) {
            return null;
        }

        return TopbarAccount::safeAvatarUrl(
            data_get($profile, 'photo_url')
                ?? data_get($profile, 'avatar_url')
                ?? data_get($profile, 'photo_path')
        );
    }

    public static function uploadAvailable(): bool
    {
        $path = config('services.backend.profile_avatar_path');

        return is_string($path) && trim($path) !== '';
    }
}
