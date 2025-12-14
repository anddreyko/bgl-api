<?php

declare(strict_types=1);

namespace Bgl\Core\Listing\Page;

final class PageSize
{
    public function __construct(
        private ?int $value = null
    ) {
    }

    public function getValue(): ?int
    {
        return $this->value;
    }
}
