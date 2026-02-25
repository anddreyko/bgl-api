<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

final class InvalidConfirmationTokenException extends \DomainException
{
    protected $message = 'Confirmation token is invalid.';
}
