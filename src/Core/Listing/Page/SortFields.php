<?php

declare(strict_types=1);

namespace Bgl\Core\Listing\Page;

/**
 * Typed collection of sort field directions.
 *
 * @implements \IteratorAggregate<array-key, SortDirection>
 */
final readonly class SortFields implements \IteratorAggregate, \Countable
{
    /**
     * @param array<array-key, SortDirection> $fields
     */
    public function __construct(
        private array $fields = [],
    ) {
    }

    public function get(string $field): ?SortDirection
    {
        return $this->fields[$field] ?? null;
    }

    public function has(string $field): bool
    {
        return array_key_exists($field, $this->fields);
    }

    public function isEmpty(): bool
    {
        return $this->fields === [];
    }

    /**
     * @return array<array-key, SortDirection>
     */
    public function toArray(): array
    {
        return $this->fields;
    }

    /**
     * @return \ArrayIterator<array-key, SortDirection>
     */
    #[\Override]
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->fields);
    }

    #[\Override]
    public function count(): int
    {
        return count($this->fields);
    }
}
