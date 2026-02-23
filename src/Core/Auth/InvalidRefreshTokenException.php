<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

final class InvalidRefreshTokenException extends AuthenticationException
{
    protected $message = 'Invalid refresh token';
}
