<?php

declare(strict_types=1);

use Bgl\Core\Persistence\TransactionManager;
use Bgl\Domain\Auth\Entities\EmailConfirmationTokens;
use Bgl\Domain\Auth\Entities\Users;
use Bgl\Domain\Plays\Entities\Sessions;
use Bgl\Infrastructure\Persistence\Doctrine\DoctrineTransactionManager;

return [
    TransactionManager::class => DI\get(DoctrineTransactionManager::class),
    Users::class => DI\get(Bgl\Infrastructure\Persistence\Doctrine\Users::class),
    EmailConfirmationTokens::class => DI\get(Bgl\Infrastructure\Persistence\Doctrine\EmailConfirmationTokens::class),
    Sessions::class => DI\get(Bgl\Infrastructure\Persistence\Doctrine\Plays\Sessions::class),
];
