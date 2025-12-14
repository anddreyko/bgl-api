<?php

declare(strict_types=1);

namespace Bgl\Tests\Integration\Repositories;

use Bgl\Core\Collections\Repository;
use Bgl\Core\Listing\Fields\AnyFieldAccessor;
use Bgl\Core\Listing\Searchable;
use Bgl\Tests\Support\Repositories\TestInMemoryRepository;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Infrastructure\Persistence\InMemory\InMemoryRepository
 * @covers \Bgl\Infrastructure\Persistence\InMemory\InMemoryFilter
 */
#[Group('repository', 'inMemory')]
class InMemoryRepositoryCest extends BaseRepository
{
    private TestInMemoryRepository $repository;

    public function _before(): void
    {
        $this->repository = new TestInMemoryRepository(new AnyFieldAccessor());
    }

    #[\Override]
    public function getRepository(array $entities = []): Repository|Searchable
    {
        foreach ($entities as $entity) {
            $this->repository->add($entity);
        }

        return $this->repository;
    }
}
