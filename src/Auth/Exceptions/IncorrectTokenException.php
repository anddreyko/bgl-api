<?php

declare(strict_types=1);

namespace App\Auth\Exceptions;

use App\Auth\Enums\CodeExceptionEnum;

class IncorrectTokenException extends \InvalidArgumentException
{
    public function __construct(
        ?string $message = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            message: $message ?? CodeExceptionEnum::IncorrectToken->message(),
            code: CodeExceptionEnum::IncorrectToken->value,
            previous: $previous
        );
    }
}
