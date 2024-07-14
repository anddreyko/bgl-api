<?php

declare(strict_types=1);

namespace App\Auth\Enums;

enum CodeExceptionEnum: int
{
    case NotExistEmail = 1;
    case IncorrectPassword = 2;
    case IncorrectToken = 3;
    case EmailAlreadyRegister = 4;

    public function message(): string
    {
        return match ($this) {
            self::NotExistEmail => 'Incorrect email.',
            self::IncorrectPassword => 'Incorrect password.',
            self::IncorrectToken => 'Incorrect token.',
            self::EmailAlreadyRegister => 'User with this email has been already exist.',
        };
    }
}
