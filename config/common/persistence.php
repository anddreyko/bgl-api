<?php

declare(strict_types=1);

use Bgl\Core\Auth\Confirmer;
use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Persistence\Transactor;
use Bgl\Domain\Profile\Entities\Users;
use Bgl\Domain\Plays\Entities\Plays;
use Bgl\Infrastructure\Auth\DoctrineConfirmer;
use Bgl\Infrastructure\Identity\RamseyUuidGenerator;
use Bgl\Infrastructure\Persistence\Doctrine\DoctrineTransactor;

return [
    UuidGenerator::class => DI\get(RamseyUuidGenerator::class),
    Transactor::class => DI\get(DoctrineTransactor::class),
    Users::class => DI\get(Bgl\Infrastructure\Persistence\Doctrine\Users::class),
    Confirmer::class => DI\get(DoctrineConfirmer::class),
    Plays::class => DI\get(Bgl\Infrastructure\Persistence\Doctrine\Plays\Plays::class),
];
