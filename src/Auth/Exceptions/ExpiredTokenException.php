<?php

declare(strict_types=1);

namespace App\Auth\Exceptions;

class ExpiredTokenException extends \RuntimeException
{
    public function __construct(
        string $message = 'This token has been expired.',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}
