<?php

declare(strict_types=1);

namespace Bgl\Core\Http;

/**
 * Typed wrapper for URL path parameters extracted from route matching.
 *
 * @implements \IteratorAggregate<string, string>
 */
final readonly class PathParams implements \IteratorAggregate, \Countable
{
    /**
     * @param array<string, string> $params
     */
    public function __construct(
        private array $params = [],
    ) {
    }

    public function get(string $key): ?string
    {
        return $this->params[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->params);
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->params;
    }

    /**
     * @return \ArrayIterator<string, string>
     */
    #[\Override]
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->params);
    }

    #[\Override]
    public function count(): int
    {
        return count($this->params);
    }
}
