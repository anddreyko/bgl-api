<?php

declare(strict_types=1);

namespace Bgl\Core\Listing\Page;

final readonly class PageSort
{
    /**
     * @param array<array-key, SortDirection> $fields
     */
    public function __construct(public array $fields)
    {
    }

    public function isEmpty(): bool
    {
        return empty($this->fields);
    }
}
