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

    /**
     * Stub: Email VO has no value accessor yet.
     * Will be implemented when Email VO gets proper constructor and getValue().
     */
    #[\Override]
    public function findByEmail(string $email): ?User
    {
        return null;
    }
}
