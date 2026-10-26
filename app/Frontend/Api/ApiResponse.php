<?php

namespace App\Frontend\Api;

/**
 * ApiResponse — mirrors Laravel backend's App\Http\Responses\ApiResponse.
 *
 * Backend shape:
 *   { success, message, data, errors?, error_code?, meta? }
 */
class ApiResponse
{
    public bool $success;
    public string $message;
    public mixed $data;
    public ?array $errors;
    public ?string $errorCode;
    public ?array $meta;
    public int $statusCode;

    public function __construct(
        bool $success,
        string $message,
        mixed $data = null,
        ?array $errors = null,
        ?string $errorCode = null,
        ?array $meta = null,
        int $statusCode = 200
    ) {
        $this->success    = $success;
        $this->message    = $message;
        $this->data       = $data;
        $this->errors     = $errors;
        $this->errorCode  = $errorCode;
        $this->meta       = $meta;
        $this->statusCode = $statusCode;
    }

    /**
     * Build from decoded JSON + HTTP status.
     */
    public static function fromJson(?array $json, int $statusCode = 200): self
    {
        if (!is_array($json)) {
            return new self(
                false,
                'Invalid response',
                null,
                null,
                'INVALID_RESPONSE',
                null,
                $statusCode
            );
        }

        return new self(
            array_key_exists('success', $json)
    ? (bool) $json['success']
    : ($statusCode >= 200 && $statusCode < 300),
            (string) ($json['message'] ?? ''),
            $json['data'] ?? null,
            $json['errors'] ?? null,
            $json['error_code'] ?? null,
            $json['meta'] ?? null,
            $statusCode
        );
    }

    /** True when backend returned success AND no errors. */
    public function ok(): bool
    {
        return $this->success === true;
    }

    /** True for validation errors (422). */
    public function hasValidationErrors(): bool
    {
        return $this->errorCode === 'VALIDATION_ERROR' && is_array($this->errors);
    }

    /** First error for a field, or null. */
    public function errorFor(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /** Flattened list of all error messages. */
    public function allErrors(): array
    {
        if (!is_array($this->errors)) {
            return [];
        }
        return array_merge(...array_map(
            fn ($messages) => (array) $messages,
            $this->errors
        ));
    }

    /** Pagination metadata (or null). */
    public function pagination(): ?array
    {
        if (!is_array($this->meta)) {
    return null;
}

if (isset($this->meta['pagination']) && is_array($this->meta['pagination'])) {
    return $this->meta['pagination'];
}

if (isset($this->meta['current_page'], $this->meta['total'])) {
    return $this->meta;
}

return null;
    }

    public function isPaginated(): bool
    {
        return $this->pagination() !== null;
    }

    /** Convert back to array (useful for json_encode / logging). */
    public function toArray(): array
    {
        return [
            'success'    => $this->success,
            'message'    => $this->message,
            'data'       => $this->data,
            'errors'     => $this->errors,
            'error_code' => $this->errorCode,
            'meta'       => $this->meta,
        ];
    }
}
