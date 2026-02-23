<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Application\Handlers\Auth\SignOut;

use Bgl\Application\Handlers\Auth\SignOut\Command;
use Bgl\Application\Handlers\Auth\SignOut\Handler;
use Bgl\Core\Auth\AuthenticationException;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\ValueObjects\Email;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Auth\Entities\User;
use Bgl\Domain\Auth\Entities\Users;
use Bgl\Domain\Auth\Entities\UserStatus;
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
        $user = new User(
            id: new Uuid('user-id-123'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed',
            createdAt: new \DateTimeImmutable('2024-01-01 00:00:00'),
            status: UserStatus::Active,
        );

        $users = Stub::makeEmpty(Users::class, [
            'find' => static fn(): User => $user,
        ]);

        $handler = new Handler($users);

        $command = new Command(userId: 'user-id-123');
        $envelope = new Envelope($command, 'msg-1');

        $result = $handler($envelope);

        $i->assertSame('sign out', $result);
    }

    public function testSignOutIncrementsTokenVersion(UnitTester $i): void
    {
        $user = new User(
            id: new Uuid('user-id-123'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed',
            createdAt: new \DateTimeImmutable('2024-01-01 00:00:00'),
            status: UserStatus::Active,
        );

        $i->assertSame(1, $user->getTokenVersion());

        $users = Stub::makeEmpty(Users::class, [
            'find' => static fn(): User => $user,
        ]);

        $handler = new Handler($users);

        $command = new Command(userId: 'user-id-123');
        $envelope = new Envelope($command, 'msg-1');

        $handler($envelope);

        $i->assertSame(2, $user->getTokenVersion());
    }

    public function testUserNotFoundThrowsAuthenticationException(UnitTester $i): void
    {
        $users = Stub::makeEmpty(Users::class, [
            'find' => static fn(): ?User => null,
        ]);

        $handler = new Handler($users);

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
