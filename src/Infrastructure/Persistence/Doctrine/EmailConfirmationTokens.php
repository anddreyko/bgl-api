<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine;

use Bgl\Domain\Profile\Entities\EmailConfirmationToken;
use Bgl\Domain\Profile\Entities\EmailConfirmationTokens as EmailConfirmationTokensInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class EmailConfirmationTokens implements EmailConfirmationTokensInterface
{
    public function __construct(private EntityManagerInterface $em)
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
