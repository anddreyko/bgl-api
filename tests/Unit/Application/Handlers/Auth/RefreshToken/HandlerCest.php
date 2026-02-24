<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Application\Handlers\Auth\RefreshToken;

use Bgl\Application\Handlers\Auth\RefreshToken\Command;
use Bgl\Application\Handlers\Auth\RefreshToken\Handler;
use Bgl\Application\Handlers\Auth\RefreshToken\Result;
use Bgl\Core\Auth\Authenticator;
use Bgl\Core\Auth\InvalidRefreshTokenException;
use Bgl\Core\Auth\TokenPair;
use Bgl\Core\Messages\Envelope;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;
use Codeception\Stub;

/**
 * @covers \Bgl\Application\Handlers\Auth\RefreshToken\Handler
 */
#[Group('auth', 'token-refresh')]
final class HandlerCest
{
    public function testSuccessfulRefresh(UnitTester $i): void
    {
        $authenticator = Stub::makeEmpty(Authenticator::class, [
            'refresh' => static fn(): TokenPair => new TokenPair(
                accessToken: 'new-access-token',
                refreshToken: 'new-refresh-token',
                expiresIn: 7200,
            ),
        ]);

        $handler = new Handler($authenticator);

        $command = new Command(refreshToken: 'old-refresh-token');
        $envelope = new Envelope($command, 'msg-1');

        $result = $handler($envelope);

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame('new-access-token', $result->accessToken);
        $i->assertSame('new-refresh-token', $result->refreshToken);
        $i->assertSame(7200, $result->expiresIn);
    }

    public function testInvalidRefreshTokenPropagate(UnitTester $i): void
    {
        $authenticator = Stub::makeEmpty(Authenticator::class, [
            'refresh' => static function (): never {
                throw new InvalidRefreshTokenException();
            },
        ]);

        $handler = new Handler($authenticator);

        $command = new Command(refreshToken: 'invalid-token');
        $envelope = new Envelope($command, 'msg-2');

        $i->expectThrowable(
            new InvalidRefreshTokenException(),
            static function () use ($handler, $envelope): void {
                $handler($envelope);
            },
        );
    }
}
