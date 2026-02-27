<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine;

use Bgl\Core\Collections\Repository;
use Bgl\Core\Listing\Filter;
use Bgl\Core\Listing\Filter\All;
use Bgl\Core\Listing\Filter\None;
use Bgl\Core\Listing\Page\PageNumber;
use Bgl\Core\Listing\Page\PageSize;
use Bgl\Core\Listing\Page\PageSort;
use Bgl\Core\Listing\Page\SortDirection;
use Bgl\Core\Listing\Searchable;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @template TEntity of object
 * @implements Repository<TEntity>
 */
abstract class DoctrineRepository implements Repository, Searchable
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    /**
     * @return class-string<TEntity>
     */
    abstract public function getType(): string;

    abstract public function getAlias(): string;

    /**
     * @return list<string> Key field names
     */
    abstract public function getKeys(): array;

    #[\Override]
    public function add(object $entity): void
    {
        $this->em->persist($entity);
    }

    #[\Override]
    public function find(string $id): ?object
    {
        return $this->em->find($this->getType(), $id);
    }

    #[\Override]
    public function remove(object $entity): void
    {
        $this->em->remove($entity);
    }

    #[\Override]
    public function search(
        Filter $filter = None::Filter,
        PageSize $size = new PageSize(),
        PageNumber $number = new PageNumber(1),
        PageSort $sort = new PageSort([])
    ): iterable {
        $alias = $this->getAlias();
        $keys = $this->getKeys();

        // Build SELECT clause for key fields only
        $select = implode(
            ', ',
            array_map(
                fn(string $key): string => "{$alias}.{$key}",
                $keys
            )
        );

        $qb = $this->em->createQueryBuilder()
            ->select($select)
            ->from($this->getType(), $alias);

        $visitor = new DoctrineFilter($qb, $alias);
        $condition = $filter->accept($visitor);
        if ($condition !== null) {
            $qb->andWhere($condition);
        }

        foreach ($sort->fields as $field => $direction) {
            $order = $direction === SortDirection::Asc ? 'ASC' : 'DESC';
            $qb->addOrderBy("{$alias}.{$field}", $order);
        }

        $limit = $size->getValue();

        if ($limit === 0) {
            return [];
        }

        if ($limit !== null) {
            $offset = ($number->getValue() - 1) * $limit;
            $qb->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        /** @var list<array<string, mixed>> */
        return $qb->getQuery()->getArrayResult();
    }

    #[\Override]
    public function count(Filter $filter = All::Filter): int
    {
        $alias = $this->getAlias();
        $qb = $this->em->createQueryBuilder()
            ->select("COUNT({$alias})")
            ->from($this->getType(), $alias);

        $visitor = new DoctrineFilter($qb, $alias);
        $condition = $filter->accept($visitor);
        if ($condition !== null) {
            $qb->andWhere($condition);
        }

        /** @var int<0, max> */
        return (int)$qb->getQuery()->getSingleScalarResult();
    }
}
