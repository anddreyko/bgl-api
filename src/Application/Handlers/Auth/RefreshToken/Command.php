<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\RefreshToken;

use Bgl\Core\Messages\Message;

/**
 * @implements Message<Result>
 */
final class Command implements Message
{
    public function __construct(
        public readonly string $refreshToken,
    ) {
    }
}
