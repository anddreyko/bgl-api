<?php

declare(strict_types=1);

namespace App\Domain\Auth\Exceptions;

final class ExpiredTokenException extends \RuntimeException
{
    public function __construct(
        string $message = 'This token has been expired.',
        ?\Throwable $previous = null
    ) {
        parent::__construct(message: $message, code: (int)$previous?->getCode(), previous: $previous);
    }
}
