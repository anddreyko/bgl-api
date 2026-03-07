<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\UpdatePlay;

use Bgl\Core\Messages\Message;
use Bgl\Core\ValueObjects\Uuid;

/**
 * @implements Message<Result>
 */
final readonly class Command implements Message
{
    /**
     * @param list<array{mate_id: non-empty-string, score?: ?int, is_winner?: ?bool, color?: ?string}> $players
     */
    public function __construct(
        public Uuid $sessionId,
        public Uuid $userId,
        public ?string $name = null,
        public ?Uuid $gameId = null,
        public string $visibility = 'private',
        public ?string $status = null,
        public array $players = [],
    ) {
    }
}
