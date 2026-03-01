<?php

declare(strict_types=1);

namespace Bgl\Core\Serialization;

/**
 * Typed wrapper for field mapping configuration (source path => target field name).
 *
 * @implements \IteratorAggregate<string, string>
 */
final readonly class FieldMapping implements \IteratorAggregate, \Countable
{
    /**
     * @param array<string, string> $mapping
     */
    private function __construct(
        private array $mapping,
    ) {
    }

    /**
     * @param array<string, string> $mapping
     */
    public static function fromArray(array $mapping): self
    {
        return new self($mapping);
    }

    public function get(string $sourcePath): ?string
    {
        return $this->mapping[$sourcePath] ?? null;
    }

    public function has(string $sourcePath): bool
    {
        return array_key_exists($sourcePath, $this->mapping);
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->mapping;
    }

    /**
     * @return \ArrayIterator<string, string>
     */
    #[\Override]
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->mapping);
    }

    #[\Override]
    public function count(): int
    {
        return count($this->mapping);
    }
}
