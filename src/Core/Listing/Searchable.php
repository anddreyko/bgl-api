<?php

declare(strict_types=1);

namespace Bgl\Core\Listing;

use Bgl\Core\Listing\Filter\None;
use Bgl\Core\Listing\Page\PageNumber;
use Bgl\Core\Listing\Page\PageSize;
use Bgl\Core\Listing\Page\PageSort;

/**
 * @template-covariant TEntity of object
 */
interface Searchable
{
    /**
     * @param Filter $filter
     * @param PageNumber $number
     * @param PageSize $size
     * @param PageSort $sort
     *
     * @return iterable<TEntity>
     */
    public function search(
        Filter $filter = None::Filter,
        PageSize $size = new PageSize(),
        PageNumber $number = new PageNumber(1),
        PageSort $sort = new PageSort([])
    ): iterable;
}
