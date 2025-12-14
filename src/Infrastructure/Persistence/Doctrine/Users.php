<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine;

use Bgl\Domain\Auth\Entities\User;
use Bgl\Domain\Auth\Entities\Users as UserRepository;

/**
 * @extends DoctrineRepository<User>
 */
final class Users extends DoctrineRepository implements UserRepository
{
    #[\Override]
    public function getType(): string
    {
        return User::class;
    }

    #[\Override]
    public function getAlias(): string
    {
        return 'u';
    }
}
