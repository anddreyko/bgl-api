<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Mates\ListMates;

final readonly class Result
{
    /**
     * @param list<array{id: string, name: string, notes: ?string, createdAt: string}> $data
     */
    public function __construct(
        public array $data,
        public int $total,
        public int $page,
        public int $size,
    ) {
    }
}
