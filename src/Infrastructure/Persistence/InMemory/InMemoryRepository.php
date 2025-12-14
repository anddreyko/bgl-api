<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\InMemory;

use Bgl\Core\Collections\Repository;
use Bgl\Core\Listing\Fields\FieldAccessor;
use Bgl\Core\Listing\Filter;
use Bgl\Core\Listing\Filter\All;
use Bgl\Core\Listing\Filter\None;
use Bgl\Core\Listing\Page\PageNumber;
use Bgl\Core\Listing\Page\PageSize;
use Bgl\Core\Listing\Page\PageSort;
use Bgl\Core\Listing\Page\SortDirection;
use Bgl\Core\Listing\Searchable;

/**
 * @template TEntity of object
 * @implements Repository<TEntity>
 * @implements Searchable<TEntity>
 * @see \Bgl\Tests\Integration\Repositories\InMemoryRepositoryCest
 */
abstract class InMemoryRepository implements Repository, Searchable
{
    /** @var TEntity[] */
    private array $entities = [];

    public function __construct(
        private readonly FieldAccessor $accessor
    ) {
    }

    abstract public function getKey(): string;

    #[\Override]
    public function add(object $entity): void
    {
        /** @psalm-suppress MixedMethodCall */
        /** @var string $key */
        $key = $entity->{$this->getKey()}();
        $this->entities[$key] = $entity;
    }

    #[\Override]
    public function find(string $id): ?object
    {
        return $this->entities[$id] ?? null;
    }

    #[\Override]
    public function remove(object $entity): void
    {
        /** @psalm-suppress MixedMethodCall */
        /** @var string $key */
        $key = $entity->{$this->getKey()}();
        if (isset($this->entities[$key])) {
            unset($this->entities[$key]);
        }
    }

    public function search(
        Filter $filter = None::Filter,
        PageSize $size = new PageSize(),
        PageNumber $number = new PageNumber(1),
        PageSort $sort = new PageSort([])
    ): iterable {
        $entities = $this->entities;
        if ($filter !== All::Filter) {
            $entities = array_filter($entities, $filter->accept(new InMemoryFilter($this->accessor)));
        }

        if (!$sort->isEmpty()) {
            usort($entities, $this->compare($sort));
        }

        /** @var list<TEntity> */
        return \array_slice($entities, ($number->getValue() - 1) * $size->getValue(), $size->getValue());
    }

    private function compare(PageSort $sort)
    {
        return
            /**
             * @param TEntity $a
             * @param TEntity $b
             */
            function (array|object $a, array|object $b) use ($sort): int {
                foreach ($sort->fields as $field => $direction) {
                    $aValue = $this->accessor->get($a, $field);
                    $bValue = $this->accessor->get($b, $field);
                    $aVsB = $aValue <=> $bValue;

                    if ($aVsB === 0) {
                        continue;
                    }

                    return match ($direction) {
                        SortDirection::Asc => $aVsB,
                        SortDirection::Desc => -$aVsB,
                    };
                }

                return 0;
            };
    }
}
