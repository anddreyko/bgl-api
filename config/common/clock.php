<?php

declare(strict_types=1);

use Bgl\Core\Clock;
use Psr\Clock\ClockInterface;

return [
    ClockInterface::class => static fn(): ClockInterface => new Clock(),
];
