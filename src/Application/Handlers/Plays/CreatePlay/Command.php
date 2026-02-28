<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\OpenSession;

use Bgl\Core\Messages\Message;

/**
 * @implements Message<Result>
 */
final readonly class Command implements Message
{
    /**
     * @param non-empty-string $userId
     * @param list<array{mate_id: non-empty-string, score?: ?int, is_winner?: ?bool, color?: ?string}> $players
     */
    public function __construct(
        public string $userId,
        public ?string $name = null,
        public array $players = [],
        public ?string $gameId = null,
        public ?string $startedAt = null,
        public ?string $finishedAt = null,
        public string $visibility = 'private',
    ) {
    }
}
