<?php

declare(strict_types=1);

namespace Bgl\Core\Auth;

final class TooManyRequestsException extends \DomainException
{
    protected $message = 'Too many verification requests. Please try again later.';
}
