<?php

declare(strict_types=1);

namespace Bgl\Core\Listing;

interface Filter
{
    /**
     * @template TResult
     * @param FilterVisitor<TResult> $visitor
     *
     * @return TResult
     */
    public function accept(FilterVisitor $visitor): mixed;
}
