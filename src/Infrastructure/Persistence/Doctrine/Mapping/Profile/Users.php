<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping\Profile;

use Bgl\Core\Listing\Field;
use Bgl\Core\Listing\Filter\Equals;
use Bgl\Domain\Profile\User;
use Bgl\Domain\Profile\Users as UserRepository;
use Bgl\Infrastructure\Persistence\Doctrine\DoctrineRepository;

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

    #[\Override]
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(new Equals(new Field('email'), $email));
    }

    #[\Override]
    public function findByName(string $name): ?User
    {
        return $this->findOneBy(new Equals(new Field('name'), $name));
    }
}
