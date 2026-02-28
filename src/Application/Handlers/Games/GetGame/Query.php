<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Games\GetGame;

use Bgl\Core\Messages\Message;

/**
 * @implements Message<Result>
 */
final readonly class Query implements Message
{
    /**
     * @param non-empty-string $gameId
     */
    public function __construct(
        public string $gameId,
    ) {
    }
}
