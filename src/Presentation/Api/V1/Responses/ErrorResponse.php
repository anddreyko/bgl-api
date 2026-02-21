<?php

declare(strict_types=1);

namespace Bgl\Presentation\Api\V1\Responses;

final readonly class ErrorResponse
{
    public int $code;

    /**
     * @param string $message Human-readable error message
     * @param array<string, string[]> $errors Field-level validation errors
     * @param \Throwable|null $exception Exception object (debug mode only)
     */
    public function __construct(
        public string $message,
        public int $httpStatus = 400,
        public array $errors = [],
        public ?\Throwable $exception = null,
    ) {
        $this->code = 1;
    }

    /**
     * Factory for validation errors (HTTP 422).
     *
     * @param array<string, string[]> $errors
     */
    public static function validation(string $message, array $errors): self
    {
        return new self(
            message: $message,
            httpStatus: 422,
            errors: $errors,
        );
    }

    /**
     * Factory for server errors with exception (HTTP 500, debug mode).
     */
    public static function serverError(string $message, \Throwable $exception): self
    {
        return new self(
            message: $message,
            httpStatus: 500,
            exception: $exception,
        );
    }
}
