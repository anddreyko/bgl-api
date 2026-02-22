<?php

declare(strict_types=1);

use Bgl\Core\Persistence\TransactionManager;
use Bgl\Infrastructure\Persistence\Doctrine\DoctrineTransactionManager;

return [
    TransactionManager::class => DI\get(DoctrineTransactionManager::class),
];
