<?php

declare(strict_types=1);

namespace Bgl\Core;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

/**
 * @see \Bgl\Tests\Unit\ClockCest
 */
final readonly class Clock implements ClockInterface
{
    public function __construct(
        private ?\DateTimeZone $timezone = null,
        private ?DateTimeImmutable $stub = null
    ) {
    }

    #[\Override]
    public function now(): DateTimeImmutable
    {
        $date = $this->stub ?? new DateTimeImmutable();
        if ($this->timezone) {
            $date = $date->setTimezone($this->timezone);
        }

        return $date;
    }
}
