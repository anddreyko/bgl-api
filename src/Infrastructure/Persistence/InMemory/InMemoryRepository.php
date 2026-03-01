<?php

declare(strict_types=1);

namespace Bgl\Infrastructure\Persistence\InMemory;

use Bgl\Core\Collections\Repository;
use Bgl\Core\Listing\Fields\AnyFieldAccessor;
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
 * @see \Bgl\Tests\Integration\Repositories\InMemoryRepositoryCest
 */
abstract class InMemoryRepository implements Repository, Searchable
{
    /** @var array<string, TEntity> */
    private array $entities = [];

    public function __construct(
        private readonly FieldAccessor $accessor = new AnyFieldAccessor(),
    ) {
    }

    /**
     * @return array<string, TEntity>
     */
    protected function getEntities(): array
    {
        return $this->entities;
    }

    public function clear(): void
    {
        $this->entities = [];
    }

    /**
     * @return list<string> Key field names
     */
    public function getKeys(): array
    {
        return ['id'];
    }

    #[\Override]
    public function add(object $entity): void
    {
        $keyField = $this->getKeys()[0];
        $key = (string)$this->accessor->get($entity, $keyField);
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
        $keyField = $this->getKeys()[0];
        $key = (string)$this->accessor->get($entity, $keyField);
        if (isset($this->entities[$key])) {
            unset($this->entities[$key]);
        }
    }

    #[\Override]
    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $result = [];
        foreach ($ids as $id) {
            if (isset($this->entities[$id])) {
                $result[] = $this->entities[$id];
            }
        }

        return $result;
    }

    #[\Override]
    public function search(
        Filter $filter = None::Filter,
        PageSize $size = new PageSize(),
        PageNumber $number = new PageNumber(1),
        PageSort $sort = new PageSort()
    ): iterable {
        $entities = $this->entities;
        if ($filter !== All::Filter) {
            $entities = array_filter($entities, $filter->accept(new InMemoryFilter($this->accessor)));
        }

        if (!$sort->isEmpty()) {
            usort($entities, $this->compare($sort));
        }

        $limit = $size->getValue();
        $sliced = \array_slice(
            array_values($entities),
            ($number->getValue() - 1) * ($limit ?? 0),
            $limit
        );

        return array_map(
            $this->extractKeys(...),
            $sliced
        );
    }

    #[\Override]
    public function count(Filter $filter = All::Filter): int
    {
        if ($filter === All::Filter) {
            return \count($this->entities);
        }

        return \count(array_filter($this->entities, $filter->accept(new InMemoryFilter($this->accessor))));
    }

    /**
     * @return array<string, mixed>
     */
    private function extractKeys(object $entity): array
    {
        $result = [];
        foreach ($this->getKeys() as $key) {
            /** @psalm-suppress MixedAssignment */
            $result[$key] = $this->accessor->get($entity, $key);
        }

        return $result;
    }

    /**
     * @return callable(TEntity, TEntity): int
     */
    private function compare(PageSort $sort): callable
    {
        $fields = $sort->fields->toArray();

        return
            /**
             * @param TEntity $a
             * @param TEntity $b
             */
            function (array|object $a, array|object $b) use ($fields): int {
                foreach ($fields as $field => $direction) {
                    /** @var string $aValue */
                    $aValue = $this->accessor->get($a, $field);
                    /** @var string $bValue */
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
