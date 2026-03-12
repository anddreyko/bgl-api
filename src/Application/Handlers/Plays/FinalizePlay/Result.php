<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\FinalizePlay;

final readonly class Result
{
    /**
     * @param array{id: string, name: string} $author
     * @param ?array{id: string, name: string} $game
     * @param list<array{
     *     id: string,
     *     mate: array{id: string, name: string},
     *     score: ?int,
     *     is_winner: bool,
     *     color: ?string,
     *     team_tag: ?string,
     *     number: ?int
     * }> $players
     * @param ?array{id: string, name: string} $location
     */
    public function __construct(
        public string $id,
        public array $author,
        public ?string $name,
        public string $visibility,
        public ?string $startedAt,
        public ?string $finishedAt,
        public ?array $game,
        public array $players,
        public string $status = 'finished',
        public ?string $notes = null,
        public ?array $location = null,
    ) {
    }
}
