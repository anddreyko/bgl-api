<?php

declare(strict_types=1);

namespace Bgl\Tests\Unit\Application\Handlers\Auth\LoginByCredentials;

use Bgl\Application\Handlers\Auth\LoginByCredentials\Command;
use Bgl\Application\Handlers\Auth\LoginByCredentials\Handler;
use Bgl\Application\Handlers\Auth\LoginByCredentials\Result;
use Bgl\Core\Messages\Envelope;
use Bgl\Core\Security\PasswordHasher;
use Bgl\Core\Security\TokenGenerator;
use Bgl\Core\ValueObjects\Email;
use Bgl\Core\ValueObjects\Uuid;
use Bgl\Domain\Auth\Entities\User;
use Bgl\Domain\Auth\Entities\Users;
use Bgl\Domain\Auth\Entities\UserStatus;
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
        $user = new User(
            id: new Uuid('user-id-123'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed_password',
            createdAt: new \DateTimeImmutable('2024-01-01 12:00:00'),
            status: UserStatus::Active,
        );

        $users = Stub::makeEmpty(Users::class, [
            'findByEmail' => static fn(): User => $user,
        ]);

        $passwordHasher = Stub::makeEmpty(PasswordHasher::class, [
            'verify' => static fn(): bool => true,
        ]);

        $tokenGenerator = Stub::makeEmpty(TokenGenerator::class, [
            'generate' => Stub::consecutive('access-token-value', 'refresh-token-value'),
        ]);

        $handler = new Handler($users, $passwordHasher, $tokenGenerator);

        $command = new Command(email: 'test@example.com', password: 'secret123');
        $envelope = new Envelope($command, 'msg-1');

        $result = $handler($envelope);

        $i->assertInstanceOf(Result::class, $result);
        $i->assertSame('access-token-value', $result->accessToken);
        $i->assertSame('refresh-token-value', $result->refreshToken);
        $i->assertSame(7200, $result->expiresIn);
    }

    public function testWrongEmailThrowsDomainException(UnitTester $i): void
    {
        $users = Stub::makeEmpty(Users::class, [
            'findByEmail' => static fn(): ?User => null,
        ]);

        $passwordHasher = Stub::makeEmpty(PasswordHasher::class);
        $tokenGenerator = Stub::makeEmpty(TokenGenerator::class);

        $handler = new Handler($users, $passwordHasher, $tokenGenerator);

        $command = new Command(email: 'nonexistent@example.com', password: 'secret123');
        $envelope = new Envelope($command, 'msg-2');

        $i->expectThrowable(
            new \DomainException('Invalid credentials'),
            static function () use ($handler, $envelope): void {
                $handler($envelope);
            },
        );
    }

    public function testWrongPasswordThrowsDomainException(UnitTester $i): void
    {
        $user = new User(
            id: new Uuid('user-id-123'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed_password',
            createdAt: new \DateTimeImmutable('2024-01-01 12:00:00'),
            status: UserStatus::Active,
        );

        $users = Stub::makeEmpty(Users::class, [
            'findByEmail' => static fn(): User => $user,
        ]);

        $passwordHasher = Stub::makeEmpty(PasswordHasher::class, [
            'verify' => static fn(): bool => false,
        ]);

        $tokenGenerator = Stub::makeEmpty(TokenGenerator::class);

        $handler = new Handler($users, $passwordHasher, $tokenGenerator);

        $command = new Command(email: 'test@example.com', password: 'wrong-password');
        $envelope = new Envelope($command, 'msg-3');

        $i->expectThrowable(
            new \DomainException('Invalid credentials'),
            static function () use ($handler, $envelope): void {
                $handler($envelope);
            },
        );
    }

    public function testInactiveUserThrowsDomainException(UnitTester $i): void
    {
        $user = new User(
            id: new Uuid('user-id-123'),
            email: new Email('test@example.com'),
            passwordHash: 'hashed_password',
            createdAt: new \DateTimeImmutable('2024-01-01 12:00:00'),
            status: UserStatus::Inactive,
        );

        $users = Stub::makeEmpty(Users::class, [
            'findByEmail' => static fn(): User => $user,
        ]);

        $passwordHasher = Stub::makeEmpty(PasswordHasher::class, [
            'verify' => static fn(): bool => true,
        ]);

        $tokenGenerator = Stub::makeEmpty(TokenGenerator::class);

        $handler = new Handler($users, $passwordHasher, $tokenGenerator);

        $command = new Command(email: 'test@example.com', password: 'secret123');
        $envelope = new Envelope($command, 'msg-4');

        $i->expectThrowable(
            new \DomainException('Email not confirmed'),
            static function () use ($handler, $envelope): void {
                $handler($envelope);
            },
        );
    }
}
