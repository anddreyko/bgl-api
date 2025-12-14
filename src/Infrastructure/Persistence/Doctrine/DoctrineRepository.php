<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\Doctrine;

use Bgl\Core\Collections\Repository;
use Bgl\Core\Listing\Filter;
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
 * @implements Searchable<TEntity>
 */
abstract class DoctrineRepository implements Repository, Searchable
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return class-string<TEntity>
     */
    abstract public function getType(): string;

    abstract public function getAlias(): string;

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
        $qb = $this->em->createQueryBuilder()
            ->select($alias)
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

        /** @var list<TEntity> */
        return $qb->getQuery()->getResult();
    }
}
