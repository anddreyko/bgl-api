<?php

declare(strict_types=1);

namespace Bgl\Domain\Auth\Exceptions;

final class InvalidConfirmationTokenException extends \DomainException
{
    protected $message = 'Confirmation token is invalid.';
}
