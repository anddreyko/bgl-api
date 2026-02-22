<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Plays\OpenSession;

use Bgl\Core\Messages\Message;

/**
 * @implements Message<Result>
 */
final class Command implements Message
{
    public function __construct(
        public readonly string $userId,
        public readonly ?string $name = null,
    ) {
    }
}
