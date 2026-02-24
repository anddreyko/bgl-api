<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Application\Handlers\Auth\SignOut;

use Bgl\Application\Handlers\Auth\SignOut\Command;
use Bgl\Application\Handlers\Auth\SignOut\Handler;
use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Auth\Authenticator;
use Bgl\Core\Messages\Envelope;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;
use Codeception\Stub;

/**
 * @covers \Bgl\Application\Handlers\Auth\SignOut\Handler
 */
#[Group('auth', 'sign-out')]
final class HandlerCest
{
    public function testSuccessfulSignOutReturnsExpectedString(UnitTester $i): void
    {
        $authenticator = Stub::makeEmpty(Authenticator::class, [
            'revoke' => static function (): void {},
        ]);

        $handler = new Handler($authenticator);

        $command = new Command(userId: 'user-id-123');
        $envelope = new Envelope($command, 'msg-1');

        $result = $handler($envelope);

        $i->assertSame('sign out', $result);
    }

    public function testUserNotFoundPropagate(UnitTester $i): void
    {
        $authenticator = Stub::makeEmpty(Authenticator::class, [
            'revoke' => static function (): never {
                throw new AuthenticationException('User not found');
            },
        ]);

        $handler = new Handler($authenticator);

        $command = new Command(userId: 'nonexistent');
        $envelope = new Envelope($command, 'msg-2');

        $i->expectThrowable(
            new AuthenticationException('User not found'),
            static function () use ($handler, $envelope): void {
                $handler($envelope);
            },
        );
    }
}
