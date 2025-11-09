<?php

declare(strict_types=1);

namespace Bgl\Tests\Support\Messages;

use Bgl\Core\Messages\Command;

final readonly class Ping implements Command
{
    public function __construct(
        public string $text,
    ) {
    }
}
