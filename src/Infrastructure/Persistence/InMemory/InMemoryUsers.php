<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\InMemory;

use Bgl\Core\Listing\Fields\AnyFieldAccessor;
use Bgl\Domain\Profile\User;
use Bgl\Domain\Profile\Users;

/**
 * @extends InMemoryRepository<User>
 */
final class InMemoryUsers extends InMemoryRepository implements Users
{
    public function __construct()
    {
        parent::__construct(new AnyFieldAccessor());
    }

    #[\Override]
    public function getKeys(): array
    {
        return ['id'];
    }

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
}
