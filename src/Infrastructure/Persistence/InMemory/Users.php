<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\InMemory;

use Bgl\Domain\Auth\Entities\User;
use Bgl\Domain\Auth\Entities\Users as UsersInterface;

/**
 * @extends InMemoryRepository<User>
 */
final class Users extends InMemoryRepository implements UsersInterface
{
    #[\Override]
    public function getKeys(): array
    {
        return ['id'];
    }

    #[\Override]
    public function findByEmail(string $email): ?User
    {
        foreach ($this->getEntities() as $user) {
            if ($user->getEmail()->getValue() === $email) {
                return $user;
            }
        }

        return null;
    }
}
