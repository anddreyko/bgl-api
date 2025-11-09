<?php

declare(strict_types=1);

namespace Bgl\Domain\Auth\Services;

final class UserBannedException extends \DomainException
{
    protected $message = 'User banned';
}
