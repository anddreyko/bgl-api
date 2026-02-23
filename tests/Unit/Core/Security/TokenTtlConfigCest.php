<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Core\Security;

use Bgl\Core\Security\TokenTtlConfig;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Core\Security\TokenTtlConfig
 */
#[Group('auth', 'security')]
final class TokenTtlConfigCest
{
    public function testDefaultValues(UnitTester $i): void
    {
        $config = new TokenTtlConfig(7200, 2592000);

        $i->assertSame(7200, $config->accessTtl);
        $i->assertSame(2592000, $config->refreshTtl);
    }

    public function testCustomValues(UnitTester $i): void
    {
        $config = new TokenTtlConfig(3600, 86400);

        $i->assertSame(3600, $config->accessTtl);
        $i->assertSame(86400, $config->refreshTtl);
    }
}
