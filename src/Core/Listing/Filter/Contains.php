<?php

declare(strict_types=1);

namespace Bgl\Core\Listing\Filter;

use Bgl\Core\Listing\Filter;
use Bgl\Core\Listing\FilterVisitor;

final readonly class Contains implements Filter
{
    public function __construct(
        public mixed $left,
        public mixed $right,
    ) {
    }

    #[\Override]
    public function accept(FilterVisitor $visitor): mixed
    {
        return $visitor->contains($this);
    }
}
