<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

class AuthenticationException extends \DomainException
{
    protected $message = 'Authentication failed';
}
