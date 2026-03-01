<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping\Profile;

use Bgl\Domain\Profile\Passkey\Passkey;
use Bgl\Domain\Profile\Passkey\Passkeys as PasskeysInterface;
use Bgl\Infrastructure\Persistence\Doctrine\DoctrineRepository;

/**
 * @extends DoctrineRepository<Passkey>
 */
final class Passkeys extends DoctrineRepository implements PasskeysInterface
{
    #[\Override]
    public function getType(): string
    {
        return Passkey::class;
    }

    #[\Override]
    public function getAlias(): string
    {
        return 'p';
    }

    #[\Override]
    public function findByCredentialId(string $credentialId): ?Passkey
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('p')
            ->from(Passkey::class, 'p')
            ->where('p.credentialId = :credentialId')
            ->setParameter('credentialId', $credentialId);

        /** @var Passkey|null */
        return $qb->getQuery()->getOneOrNullResult();
    }

    #[\Override]
    public function findAllByUserId(string $userId): iterable
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('p')
            ->from(Passkey::class, 'p')
            ->where('p.userId = :userId')
            ->setParameter('userId', $userId);

        /** @var list<Passkey> */
        return $qb->getQuery()->getResult();
    }
}
