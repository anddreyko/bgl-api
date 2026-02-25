<?php

declare(strict_types=1);

use Bgl\Core\Identity\UuidGenerator;
use Bgl\Core\Persistence\TransactionManager;
use Bgl\Domain\Profile\Entities\EmailConfirmationTokens;
use Bgl\Domain\Profile\Entities\Users;
use Bgl\Domain\Plays\Entities\Plays;
use Bgl\Infrastructure\Identity\RamseyUuidGenerator;
use Bgl\Infrastructure\Persistence\Doctrine\DoctrineTransactionManager;

return [
    UuidGenerator::class => DI\get(RamseyUuidGenerator::class),
    TransactionManager::class => DI\get(DoctrineTransactionManager::class),
    Users::class => DI\get(Bgl\Infrastructure\Persistence\Doctrine\Users::class),
    EmailConfirmationTokens::class => DI\get(Bgl\Infrastructure\Persistence\Doctrine\EmailConfirmationTokens::class),
    Plays::class => DI\get(Bgl\Infrastructure\Persistence\Doctrine\Plays\Plays::class),
];
