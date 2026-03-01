<?php

declare(strict_types=1);

namespace Bgl\Core\Http;

/**
 * Typed wrapper for parameter name mapping (URL param name => message field name).
 *
 * @implements \IteratorAggregate<string, string>
 */
final readonly class ParamMap implements \IteratorAggregate, \Countable
{
    /**
     * @param array<string, string> $mapping
     */
    public function __construct(
        private array $mapping = [],
    ) {
    }

    public function get(string $key): ?string
    {
        return $this->mapping[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->mapping);
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
