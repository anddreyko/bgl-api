<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Games\SearchGames;

final readonly class Result
{
    /**
     * @param list<array{id: string, bggId: int, name: string, yearPublished: ?int}> $data
     */
    public function __construct(
        public array $data,
        public int $total,
        public int $page,
        public int $size,
    ) {
    }
}
