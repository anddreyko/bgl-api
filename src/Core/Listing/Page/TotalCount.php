<?php

declare(strict_types=1);

namespace Bgl\Core\Listing\Page;

final readonly class TotalCount
{
    public function __construct(
        private int $value
    ) {
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
