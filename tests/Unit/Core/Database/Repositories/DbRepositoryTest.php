<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Database\Repositories;

use App\Core\Database\Repositories\DbRepository;
use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @covers \App\Core\Database\Repositories\DbRepository
 */
final class DbRepositoryTest extends Unit
{
    public function testPersist(): void
    {
        $entityRepository = $this->make(EntityRepository::class);
        $em = $this->makeEmpty(
            EntityManagerInterface::class,
            ['persist' => Expected::once(), 'getRepository' => Expected::once($entityRepository)]
        );

        $repository = $this->construct(
            DbRepository::class,
            ['em' => $em],
            ['getClass' => \stdClass::class]
        );

        $repository->persist(new \stdClass());
    }

    public function testFindOneBy(): void
    {
        $entityRepository = $this->make(EntityRepository::class, ['findOneBy' => Expected::once()]);
        $em = $this->makeEmpty(EntityManagerInterface::class, ['getRepository' => Expected::once($entityRepository)]);

        $repository = $this->construct(DbRepository::class, ['em' => $em], ['getClass' => \stdClass::class]);

        $repository->findOneBy([]);
    }

    public function testFindObjectBy(): void
    {
        $entityRepository = $this->make(EntityRepository::class, ['findOneBy' => Expected::once(new \stdClass())]);
        $em = $this->makeEmpty(EntityManagerInterface::class, ['getRepository' => Expected::once($entityRepository)]);

        $repository = $this->construct(DbRepository::class, ['em' => $em], ['getClass' => \stdClass::class]);

        $repository->findOneBy([]);
    }

    public function testCount(): void
    {
        $entityRepository = $this->makeEmpty(
            EntityRepository::class,
            ['count' => Expected::once(0)]
        );
        $em = $this->makeEmpty(
            EntityManagerInterface::class,
            ['getRepository' => Expected::once($entityRepository)]
        );

        $repository = $this->construct(
            DbRepository::class,
            ['em' => $em],
            ['getClass' => \stdClass::class]
        );

        $repository->count([]);
    }
}
