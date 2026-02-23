<?php

declare(strict_types=1);

namespace Bgl\Core\ValueObjects;

final readonly class Password
{
    public const int MIN_LENGTH = 8;

    public function __construct(
        private string $value,
    ) {
        if (mb_strlen($this->value) < self::MIN_LENGTH) {
            throw new \InvalidArgumentException(
                sprintf('Password must be at least %d characters long', self::MIN_LENGTH),
            );
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
