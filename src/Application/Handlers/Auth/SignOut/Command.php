<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\SignOut;

use Bgl\Core\Messages\Message;

/**
 * @implements Message<string>
 */
final class Command implements Message
{
    public function __construct(
        public readonly string $userId,
    ) {
    }
}
