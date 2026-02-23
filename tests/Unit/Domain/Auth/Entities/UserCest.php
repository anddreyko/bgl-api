<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Domain\Auth\Entities;

use Bgl\Core\ValueObjects\Email;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Auth\Entities\User;
use Bgl\Domain\Auth\Entities\UserStatus;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Domain\Auth\Entities\User
 */
#[Group('auth', 'domain', 'entity')]
final class UserCest
{
    public function testRegisterCreatesUserWithInactiveStatus(UnitTester $i): void
    {
        $user = User::register(
            id: new Uuid('user-id-1'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed',
            createdAt: new \DateTimeImmutable('2024-01-01 00:00:00'),
        );

        $i->assertSame(UserStatus::Inactive, $user->getStatus());
    }

    public function testConfirmChangesStatusToActive(UnitTester $i): void
    {
        $user = User::register(
            id: new Uuid('user-id-1'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed',
            createdAt: new \DateTimeImmutable('2024-01-01 00:00:00'),
        );

        $user->confirm();

        $i->assertSame(UserStatus::Active, $user->getStatus());
    }

    public function testConfirmAlreadyActiveThrowsDomainException(UnitTester $i): void
    {
        $user = new User(
            id: new Uuid('user-id-1'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed',
            createdAt: new \DateTimeImmutable('2024-01-01 00:00:00'),
            status: UserStatus::Active,
        );

        $i->expectThrowable(\DomainException::class, static function () use ($user): void {
            $user->confirm();
        });
    }

    public function testTokenVersionDefaultIsOne(UnitTester $i): void
    {
        $user = User::register(
            id: new Uuid('user-id-1'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed',
            createdAt: new \DateTimeImmutable('2024-01-01 00:00:00'),
        );

        $i->assertSame(1, $user->getTokenVersion());
    }

    public function testIncrementTokenVersion(UnitTester $i): void
    {
        $user = User::register(
            id: new Uuid('user-id-1'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed',
            createdAt: new \DateTimeImmutable('2024-01-01 00:00:00'),
        );

        $i->assertSame(1, $user->getTokenVersion());

        $user->incrementTokenVersion();

        $i->assertSame(2, $user->getTokenVersion());

        $user->incrementTokenVersion();

        $i->assertSame(3, $user->getTokenVersion());
    }
}
