<?php

declare(strict_types=1);

namespace Bgl\Tests\Integration\Repositories;

use Bgl\Core\Collections\Repository;
use Bgl\Core\Listing\Searchable;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\Repositories\TestDoctrineRepository;
use Codeception\Attribute\Group;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @covers \Bgl\Infrastructure\Persistence\Doctrine\DoctrineRepository
 * @covers \Bgl\Infrastructure\Persistence\InMemory\DoctrineFilter
 */
#[Group('repository', 'doctrine')]
class DoctrineRepositoryCest extends BaseRepository
{
    private TestDoctrineRepository $repository;

    public function _before(): void
    {
        /** @var EntityManagerInterface $em */
        $em = DiHelper::container()->get(EntityManagerInterface::class);
        $em->clear();
        $this->repository = new TestDoctrineRepository($em);
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
