<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Application\Handlers\Auth\ConfirmEmail;

use Bgl\Application\Handlers\Auth\ConfirmEmail\Command;
use Bgl\Application\Handlers\Auth\ConfirmEmail\Handler;
use Bgl\Core\Auth\Confirmer;
use Bgl\Core\Auth\ExpiredConfirmationTokenException;
use Bgl\Core\Auth\InvalidConfirmationTokenException;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\Email;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Profile\Entities\User;
use Bgl\Domain\Profile\Entities\UserStatus;
use Bgl\Domain\Profile\Entities\Users;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;
use Codeception\Stub;

/**
 * @covers \Bgl\Application\Handlers\Auth\ConfirmEmail\Handler
 */
#[Group('auth', 'confirmation')]
final class HandlerCest
{
    public function testSuccessfulConfirmation(UnitTester $i): void
    {
        $userId = new Uuid('user-uuid-1');

        $user = User::register(
            id: $userId,
            email: new Email('test@example.com'),
            passwordHash: 'hashed',
            createdAt: new \DateTimeImmutable('2024-01-01 12:00:00'),
        );

        $confirmer = Stub::makeEmpty(Confirmer::class, [
            'confirm' => static fn(): Uuid => $userId,
        ]);

        $users = Stub::makeEmpty(Users::class, [
            'find' => static fn(): User => $user,
        ]);

        $handler = new Handler($users, $confirmer);

        $command = new Command(token: 'valid-token');
        $envelope = new Envelope($command, 'msg-1');

        $result = $handler($envelope);

        $i->assertSame('Specified email is confirmed', $result);
        $i->assertSame(UserStatus::Active, $user->getStatus());
    }

    public function testInvalidTokenThrowsException(UnitTester $i): void
    {
        $confirmer = Stub::makeEmpty(Confirmer::class, [
            'confirm' => static function (): never {
                throw new InvalidConfirmationTokenException();
            },
        ]);

        $users = Stub::makeEmpty(Users::class);

        $handler = new Handler($users, $confirmer);

        $command = new Command(token: 'invalid-token');
        $envelope = new Envelope($command, 'msg-2');

        $i->expectThrowable(InvalidConfirmationTokenException::class, static function () use ($handler, $envelope): void {
            $handler($envelope);
        });
    }

    public function testExpiredTokenThrowsException(UnitTester $i): void
    {
        $confirmer = Stub::makeEmpty(Confirmer::class, [
            'confirm' => static function (): never {
                throw new ExpiredConfirmationTokenException();
            },
        ]);

        $users = Stub::makeEmpty(Users::class);

        $handler = new Handler($users, $confirmer);

        $command = new Command(token: 'expired-token');
        $envelope = new Envelope($command, 'msg-3');

        $i->expectThrowable(ExpiredConfirmationTokenException::class, static function () use ($handler, $envelope): void {
            $handler($envelope);
        });
    }
}
