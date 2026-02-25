<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Application\Handlers\Auth\ConfirmEmail;

use Bgl\Application\Handlers\Auth\ConfirmEmail\Command;
use Bgl\Application\Handlers\Auth\ConfirmEmail\Handler;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\Email;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Profile\Entities\EmailConfirmationToken;
use Bgl\Domain\Profile\Entities\EmailConfirmationTokens;
use Bgl\Domain\Profile\Entities\User;
use Bgl\Domain\Profile\Entities\UserStatus;
use Bgl\Domain\Profile\Entities\Users;
use Bgl\Domain\Profile\Exceptions\ExpiredConfirmationTokenException;
use Bgl\Domain\Profile\Exceptions\InvalidConfirmationTokenException;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;
use Codeception\Stub;
use Psr\Clock\ClockInterface;

/**
 * @covers \Bgl\Application\Handlers\Auth\ConfirmEmail\Handler
 */
#[Group('auth', 'confirmation')]
final class HandlerCest
{
    public function testSuccessfulConfirmation(UnitTester $i): void
    {
        $now = new \DateTimeImmutable('2024-01-01 12:00:00');
        $userId = new Uuid('user-uuid-1');

        $token = EmailConfirmationToken::create(
            id: new Uuid('token-uuid-1'),
            userId: $userId,
            token: 'valid-token',
            expiresAt: $now->modify('+24 hours'),
        );

        $user = User::register(
            id: $userId,
            email: new Email('test@example.com'),
            passwordHash: 'hashed',
            createdAt: $now,
        );

        $tokens = Stub::makeEmpty(EmailConfirmationTokens::class, [
            'findByToken' => static fn(): EmailConfirmationToken => $token,
            'remove' => static function (): void {
            },
        ]);

        $users = Stub::makeEmpty(Users::class, [
            'find' => static fn(): User => $user,
        ]);

        $clock = Stub::makeEmpty(ClockInterface::class, [
            'now' => static fn(): \DateTimeImmutable => $now,
        ]);

        $handler = new Handler($users, $tokens, $clock);

        $command = new Command(token: 'valid-token');
        $envelope = new Envelope($command, 'msg-1');

        $result = $handler($envelope);

        $i->assertSame('Specified email is confirmed', $result);
        $i->assertSame(UserStatus::Active, $user->getStatus());
    }

    public function testInvalidTokenThrowsException(UnitTester $i): void
    {
        $tokens = Stub::makeEmpty(EmailConfirmationTokens::class, [
            'findByToken' => static fn(): ?EmailConfirmationToken => null,
        ]);

        $users = Stub::makeEmpty(Users::class);

        $clock = Stub::makeEmpty(ClockInterface::class, [
            'now' => static fn(): \DateTimeImmutable => new \DateTimeImmutable('2024-01-01 12:00:00'),
        ]);

        $handler = new Handler($users, $tokens, $clock);

        $command = new Command(token: 'invalid-token');
        $envelope = new Envelope($command, 'msg-2');

        $i->expectThrowable(InvalidConfirmationTokenException::class, static function () use ($handler, $envelope): void {
            $handler($envelope);
        });
    }

    public function testExpiredTokenThrowsException(UnitTester $i): void
    {
        $now = new \DateTimeImmutable('2024-01-02 13:00:00');

        $token = EmailConfirmationToken::create(
            id: new Uuid('token-uuid-1'),
            userId: new Uuid('user-uuid-1'),
            token: 'expired-token',
            expiresAt: new \DateTimeImmutable('2024-01-02 12:00:00'),
        );

        $tokens = Stub::makeEmpty(EmailConfirmationTokens::class, [
            'findByToken' => static fn(): EmailConfirmationToken => $token,
        ]);

        $users = Stub::makeEmpty(Users::class);

        $clock = Stub::makeEmpty(ClockInterface::class, [
            'now' => static fn(): \DateTimeImmutable => $now,
        ]);

        $handler = new Handler($users, $tokens, $clock);

        $command = new Command(token: 'expired-token');
        $envelope = new Envelope($command, 'msg-3');

        $i->expectThrowable(ExpiredConfirmationTokenException::class, static function () use ($handler, $envelope): void {
            $handler($envelope);
        });
    }
}
