<?php

declare(strict_types=1);

namespace Bgl\Core\Listing\Page;

final readonly class PageSort
{
    public function __construct(public SortFields $fields = new SortFields())
    {
    }

    public function isEmpty(): bool
    {
        return $this->fields->isEmpty();
    }
}
