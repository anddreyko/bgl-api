<?php

declare(strict_types=1);

namespace Bgl\Core\Http;

/**
 * Typed wrapper for authentication parameter names to inject from request attributes.
 *
 * @implements \IteratorAggregate<int, string>
 */
final readonly class AuthParams implements \IteratorAggregate, \Countable
{
    /**
     * @param list<string> $params
     */
    public function __construct(
        private array $params = [],
    ) {
    }

    public function contains(string $name): bool
    {
        return in_array($name, $this->params, true);
    }

    /**
     * @return list<string>
     */
    public function toArray(): array
    {
        return $this->params;
    }

    /**
     * @return \ArrayIterator<int<0, max>, string>
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
