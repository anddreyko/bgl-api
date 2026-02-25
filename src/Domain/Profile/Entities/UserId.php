<?php

declare(strict_types=1);

namespace Bgl\Domain\Profile\Entities;

final readonly class UserId
{
    public function __construct(
        private string $value
    ) {
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
