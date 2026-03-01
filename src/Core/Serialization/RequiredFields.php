<?php

declare(strict_types=1);

namespace Bgl\Core\Serialization;

/**
 * Typed wrapper for a list of required field names.
 *
 * @implements \IteratorAggregate<int, string>
 */
final readonly class RequiredFields implements \IteratorAggregate, \Countable
{
    /**
     * @param list<string> $fields
     */
    public function __construct(
        private array $fields = [],
    ) {
    }

    /**
     * @param list<string> $fields
     */
    public static function fromArray(array $fields): self
    {
        return new self($fields);
    }

    public function contains(string $field): bool
    {
        return in_array($field, $this->fields, true);
    }

    /**
     * @return list<string>
     */
    public function toArray(): array
    {
        return $this->fields;
    }

    /**
     * @return \ArrayIterator<int<0, max>, string>
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
