<?php

declare(strict_types=1);

namespace Bgl\Core\Listing\Filter;

use Bgl\Core\Listing\Filter;
use Bgl\Core\Listing\FilterVisitor;

enum All implements Filter
{
    case Filter;

    #[\Override]
    public function accept(FilterVisitor $visitor): mixed
    {
        return $visitor->all($this);
    }
}
