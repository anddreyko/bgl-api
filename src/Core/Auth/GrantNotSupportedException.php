<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

final class GrantNotSupportedException extends AuthenticationException
{
    protected $message = 'Grant type not supported';
}
