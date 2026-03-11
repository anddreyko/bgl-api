<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\ListPlays;

use Bgl\Core\Messages\Message;

/**
 * @implements Message<Result>
 */
final readonly class Query implements Message
{
    /**
     * @param ?non-empty-string $userId
     * @param ?non-empty-string $authorId
     */
    public function __construct(
        public ?string $userId = null,
        public int $page = 1,
        public int $size = 20,
        public ?string $gameId = null,
        public ?string $from = null,
        public ?string $to = null,
        public ?string $authorId = null,
        public ?string $status = null,
    ) {
    }
}
