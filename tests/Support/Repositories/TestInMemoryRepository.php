<?php

declare(strict_types=1);

namespace Bgl\Tests\Support\Repositories;

use Bgl\Infrastructure\Persistence\InMemory\InMemoryRepository;

final class TestInMemoryRepository extends InMemoryRepository
{
    #[\Override]
    public function getKey(): string
    {
        return 'getId';
    }
}
