<?php

declare(strict_types=1);

namespace Bgl\Core\Listing\Filter;

use Bgl\Core\Listing\Filter;
use Bgl\Core\Listing\FilterVisitor;

enum None implements Filter
{
    case Filter;

    #[\Override]
    public function accept(FilterVisitor $visitor): mixed
    {
        /** @var Not */
        static $filter = new Not(All::Filter);

        return $visitor->not($filter);
    }
}
