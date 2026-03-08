<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\GetPlay;

final readonly class Result
{
    /**
     * @param array{id: string, name: string} $author
     * @param ?array{id: string, name: string} $game
     * @param list<array{id: string, mate_id: string, score: ?int, is_winner: bool, color: ?string, team_tag: ?string, number: ?int}> $players
     */
    public function __construct(
        public string $id,
        public array $author,
        public ?string $name,
        public string $status,
        public string $visibility,
        public ?string $startedAt,
        public ?string $finishedAt,
        public ?array $game,
        public array $players,
        public ?string $notes = null,
        public ?string $locationId = null,
    ) {
    }
}
