<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

final class UserNotActiveException extends AuthenticationException
{
    protected $message = 'User is not active';
}
