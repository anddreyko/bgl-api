<?php

declare(strict_types=1);

namespace App\Auth\Exceptions;

class UserAlreadyExistException extends \RuntimeException
{
    public function __construct(
        string $message = 'User with this email has been already exist.',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}
