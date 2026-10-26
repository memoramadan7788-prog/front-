<?php

namespace App\Support\Frontend;

use App\Frontend\Auth\ApiSession;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Normalizes list-page API outcomes for Candidate pipeline screens.
 * Empty 200 ≠ failure; 401 clears session; other errors render an error state (not a fake empty list).
 */
class ApiListPage
{
    /**
     * @param  array{message?: mixed, data?: mixed, errors?: mixed, statusCode?: mixed}  $response
     * @param  array<string, mixed>  $extra
     */
    public static function render(
        array $response,
        string $view,
        string $dataKey,
        array $extra = [],
    ): View|RedirectResponse {
        $status = (int) ($response['statusCode'] ?? 500);

        if ($status === 401) {
            ApiSession::forget();

            return redirect()->route('frontend.account.login')
                ->withErrors(['error' => __('candidate.pipeline.session_expired')]);
        }

        if ($status === 200) {
            return view($view, array_merge($extra, [
                $dataKey => $response['data'] ?? [],
                'loadError' => null,
                'loadStatus' => 200,
            ]));
        }

        return view($view, array_merge($extra, [
            $dataKey => [],
            'loadError' => self::errorMessage($response, $status),
            'loadStatus' => $status,
        ]));
    }

    /**
     * @param  array{message?: mixed, errors?: mixed}  $response
     */
    public static function errorMessage(array $response, int $status): string
    {
        $errors = $response['errors'] ?? null;
        if (is_array($errors) && $errors !== []) {
            $first = $errors['error'] ?? reset($errors);
            if (is_array($first)) {
                $first = reset($first);
            }
            if (is_string($first) && trim($first) !== '') {
                return trim($first);
            }
        }

        $message = $response['message'] ?? null;
        if (is_string($message) && trim($message) !== '') {
            return trim($message);
        }

        return match (true) {
            $status === 403 => __('candidate.pipeline.forbidden'),
            $status === 422 => __('candidate.pipeline.validation_error'),
            $status >= 500 => __('candidate.pipeline.server_error'),
            default => __('candidate.pipeline.load_error'),
        };
    }
}
