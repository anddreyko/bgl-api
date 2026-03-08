<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\InMemory;

use Bgl\Domain\Profile\User;
use Bgl\Domain\Profile\Users;

/**
 * @extends InMemoryRepository<User>
 */
final class InMemoryUsers extends InMemoryRepository implements Users
{
    #[\Override]
    public function findByEmail(string $email): ?User
    {
        foreach ($this->getEntities() as $user) {
            if ((string)$user->getEmail() === $email) {
                return $user;
            }
        }

        return null;
    }

    #[\Override]
    public function findByName(string $name): ?User
    {
        foreach ($this->getEntities() as $user) {
            if ($user->getName() === $name) {
                return $user;
            }
        }

        return null;
    }
}
