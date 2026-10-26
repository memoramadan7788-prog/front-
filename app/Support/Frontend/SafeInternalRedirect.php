<?php

namespace App\Support\Frontend;

use Illuminate\Http\Request;

/**
 * Same-application redirect targets only (no open redirects).
 * Used by locale switching and other presentation-only return flows.
 */
class SafeInternalRedirect
{
    /**
     * Resolve a safe in-app path from an explicit redirect query/body or the previous URL.
     */
    public static function fromRequest(Request $request, ?string $fallback = null): string
    {
        $fallback ??= route('frontend.home', [], false);

        $explicit = $request->query('redirect', $request->input('redirect'));
        if (is_string($explicit) && $explicit !== '') {
            return self::sanitize($explicit, $fallback);
        }

        $previous = url()->previous();
        if (is_string($previous) && $previous !== '' && $previous !== url()->current()) {
            return self::sanitize($previous, $fallback);
        }

        return $fallback;
    }

    /**
     * Accept relative app paths or absolute URLs on the configured app host.
     * Always returns a relative path (+ optional query) starting with "/".
     */
    public static function sanitize(mixed $target, ?string $fallback = null): string
    {
        $fallback ??= route('frontend.home', [], false);

        if (! is_string($target)) {
            return $fallback;
        }

        $target = trim($target);
        if ($target === '') {
            return $fallback;
        }

        // Protocol-relative, schemes, or backslash tricks are never safe.
        if (
            str_starts_with($target, '//')
            || str_starts_with($target, '\\')
            || preg_match('#^[a-z][a-z0-9+.-]*:#i', $target) === 1
        ) {
            // Absolute http(s) URLs are handled below; other schemes are rejected.
            if (! preg_match('#^https?://#i', $target)) {
                return $fallback;
            }
        }

        $path = null;
        $query = null;

        if (str_starts_with($target, '/')) {
            $path = parse_url($target, PHP_URL_PATH);
            $query = parse_url($target, PHP_URL_QUERY);
        } elseif (filter_var($target, FILTER_VALIDATE_URL)) {
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
            $targetHost = parse_url($target, PHP_URL_HOST);

            if (! is_string($appHost) || ! is_string($targetHost) || strcasecmp($appHost, $targetHost) !== 0) {
                return $fallback;
            }

            $path = parse_url($target, PHP_URL_PATH);
            $query = parse_url($target, PHP_URL_QUERY);
        } else {
            return $fallback;
        }

        if (! is_string($path) || $path === '' || ! str_starts_with($path, '/')) {
            return $fallback;
        }

        // Never bounce back onto the locale switch endpoint (loop risk).
        if (preg_match('#^/locale(/|$)#', $path) === 1) {
            return $fallback;
        }

        return is_string($query) && $query !== ''
            ? $path.'?'.$query
            : $path;
    }
}
