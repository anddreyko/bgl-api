<?php

declare(strict_types=1);

namespace Bgl\Tests\Support\Repositories;

use Bgl\Infrastructure\Persistence\Doctrine\DoctrineRepository;

final class TestDoctrineRepository extends DoctrineRepository
{
    #[\Override]
    public function getType(): string
    {
        return TestEntity::class;
    }

    #[\Override]
    public function getAlias(): string
    {
        return 'e';
    }

    #[\Override]
    public function getKeys(): array
    {
        return ['id'];
    }
}
