<?php

declare(strict_types=1);

namespace Bgl\Core\Serialization;

/**
 * Typed wrapper for denormalized field data (field name => scalar value).
 *
 * @implements \IteratorAggregate<string, string|int|null>
 */
final readonly class DenormalizedData implements \IteratorAggregate, \Countable
{
    /**
     * @param array<string, string|int|null> $data
     */
    private function __construct(
        private array $data,
    ) {
    }

    /**
     * @param array<string, string|int|null> $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public function get(string $field): string|int|null
    {
        return $this->data[$field] ?? null;
    }

    public function has(string $field): bool
    {
        return array_key_exists($field, $this->data);
    }

    /**
     * @return array<string, string|int|null>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @return \ArrayIterator<string, string|int|null>
     */
    #[\Override]
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->data);
    }

    #[\Override]
    public function count(): int
    {
        return count($this->data);
    }
}
