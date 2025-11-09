<?php

declare(strict_types=1);

namespace Bgl\Domain\Auth\Services;

final class UserNotRegisterException extends \DomainException
{
    protected $message = 'User not registered';
}
