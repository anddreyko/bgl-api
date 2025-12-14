<?php

declare(strict_types=1);

namespace Bgl\Core\Listing;

final readonly class Field
{
    public function __construct(
        public int|string $field,
    ) {
    }
}
