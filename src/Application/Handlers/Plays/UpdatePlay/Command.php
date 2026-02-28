<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\UpdatePlay;

use Bgl\Core\Messages\Message;

/**
 * @implements Message<Result>
 */
final readonly class Command implements Message
{
    /**
     * @param non-empty-string $sessionId
     * @param non-empty-string $userId
     */
    public function __construct(
        public string $sessionId,
        public string $userId,
        public ?string $name = null,
        public ?string $gameId = null,
        public string $visibility = 'private',
    ) {
    }
}
