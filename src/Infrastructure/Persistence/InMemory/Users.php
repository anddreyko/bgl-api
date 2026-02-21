<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\InMemory;

use Bgl\Domain\Auth\Entities\User;

/**
 * @extends InMemoryRepository<User>
 */
final class Users extends InMemoryRepository
{
    #[\Override]
    public function getKeys(): array
    {
        return ['id'];
    }
}
