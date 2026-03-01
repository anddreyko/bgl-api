<?php

declare(strict_types=1);

namespace Bgl\Core\Security;

/**
 * Typed wrapper for JWT/token payload claims.
 *
 * @implements \IteratorAggregate<string, mixed>
 */
final readonly class TokenPayload implements \IteratorAggregate, \Countable
{
    /**
     * @param array<string, mixed> $claims
     */
    private function __construct(
        private array $claims,
    ) {
    }

    /**
     * @param array<string, mixed> $claims
     */
    public static function fromArray(array $claims): self
    {
        return new self($claims);
    }

    public function get(string $key): mixed
    {
        return $this->claims[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->claims);
    }

    public function getString(string $key): ?string
    {
        /** @var mixed $value */
        $value = $this->claims[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    public function getInt(string $key): ?int
    {
        /** @var mixed $value */
        $value = $this->claims[$key] ?? null;

        return is_int($value) ? $value : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->claims;
    }

    /**
     * @return \ArrayIterator<string, mixed>
     */
    #[\Override]
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->claims);
    }

    #[\Override]
    public function count(): int
    {
        return count($this->claims);
    }
}
