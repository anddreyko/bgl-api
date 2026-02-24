<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Application\Handlers\Auth\LoginByCredentials;

use Bgl\Application\Handlers\Auth\LoginByCredentials\Command;
use Bgl\Application\Handlers\Auth\LoginByCredentials\Handler;
use Bgl\Application\Handlers\Auth\LoginByCredentials\Result;
use Bgl\Core\Auth\Authenticator;
use Bgl\Core\Auth\InvalidCredentialsException;
use Bgl\Core\Auth\TokenPair;
use Bgl\Core\Messages\Envelope;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;
use Codeception\Stub;

/**
 * @covers \Bgl\Application\Handlers\Auth\LoginByCredentials\Handler
 */
#[Group('auth', 'login')]
final class HandlerCest
{
    public function testSuccessfulLogin(UnitTester $i): void
    {
        $authenticator = Stub::makeEmpty(Authenticator::class, [
            'login' => static fn(): TokenPair => new TokenPair(
                accessToken: 'access-token-value',
                refreshToken: 'refresh-token-value',
                expiresIn: 7200,
            ),
        ]);

        $handler = new Handler($authenticator);

        $command = new Command(email: 'test@example.com', password: 'secret123');
        $envelope = new Envelope($command, 'msg-1');

        $result = $handler($envelope);

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame('access-token-value', $result->accessToken);
        $i->assertSame('refresh-token-value', $result->refreshToken);
        $i->assertSame(7200, $result->expiresIn);
    }

    public function testInvalidCredentialsPropagate(UnitTester $i): void
    {
        $authenticator = Stub::makeEmpty(Authenticator::class, [
            'login' => static function (): never {
                throw new InvalidCredentialsException();
            },
        ]);

        $handler = new Handler($authenticator);

        $command = new Command(email: 'test@example.com', password: 'wrong');
        $envelope = new Envelope($command, 'msg-2');

        $i->expectThrowable(
            new InvalidCredentialsException(),
            static function () use ($handler, $envelope): void {
                $handler($envelope);
            },
        );
    }
}
