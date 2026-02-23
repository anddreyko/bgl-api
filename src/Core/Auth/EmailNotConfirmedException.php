<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

final class EmailNotConfirmedException extends AuthenticationException
{
    protected $message = 'Email not confirmed';
}
