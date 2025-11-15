<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit;

use Bgl\Core\Clock;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Core\Clock
 */
#[Group('clock')]
final class ClockCest
{
    public function testStub(UnitTester $i): void
    {
        $stub = new \DateTimeImmutable();
        $clock = new Clock(stub: $stub);
        $clockTime = $clock->now();

        $i->assertEquals(
            $stub->format(DATE_W3C),
            $clockTime->format(DATE_W3C)
        );
    }

    public function testSystemTime(UnitTester $i): void
    {
        $systemTime = new \DateTimeImmutable();
        $clock = new Clock();
        $clockTime = $clock->now();

        $i->assertEquals(
            $systemTime->format('Y-m-d H:i:s'),
            $clockTime->format('Y-m-d H:i:s')
        );
    }

    public function testDefaultTimezone(UnitTester $i): void
    {
        $clock = new Clock();
        $result = $clock->now();

        $i->assertEquals(
            date_default_timezone_get(),
            $result->getTimezone()->getName()
        );
    }

    public function testCustomTimezone(UnitTester $i): void
    {
        $clock = new Clock(timezone: new \DateTimeZone('Europe/Moscow'));
        $result = $clock->now();

        $i->assertEquals(
            'Europe/Moscow',
            $result->getTimezone()->getName()
        );
    }

    public function testOffsetTimezone(UnitTester $i): void
    {
        $clock = new Clock(
            timezone: new \DateTimeZone('+03:00'),
            stub: new \DateTimeImmutable('2024-01-01 10:00:00', new \DateTimeZone('UTC'))
        );
        $result = $clock->now();

        $i->assertEquals(
            '2024-01-01T13:00:00+03:00',
            $result->format(DATE_W3C)
        );
    }
}
