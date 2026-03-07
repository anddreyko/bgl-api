<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\DeletePlay;

use Bgl\Core\Messages\Message;
use Bgl\Core\ValueObjects\Uuid;

/**
 * @implements Message<null>
 */
final readonly class Command implements Message
{
    public function __construct(
        public Uuid $sessionId,
        public Uuid $userId,
    ) {
    }
}
