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
    public function __construct(
        public Uuid $sessionId,
        public Uuid $userId,
        public ?string $name = null,
        public ?Uuid $gameId = null,
        public string $visibility = 'private',
    ) {
    }
}
