<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Repositories;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @see \Tests\Unit\Core\Database\Repositories\DbRepositoryTest
 */
abstract class DbRepository
{
    /** @var EntityRepository<object> */
    private EntityRepository $repository;

    public function __construct(private readonly EntityManagerInterface $em)
    {
        $this->repository = $this->em->getRepository($this->getClass());
    }

    /**
     * @return class-string
     */
    abstract public function getClass(): string;

    public function persist(object $value): void
    {
        $this->em->persist($value);
    }

    /**
     * @param array<string, mixed> $criteria
     * @param array<string, string>|null $orderBy
     *
     * @return object|null
     */
    public function findOneBy(array $criteria, ?array $orderBy = null): ?object
    {
        $result = $this->repository->findOneBy($criteria, $orderBy);
        if (is_a($result, $this->getClass())) {
            return $result;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $criteria
     *
     * @return int
     */
    public function count(array $criteria): int
    {
        return $this->repository->count($criteria);
    }
}
