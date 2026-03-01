<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping\Profile;

use Bgl\Domain\Profile\Passkey\PasskeyChallenge;
use Bgl\Domain\Profile\Passkey\PasskeyChallenges as PasskeyChallengesInterface;
use Bgl\Infrastructure\Persistence\Doctrine\DoctrineRepository;

/**
 * @extends DoctrineRepository<PasskeyChallenge>
 */
final class PasskeyChallenges extends DoctrineRepository implements PasskeyChallengesInterface
{
    #[\Override]
    public function getType(): string
    {
        return PasskeyChallenge::class;
    }

    #[\Override]
    public function getAlias(): string
    {
        return 'pc';
    }

    #[\Override]
    public function getKeys(): array
    {
        return ['id'];
    }

    #[\Override]
    public function findByChallenge(string $challenge): ?PasskeyChallenge
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('pc')
            ->from(PasskeyChallenge::class, 'pc')
            ->where('pc.challenge = :challenge')
            ->setParameter('challenge', $challenge);

        /** @var PasskeyChallenge|null */
        return $qb->getQuery()->getOneOrNullResult();
    }
}
