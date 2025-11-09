<?php

declare(strict_types=1);

namespace Bgl\Tests\Support\Messages;

use Bgl\Core\Messages\Event;

final readonly class Pong implements Event
{
    public function __construct(
        public string $text,
    ) {
    }
}
