<?php

declare(strict_types=1);

namespace App\Domain\Auth\Exceptions;

final class IdentificationException extends \InvalidArgumentException
{
    public function __construct(
        string $message = 'Incorrect email or password.',
        ?\Throwable $previous = null
    ) {
        parent::__construct(message: $message, code: (int)$previous?->getCode(), previous: $previous);
    }
}
