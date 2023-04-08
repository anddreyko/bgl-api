<?php

declare(strict_types=1);

namespace App\Auth\Repositories;

use App\Auth\Entities\User;
use App\Auth\ValueObjects\Email;

final class UserRepository
{
    public function add(User $user): void
    {
    }

    public function hasByEmail(Email $email): bool
    {
        return true;
    }
}
