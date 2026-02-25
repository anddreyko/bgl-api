<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

final class ExpiredConfirmationTokenException extends \DomainException
{
    protected $message = 'Confirmation token has expired.';
}
