<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Locations\ListLocations;

final readonly class Result
{
    /**
     * @param list<array{id: string, name: string, address: ?string, notes: ?string, url: ?string, createdAt: string}> $data
     */
    public function __construct(
        public array $data,
        public int $total,
        public int $page,
        public int $size,
    ) {
    }
}
