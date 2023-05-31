<?php

declare(strict_types=1);

namespace App\Auth\Exceptions;

class IncorrectPasswordException extends \InvalidArgumentException
{
    public function __construct(
        string $message = 'Incorrect password.',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}
