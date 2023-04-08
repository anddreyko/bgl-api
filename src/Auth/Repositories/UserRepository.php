<?php

declare(strict_types=1);

namespace App\Auth\Repositories;

use App\Auth\Entities\User;
use App\Auth\Enums\UserStatusEnum;
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

    public function findByToken(string $token): ?User
    {
        return null;
    }

    public function activateUser(User $user): void
    {
        $this->add($user->setStatus(UserStatusEnum::Active));
    }
}
