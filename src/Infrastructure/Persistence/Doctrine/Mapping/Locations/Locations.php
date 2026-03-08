<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping\Locations;

use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Locations\Location;
use Bgl\Domain\Locations\Locations as LocationRepository;
use Bgl\Infrastructure\Persistence\Doctrine\DoctrineRepository;

/**
 * @extends DoctrineRepository<Location>
 */
final class Locations extends DoctrineRepository implements LocationRepository
{
    #[\Override]
    public function getType(): string
    {
        return Location::class;
    }

    #[\Override]
    public function getAlias(): string
    {
        return 'l';
    }

    #[\Override]
    public function findByUserAndName(Uuid $userId, string $name): ?Location
    {
        /** @var Location|null */
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('l')
            ->from(Location::class, 'l')
            ->where('LOWER(l.name) = LOWER(:name)')
            ->andWhere('l.userId = :userId')
            ->andWhere('l.deletedAt IS NULL')
            ->setParameter('name', $name)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    #[\Override]
    public function findAllByUser(
        Uuid $userId,
        int $limit,
        int $offset,
        string $sortField = 'name',
        string $sortDir = 'asc',
    ): array {
        $allowed = ['name' => 'l.name', 'createdAt' => 'l.createdAt'];
        $orderField = $allowed[$sortField] ?? 'l.name';
        $orderDir = strtoupper($sortDir) === 'DESC' ? 'DESC' : 'ASC';

        /** @var list<Location> */
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('l')
            ->from(Location::class, 'l')
            ->where('l.userId = :userId')
            ->andWhere('l.deletedAt IS NULL')
            ->setParameter('userId', $userId)
            ->orderBy($orderField, $orderDir)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    #[\Override]
    public function countByUser(Uuid $userId): int
    {
        /** @var int */
        return (int)$this->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(l.id)')
            ->from(Location::class, 'l')
            ->where('l.userId = :userId')
            ->andWhere('l.deletedAt IS NULL')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
