<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Application\Handlers\Auth\SignOut;

use Bgl\Application\Handlers\Auth\SignOut\Command;
use Bgl\Application\Handlers\Auth\SignOut\Handler;
use Bgl\Core\Messages\Envelope;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Application\Handlers\Auth\SignOut\Handler
 */
#[Group('auth', 'sign-out')]
final class HandlerCest
{
    public function testSuccessfulSignOutReturnsExpectedString(UnitTester $i): void
    {
        $handler = new Handler();

        $command = new Command(userId: 'user-id-123');
        $envelope = new Envelope($command, 'msg-1');

        $result = $handler($envelope);

        $i->assertSame('sign out', $result);
    }
}
