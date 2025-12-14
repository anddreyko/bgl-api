<?php

declare(strict_types=1);

namespace Bgl\Core\Listing\Filter;

use Bgl\Core\Listing\Filter;
use Bgl\Core\Listing\FilterVisitor;

final readonly class Not implements Filter
{
    public function __construct(
        public Filter $filter,
    ) {
    }

    #[\Override]
    public function accept(FilterVisitor $visitor): mixed
    {
        return $visitor->not($this);
    }
}
