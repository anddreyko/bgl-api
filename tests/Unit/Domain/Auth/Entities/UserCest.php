<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Domain\Profile\Entities;

use Bgl\Core\ValueObjects\DateTime;
use Bgl\Core\ValueObjects\Email;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Profile\User;
use Bgl\Domain\Profile\UserAlreadyConfirmedException;
use Bgl\Domain\Profile\UserStatus;
use Bgl\Tests\Support\UnitTester;
use Codeception\Attribute\Group;

/**
 * @covers \Bgl\Domain\Profile\User
 */
#[Group('auth', 'domain', 'entity')]
final class UserCest
{
    public function testRegisterCreatesUserWithInactiveStatus(UnitTester $i): void
    {
        $user = User::register(
            id: new Uuid('88888888-8888-4888-8888-888888888881'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed',
            createdAt: new DateTime('2024-01-01 00:00:00'),
        );

        $i->assertSame(UserStatus::Inactive, $user->getStatus());
    }

    public function testConfirmChangesStatusToActive(UnitTester $i): void
    {
        $user = User::register(
            id: new Uuid('88888888-8888-4888-8888-888888888881'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed',
            createdAt: new DateTime('2024-01-01 00:00:00'),
        );

        $user->confirm();

        $i->assertSame(UserStatus::Active, $user->getStatus());
    }

    public function testConfirmAlreadyActiveThrowsDomainException(UnitTester $i): void
    {
        $user = new User(
            id: new Uuid('88888888-8888-4888-8888-888888888881'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed',
            createdAt: new DateTime('2024-01-01 00:00:00'),
            status: UserStatus::Active,
        );

        $i->expectThrowable(UserAlreadyConfirmedException::class, static function () use ($user): void {
            $user->confirm();
        });
    }

    public function testTokenVersionDefaultIsOne(UnitTester $i): void
    {
        $user = User::register(
            id: new Uuid('88888888-8888-4888-8888-888888888881'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed',
            createdAt: new DateTime('2024-01-01 00:00:00'),
        );

        $i->assertSame(1, $user->getTokenVersion());
    }

    public function testIncrementTokenVersion(UnitTester $i): void
    {
        $user = User::register(
            id: new Uuid('88888888-8888-4888-8888-888888888881'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed',
            createdAt: new DateTime('2024-01-01 00:00:00'),
        );

        $i->assertSame(1, $user->getTokenVersion());

        $user->incrementTokenVersion();

        $i->assertSame(2, $user->getTokenVersion());

        $user->incrementTokenVersion();

        $i->assertSame(3, $user->getTokenVersion());
    }

    public function testRegisterWithNameUsesProvidedName(UnitTester $i): void
    {
        $user = User::register(
            id: new Uuid('88888888-8888-4888-8888-888888888881'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed',
            createdAt: new DateTime('2024-01-01 00:00:00'),
            name: 'Alice',
        );

        $i->assertSame('Alice', $user->getName());
    }

    public function testRegisterWithoutNameGeneratesDefault(UnitTester $i): void
    {
        $user = User::register(
            id: new Uuid('88888888-8888-4888-8888-888888888881'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed',
            createdAt: new DateTime('2024-01-01 00:00:00'),
        );

        $i->assertMatchesRegularExpression('/^Player\d{4}$/', $user->getName());
    }

    public function testRenameChangesName(UnitTester $i): void
    {
        $user = User::register(
            id: new Uuid('88888888-8888-4888-8888-888888888881'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed',
            createdAt: new DateTime('2024-01-01 00:00:00'),
            name: 'OldName',
        );

        $user->rename('NewName');

        $i->assertSame('NewName', $user->getName());
    }

    public function testRenameWithInvalidNameThrowsException(UnitTester $i): void
    {
        $user = User::register(
            id: new Uuid('88888888-8888-4888-8888-888888888881'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed',
            createdAt: new DateTime('2024-01-01 00:00:00'),
        );

        $i->expectThrowable(\Bgl\Domain\Profile\InvalidNameException::class, static function () use ($user): void {
            $user->rename('Invalid Name!');
        });
    }

    public function testRenameWithEmptyStringThrowsException(UnitTester $i): void
    {
        $user = User::register(
            id: new Uuid('88888888-8888-4888-8888-888888888881'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed',
            createdAt: new DateTime('2024-01-01 00:00:00'),
        );

        $i->expectThrowable(\Bgl\Domain\Profile\InvalidNameException::class, static function () use ($user): void {
            $user->rename('');
        });
    }
}
