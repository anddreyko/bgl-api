<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

class UserAlreadyExistException extends \RuntimeException
{
    public function __construct(
        ?string $message = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            message: $message ?? CodeExceptionEnum::EmailAlreadyRegister->message(),
            code: CodeExceptionEnum::EmailAlreadyRegister->value,
            previous: $previous
        );
    }
}
