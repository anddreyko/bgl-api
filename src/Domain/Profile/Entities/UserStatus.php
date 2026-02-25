<?php

declare(strict_types=1);

namespace Bgl\Domain\Profile\Entities;

enum UserStatus: string
{
    case Inactive = 'inactive';
    case Active = 'active';
    case Deleted = 'deleted';
}
