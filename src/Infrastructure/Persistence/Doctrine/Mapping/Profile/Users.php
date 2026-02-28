<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping\Profile;

use Bgl\Domain\Profile\Entities\User;
use Bgl\Domain\Profile\Entities\Users as UserRepository;
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
    public function getKeys(): array
    {
        return ['id'];
    }

    #[\Override]
    public function findByEmail(string $email): ?User
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.email = :email')
            ->setParameter('email', $email);

        /** @var User|null */
        return $qb->getQuery()->getOneOrNullResult();
    }
}
