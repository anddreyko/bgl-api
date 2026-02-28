<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\FinalizePlay;

use Bgl\Core\Messages\Message;
use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Uuid;

/**
 * @implements Message<Result>
 */
final readonly class Command implements Message
{
    public function __construct(
        public Uuid $sessionId,
        public Uuid $userId,
        public ?DateTime $finishedAt = null,
    ) {
    }
}
