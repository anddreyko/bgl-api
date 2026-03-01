<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\ListPlays;

final readonly class Result
{
    /**
     * @param list<array<string, mixed>> $data
     */
    public function __construct(
        public array $data,
        public int $total,
        public int $page,
        public int $size,
    ) {
    }
}
