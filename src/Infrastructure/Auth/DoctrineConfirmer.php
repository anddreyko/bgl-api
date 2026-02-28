<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Auth;

use Bgl\Core\Auth\Confirmer;
use Bgl\Core\Auth\ExpiredConfirmationTokenException;
use Bgl\Core\Auth\InvalidConfirmationTokenException;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;

final readonly class DoctrineConfirmer implements Confirmer
{
    private const int TOKEN_TTL_HOURS = 24;

    public function __construct(
        private EntityManagerInterface $em,
        private UuidGenerator $uuidGenerator,
        private ClockInterface $clock,
    ) {
    }

    #[\Override]
    public function request(Uuid $userId): void
    {
        $token = EmailConfirmationToken::create(
            id: $this->uuidGenerator->generate(),
            userId: $userId,
            token: $this->uuidGenerator->generate()->getValue() ?? '',
            expiresAt: new DateTime('+' . self::TOKEN_TTL_HOURS . ' hours'),
        );

        $this->em->persist($token);
    }

    #[\Override]
    public function confirm(string $token): Uuid
    {
        $entity = $this->findByToken($token);
        if ($entity === null) {
            throw new InvalidConfirmationTokenException();
        }

        if ($entity->isExpired(new DateTime($this->clock->now()))) {
            throw new ExpiredConfirmationTokenException();
        }

        $userId = $entity->getUserId();

        $this->em->remove($entity);

        return $userId;
    }

    private function findByToken(string $token): ?EmailConfirmationToken
    {
        $qb = $this->em->createQueryBuilder()
            ->select('t')
            ->from(EmailConfirmationToken::class, 't')
            ->where('t.token = :token')
            ->setParameter('token', $token);

        /** @var EmailConfirmationToken|null */
        return $qb->getQuery()->getOneOrNullResult();
    }
}
