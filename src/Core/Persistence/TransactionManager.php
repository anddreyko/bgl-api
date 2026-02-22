<?php

declare(strict_types=1);

namespace Bgl\Core\Persistence;

interface TransactionManager
{
    public function beginTransaction(): void;

    public function flush(): void;

    public function commit(): void;

    public function rollback(): void;
}
