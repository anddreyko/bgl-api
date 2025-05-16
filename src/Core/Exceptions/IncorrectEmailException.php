<?php

declare(strict_types=1);

namespace App\Core\Exceptions;

final class IncorrectEmailException extends \InvalidArgumentException
{
    public function __construct(
        ?string $message = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            message: $message ?? CodeExceptionEnum::NotExistEmail->message(),
            code: CodeExceptionEnum::NotExistEmail->value,
            previous: $previous
        );
    }
}
