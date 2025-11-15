<?php

declare(strict_types=1);

namespace Bgl\Application\Handlers\Ping;

use Bgl\Core\Messages\Message;

/**
 * @implements Message<Result>
 */
final class Command implements Message
{
    public function __construct(
        public \DateTimeImmutable $datetime = new \DateTimeImmutable()
    ) {
    }
}
