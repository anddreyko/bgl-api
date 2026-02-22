<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine;

use Bgl\Domain\Auth\Entities\EmailConfirmationToken;
use Bgl\Domain\Auth\Entities\EmailConfirmationTokens as EmailConfirmationTokensInterface;
use Doctrine\ORM\EntityManagerInterface;

final class EmailConfirmationTokens implements EmailConfirmationTokensInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[\Override]
    public function add(EmailConfirmationToken $token): void
    {
        $this->em->persist($token);
    }

    #[\Override]
    public function findByToken(string $token): ?EmailConfirmationToken
    {
        $qb = $this->em->createQueryBuilder()
            ->select('t')
            ->from(EmailConfirmationToken::class, 't')
            ->where('t.token = :token')
            ->setParameter('token', $token);

        /** @var EmailConfirmationToken|null */
        return $qb->getQuery()->getOneOrNullResult();
    }

    #[\Override]
    public function remove(EmailConfirmationToken $token): void
    {
        $this->em->remove($token);
    }
}
