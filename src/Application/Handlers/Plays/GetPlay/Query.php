<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\GetPlay;

use Bgl\Core\Messages\Message;

/**
 * @implements Message<Result>
 */
final readonly class Query implements Message
{
    public function __construct(
        public string $playId,
        public ?string $userId = null,
    ) {
    }
}
