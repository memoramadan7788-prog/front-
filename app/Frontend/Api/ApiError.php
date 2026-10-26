<?php

namespace App\Frontend\Api;

use RuntimeException;
use Throwable;

/**
 * ApiError — thrown for every non-2xx response.
 * Carries the full ApiResponse envelope plus HTTP status.
 */
class ApiError extends RuntimeException
{
    public int $status;
    public ?ApiResponse $response;

    public function __construct(
        string $message,
        int $status = 0,
        ?ApiResponse $response = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $status, $previous);
        $this->status   = $status;
        $this->response = $response;
    }

    public function data(): mixed
    {
        return $this->response?->data;
    }

    public function errors(): ?array
    {
        return $this->response?->errors;
    }

    public function errorCode(): ?string
    {
        return $this->response?->errorCode;
    }

    public function meta(): ?array
    {
        return $this->response?->meta;
    }

    public function isValidation(): bool   { return $this->status === 422; }
    public function isUnauthorized(): bool { return $this->status === 401; }
    public function isForbidden(): bool    { return $this->status === 403; }
    public function isNotFound(): bool     { return $this->status === 404; }
    public function isServerError(): bool  { return $this->status >= 500; }
    public function isTimeout(): bool      { return $this->status === 408; }
    public function isNetworkError(): bool { return $this->status === 0; }

    public function errorFor(string $field): ?string
    {
        return $this->response?->errorFor($field);
    }

    public function allErrors(): array
    {
        return $this->response?->allErrors() ?? [];
    }
}