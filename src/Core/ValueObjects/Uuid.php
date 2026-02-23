<?php

declare(strict_types=1);

namespace Bgl\Core\ValueObjects;

final readonly class Uuid implements \Stringable
{
    /**
     * @param non-empty-string|null $value
     */
    public function __construct(
        private ?string $value = null
    ) {
    }

    public function isNull(): bool
    {
        return null === $this->value;
    }

    /**
     * @return non-empty-string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value ?? '';
    }
}
