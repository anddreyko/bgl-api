<?php

declare(strict_types=1);

namespace Bgl\Core\Listing\Filter;

use Bgl\Core\Listing\Filter;
use Bgl\Core\Listing\FilterVisitor;

final readonly class AndX implements Filter
{
    /**
     * @param non-empty-list<Filter> $filters
     */
    public function __construct(
        public array $filters,
    ) {
    }

    #[\Override]
    public function accept(FilterVisitor $visitor): mixed
    {
        return $visitor->and($this);
    }
}
