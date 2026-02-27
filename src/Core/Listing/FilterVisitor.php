<?php

declare(strict_types=1);

namespace Bgl\Core\Listing;

use Bgl\Core\Listing\Filter\All;
use Bgl\Core\Listing\Filter\AndX;
use Bgl\Core\Listing\Filter\Equals;
use Bgl\Core\Listing\Filter\Greater;
use Bgl\Core\Listing\Filter\Less;
use Bgl\Core\Listing\Filter\Not;
use Bgl\Core\Listing\Filter\Contains;
use Bgl\Core\Listing\Filter\OrX;

/**
 * @template-covariant TResult of mixed
 */
interface FilterVisitor
{
    /**
     * @return TResult
     */
    public function all(All $filter): mixed;

    /**
     * @return TResult
     */
    public function equals(Equals $filter): mixed;

    /**
     * @return TResult
     */
    public function less(Less $filter): mixed;

    /**
     * @return TResult
     */
    public function greater(Greater $filter): mixed;

    /**
     * @return TResult
     */
    public function and(AndX $filter): mixed;

    /**
     * @return TResult
     */
    public function or(OrX $filter): mixed;

    /**
     * @return TResult
     */
    public function not(Not $filter): mixed;

    /**
     * @return TResult
     */
    public function contains(Contains $filter): mixed;
}
