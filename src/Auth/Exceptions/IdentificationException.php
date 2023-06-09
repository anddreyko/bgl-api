<?php

declare(strict_types=1);

namespace App\Auth\Exceptions;

class IdentificationException extends \InvalidArgumentException
{
    public function __construct(
        string $message = 'Incorrect email or password.',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}
