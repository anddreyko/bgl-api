<?php

declare(strict_types=1);

namespace Bgl\Tests\Integration\Repositories;

use Bgl\Core\Collections\Repository;
use Bgl\Core\Listing\Searchable;
use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\Repositories\TestDoctrineRepository;
use Bgl\Tests\Support\Repositories\TestEntity;
use Codeception\Attribute\Group;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @covers \Bgl\Infrastructure\Persistence\Doctrine\DoctrineRepository
 * @covers \Bgl\Infrastructure\Persistence\Doctrine\DoctrineFilter
 */
#[Group('repository', 'doctrine')]
class DoctrineRepositoryCest extends BaseRepository
{
    private TestDoctrineRepository $repository;
    private EntityManagerInterface $em;

    public function _before(): void
    {
        /** @var EntityManagerInterface $em */
        $this->em = DiHelper::container()->get(EntityManagerInterface::class);
        $this->em->createQueryBuilder()
            ->delete(TestEntity::class, 'e')
            ->getQuery()
            ->execute();
        $this->em->clear();
        $this->repository = new TestDoctrineRepository($this->em);
    }

    #[\Override]
    public function getRepository(array $entities = []): Repository|Searchable
    {
        foreach ($entities as $entity) {
            $this->repository->add($entity);
        }

        if ($entities !== []) {
            $this->em->flush();
        }

        return $this->repository;
    }
}
