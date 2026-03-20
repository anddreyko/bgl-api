<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

final class TooManyAttemptsException extends \DomainException
{
    protected $message = 'Too many verification attempts.';
}
