<?php

declare(strict_types=1);

use Bgl\Core\Auth\Confirmer;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Persistence\Transactor;
use Bgl\Domain\Profile\Entities\PasskeyChallenges;
use Bgl\Domain\Profile\Entities\Passkeys;
use Bgl\Domain\Profile\Entities\Users;
use Bgl\Domain\Plays\Entities\Plays;
use Bgl\Infrastructure\Auth\DoctrineConfirmer;
use Bgl\Infrastructure\Identity\RamseyUuidGenerator;
use Bgl\Infrastructure\Persistence\Doctrine\DoctrineTransactor;
use Bgl\Infrastructure\Persistence\Doctrine\PasskeyChallenges as DoctrineChallenges;
use Bgl\Infrastructure\Persistence\Doctrine\Passkeys as DoctrinePasskeys;
use Bgl\Infrastructure\Persistence\Doctrine\Plays\Plays as DoctrinePlays;
use Bgl\Infrastructure\Persistence\Doctrine\Users as DoctrineUsers;
use Doctrine\ORM\EntityManagerInterface;

return [
    UuidGenerator::class => static fn(RamseyUuidGenerator $g): UuidGenerator => $g,
    Transactor::class => static fn(DoctrineTransactor $t): Transactor => $t,
    Users::class => static fn(EntityManagerInterface $em): Users => new DoctrineUsers($em),
    Confirmer::class => static fn(DoctrineConfirmer $c): Confirmer => $c,
    Plays::class => static fn(EntityManagerInterface $em): Plays => new DoctrinePlays($em),
    Passkeys::class => static fn(EntityManagerInterface $em): Passkeys => new DoctrinePasskeys($em),
    PasskeyChallenges::class => static fn(
        EntityManagerInterface $em,
    ): PasskeyChallenges => new DoctrineChallenges($em),
];
