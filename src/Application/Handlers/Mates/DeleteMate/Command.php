<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Mates\DeleteMate;

use Bgl\Core\Messages\Message;

/**
 * @implements Message<null>
 */
final readonly class Command implements Message
{
    /**
     * @param non-empty-string $userId
     * @param non-empty-string $mateId
     */
    public function __construct(
        public string $userId,
        public string $mateId,
    ) {
    }
}
