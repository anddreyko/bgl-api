<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine\Mapping\Mates;

use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Mates\Entities\Mate;
use Bgl\Domain\Mates\Entities\Mates as MateRepository;
use Bgl\Infrastructure\Persistence\Doctrine\DoctrineRepository;

/**
 * @extends DoctrineRepository<Mate>
 */
final class Mates extends DoctrineRepository implements MateRepository
{
    #[\Override]
    public function getType(): string
    {
        return Mate::class;
    }

    #[\Override]
    public function getAlias(): string
    {
        return 'm';
    }

    #[\Override]
    public function getKeys(): array
    {
        return ['id'];
    }

    #[\Override]
    public function findByUserAndName(Uuid $userId, string $name): ?Mate
    {
        /** @var Mate|null */
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('m')
            ->from(Mate::class, 'm')
            ->where('LOWER(m.name) = LOWER(:name)')
            ->andWhere('m.userId = :userId')
            ->andWhere('m.deletedAt IS NULL')
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
        $allowed = ['name' => 'm.name', 'createdAt' => 'm.createdAt'];
        $orderField = $allowed[$sortField] ?? 'm.name';
        $orderDir = strtoupper($sortDir) === 'DESC' ? 'DESC' : 'ASC';

        /** @var list<Mate> */
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('m')
            ->from(Mate::class, 'm')
            ->where('m.userId = :userId')
            ->andWhere('m.deletedAt IS NULL')
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
            ->select('COUNT(m.id)')
            ->from(Mate::class, 'm')
            ->where('m.userId = :userId')
            ->andWhere('m.deletedAt IS NULL')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
