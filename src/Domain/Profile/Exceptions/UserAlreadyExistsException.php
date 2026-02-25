<?php

declare(strict_types=1);

namespace Bgl\Domain\Profile\Exceptions;

final class UserAlreadyExistsException extends \DomainException
{
    protected $message = 'User with this email already exists.';
}
