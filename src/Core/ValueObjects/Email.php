<?php

declare(strict_types=1);

namespace Bgl\Core\ValueObjects;

/**
 * @see \Bgl\Tests\Unit\Core\ValueObjects\EmailCest
 */
final readonly class Email implements \Stringable
{
    public function __construct(
        private ?string $value = null,
    ) {
        if ($this->value !== null && filter_var($this->value, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException(
                sprintf('Invalid email format: "%s"', $this->value),
            );
        }
    }

    public function isNull(): bool
    {
        return $this->value === null;
    }

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
