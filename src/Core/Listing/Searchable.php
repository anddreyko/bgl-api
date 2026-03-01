<?php

declare(strict_types=1);

namespace Bgl\Core\Listing;

use Bgl\Core\Listing\Filter\All;
use Bgl\Core\Listing\Filter\None;
use Bgl\Core\Listing\Page\PageNumber;
use Bgl\Core\Listing\Page\PageSize;
use Bgl\Core\Listing\Page\PageSort;

interface Searchable
{
    /**
     * @return list<array<string, mixed>> List of key arrays (e.g., [['id' => 'uuid-1'], ['id' => 'uuid-2']])
     */
    public function search(
        Filter $filter = None::Filter,
        PageSize $size = new PageSize(),
        PageNumber $number = new PageNumber(1),
        PageSort $sort = new PageSort()
    ): iterable;
}
