<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Auth\RegisterPasskeyOptions;

use Bgl\Core\Messages\Message;

/**
 * @implements Message<Result>
 */
final readonly class Command implements Message
{
    public function __construct(
        public string $userId,
    ) {
    }
}
