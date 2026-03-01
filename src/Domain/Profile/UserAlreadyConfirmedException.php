<?php

declare(strict_types=1);

namespace Bgl\Domain\Profile;

final class UserAlreadyConfirmedException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('User is already confirmed.');
    }
}
