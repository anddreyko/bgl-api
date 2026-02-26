<?php

declare(strict_types=1);

namespace Bgl\Tests\Support\Dummy;

use Bgl\Core\Persistence\Transactor;

final class NullTransactor implements Transactor
{
    #[\Override]
    public function beginTransaction(): void
    {
    }

    #[\Override]
    public function flush(): void
    {
    }

    #[\Override]
    public function commit(): void
    {
    }

    #[\Override]
    public function rollback(): void
    {
    }
}
