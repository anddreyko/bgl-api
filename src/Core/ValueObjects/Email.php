<?php

declare(strict_types=1);

namespace Bgl\Core\ValueObjects;

final readonly class Email
{
    public function __construct(
        private ?string $value = null,
    ) {
    }

    public function isNull(): bool
    {
        return $this->value === null;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }
}
