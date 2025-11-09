<?php

declare(strict_types=1);

namespace Bgl\Tests\Support\Messages;

use Bgl\Core\Messages\Message;

/**
 * @implements Message<\DateTimeImmutable>
 */
final readonly class GetTimestamp implements Message
{
    public function __construct(
        public string $date,
    ) {
    }
}
