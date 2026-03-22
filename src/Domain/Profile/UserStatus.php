<?php

declare(strict_types=1);

namespace Bgl\Domain\Profile;

enum UserStatus: string
{
    case Inactive = 'inactive';
    case Active = 'active';
    case Deleted = 'deleted';

    public function isInactive(): bool
    {
        return self::Inactive === $this;
    }
}
