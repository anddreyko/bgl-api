<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

final class InvalidCredentialsException extends AuthenticationException
{
    protected $message = 'Invalid credentials';
}
