<?php

declare(strict_types=1);

namespace App\Auth\Exceptions;

class IncorrectEmailException extends \InvalidArgumentException
{
    public function __construct(
        string $message = 'Incorrect email.',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}
