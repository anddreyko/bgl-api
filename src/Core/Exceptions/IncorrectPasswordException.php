<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

final class IncorrectPasswordException extends \InvalidArgumentException
{
    public function __construct(
        ?string $message = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            message: $message ?? CodeExceptionEnum::IncorrectPassword->message(),
            code: CodeExceptionEnum::IncorrectPassword->value,
            previous: $previous
        );
    }
}
