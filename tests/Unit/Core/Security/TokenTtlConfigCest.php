<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Core\Security;

use Bgl\Core\Security\TokenConfig;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Core\Security\TokenConfig
 */
#[Group('auth', 'security')]
final class TokenTtlConfigCest
{
    public function testDefaultValues(UnitTester $i): void
    {
        $config = new TokenConfig(7200, 2592000);

        $i->assertSame(7200, $config->accessTtl);
        $i->assertSame(2592000, $config->refreshTtl);
    }

    public function testCustomValues(UnitTester $i): void
    {
        $config = new TokenConfig(3600, 86400);

        $i->assertSame(3600, $config->accessTtl);
        $i->assertSame(86400, $config->refreshTtl);
    }

    public function testAccessTtlBelowMinimumThrows(UnitTester $i): void
    {
        $i->expectThrowable(\InvalidArgumentException::class, static fn() => new TokenConfig(10, 86400));
    }

    public function testAccessTtlAboveMaximumThrows(UnitTester $i): void
    {
        $i->expectThrowable(\InvalidArgumentException::class, static fn() => new TokenConfig(3_000_000, 86400));
    }

    public function testRefreshTtlBelowMinimumThrows(UnitTester $i): void
    {
        $i->expectThrowable(\InvalidArgumentException::class, static fn() => new TokenConfig(3600, 30));
    }

    public function testRefreshTtlMustBeGreaterThanAccessTtl(UnitTester $i): void
    {
        $i->expectThrowable(\InvalidArgumentException::class, static fn() => new TokenConfig(3600, 3600));
    }

    public function testBoundaryMinimumValues(UnitTester $i): void
    {
        $config = new TokenConfig(60, 61);

        $i->assertSame(60, $config->accessTtl);
        $i->assertSame(61, $config->refreshTtl);
    }
}
