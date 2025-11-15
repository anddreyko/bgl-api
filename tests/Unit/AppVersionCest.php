<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit;

use Bgl\Core\AppVersion;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Core\AppVersion
 */
#[Group('appVersion')]
class AppVersionCest
{
    public function testCreated(UnitTester $i): void
    {
        $versionString = '1.0.0';
        $appVersion = new AppVersion($versionString);

        $i->assertSame($versionString, $appVersion->getVersion());
    }
}
