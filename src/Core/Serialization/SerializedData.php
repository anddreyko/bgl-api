<?php

declare(strict_types=1);

namespace Bgl\Core\Serialization;

/**
 * Typed wrapper for serialized data representation.
 *
 * @implements \IteratorAggregate<string, mixed>
 */
final readonly class SerializedData implements \IteratorAggregate, \Countable
{
    /**
     * @param array<string, mixed> $data
     */
    private function __construct(
        private array $data,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public function get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @return \ArrayIterator<string, mixed>
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
