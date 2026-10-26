<?php

namespace App\Support\Frontend;

use Illuminate\Http\Request;

/**
 * Safe local return destinations for Candidate resume upload ↔ apply flows.
 * Rejects absolute URLs, schemes, and any path outside the apply allowlist.
 */
class CandidateReturnPath
{
    /**
     * Resolve a safe relative path back to an apply form, or null when absent/unsafe.
     */
    public static function applyPathFromRequest(Request $request): ?string
    {
        $vacancyId = $request->input('vacancy_id', $request->query('vacancy_id'));

        if (is_numeric($vacancyId) && (int) $vacancyId > 0) {
            return self::applyPathForVacancy((int) $vacancyId);
        }

        return self::sanitizeApplyPath(
            $request->input('return_to', $request->query('return_to'))
        );
    }

    public static function applyPathForVacancy(int $vacancyId): string
    {
        return route('frontend.candidate.vacancies.apply', ['vacancyId' => $vacancyId], false);
    }

    public static function vacancyIdFromSafePath(?string $path): ?int
    {
        $safe = self::sanitizeApplyPath($path);
        if ($safe === null) {
            return null;
        }

        if (preg_match('#^/candidate/vacancies/(\d+)/apply$#', $safe, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }

    /**
     * Only relative paths matching /candidate/vacancies/{id}/apply are accepted.
     */
    public static function sanitizeApplyPath(mixed $path): ?string
    {
        if (! is_string($path) || $path === '') {
            return null;
        }

        $path = trim($path);

        // Absolute / protocol-relative / scheme URLs are never safe return targets.
        if (
            str_contains($path, '://')
            || str_starts_with($path, '//')
            || str_starts_with($path, '\\')
            || preg_match('#^[a-z][a-z0-9+.-]*:#i', $path) === 1
        ) {
            return null;
        }

        if (! str_starts_with($path, '/')) {
            return null;
        }

        $pathOnly = parse_url($path, PHP_URL_PATH);
        if (! is_string($pathOnly) || $pathOnly === '') {
            return null;
        }

        if (preg_match('#^/candidate/vacancies/(\d+)/apply$#', $pathOnly, $matches) !== 1) {
            return null;
        }

        return self::applyPathForVacancy((int) $matches[1]);
    }
}
